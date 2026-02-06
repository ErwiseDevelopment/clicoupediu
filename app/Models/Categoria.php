<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Categoria {
    
    // Lista todas as categorias da empresa logada
    public function listar($empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM categorias WHERE empresa_id = :id ORDER BY ordem ASC");
        $stmt->execute(['id' => $empresaId]);
        return $stmt->fetchAll();
    }

    // Cria uma nova categoria
    public function salvar($dados) {
        $db = Database::connect();
        
        // Se tiver ID, é UPDATE. Se não, é INSERT.
        if (!empty($dados['id'])) {
            $sql = "UPDATE categorias SET nome = :nome, ativa = :ativa WHERE id = :id AND empresa_id = :empresa_id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'nome' => $dados['nome'],
                'ativa' => $dados['ativa'],
                'id' => $dados['id'],
                'empresa_id' => $dados['empresa_id']
            ]);
        } else {
            $sql = "INSERT INTO categorias (empresa_id, nome, ordem, ativa) VALUES (:empresa_id, :nome, 0, :ativa)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'empresa_id' => $dados['empresa_id'],
                'nome' => $dados['nome'],
                'ativa' => $dados['ativa']
            ]);
        }
    }

    // Exclui (logicamente ou fisicamente)
    public function excluir($id, $empresaId) {
        $db = Database::connect();
        // Segurança: só deleta se pertencer à empresa logada
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id AND empresa_id = :empresa_id");
        return $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
    }

    // Busca uma específica para editar
    public function buscarPorId($id, $empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id = :id AND empresa_id = :empresa_id");
        $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
        return $stmt->fetch();
    }
}