<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Estoque {

    // Lista produtos e calcula estoque virtual de combos
    public function listar($filialId) {
        $db = Database::connect();
        // SQL ajustado: Adicionada a condição AND p.controle_estoque = 1
        $sql = "SELECT p.id, p.nome, p.imagem_url, p.tipo, c.nome as categoria, 
                       CASE 
                           WHEN p.tipo = 'combo' THEN (
                               SELECT MIN(FLOOR(IFNULL(ef_item.quantidade, 0) / pc.quantidade))
                               FROM produto_combos pc
                               LEFT JOIN estoque_filial ef_item ON pc.item_id = ef_item.produto_id 
                               WHERE pc.produto_pai_id = p.id
                           )
                           ELSE COALESCE(e.quantidade, 0)
                       END as quantidade,
                       COALESCE(e.ativo_nesta_filial, 1) as ativo
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN estoque_filial e ON p.id = e.produto_id AND e.filial_id = :filial_id
                WHERE p.empresa_id = (SELECT empresa_id FROM filiais WHERE id = :filial_id)
                AND p.ativo = 1
                AND p.controle_estoque = 1 -- Filtra apenas produtos com controle de estoque ativo
                ORDER BY p.tipo DESC, p.nome ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['filial_id' => $filialId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarEstoque($filialId, $produtoId, $novaQtd) {
        $db = Database::connect();
        
        // Verifica se o produto tem controle de estoque ativo antes de atualizar
        // Isso previne atualizações acidentais via API ou requisições diretas
        $checkProd = $db->prepare("SELECT controle_estoque FROM produtos WHERE id = ?");
        $checkProd->execute([$produtoId]);
        $prod = $checkProd->fetch();

        if (!$prod || $prod['controle_estoque'] != 1) {
            return false; // Não faz nada se o controle de estoque estiver desativado
        }

        $check = $db->prepare("SELECT id FROM estoque_filial WHERE filial_id = ? AND produto_id = ?");
        $check->execute([$filialId, $produtoId]);
        if ($check->fetch()) {
            $sql = "UPDATE estoque_filial SET quantidade = :qtd WHERE filial_id = :fid AND produto_id = :pid";
        } else {
            $sql = "INSERT INTO estoque_filial (filial_id, produto_id, quantidade) VALUES (:fid, :pid, :qtd)";
        }
        $stmt = $db->prepare($sql);
        return $stmt->execute(['qtd' => $novaQtd, 'fid' => $filialId, 'pid' => $produtoId]);
    }


    public function alternarDisponibilidade($filialId, $produtoId, $status) {
        $db = Database::connect();
        
        // Verifica controle de estoque também aqui
        $checkProd = $db->prepare("SELECT controle_estoque FROM produtos WHERE id = ?");
        $checkProd->execute([$produtoId]);
        $prod = $checkProd->fetch();

         if (!$prod || $prod['controle_estoque'] != 1) {
            return false;
        }

        $this->atualizarEstoque($filialId, $produtoId, 0); 
        $sql = "UPDATE estoque_filial SET ativo_nesta_filial = :status WHERE filial_id = :fid AND produto_id = :pid";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['status' => $status, 'fid' => $filialId, 'pid' => $produtoId]);
    }
}