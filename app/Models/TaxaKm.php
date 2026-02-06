<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class TaxaKm {

    // Lista as faixas de preço
    public function listar($filialId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM taxas_entrega_km WHERE filial_id = :id ORDER BY km_min ASC");
        $stmt->execute(['id' => $filialId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salva ou Atualiza uma faixa
    public function salvar($dados) {
        $db = Database::connect();
        
        if (!empty($dados['id'])) {
            $sql = "UPDATE taxas_entrega_km SET km_min = :min, km_max = :max, valor = :valor, tempo_estimado = :tempo WHERE id = :id AND filial_id = :fid";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'min' => $dados['km_min'],
                'max' => $dados['km_max'],
                'valor' => $dados['valor'],
                'tempo' => $dados['tempo'],
                'id' => $dados['id'],
                'fid' => $dados['filial_id']
            ]);
        } else {
            $sql = "INSERT INTO taxas_entrega_km (filial_id, km_min, km_max, valor, tempo_estimado) VALUES (:fid, :min, :max, :valor, :tempo)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'fid' => $dados['filial_id'],
                'min' => $dados['km_min'],
                'max' => $dados['km_max'],
                'valor' => $dados['valor'],
                'tempo' => $dados['tempo']
            ]);
        }
    }

  
        // Gera o padrão (0 a 5km) para novas contas
    public function gerarPadrao($filialId) {
        $db = Database::connect();
        
        $check = $db->prepare("SELECT COUNT(*) FROM taxas_entrega_km WHERE filial_id = ?");
        $check->execute([$filialId]);
        if($check->fetchColumn() > 0) return; 

        $padroes = [
            ['min' => 0.0, 'max' => 1.0, 'val' => 0.00, 'tempo' => 20],
            ['min' => 1.1, 'max' => 2.0, 'val' => 5.00, 'tempo' => 30],
            ['min' => 2.1, 'max' => 3.0, 'val' => 7.00, 'tempo' => 40],
            ['min' => 3.1, 'max' => 4.0, 'val' => 9.00, 'tempo' => 50],
            ['min' => 4.1, 'max' => 5.0, 'val' => 12.00, 'tempo' => 60]
        ];

        $sql = "INSERT INTO taxas_entrega_km (filial_id, km_min, km_max, valor, tempo_estimado) VALUES (:fid, :min, :max, :val, :tempo)";
        $stmt = $db->prepare($sql);

        foreach ($padroes as $p) {
            $stmt->execute([
                'fid' => $filialId,
                'min' => $p['min'],
                'max' => $p['max'],
                'val' => $p['val'],
                'tempo' => $p['tempo']
            ]);
        }
    }

    // --- FUNÇÃO QUE ESTAVA FALTANDO: CALCULAR FRETE ---
    public function calcularFrete($filialId, $latCliente, $lngCliente) {
        $db = Database::connect();
        
        // 1. Pega coordenadas da loja
        // Tenta pegar da tabela de configurações primeiro
        $stmtLoja = $db->prepare("SELECT lat, lng FROM configuracoes_filial WHERE filial_id = :id");
        $stmtLoja->execute(['id' => $filialId]);
        $loja = $stmtLoja->fetch();

        // Se não tiver na config, tenta pegar da tabela de empresas (fallback)
        if (!$loja || !$loja['lat']) {
            $stmtEmp = $db->prepare("SELECT lat, lng FROM empresas WHERE id = (SELECT empresa_id FROM filiais WHERE id = :fid)");
            $stmtEmp->execute(['fid' => $filialId]);
            $loja = $stmtEmp->fetch();
        }

        if (!$loja || !$loja['lat'] || !$loja['lng']) {
            return ['erro' => 'A loja não possui endereço/coordenadas configuradas.'];
        }

        // 2. Calcula Distância (Fórmula de Haversine)
        $distanciaKm = $this->haversine($loja['lat'], $loja['lng'], $latCliente, $lngCliente);

        // 3. Busca a faixa de preço correspondente
        // Busca uma faixa onde a distância calculada esteja entre o Mínimo e o Máximo
        $stmtFaixa = $db->prepare("SELECT valor, tempo_estimado FROM taxas_entrega_km WHERE filial_id = :id AND :km >= km_min AND :km <= km_max LIMIT 1");
        $stmtFaixa->execute(['id' => $filialId, 'km' => $distanciaKm]);
        $faixa = $stmtFaixa->fetch();

        if ($faixa) {
            return [
                'valor' => $faixa['valor'], 
                'tempo' => $faixa['tempo_estimado'],
                'distancia' => round($distanciaKm, 2)
            ];
        } else {
            // Se não achou faixa (ex: deu 8km e a loja só entrega até 5km)
            return [
                'erro' => 'Endereço fora da área de entrega (' . round($distanciaKm, 1) . 'km).', 
                'distancia' => $distanciaKm
            ];
        }
    }

    // Matemática pura para calcular distância entre dois pontos no globo
    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Raio da terra em km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function excluir($id, $filialId) {
    $db = Database::connect();
    // A cláusula WHERE com id e filial_id impede que excluam dados de outras empresas
    $stmt = $db->prepare("DELETE FROM taxas_entrega_km WHERE id = :id AND filial_id = :fid");
    return $stmt->execute(['id' => $id, 'fid' => $filialId]);
}

    
}