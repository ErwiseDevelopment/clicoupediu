<?php
namespace App\Controllers;

use App\Models\Adicional;

class AdicionalController {

    // Tela Principal: Lista os Grupos
    public function index() {
        $this->verificarLogin();
        $model = new Adicional();
        $grupos = $model->listarGrupos($_SESSION['empresa_id']);
        
        // ATENÇÃO: O erro da tela branca geralmente é aqui. 
        // Ele tenta carregar a view e não acha.
        if (file_exists(__DIR__ . '/../Views/admin/adicionais/index.php')) {
            require __DIR__ . '/../Views/admin/adicionais/index.php';
        } else {
            die("ERRO: A View 'app/Views/admin/adicionais/index.php' não existe. Crie a pasta e o arquivo.");
        }
    }

    public function detalhes() {
        $this->verificarLogin();
        $grupoId = $_GET['id'] ?? null;
        $model = new Adicional();
        $grupo = $model->buscarGrupo($grupoId, $_SESSION['empresa_id']);
        
        if(!$grupo) { header('Location: ' . BASE_URL . '/admin/adicionais'); exit; }
        
        $itens = $model->listarItens($grupoId);
        require __DIR__ . '/../Views/admin/adicionais/detalhes.php';
    }

    public function salvarGrupo() {
        $this->verificarLogin();
        $model = new Adicional();
        $dados = [
            'id' => $_POST['id'] ?? '',
            'emp_id' => $_SESSION['empresa_id'],
            'nome' => $_POST['nome'],
            'desc' => $_POST['descricao'],
            'obrig' => isset($_POST['obrigatorio']) ? 1 : 0,
            'min' => $_POST['qtd_min'] ?? 0,
            'max' => $_POST['qtd_max'] ?? 1
        ];
        $model->salvarGrupo($dados);
        header('Location: ' . BASE_URL . '/admin/adicionais');
    }

    public function salvarItem() {
        $this->verificarLogin();
        $preco = str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0'); // Corrige formato moeda
        
        $dados = [
            'id' => $_POST['id'] ?? '',
            'grupo_id' => $_POST['grupo_id'],
            'nome' => $_POST['nome'],
            'preco' => $preco,
            'desc' => $_POST['descricao'],
            'ativo' => 1
        ];
        (new Adicional())->salvarItem($dados);
        header('Location: ' . BASE_URL . '/admin/adicionais/detalhes?id=' . $_POST['grupo_id']);
    }

    public function excluirGrupo() {
        $this->verificarLogin();
        (new Adicional())->excluirGrupo($_GET['id'], $_SESSION['empresa_id']);
        header('Location: ' . BASE_URL . '/admin/adicionais');
    }

    public function excluirItem() {
        $this->verificarLogin();
        (new Adicional())->excluirItem($_GET['id']);
        header("Location: {$_SERVER['HTTP_REFERER']}");
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}