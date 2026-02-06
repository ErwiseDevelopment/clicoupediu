<?php
namespace App\Controllers;
use App\Core\Database;
use PDO;

class AppMotoboyController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['motoboy_id'])) {
            header('Location: ' . BASE_URL . '/app-motoboy/painel');
            exit;
        }
        require __DIR__ . '/../Views/motoboy/login.php';
    }

    public function autenticar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp']);
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT * FROM motoboys WHERE (whatsapp = ? OR whatsapp LIKE ?) AND ativo = 1 LIMIT 1");
        $stmt->execute([$whatsapp, "%{$whatsapp}%"]); 
        $motoboy = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($motoboy) {
            $_SESSION['motoboy_id'] = $motoboy['id'];
            $_SESSION['motoboy_nome'] = $motoboy['nome'];
            $_SESSION['empresa_id'] = $motoboy['empresa_id'];
            header('Location: ' . BASE_URL . '/app-motoboy/painel');
        } else {
            header('Location: ' . BASE_URL . '/app-motoboy?erro=nao_encontrado');
        }
        exit;
    }

   public function painel() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['motoboy_id'])) { header('Location: ' . BASE_URL . '/app-motoboy'); exit; }

        $id = $_SESSION['motoboy_id'];
        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();

        // 1. DADOS DA LOJA (Ponto de Partida)
        $stmtLoja = $db->prepare("SELECT cf.lat, cf.lng, cf.endereco_completo FROM configuracoes_filial cf JOIN filiais f ON cf.filial_id = f.id WHERE f.empresa_id = ? LIMIT 1");
        $stmtLoja->execute([$empresaId]);
        $loja = $stmtLoja->fetch(PDO::FETCH_ASSOC);

        // Garante que lat/lng sejam float e troca vírgula por ponto
        $latLoja = $this->fixCoord($loja['lat'] ?? -23.5505);
        $lngLoja = $this->fixCoord($loja['lng'] ?? -46.6333);
        $enderecoLoja = $loja['endereco_completo'] ?? "Loja";

        // 2. BUSCA PEDIDOS
        $sql = "SELECT * FROM pedidos WHERE motoboy_id = ? AND status IN ('entrega', 'preparo', 'saiu_entrega')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $todosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. OTIMIZAÇÃO DE ROTA (Vizinho Mais Próximo)
        $comGPS = [];
        $semGPS = [];

        foreach ($todosPedidos as $p) {
            // Verifica se tem coordenada válida (diferente de 0 e não vazia)
            $lat = $this->fixCoord($p['lat_entrega']);
            $lng = $this->fixCoord($p['lng_entrega']);

            if ($lat != 0 && $lng != 0) {
                $p['lat_entrega'] = $lat; // Atualiza com o valor corrigido
                $p['lng_entrega'] = $lng;
                $comGPS[] = $p;
            } else {
                $semGPS[] = $p;
            }
        }

        $rotaOrdenada = [];
        $pontoAtual = ['lat' => $latLoja, 'lng' => $lngLoja];

        // Loop de Ordenação
        while (count($comGPS) > 0) {
            $melhorIndex = null;
            $menorDistancia = PHP_FLOAT_MAX;

            foreach ($comGPS as $index => $pedido) {
                $dist = $this->calcularDistancia($pontoAtual['lat'], $pontoAtual['lng'], $pedido['lat_entrega'], $pedido['lng_entrega']);
                
                if ($dist < $menorDistancia) {
                    $menorDistancia = $dist;
                    $melhorIndex = $index;
                }
            }

            if ($melhorIndex !== null) {
                $escolhido = $comGPS[$melhorIndex];
                $rotaOrdenada[] = $escolhido;
                
                // O ponto atual vira o destino deste pedido
                $pontoAtual = ['lat' => $escolhido['lat_entrega'], 'lng' => $escolhido['lng_entrega']];
                
                unset($comGPS[$melhorIndex]);
                $comGPS = array_values($comGPS); // Reindexa
            } else {
                break;
            }
        }

        // 4. JUNTAR TUDO NA VARIÁVEL FINAL (IMPORTANTE)
        // Pedidos ordenados primeiro + Pedidos sem GPS no final
        $entregas = array_merge($rotaOrdenada, $semGPS);

        // 5. GERAÇÃO DO LINK COM PARADAS (WAYPOINTS)
        $linkRotaCompleta = "";
        
        // Só gera rota se tiver pelo menos 1 pedido COM GPS
        if (count($rotaOrdenada) > 0) {
            $origin = "{$latLoja},{$lngLoja}";
            
            // O destino final é o ÚLTIMO pedido da lista ordenada
            $ultimoPedido = end($rotaOrdenada);
            $destination = "{$ultimoPedido['lat_entrega']},{$ultimoPedido['lng_entrega']}";
            
            $waypoints = [];
            // Percorre todos MENOS o último (que já é o destino)
            $totalParadas = count($rotaOrdenada);
            
            if ($totalParadas > 1) {
                for ($i = 0; $i < $totalParadas - 1; $i++) {
                    $p = $rotaOrdenada[$i];
                    $waypoints[] = "{$p['lat_entrega']},{$p['lng_entrega']}";
                }
            }

            // Monta a URL Oficial do Google Maps Universal
            $params = [
                'api' => 1,
                'origin' => $origin,
                'destination' => $destination,
                'travelmode' => 'motorcycle'
            ];

            if (!empty($waypoints)) {
                $params['waypoints'] = implode('|', $waypoints);
            }

            $linkRotaCompleta = "https://www.google.com/maps/dir/?api=1&" . http_build_query($params);
        }

        require __DIR__ . '/../Views/motoboy/painel.php';
    }

    // Auxiliar para limpar coordenadas (Troca vírgula por ponto e converte pra float)
    private function fixCoord($valor) {
        if(empty($valor)) return 0.0;
        return floatval(str_replace(',', '.', (string)$valor));
    }

    // Mantido o cálculo de distância...
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) return 0;
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos(min(max($dist, -1.0), 1.0));
        $dist = rad2deg($dist);
        return ($dist * 60 * 1.1515 * 1.609344);
    }

   

    public function finalizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['motoboy_id'])) { echo json_encode(['ok'=>false, 'erro'=>'Sessão expirada']); exit; }

        $pedidoId = $_POST['id'];
        $motoboyId = $_SESSION['motoboy_id'];
        $db = Database::connect();

        $check = $db->prepare("SELECT id, valor_total, cliente_nome, forma_pagamento, empresa_id FROM pedidos WHERE id = ? AND motoboy_id = ?");
        $check->execute([$pedidoId, $motoboyId]);
        $pedido = $check->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            $db->prepare("UPDATE pedidos SET status = 'finalizado' WHERE id = ?")->execute([$pedidoId]);

            $statusFin = ($pedido['forma_pagamento'] === 'fiado') ? 'pendente' : 'pago';
            $sqlFin = "INSERT INTO contas_receber (empresa_id, pedido_id, cliente_nome, valor, data_vencimento, status, forma_pagamento, created_at) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, NOW())";
            $db->prepare($sqlFin)->execute([$pedido['empresa_id'], $pedidoId, $pedido['cliente_nome'], $pedido['valor_total'], $statusFin, $pedido['forma_pagamento']]);

            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'erro' => 'Pedido inválido']);
        }
        exit;
    }
    
    public function sair() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/app-motoboy');
    }
}