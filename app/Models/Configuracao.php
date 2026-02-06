<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Configuracao {
    
    // Busca configs da filial + dados da empresa (PIX) + horários
    public function buscarConfiguracoes($filialId) {
        $db = Database::connect();
        

        $sql = "SELECT c.*, e.chave_pix, e.telefone_suporte 
                FROM configuracoes_filial c 
                LEFT JOIN empresas e ON e.id = :id_empresa
                WHERE c.filial_id = :id_filial";

        $stmt = $db->prepare($sql);
        // Usamos o mesmo ID para ambos por enquanto, baseado na lógica do seu controller atual
        $stmt->execute(['id_filial' => $filialId, 'id_empresa' => $filialId]); 
        $geral = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não existir, cria o registro padrão
        if (!$geral) {
            $this->criarConfiguracaoPadrao($filialId);
            return $this->buscarConfiguracoes($filialId);
        }

        // 2. Horários
        $stmtHorarios = $db->prepare("SELECT * FROM horarios_funcionamento WHERE filial_id = :id ORDER BY dia_semana ASC");
        $stmtHorarios->execute(['id' => $filialId]);
        $horarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

        // Se não tiver horários, cria os 7 dias
        if (count($horarios) < 7) {
            $this->criarHorariosPadrao($filialId);
            $stmtHorarios->execute(['id' => $filialId]);
            $horarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
        }

        return ['geral' => $geral, 'horarios' => $horarios];
    }

    public function salvarGeral($dados) {
        $db = Database::connect();
        
        // Atualiza configurações visuais na tabela configuracoes_filial
        $sql = "UPDATE configuracoes_filial SET 
                logo_url = :logo,
                banner_capa_url = :capa,
                cor_primaria = :cor,
                tempo_medio_entrega = :tempo,
                pedido_minimo = :minimo,
                aberto_automatico = :aberto,
                endereco_completo = :end,
                lat = :lat,
                lng = :lng
                WHERE filial_id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($dados);

        // --- NOVO: Atualiza a Chave PIX na tabela empresas ---
        // $dados['id'] aqui é o ID da empresa/filial vindo do controller
        if (isset($dados['chave_pix'])) {
            $this->salvarPixEmpresa($dados['id'], $dados['chave_pix']);
        }
    }

    // Método auxiliar para salvar apenas o PIX na tabela empresas
    private function salvarPixEmpresa($empresaId, $chavePix) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE empresas SET chave_pix = :pix WHERE id = :id");
        $stmt->execute(['pix' => $chavePix, 'id' => $empresaId]);
    }

    public function salvarHorarios($filialId, $horarios) {
        $db = Database::connect();
        $sql = "UPDATE horarios_funcionamento SET 
                abertura = :abre, 
                fechamento = :fecha, 
                fechado_hoje = :fechado 
                WHERE filial_id = :id AND dia_semana = :dia";
        
        $stmt = $db->prepare($sql);

        foreach ($horarios as $dia => $h) {
            $stmt->execute([
                'abre' => $h['abre'],
                'fecha' => $h['fecha'],
                'fechado' => isset($h['fechado']) ? 1 : 0,
                'id' => $filialId,
                'dia' => $dia
            ]);
        }
    }

    // --- Helpers Privados ---
    private function criarConfiguracaoPadrao($id) {
        $db = Database::connect();
        // Verifica se já existe para evitar erro de duplicidade
        $check = $db->prepare("SELECT filial_id FROM configuracoes_filial WHERE filial_id = ?");
        $check->execute([$id]);
        if(!$check->fetch()){
             $db->prepare("INSERT INTO configuracoes_filial (filial_id) VALUES (?)")->execute([$id]);
        }
    }

    private function criarHorariosPadrao($id) {
        $db = Database::connect();
        $sql = "INSERT INTO horarios_funcionamento (filial_id, dia_semana, abertura, fechamento) VALUES (:id, :dia, '18:00', '23:00')";
        $stmt = $db->prepare($sql);
        
        for ($i = 0; $i <= 6; $i++) {
            $check = $db->prepare("SELECT id FROM horarios_funcionamento WHERE filial_id = ? AND dia_semana = ?");
            $check->execute([$id, $i]);
            if(!$check->fetch()) {
                $stmt->execute(['id' => $id, 'dia' => $i]);
            }
        }
    }
}