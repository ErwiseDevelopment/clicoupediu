<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Produto {
    
   public function listar($empresaId) {
        $db = Database::connect();
        
        $sql = "SELECT p.*, c.nome as nome_categoria, 
                COALESCE(SUM(e.quantidade), 0) as estoque_atual
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN estoque_filial e ON p.id = e.produto_id
                WHERE p.empresa_id = :id 
                AND p.ativo = 1 
                AND (p.tipo = 'simples' OR p.tipo IS NULL)
                GROUP BY p.id
                ORDER BY p.id DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $empresaId]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($produtos as &$prod) {
            $prod['disponibilidade'] = $this->getDisponibilidade($prod['id']);
            $prod['complementos'] = $this->getComplementosIds($prod['id']); 
        }

        return $produtos;
    }
    
    public function listarSimples($empresaId) {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT id, nome, preco_base, imagem_url 
            FROM produtos 
            WHERE empresa_id = ? AND ativo = 1 AND (tipo = 'simples' OR tipo IS NULL) 
            ORDER BY nome ASC
        ");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($dados) {
        $db = Database::connect();
        
        $valorBase = $this->tratarPreco($dados['preco'] ?? 0);
        $valorPromo = $this->tratarPreco($dados['preco_promocional'] ?? 0);

        if (!empty($dados['id'])) {
            // ATUALIZAR
            $sql = "UPDATE produtos SET 
                    categoria_id = :cat_id, 
                    nome = :nome, 
                    descricao = :desc, 
                    preco_base = :preco, 
                    preco_promocional = :promo,
                    imagem_url = :img, 
                    ativo = :ativo,
                    visivel_online = :visivel,
                    controle_estoque = :controle_estoque,
                    precisa_preparo = :precisa_preparo,
                    tipo = :tipo 
                    WHERE id = :id AND empresa_id = :emp_id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'cat_id' => $dados['categoria_id'],
                'nome' => $dados['nome'],
                'desc' => $dados['descricao'],
                'preco' => $valorBase,
                'promo' => $valorPromo,
                'img' => $dados['imagem_url'] ?? '',
                'ativo' => $dados['ativo'],
                'visivel' => $dados['visivel_online'],
                'controle_estoque' => $dados['controle_estoque'],
                'precisa_preparo' => $dados['precisa_preparo'],
                'tipo' => $dados['tipo'] ?? 'simples',
                'id' => $dados['id'],
                'emp_id' => $dados['empresa_id']
            ]);
            $id = $dados['id']; 
        } else {
            // CRIAR
            $sql = "INSERT INTO produtos (empresa_id, categoria_id, nome, descricao, preco_base, preco_promocional, imagem_url, ativo, visivel_online, controle_estoque, precisa_preparo, tipo) 
                    VALUES (:emp_id, :cat_id, :nome, :desc, :preco, :promo, :img, :ativo, :visivel, :controle_estoque, :precisa_preparo, :tipo)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'emp_id' => $dados['empresa_id'],
                'cat_id' => $dados['categoria_id'],
                'nome' => $dados['nome'],
                'desc' => $dados['descricao'],
                'preco' => $valorBase,
                'promo' => $valorPromo,
                'img' => $dados['imagem_url'] ?? '',
                'ativo' => $dados['ativo'],
                'visivel' => $dados['visivel_online'],
                'controle_estoque' => $dados['controle_estoque'],
                'precisa_preparo' => $dados['precisa_preparo'],
                'tipo' => $dados['tipo'] ?? 'simples'
            ]);
            $id = $db->lastInsertId(); 
        }

        if (isset($dados['disponibilidade'])) {
            $this->salvarDisponibilidade($id, $dados['disponibilidade']);
        }

        if (isset($dados['complementos'])) {
            $this->salvarComplementos($id, $dados['complementos']);
        }

        return $id;
    }

    private function tratarPreco($valor) {
        if (empty($valor)) return 0;
        $v = is_array($valor) ? ($valor[0] ?? 0) : $valor;
        $v = str_replace(['.', ','], ['', '.'], $v);
        return is_numeric($v) ? $v : 0;
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

    private function salvarComplementos($produtoId, $gruposAtivos) {
        $db = Database::connect();
        $db->prepare("DELETE FROM produto_complementos WHERE produto_id = ?")->execute([$produtoId]);

        if (!empty($gruposAtivos) && is_array($gruposAtivos)) {
            $sql = "INSERT INTO produto_complementos (produto_id, grupo_id, ativo) VALUES (?, ?, 1)";
            $stmt = $db->prepare($sql);
            foreach ($gruposAtivos as $grupoId) {
                $stmt->execute([$produtoId, $grupoId]);
            }
        }
    }

    public function getComplementosIds($produtoId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT grupo_id FROM produto_complementos WHERE produto_id = ?");
        $stmt->execute([$produtoId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN); 
    }

    public function listarTodosGrupos($empresaId) {
        $db = Database::connect();
        $sql = "SELECT g.*, 
                GROUP_CONCAT(o.nome SEPARATOR ', ') as lista_itens
                FROM grupos_adicionais g
                LEFT JOIN opcionais o ON g.id = o.grupo_id
                WHERE g.empresa_id = ?
                GROUP BY g.id
                ORDER BY g.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir($id, $empresaId) {
        $db = Database::connect();
        return $db->prepare("UPDATE produtos SET ativo = 0 WHERE id = :id AND empresa_id = :emp_id")->execute(['id' => $id, 'emp_id' => $empresaId]);
    }

    public function buscarPorId($id, $empresaId = null) {
        $db = Database::connect();
        $sql = "SELECT p.*, COALESCE(e.quantidade, 0) as estoque_atual 
                FROM produtos p 
                LEFT JOIN estoque_filial e ON p.id = e.produto_id
                WHERE p.id = :id";
        $params = ['id' => $id];
        if($empresaId) {
            $sql .= " AND p.empresa_id = :emp_id";
            $params['emp_id'] = $empresaId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if($prod) {
            $prod['disponibilidade'] = $this->getDisponibilidade($prod['id']);
            $prod['complementos'] = $this->getComplementosIds($prod['id']);
        }
        return $prod;
    }

    public function atualizarEstoque($produtoId, $novaQtd, $filialId, $usuarioId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT quantidade FROM estoque_filial WHERE produto_id = ? AND filial_id = ?");
        $stmt->execute([$produtoId, $filialId]);
        $atual = $stmt->fetchColumn();
        
        if ($atual === false) $atual = 0;
        if (floatval($atual) == floatval($novaQtd)) return;

        $check = $db->prepare("SELECT id FROM estoque_filial WHERE produto_id = ? AND filial_id = ?");
        $check->execute([$produtoId, $filialId]);
        
        if ($check->fetch()) {
            $sqlUpd = "UPDATE estoque_filial SET quantidade = :qtd WHERE produto_id = :pid AND filial_id = :fid";
        } else {
            $sqlUpd = "INSERT INTO estoque_filial (produto_id, filial_id, quantidade) VALUES (:pid, :fid, :qtd)";
        }
        $db->prepare($sqlUpd)->execute(['qtd' => $novaQtd, 'pid' => $produtoId, 'fid' => $filialId]);

        $diferenca = $novaQtd - $atual;
        $sqlLog = "INSERT INTO estoque_logs (filial_id, produto_id, usuario_id, qtd_anterior, qtd_nova, diferenca, motivo) 
                   VALUES (:fid, :pid, :uid, :ant, :nov, :dif, 'Alteração no Cadastro')";
        $db->prepare($sqlLog)->execute(['fid' => $filialId, 'pid' => $produtoId, 'uid' => $usuarioId, 'ant' => $atual, 'nov' => $novaQtd, 'dif' => $diferenca]);
    }
}