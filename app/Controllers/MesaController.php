<?php
namespace App\Controllers;

use App\Core\Database;

class MesaController {

    public function index() {
        $this->verificarLogin();
        $db = Database::connect();
        
        // Busca o ID da filial atual (baseado na sessão da empresa)
        // Se seu sistema já tiver $_SESSION['filial_id'], use direto.
        // Aqui busco a primeira filial da empresa logada por garantia.
        $stmtFilial = $db->prepare("SELECT id FROM filiais WHERE empresa_id = ? LIMIT 1");
        $stmtFilial->execute([$_SESSION['empresa_id']]);
        $filial = $stmtFilial->fetch();
        $filialId = $filial['id'];

        // Lista mesas ordenadas por número (tentando ordenar numericamente)
        $sql = "SELECT * FROM mesas 
                WHERE filial_id = ? 
                ORDER BY CAST(REGEXP_REPLACE(numero, '[^0-9]', '') AS UNSIGNED), numero";
        $stmt = $db->prepare($sql);
        $stmt->execute([$filialId]);
        $mesas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Carrega a view (que criaremos no passo 3)
        require __DIR__ . '/../Views/admin/mesas/index.php';
    }

    public function salvar() {
        $this->verificarLogin();
        $numero = $_POST['numero'];
        $filialId = $this->getFilialId(); // Método auxiliar abaixo

        $db = Database::connect();
        
        // Verifica duplicidade
        $check = $db->prepare("SELECT id FROM mesas WHERE filial_id = ? AND numero = ?");
        $check->execute([$filialId, $numero]);
        if($check->rowCount() > 0) {
            echo "<script>alert('Já existe uma mesa com este número!'); window.history.back();</script>";
            exit;
        }

        // Cria Hash Único para o QR Code
        $hash = md5(uniqid($numero . time(), true));

        $db->prepare("INSERT INTO mesas (filial_id, numero, hash_qr, status_atual) VALUES (?, ?, ?, 'livre')")
           ->execute([$filialId, $numero, $hash]);

        header('Location: ' . BASE_URL . '/admin/mesas');
    }

    public function gerarEmLote() {
        $this->verificarLogin();
        $inicio = (int)$_POST['inicio'];
        $fim = (int)$_POST['fim'];
        $prefixo = $_POST['prefixo'] ?? ''; 
        $filialId = $this->getFilialId();

        if ($inicio > $fim) {
            header('Location: ' . BASE_URL . '/admin/mesas?erro=intervalo_invalido');
            exit;
        }

        $db = Database::connect();
        
        try {
            $db->beginTransaction();
            
            $stmtCheck = $db->prepare("SELECT id FROM mesas WHERE filial_id = ? AND numero = ?");
            $stmtInsert = $db->prepare("INSERT INTO mesas (filial_id, numero, hash_qr, status_atual) VALUES (?, ?, ?, 'livre')");

            $criadas = 0;

            for ($i = $inicio; $i <= $fim; $i++) {
                $nomeMesa = $prefixo . $i;

                // Só cria se não existir
                $stmtCheck->execute([$filialId, $nomeMesa]);
                if ($stmtCheck->rowCount() == 0) {
                    $hash = md5(uniqid($nomeMesa . time() . rand(), true));
                    $stmtInsert->execute([$filialId, $nomeMesa, $hash]);
                    $criadas++;
                }
            }

            $db->commit();
            header('Location: ' . BASE_URL . '/admin/mesas?msg=sucesso_lote&qtd=' . $criadas);

        } catch (\Exception $e) {
            $db->rollBack();
            die("Erro ao gerar mesas: " . $e->getMessage());
        }
    }

    public function excluir() {
        $this->verificarLogin();
        $id = $_GET['id'];
        $db = Database::connect();
        
        // Só permite excluir se a mesa não tiver sessão aberta
        $check = $db->prepare("SELECT id FROM mesa_sessoes WHERE mesa_id = ? AND status != 'encerrada'");
        $check->execute([$id]);
        if($check->rowCount() > 0) {
            echo "<script>alert('Esta mesa está ocupada! Encerre a conta antes de excluir.'); window.history.back();</script>";
            exit;
        }

        $db->prepare("DELETE FROM mesas WHERE id = ?")->execute([$id]);
        header('Location: ' . BASE_URL . '/admin/mesas');
    }

    private function getFilialId() {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id FROM filiais WHERE empresa_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['empresa_id']]);
        return $stmt->fetchColumn();
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}