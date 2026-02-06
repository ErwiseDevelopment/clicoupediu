<?php
namespace App\Models;
use App\Core\Database;

class CategoriaFinanceira {
    
    // Lista todas as categorias da empresa
    public function listar($empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM categorias_financeiro WHERE empresa_id = ? ORDER BY tipo, descricao ASC");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Adicione dentro da classe CategoriaFinanceira
public function listarPorTipo($empresaId, $tipo) {
    $db = Database::connect();
    // Busca apenas as ativas (ativo = 1) e do tipo específico (entrada ou saida)
    $stmt = $db->prepare("SELECT descricao FROM categorias_financeiro WHERE empresa_id = ? AND tipo = ? AND ativo = 1 ORDER BY descricao ASC");
    $stmt->execute([$empresaId, $tipo]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
    // Cria ou Atualiza
    public function salvar($dados) {
        $db = Database::connect();
        
        if (empty($dados['id'])) {
            // Criar Nova
            $sql = "INSERT INTO categorias_financeiro (empresa_id, descricao, tipo, ativo) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $dados['empresa_id'], 
                $dados['descricao'], 
                $dados['tipo'], 
                $dados['ativo']
            ]);
        } else {
            // Atualizar Existente
            $sql = "UPDATE categorias_financeiro SET descricao = ?, tipo = ?, ativo = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $dados['descricao'], 
                $dados['tipo'], 
                $dados['ativo'], 
                $dados['id'], 
                $dados['empresa_id']
            ]);
        }
    }

    // Excluir
    public function excluir($id, $empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM categorias_financeiro WHERE id = ? AND empresa_id = ?");
        return $stmt->execute([$id, $empresaId]);
    }
}