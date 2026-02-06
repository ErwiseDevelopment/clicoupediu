<?php
namespace App\Controllers;
use App\Models\Configuracao;
use App\Core\Database;

class ConfiguracaoController {

    public function index() {
        $this->empresa();
    }

    public function empresa() {
        $this->verificarLogin();
        $filialId = $_SESSION['empresa_id']; 

        $model = new Configuracao();
        $dados = $model->buscarConfiguracoes($filialId);
        
        $config = $dados['geral'];
        $horarios = $dados['horarios'];

        require __DIR__ . '/../Views/admin/configuracoes/empresa.php';
    }

    public function salvarEmpresa() {
        $this->verificarLogin();
        $filialId = $_SESSION['empresa_id'];
        $model = new Configuracao();

        // Busca dados atuais
        $dadosAtuais = $model->buscarConfiguracoes($filialId)['geral'];
        
        // 1. Uploads
        $logo = $dadosAtuais['logo_url'];
        $capa = $dadosAtuais['banner_capa_url'];

        if (!empty($_FILES['logo']['name'])) {
            $up = $this->uploadArquivo($_FILES['logo']);
            if($up) $logo = $up;
        }

        if (!empty($_FILES['capa']['name'])) {
            $up = $this->uploadArquivo($_FILES['capa']);
            if($up) $capa = $up;
        }

        // 2. Geolocalização
        $lat = $_POST['lat'] ?? '';
        $lng = $_POST['lng'] ?? '';
        $endereco = $_POST['endereco_completo'] ?? '';

        if ((empty($lat) || empty($lng)) && !empty($endereco)) {
            $geo = $this->buscarGeolocalizacaoGoogle($endereco);
            if ($geo) {
                $lat = $geo['lat'];
                $lng = $geo['lon'];
            }
        }
        
        if (empty($lat)) $lat = $dadosAtuais['lat'];
        if (empty($lng)) $lng = $dadosAtuais['lng'];

        // Formatação monetária
        $valorMin = str_replace(['.', ','], ['', '.'], $_POST['pedido_minimo']);
        
        $db = Database::connect();

        // --- ETAPA 1: Salvar configurações da filial (SEM O PIX) ---
        // Removemos 'chave_pix = :pix' desta query pois a coluna não existe nesta tabela
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
        $stmt->execute([
            'logo' => $logo,
            'capa' => $capa,
            'cor' => $_POST['cor_primaria'],
            'tempo' => $_POST['tempo_entrega'],
            'minimo' => $valorMin,
            // 'chave_pix' foi removido daqui
            'aberto' => isset($_POST['aberto_auto']) ? 1 : 0,
            'end' => $endereco,
            'lat' => $lat,
            'lng' => $lng,
            'id' => $filialId
        ]);

        // --- ETAPA 2: Salvar o PIX na tabela 'empresas' ---
        // Criamos uma query separada apenas para atualizar o PIX na tabela correta
      
          $sqlEmpresa = "UPDATE empresas SET 
                        chave_pix = :pix, 
                        telefone_suporte = :tel 
                       WHERE id = :id";
        
        $stmtEmpresa = $db->prepare($sqlEmpresa);
        $stmtEmpresa->execute([
            'pix' => $_POST['chave_pix'] ?? '',
            'tel' => preg_replace('/[^0-9]/', '', $_POST['telefone_suporte'] ?? ''), // Limpa caracteres
            'id' => $filialId
        ]);
        

        if (isset($_POST['horarios'])) {
            $model->salvarHorarios($filialId, $_POST['horarios']);
        }

        header('Location: ' . BASE_URL . '/admin/configuracoes/empresa?msg=sucesso');
    }

    private function uploadArquivo($file) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $novoNome = uniqid('loja_') . "." . $ext;
        $dir = __DIR__ . '/../../public/assets/uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        $destino = $dir . $novoNome;
        if (move_uploaded_file($file['tmp_name'], $destino)) {
            return BASE_URL . '/assets/uploads/' . $novoNome;
        }
        return null;
    }

    private function buscarGeolocalizacaoGoogle($endereco) {
        $apiKey = "AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U"; 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key=" . $apiKey;
        
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($ch); 
        curl_close($ch); 
        
        $data = json_decode($json, true);
        if (isset($data['status']) && $data['status'] === 'OK') {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'], 
                'lon' => $data['results'][0]['geometry']['location']['lng']
            ];
        }
        return null;
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}