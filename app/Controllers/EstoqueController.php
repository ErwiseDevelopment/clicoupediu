<?php
namespace App\Controllers;
use App\Models\Estoque;

class EstoqueController {

    public function index() {
        $this->verificarLogin();
        $filialId = $_SESSION['empresa_id']; // Assumindo monoloja por enquanto

        $model = new Estoque();
        $produtos = $model->listar($filialId);

        require __DIR__ . '/../Views/admin/estoque/index.php';
    }

    public function salvarAjuste() {
        $this->verificarLogin();
        $filialId = $_SESSION['empresa_id'];
        $model = new Estoque();

        // Recebe array de ajustes: [produto_id => nova_qtd]
        if (isset($_POST['estoque']) && is_array($_POST['estoque'])) {
            foreach ($_POST['estoque'] as $prodId => $qtd) {
                // Se a quantidade estiver vazia, ignora
                if ($qtd !== '') {
                    $model->atualizarEstoque($filialId, $prodId, floatval($qtd));
                }
            }
        }

        header('Location: ' . BASE_URL . '/admin/estoque?msg=sucesso');
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}