<?php
namespace App\Models;
use App\Core\Database;

class ContasPagar {
    public function listar($empresaId, $filtros = []) {
        $db = Database::connect();
        
        $sql = "SELECT cp.*, f.telefone as fornecedor_telefone 
                FROM contas_pagar cp
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                WHERE cp.empresa_id = ?";
        
        $params = [$empresaId];

        if (!empty($filtros['busca'])) {
            $sql .= " AND cp.fornecedor_nome LIKE ?";
            $params[] = "%" . $filtros['busca'] . "%";
        }

        if (!empty($filtros['inicio']) && !empty($filtros['fim'])) {
            $sql .= " AND cp.data_vencimento BETWEEN ? AND ?";
            $params[] = $filtros['inicio'];
            $params[] = $filtros['fim'];
        }

        $sql .= " ORDER BY cp.data_vencimento ASC"; // Ordena pelo vencimento (urgência)
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotais($empresaId) {
        $db = Database::connect();
        $sql = "SELECT 
                SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END) as total_pendente,
                SUM(CASE WHEN status = 'pago' THEN valor ELSE 0 END) as total_pago
                FROM contas_pagar WHERE empresa_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}