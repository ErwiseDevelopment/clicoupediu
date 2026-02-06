<?php
namespace App\Controllers;

use App\Models\CategoriaFinanceira;

class CategoriaFinanceiraController {

    public function index() {
        $this->verificarLogin();
        
        $model = new CategoriaFinanceira();
        $categorias = $model->listar($_SESSION['empresa_id']);
        
        require __DIR__ . '/../Views/admin/financeiro/categorias.php';
    }

    public function salvar() {
        $this->verificarLogin();

        $dados = [
            'id' => $_POST['id'] ?? null,
            'empresa_id' => $_SESSION['empresa_id'],
            'descricao' => $_POST['descricao'],
            'tipo' => $_POST['tipo'], // entrada ou saida
            'ativo' => $_POST['ativo'] // 1 ou 0
        ];

        $model = new CategoriaFinanceira();
        $model->salvar($dados);

        header('Location: ' . BASE_URL . '/admin/categorias-financeiro');
        exit;
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_GET['id'];
        
        $model = new CategoriaFinanceira();
        $model->excluir($id, $_SESSION['empresa_id']);

        header('Location: ' . BASE_URL . '/admin/categorias-financeiro');
        exit;
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}