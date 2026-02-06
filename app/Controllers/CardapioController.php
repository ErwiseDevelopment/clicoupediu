<?php
namespace App\Controllers;

use App\Models\Cardapio;
use App\Models\Pedido;
use App\Core\Database;

class CardapioController {
    
    // --- ROTEADOR ---
    public function index($empresa) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Intercepta rotas da API
        if (strpos($uri, 'detalhesProduto') !== false) { $this->detalhesProduto(); exit; }
        if (strpos($uri, 'calcularFrete') !== false) { $this->calcularFrete(); exit; }
        if (strpos($uri, 'frete_gps') !== false) { $this->calcularFreteGPS(); exit; }
        if (strpos($uri, 'buscarCliente') !== false) { $this->buscarCliente(); exit; }
        if (strpos($uri, 'salvarPedido') !== false) { $this->salvarPedido(); exit; }
        if (strpos($uri, 'historico') !== false) { $this->meusPedidos(); exit; }

        date_default_timezone_set('America/Sao_Paulo');
        $db = Database::connect();

        // Dados básicos para a View
        $stmt = $db->prepare("SELECT * FROM filiais WHERE empresa_id = :id ORDER BY id ASC LIMIT 1");
        $stmt->execute(['id' => $empresa['id']]);
        $filial = $stmt->fetch();
        if (!$filial) die("<h1>Loja Indisponível</h1>");

        $stmt = $db->prepare("SELECT * FROM configuracoes_filial WHERE filial_id = :id");
        $stmt->execute(['id' => $filial['id']]);
        $config = $stmt->fetch();

        $stmtEmp = $db->prepare("SELECT chave_pix, telefone_suporte, nome_fantasia, endereco_completo, slug FROM empresas WHERE id = ?");
        $stmtEmp->execute([$empresa['id']]);
        $dadosEmpresa = $stmtEmp->fetch();
        
        $chavePix = $dadosEmpresa['chave_pix'] ?? '';
        $empresa['slug'] = $dadosEmpresa['slug'] ?? 'loja';

        // Horário
        $diaSemana = date('w'); $horaAgora = date('H:i');
        $stmtHora = $db->prepare("SELECT * FROM horarios_funcionamento WHERE filial_id = ? AND dia_semana = ?");
        $stmtHora->execute([$filial['id'], $diaSemana]);
        $horarioHoje = $stmtHora->fetch();

        $lojaAberta = false;
        $msgHorario = "Fechado agora";
        if ($horarioHoje && $horarioHoje['fechado_hoje'] == 0) {
            if ($horaAgora >= $horarioHoje['abertura'] && $horaAgora <= $horarioHoje['fechamento']) {
                $lojaAberta = true; $msgHorario = "Aberto até " . date('H:i', strtotime($horarioHoje['fechamento']));
            } else { $msgHorario = "Fechado"; }
        }

        // Produtos
        $model = new Cardapio();
        $combos = $model->buscarCombos($empresa['id'], $filial['id']);
        $destaques = $model->buscarMaisVendidos($empresa['id'], $filial['id']);
        $categorias = $model->buscarCardapioCompleto($empresa['id'], $filial['id']);
        
        $filial_id = $filial['id'];
        $whatsappLoja = $filial['telefone_whatsapp'] ?? '';
        
        require __DIR__ . '/../Views/cardapio/home.php';
    }

    // --- API SALVAR PEDIDO ---
    public function salvarPedido() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        date_default_timezone_set('America/Sao_Paulo');
        
        try { 
            $dados = $_POST; 
            if(empty($dados['itens_json'])) throw new \Exception('Carrinho vazio.'); 
            
            $itens = json_decode($dados['itens_json'], true); 
            $modelPedido = new Pedido(); 
            
            // Recálculo do valor dos produtos
            $valorProd = 0;
            foreach($itens as $item) {
                $valorProd += ($item['qtd'] * $item['preco']);
            }

            $taxaEntrega = floatval($dados['taxa_entrega']);
            $valorTotal = $valorProd + $taxaEntrega;
            
            // Cálculo do Troco
            $troco = 0.00;
            if ($dados['forma_pagamento'] === 'dinheiro') {
                $valorPago = floatval(str_replace(',', '.', $dados['troco_para']));
                if ($valorPago > $valorTotal) {
                    $troco = $valorPago - $valorTotal;
                }
            }

            $dadosPedido = [ 
                'empresa_id' => $dados['empresa_id'], 
                'cliente_nome' => $dados['cliente_nome'], 
                'cliente_telefone' => preg_replace('/[^0-9]/', '', $dados['cliente_telefone']), 
                'tipo_entrega' => $dados['tipo_entrega'], 
                'endereco' => $dados['endereco_entrega'] ?? '', 
                'numero' => $dados['numero'] ?? '', 
                'bairro' => $dados['bairro'] ?? '', 
                'complemento' => $dados['complemento'] ?? '', 
                'taxa_entrega' => $taxaEntrega, 
                'valor_produtos' => $valorProd, 
                'valor_total' => $valorTotal, 
                'forma_pagamento' => $dados['forma_pagamento'], 
                'troco' => $troco,
                'lat_entrega' => !empty($dados['lat_entrega']) ? $dados['lat_entrega'] : null, 
                'lng_entrega' => !empty($dados['lng_entrega']) ? $dados['lng_entrega'] : null
            ];
            
            if ($dadosPedido['tipo_entrega'] === 'entrega' && empty($dadosPedido['endereco'])) {
                throw new \Exception('Endereço é obrigatório.');
            }

            $idPedido = $modelPedido->criar($dadosPedido, $itens); 
            
            echo json_encode(['ok' => true, 'id' => $idPedido, 'whatsapp_loja' => $dados['whatsapp_loja'] ?? '']); 
        } catch (\Exception $e) { 
            echo json_encode(['ok' => false, 'erro' => "Erro: " . $e->getMessage()]); 
        } 
    }

    // --- OUTRAS APIs (COM LIMPEZA) ---
    
    public function detalhesProduto() {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = $_GET['id'] ?? 0;
            if (!$id) throw new \Exception("ID inválido.");
            $model = new Cardapio();
            echo json_encode(['ok' => true, 'complementos' => $model->getComplementos($id)]);
        } catch (\Exception $e) { echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); }
        exit;
    }

    public function calcularFrete() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        try { 
            $filialId = $_POST['filial_id'] ?? 0; 
            $enderecoCliente = $_POST['endereco'] ?? ''; 
            $numero = $_POST['numero'] ?? ''; 
            
            if(empty($enderecoCliente) || empty($numero)) throw new \Exception('Endereço incompleto'); 
            
            $db = Database::connect(); 
            $stmtLoja = $db->prepare("SELECT lat, lng FROM configuracoes_filial WHERE filial_id = ?"); 
            $stmtLoja->execute([$filialId]); 
            $loja = $stmtLoja->fetch(); 
            
            if(!$loja || empty($loja['lat'])) throw new \Exception('Loja sem localização configurada.'); 
            
            $geoCliente = $this->geocodificar("$enderecoCliente, $numero"); 
            if(!$geoCliente) throw new \Exception('Endereço não encontrado.'); 
            
            $distancia = $this->calcularDistancia($loja['lat'], $loja['lng'], $geoCliente['lat'], $geoCliente['lon']); 
            
            $stmtTaxa = $db->prepare("SELECT valor FROM taxas_entrega_km WHERE filial_id = ? AND km_max >= ? AND ativo = 1 ORDER BY km_max ASC LIMIT 1"); 
            $stmtTaxa->execute([$filialId, $distancia]); 
            $taxa = $stmtTaxa->fetch(); 
            
            if ($taxa) {
                echo json_encode([
                    'ok' => true, 
                    'valor' => floatval($taxa['valor']), 
                    'distancia' => $distancia, 
                    'lat' => $geoCliente['lat'], 
                    'lng' => $geoCliente['lon'],
                    'endereco_sugerido' => $geoCliente['endereco_completo']
                ]); 
            } else { 
                echo json_encode(['ok' => false, 'erro' => "Não entregamos nessa distância ($distancia km)."]); 
            } 
        } catch (\Exception $e) { 
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); 
        } 
    }

    public function buscarCliente() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        try { 
            $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''); 
            $empresaId = $_POST['empresa_id'] ?? 0; 
            if (strlen($telefone) < 8) throw new \Exception('Telefone inválido'); 
            $db = Database::connect(); 
            $stmt = $db->prepare("SELECT nome, endereco_ultimo, numero_ultimo, bairro_ultimo FROM clientes WHERE telefone = ? AND empresa_id = ? LIMIT 1"); 
            $stmt->execute([$telefone, $empresaId]); 
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC); 
            echo json_encode(['encontrado' => !!$cliente, 'dados' => $cliente]); 
        } catch (\Exception $e) { echo json_encode(['encontrado' => false]); } 
    }

    public function calcularFreteGPS() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        try {
            $filialId = $_POST['filial_id'] ?? 0; $latCli = $_POST['lat'] ?? 0; $lngCli = $_POST['lng'] ?? 0;
            $db = Database::connect(); $stmtLoja = $db->prepare("SELECT lat, lng FROM configuracoes_filial WHERE filial_id = ?");
            $stmtLoja->execute([$filialId]); $loja = $stmtLoja->fetch();
            if(!$loja || empty($loja['lat'])) throw new \Exception("Loja sem GPS");
            $distancia = $this->calcularDistancia($loja['lat'], $loja['lng'], $latCli, $lngCli);
            $stmtTaxa = $db->prepare("SELECT valor FROM taxas_entrega_km WHERE filial_id = ? AND km_max >= ? AND ativo = 1 ORDER BY km_max ASC LIMIT 1");
            $stmtTaxa->execute([$filialId, $distancia]); $taxa = $stmtTaxa->fetch();
            $endereco = $this->reverseGeocoding($latCli, $lngCli); 
            if ($taxa) echo json_encode(['ok' => true, 'valor' => floatval($taxa['valor']), 'endereco_sugerido' => $endereco]);
            else echo json_encode(['ok' => false, 'erro' => "Fora da área ($distancia km)"]);
        } catch (\Exception $e) { echo json_encode(['ok'=>false, 'erro'=>$e->getMessage()]); }
    }

    public function meusPedidos() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json'); 
        try {
            $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''); $empresaId = $_POST['empresa_id'] ?? 0;
            $model = new Pedido();
            if(method_exists($model, 'buscarPorTelefone')) {
                $pedidos = $model->buscarPorTelefone($empresaId, $telefone);
                $lista = [];
                foreach($pedidos as $p) {
                    $lista[] = ['id' => str_pad($p['id'], 4, '0', STR_PAD_LEFT), 'total' => number_format($p['valor_total'],2,',','.'), 'status' => ucfirst($p['status']), 'data' => date('d/m H:i', strtotime($p['created_at']))]; 
                }
                echo json_encode(['ok'=>true, 'pedidos'=>$lista]);
            } else { echo json_encode(['ok'=>true, 'pedidos'=>[]]); }
        } catch(\Exception $e) { echo json_encode(['ok'=>false]); }
    }

    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) { 
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1); 
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2); 
        $c = 2 * atan2(sqrt($a), sqrt(1-$a)); return round($earthRadius * $c, 1); 
    }

    private function geocodificar($endereco) {
        $apiKey = "AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U"; 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key=" . $apiKey; 
        $json = @file_get_contents($url); $data = json_decode($json, true); 
        if (isset($data['status']) && $data['status'] === 'OK') { 
            $comp = $data['results'][0]['address_components'];
            $rua = ''; $bairro = '';
            foreach($comp as $c) {
                if(in_array('route', $c['types'])) $rua = $c['long_name'];
                if(in_array('sublocality', $c['types'])) $bairro = $c['long_name'];
            }
            return ['lat' => $data['results'][0]['geometry']['location']['lat'], 'lon' => $data['results'][0]['geometry']['location']['lng'], 'endereco_completo' => ['rua' => $rua, 'bairro' => $bairro]]; 
        } 
        return null; 
    }
    private function reverseGeocoding($lat, $lng) { return null; }
}