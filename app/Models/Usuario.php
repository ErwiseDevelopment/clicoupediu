<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario {
    
    public function listar($empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE empresa_id = :eid AND ativo = 1 ORDER BY nome ASC");
        $stmt->execute(['eid' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($dados) {
        $db = Database::connect();
        
        if (!empty($dados['id'])) {
            // Edição
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, nivel = :nivel";
            $params = [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'nivel' => $dados['nivel'],
                'id' => $dados['id']
            ];

            // Só atualiza a senha se for preenchida
            if (!empty($dados['senha'])) {
                $sql .= ", senha_hash = :senha";
                $params['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id AND empresa_id = :eid";
            $params['eid'] = $dados['empresa_id'];
            
            return $db->prepare($sql)->execute($params);
        } else {
            // Novo Utilizador
            $sql = "INSERT INTO usuarios (empresa_id, nome, email, senha_hash, nivel, ativo) 
                    VALUES (:eid, :nome, :email, :senha, :nivel, 1)";
            return $db->prepare($sql)->execute([
                'eid' => $dados['empresa_id'],
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                'nivel' => $dados['nivel']
            ]);
        }
    }

    public function excluir($id, $empresaId) {
        $db = Database::connect();
        // Soft delete para manter histórico
        return $db->prepare("UPDATE usuarios SET ativo = 0 WHERE id = :id AND empresa_id = :eid")
                  ->execute(['id' => $id, 'eid' => $empresaId]);
    }
}