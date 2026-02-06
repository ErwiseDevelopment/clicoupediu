<?php
namespace App\Controllers;
use App\Core\Database;

class CadastroController {

    public function index() {
        $view = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'cadastro.php';
        if (file_exists($view)) {
            require $view;
        } else {
            die("Erro: Arquivo não encontrado em: " . $view);
        }
    }

   public function salvar() {
    $db = Database::connect();
    
    // 1. Dados do Formulário
    $nomeFantasia = $_POST['nome_fantasia'];
    
    // NOVO: Captura o nome da pessoa
    $nomeUsuario = $_POST['nome_usuario'] ?? 'Admin'; 

    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nomeFantasia)));
    $endereco = $_POST['endereco_completo'] ?? '';
    
    // Limpa máscara do documento
    $documento = isset($_POST['documento']) ? preg_replace('/[^0-9]/', '', $_POST['documento']) : null;

    // 2. Geolocalização
    $lat = null; $lng = null;
    if (!empty($endereco)) {
        $geo = $this->buscarGeolocalizacao($endereco);
        if ($geo) { 
            $lat = $geo['lat']; 
            $lng = $geo['lon']; 
        }
    }

    try {
        $db->beginTransaction();

        // A. Cria a EMPRESA (com CNPJ)
        $stmt = $db->prepare("INSERT INTO empresas (nome_fantasia, slug, cnpj, email_admin, endereco_completo, lat, lng) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nomeFantasia, $slug, $documento, $email, $endereco, $lat, $lng]);
        $empresaId = $db->lastInsertId();

        // B. Cria a FILIAL MATRIZ
        $stmtFilial = $db->prepare("INSERT INTO filiais (empresa_id, nome, endereco_completo) VALUES (?, 'Matriz', ?)");
        $stmtFilial->execute([$empresaId, $endereco]);
        $filialId = $db->lastInsertId(); 

        // C. Configurações da Filial
        $stmtConfig = $db->prepare("INSERT INTO configuracoes_filial (filial_id, lat, lng) VALUES (?, ?, ?)");
        $stmtConfig->execute([$filialId, $lat, $lng]);

        // D. Usuário Admin (AGORA COM O NOME CORRETO)
        $stmtUser = $db->prepare("INSERT INTO usuarios (empresa_id, filial_id, nome, email, senha_hash, nivel) VALUES (?, ?, ?, ?, ?, 'dono')");
        // Passamos $nomeUsuario no lugar de 'Admin'
        $stmtUser->execute([$empresaId, $filialId, $nomeUsuario, $email, $senha]);

        // E. Gera Raios de Entrega
        $this->gerarRaiosPadrao($filialId);

        $db->commit();
        header('Location: ' . BASE_URL . '/admin?msg=criada');

    } catch (\Exception $e) {
        $db->rollBack();
        die("Erro ao criar conta: " . $e->getMessage());
    }
}

    private function buscarGeolocalizacao($endereco) {
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=".urlencode($endereco)."&limit=1";
        $opts = ["http" => ["header" => "User-Agent: ClicouPediu/1.0"]];
        $json = @file_get_contents($url, false, stream_context_create($opts));
        if ($json) {
            $data = json_decode($json, true);
            return !empty($data[0]) ? ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']] : null;
        }
        return null;
    }

    private function gerarRaiosPadrao($filialId) {
        $db = Database::connect();
        $padroes = [[0.0, 1.0, 0.00, 20], [1.1, 2.0, 5.00, 30], [2.1, 3.0, 7.00, 40], [3.1, 4.0, 9.00, 50], [4.1, 5.0, 12.00, 60]];
        $stmt = $db->prepare("INSERT INTO taxas_entrega_km (filial_id, km_min, km_max, valor, tempo_estimado) VALUES (?, ?, ?, ?, ?)");
        foreach ($padroes as $p) { $stmt->execute([$filialId, $p[0], $p[1], $p[2], $p[3]]); }
    }
}