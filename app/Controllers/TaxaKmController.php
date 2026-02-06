<?php
namespace App\Controllers;
use App\Models\TaxaKm;
use App\Models\Configuracao; 

class TaxaKmController {

    public function index() {
        $this->verificarLogin();
        $filialId = $_SESSION['empresa_id'];

        $model = new TaxaKm();
        $faixas = $model->listar($filialId);
        
        // Busca o endereço da empresa para mostrar no mapa/form
        $configModel = new Configuracao();
        $config = $configModel->buscarConfiguracoes($filialId)['geral'];

        require __DIR__ . '/../Views/admin/taxas_km/index.php';
    }

    public function salvarFaixa() {
        $this->verificarLogin();
        $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
        
        $dados = [
            'id' => $_POST['id'] ?? '',
            'filial_id' => $_SESSION['empresa_id'],
            'km_min' => $_POST['km_min'],
            'km_max' => $_POST['km_max'],
            'valor' => $valor,
            'tempo' => $_POST['tempo'] ?? 40 // Salva o tempo
        ];

        (new TaxaKm())->salvar($dados);
        header('Location: ' . BASE_URL . '/admin/taxas-km');
    }

    // AQUI ESTÁ A VERIFICAÇÃO DO CADASTRO DA EMPRESA
    public function salvarLocalizacao() {
        $this->verificarLogin();
        $db = \App\Core\Database::connect();
        
        // Salva Lat/Lng na tabela de configurações da filial
        $sql = "UPDATE configuracoes_filial SET lat = :lat, lng = :lng, endereco_completo = :end WHERE filial_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'lat' => $_POST['lat'],
            'lng' => $_POST['lng'],
            'end' => $_POST['endereco'],
            'id' => $_SESSION['empresa_id']
        ]);

        header('Location: ' . BASE_URL . '/admin/taxas-km');
    }

        public function excluir() {
            $this->verificarLogin();
            $id = $_GET['id'] ?? null;
            $filialId = $_SESSION['empresa_id'];

            if ($id) {
                $model = new TaxaKm();
                $model->excluir($id, $filialId); // Agora envia os 2 argumentos esperados
            }
            
            header('Location: ' . BASE_URL . '/admin/taxas-km');
            exit;
        }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}