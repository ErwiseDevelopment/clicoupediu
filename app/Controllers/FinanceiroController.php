<?php
namespace App\Controllers;

use App\Models\ContasReceber;
use App\Models\CategoriaFinanceira; // Assumindo que você tenha ou use string pura
use App\Core\Database;

class FinanceiroController {
    
    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        
        $inicio = $_GET['inicio'] ?? date('Y-m-d');
        $fim = $_GET['fim'] ?? date('Y-m-d');

        $model = new ContasReceber();
        $titulos = $model->listar($empresaId, ['inicio' => $inicio, 'fim' => $fim, 'busca' => $_GET['busca'] ?? '']);
        $resumo = $model->getTotais($empresaId);
        
        // Se tiver categorias de entrada no banco, carregue aqui. Se for fixo na view, tudo bem.
        // $catModel = new CategoriaFinanceira();
        // $categorias = $catModel->listarPorTipo($empresaId, 'entrada');
        $categorias = [['descricao' => 'Serviço'], ['descricao' => 'Produto']]; // Exemplo

        require __DIR__ . '/../Views/admin/financeiro/contas_receber.php';
    }

    public function buscarClientes() {
        $this->verificarLogin();
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $termo = $_GET['q'] ?? '';
        $empresaId = $_SESSION['empresa_id'];

        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, nome, telefone FROM clientes WHERE empresa_id = ? AND (nome LIKE ? OR telefone LIKE ?) LIMIT 5");
        $stmt->execute([$empresaId, "%$termo%", "%$termo%"]);
        
        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        exit;
    }

    public function salvar() {
        $this->verificarLogin();
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $db = Database::connect();
            $empresaId = $_SESSION['empresa_id'];
            
            $id = $_POST['id'] ?? null; // ID para Edição
            $nome = $_POST['cliente_nome'];
            $telefone = $_POST['telefone'];
            $clienteId = $_POST['cliente_id'] ?? null;

            // Cria cliente se não existir
            if (empty($clienteId) && !empty($nome)) {
                $check = $db->prepare("SELECT id FROM clientes WHERE empresa_id = ? AND telefone = ? LIMIT 1");
                $check->execute([$empresaId, $telefone]);
                $existente = $check->fetch();

                if ($existente) {
                    $clienteId = $existente['id'];
                } else {
                    $sqlCli = "INSERT INTO clientes (empresa_id, nome, telefone) VALUES (?, ?, ?)";
                    $db->prepare($sqlCli)->execute([$empresaId, $nome, $telefone]);
                    $clienteId = $db->lastInsertId();
                }
            }

            $valorRaw = $_POST['valor'] ?? '0,00';
            $valor = str_replace(['.', ','], ['', '.'], $valorRaw);

            if ($id) {
                // --- UPDATE ---
                $sql = "UPDATE contas_receber SET 
                        cliente_id=?, cliente_nome=?, valor=?, status=?, 
                        forma_pagamento=?, categoria=?, observacoes=?, data_vencimento=?, updated_at=NOW()
                        WHERE id=? AND empresa_id=?";
                $db->prepare($sql)->execute([
                    $clienteId, $nome, $valor, $_POST['status'],
                    $_POST['forma_pagamento'], $_POST['categoria'], $_POST['observacoes'],
                    $_POST['data_vencimento'], $id, $empresaId
                ]);
            } else {
                // --- INSERT ---
                $sql = "INSERT INTO contas_receber (
                            empresa_id, cliente_id, cliente_nome, valor, status, 
                            forma_pagamento, categoria, observacoes, data_vencimento, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $db->prepare($sql)->execute([
                    $empresaId, $clienteId, $nome, $valor, $_POST['status'],
                    $_POST['forma_pagamento'] ?? 'dinheiro', $_POST['categoria'] ?? 'Venda',
                    $_POST['observacoes'] ?? '', $_POST['data_vencimento'] ?? date('Y-m-d')
                ]);
            }

            echo json_encode(['ok' => true]);
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit; 
    }

    public function baixarPagamento() {
        $this->verificarLogin();
        $id = $_POST['id'];
        $db = Database::connect();
        $db->prepare("UPDATE contas_receber SET status = 'pago', updated_at = NOW() WHERE id = ? AND empresa_id = ?")
           ->execute([$id, $_SESSION['empresa_id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_POST['id'];
        $db = Database::connect();
        $db->prepare("DELETE FROM contas_receber WHERE id = ? AND empresa_id = ?")->execute([$id, $_SESSION['empresa_id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}