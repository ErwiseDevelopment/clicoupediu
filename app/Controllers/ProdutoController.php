<?php
namespace App\Controllers;

use App\Models\Produto;
use App\Models\Categoria;

class ProdutoController {

    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];

        $modelProd = new Produto();
        $produtos = $modelProd->listar($empresaId); 
        $gruposAdicionais = $modelProd->listarTodosGrupos($empresaId);

        $modelCat = new Categoria();
        $categorias = $modelCat->listar($empresaId);

        require __DIR__ . '/../Views/admin/produtos/index.php';
    }

    public function salvar() {
        $this->verificarLogin();
        
        $id = $_POST['id'] ?? '';
        $empresaId = $_SESSION['empresa_id'];
        $model = new Produto();

        $tipo = $_POST['tipo'] ?? null;
        
        if (empty($tipo) && !empty($id)) {
            $prodAtual = $model->buscarPorId($id, $empresaId);
            $tipo = $prodAtual['tipo'] ?? 'simples';
        }
        if (empty($tipo)) {
            $tipo = 'simples';
        }

        $imagemUrl = $_POST['imagem_atual'] ?? ''; 
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $novoNome = uniqid() . "." . $extensao; 
            $caminhoRelativo = 'assets/uploads/' . $novoNome;
            $destino = __DIR__ . '/../../public/' . $caminhoRelativo;
            if (!is_dir(dirname($destino))) { mkdir(dirname($destino), 0777, true); }
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                $imagemUrl = BASE_URL . '/' . $caminhoRelativo;
            }
        }
        
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

        $complementos = $_POST['grupos_complementos'] ?? []; 

        $produtoIdRetornado = $model->salvar([
            'id' => $id,
            'empresa_id' => $empresaId,
            'categoria_id' => $_POST['categoria_id'],
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'],
            'preco' => $_POST['preco'] ?? '0',
            'preco_promocional' => $_POST['preco_promocional'] ?? '0', 
            'imagem_url' => $imagemUrl,
            'ativo' => isset($_POST['ativo']) ? $_POST['ativo'] : 1,
            'visivel_online' => isset($_POST['visivel_online']) ? 1 : 0,
            'controle_estoque' => isset($_POST['controle_estoque']) ? 1 : 0,
            'precisa_preparo' => isset($_POST['precisa_preparo']) ? 1 : 0, // <-- AQUI A MÁGICA
            'tipo' => $tipo, 
            'disponibilidade' => $disponibilidade,
            'complementos' => $complementos
        ]);

        $prodIdFinal = empty($id) ? $produtoIdRetornado : $id;

        if (isset($_POST['estoque']) && $_POST['estoque'] !== '') {
            $model->atualizarEstoque($prodIdFinal, $_POST['estoque'], $_SESSION['empresa_id'], $_SESSION['usuario_id']);
        }

        header('Location: ' . BASE_URL . '/admin/produtos?msg=salvo');
        exit;
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? null;
        if ($id) { (new Produto())->excluir($id, $_SESSION['empresa_id']); }
        header('Location: ' . BASE_URL . '/admin/produtos');
        exit;
    }
    
    public function alternarStatus() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? null;
        if($id) {
            $db = \App\Core\Database::connect();
            $stmt = $db->prepare("UPDATE produtos SET ativo = NOT ativo WHERE id = :id AND empresa_id = :eid");
            $stmt->execute(['id' => $id, 'eid' => $_SESSION['empresa_id']]);
        }
        header('Location: ' . BASE_URL . '/admin/produtos');
        exit;
    }
    
    public function adicionais() {
        header('Location: ' . BASE_URL . '/admin/adicionais');
        exit;
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}