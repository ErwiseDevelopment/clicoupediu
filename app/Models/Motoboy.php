<?php
namespace App\Models;
use App\Core\Database;

class Motoboy {
    public function listar($empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM motoboys WHERE empresa_id = ? ORDER BY nome ASC");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function criar($dados) {
        $db = Database::connect();
        $sql = "INSERT INTO motoboys (empresa_id, nome, whatsapp, ativo) VALUES (?, ?, ?, ?)";
        return $stmt = $db->prepare($sql)->execute([
            $dados['empresa_id'], $dados['nome'], $dados['whatsapp'], $dados['ativo']
        ]);
    }

    public function atualizar($id, $dados) {
        $db = Database::connect();
        $sql = "UPDATE motoboys SET nome = ?, whatsapp = ?, ativo = ? WHERE id = ? AND empresa_id = ?";
        return $db->prepare($sql)->execute([
            $dados['nome'], $dados['whatsapp'], $dados['ativo'], $id, $dados['empresa_id']
        ]);
    }

    public function excluir($id, $empresaId) {
        $db = Database::connect();
        return $db->prepare("DELETE FROM motoboys WHERE id = ? AND empresa_id = ?")->execute([$id, $empresaId]);
    }
}