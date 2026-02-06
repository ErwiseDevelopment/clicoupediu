<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Adicional {
    
    // =========================================================================
    // PARTE 1: GERENCIAMENTO DE GRUPOS (Ex: "Escolha o Molho", "Bordas")
    // =========================================================================

    // Lista todos os grupos da empresa
    public function listarGrupos($empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM grupos_adicionais WHERE empresa_id = :id ORDER BY id DESC");
        $stmt->execute(['id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca um grupo específico (para editar ou validar)
    public function buscarGrupo($id, $empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM grupos_adicionais WHERE id = :id AND empresa_id = :emp_id");
        $stmt->execute(['id' => $id, 'emp_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cria ou Atualiza um Grupo
    public function salvarGrupo($dados) {
        $db = Database::connect();
        
        // Se tiver ID, faz UPDATE
        if (!empty($dados['id'])) {
            $sql = "UPDATE grupos_adicionais SET 
                    nome = :nome, 
                    descricao = :desc, 
                    obrigatorio = :obrig, 
                    minimo = :min, 
                    maximo = :max 
                    WHERE id = :id AND empresa_id = :emp_id";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'nome' => $dados['nome'],
                'desc' => $dados['desc'],
                'obrig' => $dados['obrig'],
                'min' => $dados['min'],
                'max' => $dados['max'],
                'id' => $dados['id'],
                'emp_id' => $dados['emp_id']
            ]);
        } 
        // Se não tiver ID, faz INSERT
        else {
            $sql = "INSERT INTO grupos_adicionais (empresa_id, nome, descricao, obrigatorio, minimo, maximo) 
                    VALUES (:emp_id, :nome, :desc, :obrig, :min, :max)";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'emp_id' => $dados['emp_id'],
                'nome' => $dados['nome'],
                'desc' => $dados['desc'],
                'obrig' => $dados['obrig'],
                'min' => $dados['min'],
                'max' => $dados['max']
            ]);
        }
    }

    // Exclui um grupo (e seus itens via CASCADE do banco, se configurado)
    public function excluirGrupo($id, $empresaId) {
        $db = Database::connect();
        // Primeiro remove os itens vinculados (caso o banco não tenha ON DELETE CASCADE)
        $db->prepare("DELETE FROM opcionais WHERE grupo_id = :id")->execute(['id' => $id]);

        // Depois remove o grupo
        $stmt = $db->prepare("DELETE FROM grupos_adicionais WHERE id = :id AND empresa_id = :emp_id");
        return $stmt->execute(['id' => $id, 'emp_id' => $empresaId]);
    }

    // =========================================================================
    // PARTE 2: GERENCIAMENTO DE ITENS/OPCIONAIS (Ex: "Cheddar", "Bacon")
    // =========================================================================

    // Lista os itens de um grupo específico
    public function listarItens($grupoId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM opcionais WHERE grupo_id = :id ORDER BY id DESC");
        $stmt->execute(['id' => $grupoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cria ou Atualiza um Item/Opcional
    public function salvarItem($dados) {
        $db = Database::connect();

        if (!empty($dados['id'])) {
            // Update
            $sql = "UPDATE opcionais SET nome = :nome, preco = :preco, descricao = :desc WHERE id = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'nome' => $dados['nome'],
                'preco' => $dados['preco'],
                'desc' => $dados['desc'],
                'id' => $dados['id']
            ]);
        } else {
            // Insert
            $sql = "INSERT INTO opcionais (grupo_id, nome, preco, descricao) VALUES (:grupo_id, :nome, :preco, :desc)"; // Removido 'ativo' se não existir na tabela
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'grupo_id' => $dados['grupo_id'],
                'nome' => $dados['nome'],
                'preco' => $dados['preco'],
                'desc' => $dados['desc']
            ]);
        }
    }

    // Exclui um item específico
    public function excluirItem($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM opcionais WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}