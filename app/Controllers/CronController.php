<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\AsaasClient;

require_once dirname(__DIR__) . '/Core/AsaasClient.php';

class CronController {

    private $asaas;
    private $db;

    public function __construct() {
        // Conecta ao Banco e API
        $this->db = Database::connect();
        $this->asaas = new AsaasClient('SUA_API_KEY_AQUI', true); // True = Sandbox
    }

    // =========================================================================
    // ROTA PRINCIPAL DO CRON (Chame esta URL no seu servidor: /cron/rodar)
    // =========================================================================
    public function rodar() {
        // Token de segurança simples para ninguém rodar seu cron pela URL
        if (($_GET['token'] ?? '') !== 'SENHA_DO_CRON_123') {
            die('Acesso negado.');
        }

        echo "<pre><h1>🚀 INICIANDO ROTINA FINANCEIRA</h1>";
        
        // 1. Verifica quem pagou (Baixa)
        $this->sincronizarStatus();

        echo "<hr>";

        // 2. Gera novas cobranças para quem vai vencer (Geração)
        $this->gerarCobrancas();

        echo "<br><strong>🏁 FIM DO PROCESSO.</strong></pre>";
    }

    // =========================================================================
    // PARTE 1: GERAÇÃO DE COBRANÇAS (Cria Boleto/Pix no Asaas)
    // =========================================================================
    private function gerarCobrancas() {
        echo "<h3>1. GERANDO NOVAS COBRANÇAS</h3>";

        // Busca empresas ATIVAS ou TESTE cuja validade vence em 5 dias ou menos (e que não estejam bloqueadas manualmente)
        // Regra: Vence hoje, ou daqui a 5 dias, ou já venceu mas ainda não foi bloqueado.
        $sql = "SELECT * FROM empresas 
                WHERE licenca_tipo != 'BLOQUEADO' 
                AND licenca_tipo != 'VIP'
                AND (licenca_validade IS NULL OR licenca_validade <= DATE_ADD(CURDATE(), INTERVAL 5 DAY))";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        foreach ($empresas as $empresa) {
            echo "Analisando: <strong>{$empresa['nome_fantasia']}</strong> (Validade: {$empresa['licenca_validade']})... ";

            // VERIFICAÇÃO DE DUPLICIDADE (CRUCIAL!)
            // Verifica se já existe uma fatura 'PENDENTE' gerada nos últimos 20 dias para não gerar duas vezes no mesmo mês
            $stmtFatura = $this->db->prepare("SELECT id FROM faturas WHERE empresa_id = ? AND status = 'PENDENTE' AND data_criacao >= DATE_SUB(NOW(), INTERVAL 20 DAY)");
            $stmtFatura->execute([$empresa['id']]);
            
            if ($stmtFatura->fetch()) {
                echo "<span style='color:orange'>Já possui fatura pendente recente. Ignorado.</span><br>";
                continue;
            }

            // SE CHEGOU AQUI, PRECISA GERAR PAGAMENTO
            $this->criarPagamentoAsaas($empresa);
        }
    }

    private function criarPagamentoAsaas($empresa) {
        try {
            // 1. Garante Customer ID
            $customerId = $empresa['asaas_customer_id'];
            if (empty($customerId)) {
                $cliente = $this->asaas->createCustomer($empresa['nome_fantasia'], $empresa['cnpj'], $empresa['email_admin']);
                if (isset($cliente['body']['id'])) {
                    $customerId = $cliente['body']['id'];
                    $this->db->prepare("UPDATE empresas SET asaas_customer_id = ? WHERE id = ?")->execute([$customerId, $empresa['id']]);
                } else {
                    echo "<span style='color:red'>Erro ao criar cliente.</span><br>";
                    return;
                }
            }

            // 2. Define Valor e Vencimento
            $valor = $empresa['valor_mensalidade'] ?? 230.00;
            
            // Se a licença já venceu, o vencimento é Hoje + 1 dia. 
            // Se não venceu, o vencimento é a data da validade atual.
            $dataVencimento = ($empresa['licenca_validade'] && $empresa['licenca_validade'] > date('Y-m-d')) 
                              ? $empresa['licenca_validade'] 
                              : date('Y-m-d', strtotime('+1 day'));

            // 3. Payload do Pagamento (Sem ser assinatura)
            $dados = [
                'customer' => $customerId,
                'billingType' => 'PIX', // Padrão PIX (ou BOLETO)
                'value' => $valor,
                'dueDate' => $dataVencimento,
                'description' => 'Mensalidade Sistema - Renovação Automática',
                'externalReference' => 'AUTO-' . $empresa['id'] . '-' . date('mY') // Ex: AUTO-5-022026
            ];

            $res = $this->asaas->request('/payments', 'POST', $dados);

            if ($res['code'] == 200) {
                $pg = $res['body'];
                
                // Salva no Banco Local
                $sql = "INSERT INTO faturas (empresa_id, asaas_payment_id, valor, status, forma_pagamento, url_pagamento) VALUES (?, ?, ?, ?, ?, ?)";
                $this->db->prepare($sql)->execute([
                    $empresa['id'], $pg['id'], $pg['value'], 'PENDENTE', $pg['billingType'], $pg['invoiceUrl']
                ]);

                echo "<span style='color:green'>Fatura Gerada! (R$ $valor)</span><br>";
            } else {
                echo "<span style='color:red'>Erro Asaas: " . ($res['body']['errors'][0]['description'] ?? 'Desc') . "</span><br>";
            }

        } catch (\Exception $e) {
            echo "Erro Crítico: " . $e->getMessage() . "<br>";
        }
    }

    // =========================================================================
    // PARTE 2: SINCRONIZAÇÃO (Verifica quem pagou e renova)
    // =========================================================================
    private function sincronizarStatus() {
        echo "<h3>2. SINCRONIZANDO PAGAMENTOS</h3>";

        // Busca todas as faturas PENDENTES no nosso banco
        $stmt = $this->db->prepare("SELECT * FROM faturas WHERE status = 'PENDENTE'");
        $stmt->execute();
        $faturas = $stmt->fetchAll();

        foreach ($faturas as $fatura) {
            // Consulta o status atual no Asaas
            $res = $this->asaas->request('/payments/' . $fatura['asaas_payment_id'], 'GET');

            if ($res['code'] == 200) {
                $statusReal = $res['body']['status']; // PENDENTE, RECEIVED, CONFIRMED, OVERDUE

                // Se mudou o status, atualiza no banco local
                if ($statusReal != $fatura['status']) {
                    $this->db->prepare("UPDATE faturas SET status = ? WHERE id = ?")->execute([$statusReal, $fatura['id']]);
                    echo "Fatura #{$fatura['id']} atualizada para <strong>$statusReal</strong>.<br>";

                    // SE PAGOU -> RENOVA A LICENÇA
                    if ($statusReal == 'RECEIVED' || $statusReal == 'CONFIRMED') {
                        $this->renovarLicenca($fatura['empresa_id']);
                    }
                    
                    // SE VENCEU E NÃO PAGOU -> BLOQUEIA (Opcional, pode dar uma carência)
                    if ($statusReal == 'OVERDUE') {
                         // Lógica de bloqueio aqui se quiser ser rígido
                         // $this->db->prepare("UPDATE empresas SET licenca_tipo = 'BLOQUEADO' WHERE id = ?")->execute([$fatura['empresa_id']]);
                    }
                }
            }
        }
    }

    private function renovarLicenca($empresaId) {
        // Busca validade atual
        $stmt = $this->db->prepare("SELECT licenca_validade FROM empresas WHERE id = ?");
        $stmt->execute([$empresaId]);
        $empresa = $stmt->fetch();

        $hoje = date('Y-m-d');
        $atual = $empresa['licenca_validade'];

        // Se a validade atual for maior que hoje (está em dia), soma +30 dias nela
        // Se já venceu, soma +30 dias a partir de HOJE
        if ($atual && $atual > $hoje) {
            $novaValidade = date('Y-m-d', strtotime($atual . ' +30 days'));
        } else {
            $novaValidade = date('Y-m-d', strtotime($hoje . ' +30 days'));
        }

        $this->db->prepare("UPDATE empresas SET licenca_validade = ?, licenca_tipo = 'ATIVO' WHERE id = ?")
                 ->execute([$novaValidade, $empresaId]);

        echo "<span style='color:blue; font-weight:bold'> LICENÇA RENOVADA ATÉ " . date('d/m/Y', strtotime($novaValidade)) . "</span><br>";
    }
}