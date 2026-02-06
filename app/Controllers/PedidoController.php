<?php
namespace App\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Categoria;
use App\Core\Database;
use App\Models\Promocao;
use App\Models\Cardapio;

class PedidoController {

    // 1. TELA PRINCIPAL (Monitor KDS)
    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $dataFiltro = $_GET['data'] ?? date('Y-m-d');

        $db = Database::connect();
        
        // Busca dados básicos da empresa para o Header
        $stmtEmp = $db->prepare("SELECT chave_pix, nome_fantasia, endereco_completo FROM empresas WHERE id = ? LIMIT 1");
        $stmtEmp->execute([$empresaId]);
        $empresaDados = $stmtEmp->fetch(\PDO::FETCH_ASSOC);

        $chavePixLoja = $empresaDados['chave_pix'] ?? '';
        
        // Usa o método privado auxiliar (mantendo sua lógica original)
        $nomeLojaPix  = $this->limparStringPix($empresaDados['nome_fantasia'] ?? 'LOJA', 25);

        $cidadeLojaPix = 'CIDADE'; 
        if (!empty($empresaDados['endereco_completo'])) {
            $partes = explode('-', $empresaDados['endereco_completo']);
            $ultimaParte = end($partes); 
            if (strlen(trim($ultimaParte)) <= 2 && count($partes) > 1) {
                $cidadeProvavel = prev($partes);
            } else {
                $cidadeProvavel = $ultimaParte;
            }
            $cidadeLojaPix = $this->limparStringPix($cidadeProvavel, 15);
        }

        // Listagens de Pedidos
        $model = new Pedido();
        $analise = $model->listarPorStatus($empresaId, 'analise', $dataFiltro);
        $preparo = $model->listarPorStatus($empresaId, 'preparo', $dataFiltro);
        $entrega = $model->listarPorStatus($empresaId, 'entrega', $dataFiltro);
        $finalizados = $model->listarPorStatus($empresaId, 'finalizado', $dataFiltro);

        // Busca Produtos para o PDV (Modal)
        $prodModel = new Produto();
        $promoModel = new Promocao();
        
        $todosProdutos = $prodModel->listar($empresaId);
        $todosCombos = $promoModel->listarCombos($empresaId);
        
        // Marca quem tem adicionais para o JS saber quando chamar a API
        $stmtTemAdd = $db->prepare("SELECT DISTINCT produto_id FROM produto_complementos WHERE ativo = 1");
        $stmtTemAdd->execute();
        $idsComAdicionais = $stmtTemAdd->fetchAll(\PDO::FETCH_COLUMN);

        $produtosPDV = [];

        foreach($todosProdutos as $p) {
            $p['tipo_item'] = 'produto';
            $p['tem_adicionais'] = in_array($p['id'], $idsComAdicionais) ? 1 : 0;
            $produtosPDV[] = $p;
        }

        foreach($todosCombos as $c) {
            $c['tipo_item'] = 'combo';
            $c['tem_adicionais'] = 0; 
            if(!isset($c['categoria_id'])) $c['categoria_id'] = 0;
            $produtosPDV[] = $c;
        }

        $catModel = new Categoria();
        $categorias = $catModel->listar($empresaId);

        $stmtM = $db->prepare("SELECT id, nome FROM motoboys WHERE empresa_id = ? AND ativo = 1");
        $stmtM->execute([$empresaId]);
        $motoboys = $stmtM->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/pedidos/index.php';
    }

    // 2. BUSCA ADICIONAIS (Substitui o getComplementosProduto)
    public function buscaradicionais() {
        $this->verificarLogin(); 
        while (ob_get_level()) { ob_end_clean(); } // Limpa buffer
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = $_GET['id'] ?? 0;
            $empresaId = $_SESSION['empresa_id'];

            if (!$id) throw new \Exception("ID inválido");

            $db = Database::connect();
            
            // Busca Grupos vinculados ao produto E à empresa
            $sql = "SELECT g.* FROM grupos_adicionais g
                    INNER JOIN produto_complementos pc ON g.id = pc.grupo_id
                    WHERE pc.produto_id = :prod_id 
                      AND pc.ativo = 1
                      AND g.empresa_id = :emp_id
                    ORDER BY g.obrigatorio DESC, g.id ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['prod_id' => $id, 'emp_id' => $empresaId]);
            $grupos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Busca os itens de cada grupo
            if (!empty($grupos)) {
                foreach ($grupos as &$g) {
                    $sqlOpcoes = "SELECT * FROM opcionais WHERE grupo_id = ? ORDER BY preco ASC, nome ASC";
                    $stmtOp = $db->prepare($sqlOpcoes);
                    $stmtOp->execute([$g['id']]);
                    $g['itens'] = $stmtOp->fetchAll(\PDO::FETCH_ASSOC);
                }
            } else {
                $grupos = [];
            }
            
            echo json_encode(['ok' => true, 'grupos' => $grupos]);

        } catch (\Throwable $e) { 
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); 
        }
        exit;
    }

    // 3. SALVAR PEDIDO
    public function salvar() {
        $this->verificarLogin(); 
        if(ob_get_contents()) ob_clean();
        header('Content-Type: application/json');

        try {
            $itens = json_decode($_POST['itens_json'] ?? '[]', true);
            if(empty($itens)) throw new \Exception('Carrinho vazio.');

            $dados = [
                'empresa_id'       => $_SESSION['empresa_id'],
                'pedido_id'        => $_POST['pedido_id'] ?? '',
                'cliente_nome'     => $_POST['nome'] ?? 'Consumidor',
                'cliente_telefone' => preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''),
                'tipo_entrega'     => $_POST['tipo_entrega'],
                'endereco'         => $_POST['logradouro'] ?? '',
                'numero'           => $_POST['numero'] ?? '',
                'bairro'           => $_POST['bairro'] ?? '',
                'complemento'      => $_POST['complemento'] ?? '',
                'taxa_entrega'     => $this->moedaParaFloat($_POST['taxa_entrega'] ?? '0'),
                'desconto'         => $this->moedaParaFloat($_POST['desconto'] ?? '0'),
                'valor_total'      => $this->moedaParaFloat($_POST['valor_total'] ?? '0'),
                'forma_pagamento'  => $_POST['forma_pagamento'],
                'troco_para'       => $this->moedaParaFloat($_POST['troco_para'] ?? '0'),
                'lat_entrega'      => $_POST['lat_entrega'] ?? null,
                'lng_entrega'      => $_POST['lng_entrega'] ?? null
            ];

            // Recalcula valor dos produtos
            $valorProd = 0;
            foreach($itens as $item) {
                $valorProd += ($item['qtd'] * $item['preco']);
            }
            $dados['valor_produtos'] = $valorProd;

            if ($dados['tipo_entrega'] === 'entrega' && empty($dados['endereco'])) {
                throw new \Exception('Endereço é obrigatório para entrega.');
            }

            $model = new Pedido();
            if ($dados['pedido_id']) {
                $model->atualizar($dados['pedido_id'], $dados, $itens);
                echo json_encode(['ok' => true, 'msg' => 'Pedido atualizado!']);
            } else {
                $model->criar($dados, $itens);
                echo json_encode(['ok' => true, 'msg' => 'Pedido criado!']);
            }

        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    // 4. IMPRIMIR CUPOM
   public function imprimir() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $pedidoId = $_GET['id'] ?? 0;
        $db = Database::connect();

        // 1. Busca Pedido
        $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$pedidoId, $empresaId]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$pedido) die("Pedido não encontrado.");

        // 2. Busca Itens
        $stmtI = $db->prepare("SELECT pi.*, p.nome as p_nome FROM pedido_itens pi LEFT JOIN produtos p ON pi.produto_id = p.id WHERE pi.pedido_id = ?");
        $stmtI->execute([$pedidoId]);
        $itens = $stmtI->fetchAll(\PDO::FETCH_ASSOC);

        // >>> NOVO: Busca Complementos de cada item <<<
        foreach ($itens as &$item) {
            // Verifica se a tabela existe antes de tentar buscar (evita erro em bancos antigos)
            // Se você já rodou o SQL novo, pode usar o SELECT direto.
            try {
                $stmtAdd = $db->prepare("SELECT nome, preco FROM pedido_item_complementos WHERE pedido_item_id = ?");
                $stmtAdd->execute([$item['id']]);
                $item['complementos'] = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $item['complementos'] = [];
            }
        }

        // 3. Busca Dados da Loja
        $stmtLoja = $db->prepare("
            SELECT e.nome_fantasia, e.cnpj, e.chave_pix,
                   COALESCE(f.endereco_completo, cf.endereco_completo) as endereco_completo,
                   COALESCE(f.telefone_whatsapp, '') as telefone_whatsapp,
                   cf.logo_url
            FROM empresas e
            LEFT JOIN filiais f ON f.empresa_id = e.id
            LEFT JOIN configuracoes_filial cf ON cf.filial_id = f.id
            WHERE e.id = ? LIMIT 1
        ");
        $stmtLoja->execute([$empresaId]);
        $loja = $stmtLoja->fetch(\PDO::FETCH_ASSOC);

        if (!$loja) $loja = ['nome_fantasia'=>'Loja', 'endereco_completo'=>''];

        require __DIR__ . '/../Views/admin/pedidos/cupom.php';
    }

    // 5. OBTER DADOS PARA EDIÇÃO
    public function getDadosPedido() {
        $this->verificarLogin(); ob_clean();
        $id = $_GET['id'];
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $_SESSION['empresa_id']]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $stmtI = $db->prepare("SELECT pi.*, p.nome as p_nome, p.tem_adicionais 
                               FROM pedido_itens pi 
                               LEFT JOIN produtos p ON pi.produto_id = p.id 
                               WHERE pi.pedido_id = ?");
        $stmtI->execute([$id]);
        
        $itensCarrinho = [];
        foreach($stmtI->fetchAll(\PDO::FETCH_ASSOC) as $i) {
            // Tenta recuperar adicionais salvos (estrutura básica)
            $stmtAdd = $db->prepare("SELECT id, nome, preco FROM pedido_item_complementos WHERE pedido_item_id = ?");
            $stmtAdd->execute([$i['id']]); 
            $adicionais = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC);

            $itensCarrinho[] = [
                'id' => $i['produto_id'],
                'name' => $i['p_nome'],
                'preco' => floatval($i['preco_unitario']),
                'qtd' => intval($i['quantidade']),
                'observacao' => $i['observacao_item'],
                'estoque' => 999,
                'tem_adicionais' => $i['tem_adicionais'],
                'adicionais' => $adicionais
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['pedido' => $pedido, 'itensCarrinho' => $itensCarrinho]); 
        exit;
    }

    // 6. CÁLCULO DE FRETE
    public function calcularFreteAjax() {
        if(ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        try {
            $this->verificarLogin();
            $rua = $_POST['rua'] ?? ''; $numero = $_POST['numero'] ?? ''; $bairro = $_POST['bairro'] ?? '';
            $empresaId = $_SESSION['empresa_id'];

            if (empty($rua) || empty($numero)) throw new \Exception('Endereço incompleto.');

            $db = Database::connect();
            $stmt = $db->prepare("SELECT f.id as filial_id, cf.lat, cf.lng FROM filiais f JOIN configuracoes_filial cf ON f.id = cf.filial_id WHERE f.empresa_id = ? LIMIT 1");
            $stmt->execute([$empresaId]);
            $loja = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$loja || empty($loja['lat'])) throw new \Exception('Loja sem coordenadas.');

            $geo = $this->geocodificar("$rua, $numero - $bairro"); 
            if (!$geo) throw new \Exception('Endereço não encontrado no Maps.');

            $distancia = $this->calcularDistancia($loja['lat'], $loja['lng'], $geo['lat'], $geo['lon']);

            $stmtTaxa = $db->prepare("SELECT valor FROM taxas_entrega_km WHERE filial_id = ? AND km_max >= ? AND ativo = 1 ORDER BY km_max ASC LIMIT 1");
            $stmtTaxa->execute([$loja['filial_id'], $distancia]);
            $taxa = $stmtTaxa->fetch(\PDO::FETCH_ASSOC);

            if ($taxa) {
                echo json_encode(['ok' => true, 'valor' => floatval($taxa['valor']), 'lat_cliente' => $geo['lat'], 'lng_cliente' => $geo['lon']]);
            } else {
                echo json_encode(['ok' => false, 'motivo' => 'fora_area', 'distancia' => $distancia]);
            }
        } catch (\Exception $e) { echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); }
        exit;
    }

    // 7. HISTÓRICO
    public function historico() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $dataInicio = $_GET['inicio'] ?? date('Y-m-01');
        $dataFim    = $_GET['fim'] ?? date('Y-m-d');
        
        $model = new Pedido();
        $pedidos = $model->listarHistorico($empresaId, $dataInicio, $dataFim);
        
        $totalFaturado = 0;
        $totalPedidos = count($pedidos);
        $totalDelivery = 0;
        foreach($pedidos as $p) { 
            if($p['status']!='cancelado') {
                $totalFaturado += $p['valor_total']; 
                $totalDelivery += $p['taxa_entrega'];
            }
        }
        $ticketMedio = $totalPedidos > 0 ? $totalFaturado / $totalPedidos : 0;
        
        require __DIR__ . '/../Views/admin/pedidos/historico.php';
    }

    // 8. OUTRAS FUNÇÕES
    public function mudarStatus() {
        $this->verificarLogin(); ob_clean();
        (new Pedido())->atualizarStatus($_POST['id'], $_POST['status']);
        echo json_encode(['ok' => true]); exit;
    }

    public function excluir() {
        $this->verificarLogin(); ob_clean();
        (new Pedido())->excluir($_POST['id']);
        echo json_encode(['ok' => true]); exit;
    }

    public function buscarClienteAjax() {
        $this->verificarLogin(); ob_clean();
        $tel = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
        $db = Database::connect();
        $stmt = $db->prepare("SELECT nome FROM clientes WHERE telefone LIKE ? AND empresa_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(["%$tel%", $_SESSION['empresa_id']]);
        echo json_encode(['encontrado' => ($stmt->rowCount() > 0), 'dados' => $stmt->fetch(\PDO::FETCH_ASSOC)]); exit;
    }
    
    public function getItensPedido() {
        $this->verificarLogin(); if(ob_get_contents()) ob_clean(); 
        $id = $_GET['id'] ?? 0; 
        $db = Database::connect(); 
        $stmtP = $db->prepare("SELECT * FROM pedidos WHERE id = ?"); 
        $stmtP->execute([$id]); 
        $pedido = $stmtP->fetch(\PDO::FETCH_ASSOC); 
        $stmtI = $db->prepare("SELECT i.*, COALESCE(p.nome, 'Item Excluído') as produto_nome FROM pedido_itens i LEFT JOIN produtos p ON i.produto_id = p.id WHERE i.pedido_id = ?"); 
        $stmtI->execute([$id]); 
        $itens = $stmtI->fetchAll(\PDO::FETCH_ASSOC); 
        header('Content-Type: application/json'); 
        echo json_encode(['pedido' => $pedido, 'itens' => $itens]); exit; 
    }
    
    public function vincularMotoboyAjax() {
        $this->verificarLogin(); ob_clean();
        $db = Database::connect(); 
        $db->prepare("UPDATE pedidos SET motoboy_id = ? WHERE id = ?")->execute([$_POST['motoboy_id'] ?: null, $_POST['pedido_id']]); 
        echo json_encode(['ok' => true]); exit; 
    }

    // --- FUNÇÕES PRIVADAS / AUXILIARES ---

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }

    private function moedaParaFloat($v) {
        if (is_numeric($v)) return (float)$v;
        return (float) str_replace(',', '.', str_replace(['R$', ' ', '.'], '', $v));
    }

    private function limparStringPix($str, $limit) {
        if (empty($str)) return 'LOJA';
        $str = preg_replace(
            ["/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"], 
            explode(" ","a A e E i I o O u U n N c C"), 
            $str
        );
        $str = preg_replace('/[^a-zA-Z0-9 ]/', '', $str);
        return strtoupper(substr(trim($str), 0, $limit));
    }

    private function geocodificar($endereco) {
        $apiKey = "AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U"; 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key=" . $apiKey;
        $json = @file_get_contents($url);
        $data = json_decode($json, true);
        if (isset($data['status']) && $data['status'] === 'OK') {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'], 
                'lon' => $data['results'][0]['geometry']['location']['lng']
            ];
        }
        return null; 
    }
    
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 1);
    }
}