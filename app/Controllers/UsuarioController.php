<?php
namespace App\Controllers;

use App\Models\Usuario;

class UsuarioController {

    public function index() {
        $this->verificarLogin();
        $model = new Usuario();
        $usuarios = $model->listar($_SESSION['empresa_id']);
        require __DIR__ . '/../Views/admin/usuarios/index.php';
    }

    public function salvar() {
        $this->verificarLogin();
        $model = new Usuario();
        
        $dados = [
            'id' => $_POST['id'] ?? '',
            'empresa_id' => $_SESSION['empresa_id'],
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'nivel' => $_POST['nivel'],
            'senha' => $_POST['senha'] ?? ''
        ];

        $model->salvar($dados);
        header('Location: ' . BASE_URL . '/admin/usuarios?msg=sucesso');
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? null;
        if ($id) {
            (new Usuario())->excluir($id, $_SESSION['empresa_id']);
        }
        header('Location: ' . BASE_URL . '/admin/usuarios');
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] !== 'dono') {
            header('Location: ' . BASE_URL . '/admin'); exit;
        }
    }
}