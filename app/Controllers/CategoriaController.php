<?php
namespace App\Controllers;

use App\Core\Database;

class CategoriaController {

    // =========================================================================
    // 1. LISTAR (TELA PRINCIPAL)
    // =========================================================================
    public function index() {
        $this->verificarLogin();
        $db = Database::connect();

        // Busca todas as categorias da empresa logada
        $stmt = $db->prepare("SELECT * FROM categorias WHERE empresa_id = :id ORDER BY id DESC");
        $stmt->execute(['id' => $_SESSION['empresa_id']]);
        $categorias = $stmt->fetchAll();

        // Carrega a View (HTML)
        require __DIR__ . '/../Views/admin/categorias/index.php';
    }

    // =========================================================================
    // 2. SALVAR (CRIAR OU ATUALIZAR)
    // =========================================================================
    public function salvar() {
        $this->verificarLogin();
        $db = Database::connect();

        $nome = $_POST['nome'] ?? '';
        $id = $_POST['id'] ?? '';
        $ativa = isset($_POST['ativa']) ? 1 : 0;
        $empresaId = $_SESSION['empresa_id'];

        if (empty($nome)) {
            // Se estiver vazio, volta pra tela com erro (pode melhorar depois)
            header('Location: ' . BASE_URL . '/admin/categorias');
            exit;
        }

        if (!empty($id)) {
            // --- EDITAR ---
            // Atualiza apenas se o ID pertencer à empresa (segurança)
            $stmt = $db->prepare("UPDATE categorias SET nome = :nome, ativa = :ativa WHERE id = :id AND empresa_id = :emp_id");
            $stmt->execute([
                'nome' => $nome,
                'ativa' => $ativa,
                'id' => $id,
                'emp_id' => $empresaId
            ]);
        } else {
            // --- CRIAR NOVA ---
            $stmt = $db->prepare("INSERT INTO categorias (empresa_id, nome, ativa) VALUES (:emp_id, :nome, :ativa)");
            $stmt->execute([
                'emp_id' => $empresaId,
                'nome' => $nome,
                'ativa' => $ativa
            ]);
        }

        // Volta para a lista
        header('Location: ' . BASE_URL . '/admin/categorias');
        exit;
    }

    // =========================================================================
    // 3. EXCLUIR
    // =========================================================================
    public function excluir() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? null;

        if ($id) {
            $db = Database::connect();
            // Segurança: Só deleta se for da minha empresa
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id AND empresa_id = :emp_id");
            $stmt->execute([
                'id' => $id, 
                'emp_id' => $_SESSION['empresa_id']
            ]);
        }
        

        header('Location: ' . BASE_URL . '/admin/categorias');
        exit;
    }

    // =========================================================================
    // 4. ALTERNAR STATUS (LIGAR/DESLIGAR RÁPIDO)
    // =========================================================================
    public function alternarStatus() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? null;

        if ($id) {
            $db = Database::connect();
            // Query inteligente: inverte o status atual
            $stmt = $db->prepare("UPDATE categorias SET ativa = NOT ativa WHERE id = :id AND empresa_id = :emp_id");
            $stmt->execute([
                'id' => $id, 
                'emp_id' => $_SESSION['empresa_id']
            ]);
        }

        // Volta para a lista instantaneamente
        header('Location: ' . BASE_URL . '/admin/categorias');
        exit;
    }

    // =========================================================================
    // FUNÇÃO AUXILIAR DE SEGURANÇA
    // =========================================================================
    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            // Se não estiver logado, manda pro login
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}