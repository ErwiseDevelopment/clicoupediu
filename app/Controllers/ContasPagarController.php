<?php
namespace App\Controllers;

use App\Models\ContasPagar;
use App\Models\CategoriaFinanceira;
use App\Core\Database;

class ContasPagarController {
    
    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        
        $inicio = $_GET['inicio'] ?? date('Y-m-01');
        $fim = $_GET['fim'] ?? date('Y-m-t');

        $model = new ContasPagar();
        $titulos = $model->listar($empresaId, ['inicio' => $inicio, 'fim' => $fim, 'busca' => $_GET['busca'] ?? '']);
        $resumo = $model->getTotais($empresaId);

        $catModel = new CategoriaFinanceira();
        $categorias = $catModel->listarPorTipo($empresaId, 'saida');

        require __DIR__ . '/../Views/admin/financeiro/contas_pagar.php';
    }

    public function buscarFornecedores() {
        $this->verificarLogin();
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $termo = $_GET['q'] ?? '';
        $empresaId = $_SESSION['empresa_id'];

        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, nome, telefone FROM fornecedores WHERE empresa_id = ? AND nome LIKE ? LIMIT 5");
        $stmt->execute([$empresaId, "%$termo%"]);
        
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
            $nome = $_POST['fornecedor_nome'];
            $fornecedorId = $_POST['fornecedor_id'] ?? null;

            // Lógica de Fornecedor (Cria se não existir)
            if (empty($fornecedorId) && !empty($nome)) {
                $check = $db->prepare("SELECT id FROM fornecedores WHERE empresa_id = ? AND nome = ?");
                $check->execute([$empresaId, $nome]);
                $existente = $check->fetch();

                if ($existente) {
                    $fornecedorId = $existente['id'];
                } else {
                    $db->prepare("INSERT INTO fornecedores (empresa_id, nome) VALUES (?, ?)")
                       ->execute([$empresaId, $nome]);
                    $fornecedorId = $db->lastInsertId();
                }
            }

            $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);

            if ($id) {
                // --- EDIÇÃO (UPDATE) ---
                $sql = "UPDATE contas_pagar SET 
                        fornecedor_id=?, fornecedor_nome=?, valor=?, status=?, 
                        forma_pagamento=?, categoria=?, observacoes=?, data_vencimento=?, updated_at=NOW()
                        WHERE id=? AND empresa_id=?";
                $db->prepare($sql)->execute([
                    $fornecedorId, $nome, $valor, $_POST['status'],
                    $_POST['forma_pagamento'], $_POST['categoria'], $_POST['observacoes'],
                    $_POST['data_vencimento'], $id, $empresaId
                ]);
            } else {
                // --- CRIAÇÃO (INSERT) ---
                $sql = "INSERT INTO contas_pagar (
                            empresa_id, fornecedor_id, fornecedor_nome, valor, status, 
                            forma_pagamento, categoria, observacoes, data_vencimento, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $db->prepare($sql)->execute([
                    $empresaId, $fornecedorId, $nome, $valor, $_POST['status'],
                    $_POST['forma_pagamento'], $_POST['categoria'], $_POST['observacoes'],
                    $_POST['data_vencimento'] ?? date('Y-m-d')
                ]);
            }

            echo json_encode(['ok' => true]);
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit; 
    }

    public function pagar() {
        $this->verificarLogin();
        $id = $_POST['id'];
        $db = Database::connect();
        $db->prepare("UPDATE contas_pagar SET status = 'pago' WHERE id = ? AND empresa_id = ?")
           ->execute([$id, $_SESSION['empresa_id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_POST['id'];
        $db = Database::connect();
        $db->prepare("DELETE FROM contas_pagar WHERE id = ? AND empresa_id = ?")->execute([$id, $_SESSION['empresa_id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}