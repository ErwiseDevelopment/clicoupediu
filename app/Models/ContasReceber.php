<?php
namespace App\Models;
use App\Core\Database;

class ContasReceber {
    public function listar($empresaId, $filtros = []) {
        $db = Database::connect();
        $sql = "SELECT cr.*, c.telefone as cliente_whatsapp 
                FROM contas_receber cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE cr.empresa_id = ?";
        
        $params = [$empresaId];

        if (!empty($filtros['busca'])) {
            $sql .= " AND cr.cliente_nome LIKE ?";
            $params[] = "%" . $filtros['busca'] . "%";
        }

        if (!empty($filtros['inicio']) && !empty($filtros['fim'])) {
            $sql .= " AND cr.created_at BETWEEN ? AND ?";
            $params[] = $filtros['inicio'] . ' 00:00:00';
            $params[] = $filtros['fim'] . ' 23:59:59';
        }

        $sql .= " ORDER BY cr.id DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotais($empresaId) {
        $db = Database::connect();
        $sql = "SELECT 
                SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END) as total_pendente,
                SUM(CASE WHEN status = 'pago' THEN valor ELSE 0 END) as total_pago,
                SUM(CASE WHEN forma_pagamento = 'fiado' AND status = 'pendente' THEN valor ELSE 0 END) as total_fiado
                FROM contas_receber WHERE empresa_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}