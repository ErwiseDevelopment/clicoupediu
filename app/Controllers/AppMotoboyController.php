<?php
namespace App\Controllers;
use App\Core\Database;
use PDO;

class AppMotoboyController {

    // Palavra-chave secreta para gerar o Hash de segurança (Nunca mude isso, senão desloga todo mundo)
    private $chaveSecreta = "MotoDelivery2026!@#MK";

    // --- FUNÇÃO DE SEGURANÇA: Restaura sessão via Cookie + Validação de Hash ---
    private function verificarOuRestaurarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Se não tem sessão, mas tem os cookies salvos, tenta validar
        if (!isset($_SESSION['motoboy_id']) && isset($_COOKIE['moto_id']) && isset($_COOKIE['moto_hash'])) {
            
            $db = Database::connect();
            $stmt = $db->prepare("SELECT id, nome, empresa_id, whatsapp FROM motoboys WHERE id = ? AND ativo = 1 LIMIT 1");
            $stmt->execute([$_COOKIE['moto_id']]);
            $moto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($moto) {
                // Recalcula o hash com os dados do banco para ver se o cookie não foi falsificado
                $hashReal = hash('sha256', $moto['id'] . $moto['whatsapp'] . $this->chaveSecreta);
                
                // Se o hash do cookie for idêntico ao real, o login é legítimo!
                if (hash_equals($hashReal, $_COOKIE['moto_hash'])) {
                    $_SESSION['motoboy_id'] = $moto['id'];
                    $_SESSION['motoboy_nome'] = $moto['nome'];
                    $_SESSION['empresa_id'] = $moto['empresa_id'];
                    return true;
                }
            }
            
            // Se chegou aqui, o cookie é falso, o hash não bateu ou o motoboy foi desativado. 
            // Limpamos os cookies invasores.
            setcookie('moto_id', '', time() - 3600, '/');
            setcookie('moto_hash', '', time() - 3600, '/');
        }
        
        return isset($_SESSION['motoboy_id']);
    }

    public function index() {
        if ($this->verificarOuRestaurarLogin()) {
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
            // Cria a sessão padrão
            $_SESSION['motoboy_id'] = $motoboy['id'];
            $_SESSION['motoboy_nome'] = $motoboy['nome'];
            $_SESSION['empresa_id'] = $motoboy['empresa_id'];

            // === CRIAÇÃO DO COOKIE COM HASH DE SEGURANÇA ===
            $hashValidacao = hash('sha256', $motoboy['id'] . $motoboy['whatsapp'] . $this->chaveSecreta);
            $umAno = time() + (365 * 24 * 60 * 60); 
            
            setcookie('moto_id', $motoboy['id'], $umAno, '/');
            setcookie('moto_hash', $hashValidacao, $umAno, '/');
            // ===============================================

            header('Location: ' . BASE_URL . '/app-motoboy/painel');
        } else {
            header('Location: ' . BASE_URL . '/app-motoboy?erro=nao_encontrado');
        }
        exit;
    }

    public function painel() {
        // Usa a nova função que blinda contra queda de sessão e valida o Hash
        if (!$this->verificarOuRestaurarLogin()) { 
            header('Location: ' . BASE_URL . '/app-motoboy'); 
            exit; 
        }

        $id = $_SESSION['motoboy_id'];
        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();

        // 1. DADOS DA LOJA (Ponto de Partida)
        $stmtLoja = $db->prepare("SELECT cf.lat, cf.lng, cf.endereco_completo FROM configuracoes_filial cf JOIN filiais f ON cf.filial_id = f.id WHERE f.empresa_id = ? LIMIT 1");
        $stmtLoja->execute([$empresaId]);
        $loja = $stmtLoja->fetch(PDO::FETCH_ASSOC);

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
            $lat = $this->fixCoord($p['lat_entrega']);
            $lng = $this->fixCoord($p['lng_entrega']);

            if ($lat != 0 && $lng != 0) {
                $p['lat_entrega'] = $lat;
                $p['lng_entrega'] = $lng;
                $comGPS[] = $p;
            } else {
                $semGPS[] = $p;
            }
        }

        $rotaOrdenada = [];
        $pontoAtual = ['lat' => $latLoja, 'lng' => $lngLoja];

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
                
                $pontoAtual = ['lat' => $escolhido['lat_entrega'], 'lng' => $escolhido['lng_entrega']];
                
                unset($comGPS[$melhorIndex]);
                $comGPS = array_values($comGPS); 
            } else {
                break;
            }
        }

        $entregas = array_merge($rotaOrdenada, $semGPS);
        $linkRotaCompleta = "";
        
        if (count($rotaOrdenada) > 0) {
            $origin = "{$latLoja},{$lngLoja}";
            $ultimoPedido = end($rotaOrdenada);
            $destination = "{$ultimoPedido['lat_entrega']},{$ultimoPedido['lng_entrega']}";
            
            $waypoints = [];
            $totalParadas = count($rotaOrdenada);
            
            if ($totalParadas > 1) {
                for ($i = 0; $i < $totalParadas - 1; $i++) {
                    $p = $rotaOrdenada[$i];
                    $waypoints[] = "{$p['lat_entrega']},{$p['lng_entrega']}";
                }
            }

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

    private function fixCoord($valor) {
        if(empty($valor)) return 0.0;
        return floatval(str_replace(',', '.', (string)$valor));
    }

    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) return 0;
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos(min(max($dist, -1.0), 1.0));
        $dist = rad2deg($dist);
        return ($dist * 60 * 1.1515 * 1.609344);
    }

    public function finalizar() {
        header('Content-Type: application/json');
        
        if (!$this->verificarOuRestaurarLogin()) { 
            echo json_encode(['ok'=>false, 'erro'=>'Sessão expirada. Recarregue a página.']); 
            exit; 
        }

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

        // Destrói os cookies de segurança
        setcookie('moto_id', '', time() - 3600, '/');
        setcookie('moto_hash', '', time() - 3600, '/');

        header('Location: ' . BASE_URL . '/app-motoboy');
    }
}