<?php
namespace App\Controllers;

use App\Models\Promocao;
use App\Models\Produto;
use App\Models\Categoria;
use App\Core\Database;

class PromocaoController {

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }

    public function index() {
        $this->verificarLogin();
        $model = new Promocao();
        $combos = $model->listarCombos($_SESSION['empresa_id']);
        require __DIR__ . '/../Views/admin/promocoes/index.php';
    }

    public function criar() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        
        $catModel = new Categoria();
        $categorias = $catModel->listar($empresaId);
        
        $prodModel = new Produto();
        $produtosSimples = $prodModel->listarSimples($empresaId);

        require __DIR__ . '/../Views/admin/promocoes/form.php';
    }

    public function editar() {
        $this->verificarLogin();
        $id = $_GET['id'];
        $empresaId = $_SESSION['empresa_id'];
        
        $model = new Promocao();
        $combo = $model->buscarPorId($id);
        
        $catModel = new Categoria();
        $categorias = $catModel->listar($empresaId);
        
        $prodModel = new Produto();
        $produtosSimples = $prodModel->listarSimples($empresaId);

        require __DIR__ . '/../Views/admin/promocoes/form.php';
    }

    public function salvar() {
        $this->verificarLogin();
        
        // 1. Tratamento da Imagem
        $imagemUrl = $_POST['imagem_atual'] ?? '';
        
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeImg = uniqid() . "." . $ext;
            $diretorioDestino = __DIR__ . '/../../public/uploads/';
            
            if (!is_dir($diretorioDestino)) mkdir($diretorioDestino, 0777, true);

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorioDestino . $nomeImg)) {
                $imagemUrl = 'uploads/' . $nomeImg;
            }
        }

        // 2. Processamento da Disponibilidade
        $disponibilidade = [];
        if(isset($_POST['dia_semana']) && is_array($_POST['dia_semana'])) {
            foreach($_POST['dia_semana'] as $k => $dia) {
                if(!empty($_POST['horario_inicio'][$k]) && !empty($_POST['horario_fim'][$k])){
                    $disponibilidade[] = [
                        'dia' => $dia,
                        'inicio' => $_POST['horario_inicio'][$k],
                        'fim' => $_POST['horario_fim'][$k]
                    ];
                }
            }
        }

        $dados = [
            'id'           => $_POST['id'] ?? null,
            'empresa_id'   => $_SESSION['empresa_id'],
            'nome'         => $_POST['nome'],
            'descricao'    => $_POST['descricao'],
            'preco'        => $_POST['preco'],
            'preco_promocional' => $_POST['preco_promocional'] ?? '0', // NOVO
            'categoria_id' => $_POST['categoria_id'],
            'imagem'       => $imagemUrl,
            'visivel_online' => isset($_POST['visivel_online']) ? 1 : 0, // NOVO
            'controle_estoque' => isset($_POST['controle_estoque']) ? 1 : 0, // NOVO
            'disponibilidade' => $disponibilidade
        ];

        $itens = json_decode($_POST['itens_json'], true);
        $model = new \App\Models\Promocao();
        $model->salvar($dados, $itens);

        header('Location: ' . BASE_URL . '/admin/promocoes?msg=salvo');
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_POST['id'];
        (new Promocao())->excluir($id);
        echo json_encode(['ok' => true]);
    }
}   