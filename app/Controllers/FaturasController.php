<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\AsaasClient;

require_once dirname(__DIR__) . '/Core/AsaasClient.php';

class FaturasController {

    private $asaas;
    private $empresaId;

    public function __construct() {
        $this->verificarLogin();
        $this->empresaId = $_SESSION['empresa_id'];
        // Configura API (True = Sandbox/Teste | False = Produção)
        $this->asaas = new AsaasClient('SUA_API_KEY_AQUI', true); 
    }

    public function index() {
        $db = Database::connect();
        
        $stmtEmp = $db->prepare("SELECT nome_fantasia, cnpj, email_admin, licenca_validade, licenca_tipo, valor_mensalidade FROM empresas WHERE id = ?");
        $stmtEmp->execute([$this->empresaId]);
        $empresa = $stmtEmp->fetch();

        $stmt = $db->prepare("SELECT * FROM faturas WHERE empresa_id = ? ORDER BY data_criacao DESC");
        $stmt->execute([$this->empresaId]);
        $faturas = $stmt->fetchAll();

        $view = dirname(__DIR__) . '/Views/admin/faturas/index.php';
        if (file_exists($view)) {
            require $view;
        } else {
            die("View não encontrada.");
        }
    }

    // Valida cupom via AJAX
    public function validarCupom() {
        header('Content-Type: application/json');
        
        $codigo = $_POST['cupom'] ?? '';
        $db = Database::connect();
        
        $stmtEmp = $db->prepare("SELECT valor_mensalidade FROM empresas WHERE id = ?");
        $stmtEmp->execute([$this->empresaId]);
        $empresa = $stmtEmp->fetch();
        $valorOriginal = $empresa['valor_mensalidade'] ?? 230.00;

        $stmt = $db->prepare("SELECT * FROM cupons WHERE codigo = ? AND ativo = 1 AND validade >= CURDATE()");
        $stmt->execute([$codigo]);
        $cupom = $stmt->fetch();

        if ($cupom) {
            $novoValor = $valorOriginal;
            if ($cupom['tipo'] == 'valor_fixo') {
                $novoValor -= $cupom['desconto'];
            } elseif ($cupom['tipo'] == 'porcentagem') {
                $novoValor -= ($valorOriginal * ($cupom['desconto'] / 100));
            }
            $novoValor = max($novoValor, 0);

            echo json_encode([
                'sucesso' => true,
                'valor_original' => number_format($valorOriginal, 2, ',', '.'),
                'novo_valor' => number_format($novoValor, 2, ',', '.'),
                'mensagem' => 'Desconto aplicado!'
            ]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Cupom inválido.']);
        }
        exit;
    }

    // --- GERA O PAGAMENTO E RETORNA JSON (SEM REDIRECIONAR) ---
    public function gerar() {
        header('Content-Type: application/json'); // Resposta sempre em JSON

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['erro' => true, 'msg' => 'Método inválido']);
            exit;
        }

        $db = Database::connect();
        
        // 1. Busca Empresa
        $stmt = $db->prepare("SELECT * FROM empresas WHERE id = ?");
        $stmt->execute([$this->empresaId]);
        $empresa = $stmt->fetch();
        
        if (empty($empresa['cnpj'])) {
            echo json_encode(['erro' => true, 'msg' => 'CNPJ não cadastrado. Contate o suporte.']);
            exit;
        }

        // 2. Calcula Valor
        $valorFinal = $empresa['valor_mensalidade'] ?? 230.00;
        $cupomCodigo = $_POST['cupom'] ?? '';

        if (!empty($cupomCodigo)) {
            $stmtCupom = $db->prepare("SELECT * FROM cupons WHERE codigo = ? AND ativo = 1 AND validade >= CURDATE()");
            $stmtCupom->execute([$cupomCodigo]);
            $cupom = $stmtCupom->fetch();

            if ($cupom) {
                if ($cupom['tipo'] == 'valor_fixo') {
                    $valorFinal -= $cupom['desconto'];
                } elseif ($cupom['tipo'] == 'porcentagem') {
                    $valorFinal -= ($valorFinal * ($cupom['desconto'] / 100));
                }
            }
        }
        $valorFinal = max($valorFinal, 0);
        $metodoPagamento = $_POST['metodo'] ?? 'PIX';

        try {
            // 3. Garante Cliente
            $customerId = $empresa['asaas_customer_id'];
            if (empty($customerId)) {
                $clienteAsaas = $this->asaas->createCustomer($empresa['nome_fantasia'], $empresa['cnpj'], $empresa['email_admin']);
                if (isset($clienteAsaas['body']['id'])) {
                    $customerId = $clienteAsaas['body']['id'];
                    $db->prepare("UPDATE empresas SET asaas_customer_id = ? WHERE id = ?")->execute([$customerId, $this->empresaId]);
                } else {
                    throw new \Exception("Erro ao criar cliente Asaas.");
                }
            }

            // 4. Cria Cobrança
            $dadosCobranca = [
                'customer' => $customerId,
                'billingType' => $metodoPagamento,
                'value' => $valorFinal,
                'dueDate' => date('Y-m-d', strtotime('+3 days')),
                'description' => 'Renovação ClicouPediu',
                'externalReference' => 'REN-' . $this->empresaId . '-' . time()
            ];

            $cobranca = $this->asaas->request('/payments', 'POST', $dadosCobranca);

            if ($cobranca['code'] == 200) {
                $pagamento = $cobranca['body'];
                $paymentId = $pagamento['id'];

                // 5. Salva no Banco
                $sql = "INSERT INTO faturas (empresa_id, asaas_payment_id, valor, status, forma_pagamento, url_pagamento) VALUES (?, ?, ?, ?, ?, ?)";
                $db->prepare($sql)->execute([
                    $this->empresaId, $paymentId, $pagamento['value'], 'PENDENTE', $pagamento['billingType'], $pagamento['invoiceUrl']
                ]);

                // 6. BUSCA DADOS VISUAIS (PIX OU BOLETO) PARA O MODAL
                $retorno = [
                    'sucesso' => true,
                    'tipo' => $metodoPagamento,
                    'url_fatura' => $pagamento['invoiceUrl']
                ];

                if ($metodoPagamento == 'PIX') {
                    $pixData = $this->asaas->getPixQrCode($paymentId);
                    if ($pixData['code'] == 200) {
                        $retorno['pix_imagem'] = $pixData['body']['encodedImage'];
                        $retorno['pix_copia_cola'] = $pixData['body']['payload'];
                    }
                } elseif ($metodoPagamento == 'BOLETO') {
                    $boletoData = $this->asaas->getBoletoCode($paymentId);
                    if ($boletoData['code'] == 200) {
                        $retorno['boleto_linha'] = $boletoData['body']['identificationField'];
                        $retorno['boleto_codigo'] = $boletoData['body']['barCode'];
                    }
                }

                echo json_encode($retorno);
                exit;

            } else {
                echo json_encode(['erro' => true, 'msg' => 'Erro Asaas: ' . json_encode($cobranca['body'])]);
                exit;
            }

        } catch (\Exception $e) {
            echo json_encode(['erro' => true, 'msg' => $e->getMessage()]);
            exit;
        }
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}