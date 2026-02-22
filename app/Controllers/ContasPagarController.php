<?php
namespace App\Controllers;

use App\Models\ContasPagar;
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
        
        $categorias = [['descricao' => 'Aluguel'], ['descricao' => 'Fornecedor'], ['descricao' => 'Impostos'], ['descricao' => 'Salários']];

        require __DIR__ . '/../Views/admin/financeiro/contas_pagar.php';
    }

    public function buscarFornecedores() {
        $this->verificarLogin();
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $termo = $_GET['q'] ?? '';
        $empresaId = $_SESSION['empresa_id'];

        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, nome, telefone FROM fornecedores WHERE empresa_id = ? AND (nome LIKE ? OR telefone LIKE ?) LIMIT 5");
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
            
            $id = $_POST['id'] ?? null;
            $nome = $_POST['fornecedor_nome'];
            $fornecedorId = $_POST['fornecedor_id'] ?? null;

            // Cria fornecedor se não existir
            if (empty($fornecedorId) && !empty($nome)) {
                $sqlForn = "INSERT INTO fornecedores (empresa_id, nome) VALUES (?, ?)";
                $db->prepare($sqlForn)->execute([$empresaId, $nome]);
                $fornecedorId = $db->lastInsertId();
            }

            $valorRaw = $_POST['valor'] ?? '0,00';
            $valor = str_replace(['.', ','], ['', '.'], $valorRaw);
            $parcelas = isset($_POST['parcelas']) ? (int)$_POST['parcelas'] : 1;

            if ($id) {
                // UPDATE (Apenas o título atual, não afeta outras parcelas)
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
                // INSERT COM RECORRÊNCIA
                $dataBase = $_POST['data_vencimento'] ?? date('Y-m-d');
                $sql = "INSERT INTO contas_pagar (
                            empresa_id, fornecedor_id, fornecedor_nome, valor, status, 
                            forma_pagamento, categoria, observacoes, data_vencimento, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($sql);

                for ($i = 0; $i < $parcelas; $i++) {
                    $vencimentoAtual = date('Y-m-d', strtotime("+$i months", strtotime($dataBase)));
                    $obsParcela = $parcelas > 1 ? $_POST['observacoes'] . " (Parcela " . ($i + 1) . "/$parcelas)" : $_POST['observacoes'];
                    
                    $stmt->execute([
                        $empresaId, $fornecedorId, $nome, $valor, $_POST['status'],
                        $_POST['forma_pagamento'] ?? 'dinheiro', $_POST['categoria'] ?? 'Geral',
                        $obsParcela ?? '', $vencimentoAtual
                    ]);
                }
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

    public function cancelar() {
        $this->verificarLogin();
        $id = $_POST['id'];
        $db = Database::connect();
        $db->prepare("UPDATE contas_pagar SET status = 'cancelado' WHERE id = ? AND empresa_id = ?")
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