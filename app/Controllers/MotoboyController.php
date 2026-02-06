<?php
namespace App\Controllers;
use App\Models\Motoboy;

class MotoboyController {
    public function index() {
        $this->verificarLogin();
        $model = new Motoboy();
        $motoboys = $model->listar($_SESSION['empresa_id']);
        require __DIR__ . '/../Views/admin/motoboys/index.php';
    }

    public function salvar() {
        $this->verificarLogin();
        $model = new Motoboy();
        $dados = [
            'empresa_id' => $_SESSION['empresa_id'],
            'nome' => $_POST['nome'],
            'whatsapp' => $_POST['whatsapp'],
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];

        if (!empty($_POST['id'])) {
            $model->atualizar($_POST['id'], $dados);
        } else {
            $model->criar($dados);
        }
        header('Location: ' . BASE_URL . '/admin/motoboys');
    }

    public function excluir() {
        $this->verificarLogin();
        (new Motoboy())->excluir($_GET['id'], $_SESSION['empresa_id']);
        header('Location: ' . BASE_URL . '/admin/motoboys');
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}