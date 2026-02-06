<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Promocao {

    // CORREÇÃO: Agora calcula o estoque virtual dos combos (baseado nos ingredientes)
    public function listarCombos($empresaId) {
        $db = Database::connect();
        
        // Query inteligente que verifica o estoque dos itens que compõem o combo
        $sql = "SELECT p.*, 
                (
                    SELECT MIN(FLOOR(IFNULL(ef.quantidade, 0) / pc.quantidade))
                    FROM produto_combos pc
                    LEFT JOIN estoque_filial ef ON pc.item_id = ef.produto_id 
                    WHERE pc.produto_pai_id = p.id
                ) as estoque_atual
                FROM produtos p 
                WHERE p.empresa_id = ? 
                AND p.tipo = 'combo' 
                AND p.ativo = 1 
                ORDER BY p.id DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $combo = $stmt->fetch(PDO::FETCH_ASSOC);

        if($combo) {
            $stmtItens = $db->prepare("
                SELECT pc.item_id as id, pc.quantidade as qtd, p.nome, p.preco_base
                FROM produto_combos pc 
                JOIN produtos p ON pc.item_id = p.id 
                WHERE pc.produto_pai_id = ?
            ");
            $stmtItens->execute([$id]);
            $combo['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
            $combo['disponibilidade'] = $this->getDisponibilidade($id);
        }

        return $combo;
    }

    public function salvar($dados, $itensCombo) {
        $db = Database::connect();
        try {
            $db->beginTransaction();

            $precoBase = $this->tratarPreco($dados['preco']);
            $precoPromo = $this->tratarPreco($dados['preco_promocional'] ?? 0);

            $id = $dados['id'];
            $visivel = $dados['visivel_online'] ?? 1; 
            $estoque = $dados['controle_estoque'] ?? 1;
            
            if ($id) {
                $sql = "UPDATE produtos SET 
                        nome=?, descricao=?, preco_base=?, preco_promocional=?, 
                        categoria_id=?, imagem_url=?, visivel_online=?, controle_estoque=? 
                        WHERE id=?";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $dados['nome'], $dados['descricao'], $precoBase, $precoPromo,
                    $dados['categoria_id'], $dados['imagem'], $visivel, $estoque, $id
                ]);
                $db->prepare("DELETE FROM produto_combos WHERE produto_pai_id = ?")->execute([$id]);
                $produtoId = $id;
            } else {
                $sql = "INSERT INTO produtos (
                            empresa_id, nome, descricao, preco_base, preco_promocional, 
                            categoria_id, imagem_url, tipo, ativo, visivel_online, controle_estoque
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'combo', 1, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $dados['empresa_id'], $dados['nome'], $dados['descricao'], $precoBase, $precoPromo,
                    $dados['categoria_id'], $dados['imagem'], $visivel, $estoque
                ]);
                $produtoId = $db->lastInsertId();
            }

            $stmtItem = $db->prepare("INSERT INTO produto_combos (produto_pai_id, item_id, quantidade) VALUES (?, ?, ?)");
            foreach ($itensCombo as $item) {
                $itemId = $item['id'] ?? $item['item_id']; 
                $qtd = $item['qtd'] ?? $item['quantidade'];
                $stmtItem->execute([$produtoId, $itemId, $qtd]);
            }

            if (isset($dados['disponibilidade'])) {
                $this->salvarDisponibilidade($produtoId, $dados['disponibilidade']);
            }

            $db->commit();
            return true;

        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    private function tratarPreco($valor) {
        if (is_array($valor)) $valor = $valor[0] ?? 0;
        if (empty($valor)) return 0;
        if (strpos((string)$valor, ',') !== false) {
            $preco = str_replace('.', '', $valor);
            return str_replace(',', '.', $preco);
        }
        return $valor;
    }

    private function salvarDisponibilidade($produtoId, $periodos) {
        $db = Database::connect();
        $db->prepare("DELETE FROM produto_disponibilidade WHERE produto_id = ?")->execute([$produtoId]);

        if (!empty($periodos) && is_array($periodos)) {
            $sql = "INSERT INTO produto_disponibilidade (produto_id, dia_semana, horario_inicio, horario_fim) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            foreach ($periodos as $p) {
                if(isset($p['dia']) && $p['inicio'] != '' && $p['fim'] != '') {
                    $stmt->execute([$produtoId, $p['dia'], $p['inicio'], $p['fim']]);
                }
            }
        }
    }

    public function getDisponibilidade($produtoId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM produto_disponibilidade WHERE produto_id = ? ORDER BY dia_semana, horario_inicio");
        $stmt->execute([$produtoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function excluir($id) {
        $db = Database::connect();
        $db->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?")->execute([$id]);
    }
}