<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Cardapio {

    // --- 1. BUSCAR MAIS VENDIDOS ---
    public function buscarMaisVendidos($empresaId, $filialId, $limite = 6) {
        $db = Database::connect();
        $sql = "SELECT p.*, COALESCE(SUM(pi.quantidade), 0) as total_vendas, COALESCE(MAX(ef.quantidade), 0) as estoque_atual
                FROM produtos p
                LEFT JOIN pedido_itens pi ON p.id = pi.produto_id
                LEFT JOIN pedidos ped ON pi.pedido_id = ped.id AND ped.status IN ('finalizado', 'entregue', 'entrega')
                LEFT JOIN estoque_filial ef ON p.id = ef.produto_id AND ef.filial_id = :filial
                WHERE p.empresa_id = :empresa AND p.ativo = 1 AND p.visivel_online = 1 AND p.tipo != 'combo' 
                GROUP BY p.id ORDER BY total_vendas DESC LIMIT :limite";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa', $empresaId);
        $stmt->bindValue(':filial', $filialId);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->processarEstoque($db, $produtos, $filialId);
        return $this->filtrarPorHorario($db, $produtos);
    }

    // --- 2. BUSCAR COMBOS ---
    public function buscarCombos($empresaId, $filialId) {
        $db = Database::connect();
        $sql = "SELECT p.*, c.nome as categoria_nome, COALESCE(ef.quantidade, 0) as estoque_atual
                FROM produtos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN estoque_filial ef ON p.id = ef.produto_id AND ef.filial_id = :filial
                WHERE p.empresa_id = :empresa AND p.ativo = 1 AND p.visivel_online = 1 AND (c.nome LIKE '%Combo%' OR p.tipo = 'combo') 
                ORDER BY p.nome ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['empresa' => $empresaId, 'filial' => $filialId]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->processarEstoque($db, $produtos, $filialId);
        return $this->filtrarPorHorario($db, $produtos);
    }

    // --- 3. CARDÁPIO GERAL ---
    public function buscarCardapioCompleto($empresaId, $filialId) {
        $db = Database::connect();
        $stmtCat = $db->prepare("SELECT * FROM categorias WHERE empresa_id = ? AND ativa = 1 ORDER BY ordem ASC");
        $stmtCat->execute([$empresaId]);
        $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categorias as &$cat) {
            $sql = "SELECT p.*, COALESCE(ef.quantidade, 0) as estoque_atual
                    FROM produtos p
                    LEFT JOIN estoque_filial ef ON p.id = ef.produto_id AND ef.filial_id = :filial
                    WHERE p.categoria_id = :cat AND p.ativo = 1 AND p.visivel_online = 1 AND p.tipo != 'combo'
                    ORDER BY p.nome ASC";
            $stmtProd = $db->prepare($sql);
            $stmtProd->execute(['cat' => $cat['id'], 'filial' => $filialId]);
            $produtos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
            $this->processarEstoque($db, $produtos, $filialId);
            $cat['itens'] = $this->filtrarPorHorario($db, $produtos);
        }
        return array_filter($categorias, function($c) { return count($c['itens']) > 0; });
    }

    // --- 4. DETALHES E COMPLEMENTOS (TABELA CORRETA: produto_complementos) ---
    public function getComplementos($produtoId) {
        $db = Database::connect();
        
        $sql = "
            SELECT g.* FROM grupos_adicionais g
            INNER JOIN produto_complementos pc ON g.id = pc.grupo_id
            WHERE pc.produto_id = ? 
            AND pc.ativo = 1
            ORDER BY g.obrigatorio DESC, g.id ASC
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$produtoId]);
            $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Busca Opções de cada Grupo
            foreach ($grupos as &$g) {
                $sqlOpcoes = "SELECT * FROM opcionais WHERE grupo_id = ? ORDER BY preco ASC, nome ASC";
                $stmtOp = $db->prepare($sqlOpcoes);
                $stmtOp->execute([$g['id']]);
                $g['itens'] = $stmtOp->fetchAll(PDO::FETCH_ASSOC);
            }

            return $grupos;

        } catch (\PDOException $e) {
            return [];
        }
    }

    // --- AUXILIARES ---
    private function processarEstoque($db, &$produtos, $filialId) {
        $sqlCalculoCombo = "SELECT MIN(FLOOR(COALESCE(ef.quantidade, 0) / pc.quantidade)) as estoque_calculado FROM produto_combos pc LEFT JOIN estoque_filial ef ON pc.item_id = ef.produto_id AND ef.filial_id = :filial WHERE pc.produto_pai_id = :id_combo";
        $stmtCombo = $db->prepare($sqlCalculoCombo);
        foreach ($produtos as &$prod) {
            if ($prod['tipo'] === 'combo') {
                $stmtCombo->execute(['filial' => $filialId, 'id_combo' => $prod['id']]);
                $resultado = $stmtCombo->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['estoque_calculado'])) { $prod['estoque_atual'] = $resultado['estoque_calculado']; }
            }
            if (isset($prod['controle_estoque']) && $prod['controle_estoque'] == 0) { $prod['estoque_atual'] = 9999; }
        }
    }

    private function filtrarPorHorario($db, $produtos) {
        if (empty($produtos)) return [];
        $diaSemanaHoje = date('w'); $horaAgora = date('H:i:s');
        $filtrados = [];
        $stmt = $db->prepare("SELECT * FROM produto_disponibilidade WHERE produto_id = ?");
        foreach ($produtos as $p) {
            $stmt->execute([$p['id']]);
            $regras = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($regras)) { $filtrados[] = $p; continue; }
            $estaDisponivel = false;
            foreach ($regras as $r) {
                if ($r['dia_semana'] == $diaSemanaHoje) {
                    if ($horaAgora >= $r['horario_inicio'] && $horaAgora <= $r['horario_fim']) { $estaDisponivel = true; break; }
                }
            }
            if ($estaDisponivel) { $filtrados[] = $p; }
        }
        return $filtrados;
    }
}   