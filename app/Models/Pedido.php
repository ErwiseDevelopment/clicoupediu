<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class Pedido {

public function listarPorStatus($empresaId, $status, $data = null) {
        $db = Database::connect();
        
        $sql = "SELECT * FROM pedidos WHERE empresa_id = ? AND status = ?";
        $params = [$empresaId, $status];

        // Se uma data for passada, filtra pelo dia de criação
        if ($data) {
            $sql .= " AND DATE(created_at) = ?";
            $params[] = $data;
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function buscarPorTelefone($empresaId, $telefone) {
        $db = Database::connect();
        $telefone = preg_replace('/[^0-9]/', '', $telefone);

        $sql = "SELECT id, status, valor_total, created_at, tipo_entrega 
                FROM pedidos 
                WHERE empresa_id = ? AND cliente_telefone = ? 
                ORDER BY id DESC LIMIT 20";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId, $telefone]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarHistorico($empresaId, $dataInicio, $dataFim) {
        $db = Database::connect();
        
        // Ajusta as datas para pegar o dia inteiro (00:00:00 até 23:59:59)
        $inicio = $dataInicio . ' 00:00:00';
        $fim    = $dataFim . ' 23:59:59';

        $sql = "SELECT * FROM pedidos 
                WHERE empresa_id = :id 
                AND created_at >= :inicio 
                AND created_at <= :fim 
                ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id' => $empresaId,
            'inicio' => $inicio,
            'fim' => $fim
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


   public function criar($dados, $itens) {
        $db = Database::connect();
        
        try {
            $db->beginTransaction();

            // 1. Gerencia Cliente (Cria ou Atualiza)
            $clienteId = $this->gerenciarCliente($db, $dados);

            // 2. Insere Pedido
            $sqlPedido = "INSERT INTO pedidos (
                empresa_id, cliente_id, cliente_nome, cliente_telefone, 
                tipo_entrega, endereco_entrega, numero, bairro, complemento,
                taxa_entrega, valor_produtos, valor_total, 
                forma_pagamento, troco_para, lat_entrega, lng_entrega,
                status, created_at
            ) VALUES (
                :eid, :cid, :nome, :tel,
                :tipo, :end, :num, :bairro, :comp,
                :taxa, :vprod, :vtotal,
                :pgto, :troco, :lat, :lng,
                'analise', NOW()
            )";

            $stmt = $db->prepare($sqlPedido);
            $stmt->execute([
                'eid' => $dados['empresa_id'],
                'cid' => $clienteId,
                'nome' => $dados['cliente_nome'],
                'tel' => $dados['cliente_telefone'],
                'tipo' => $dados['tipo_entrega'],
                'end' => $dados['endereco'],
                'num' => $dados['numero'],
                'bairro' => $dados['bairro'],
                'comp' => $dados['complemento'],
                'taxa' => $dados['taxa_entrega'],
                'vprod' => $dados['valor_produtos'],
                'vtotal' => $dados['valor_total'],
                'pgto' => $dados['forma_pagamento'],
                'troco' => $dados['troco'] ?? 0.00, // Novo campo
                'lat' => $dados['lat_entrega'],
                'lng' => $dados['lng_entrega']
            ]);

            $pedidoId = $db->lastInsertId();

            // 3. Insere Itens e Complementos
            $this->inserirItens($db, $pedidoId, $itens, $dados['empresa_id']);

            $db->commit();
            return $pedidoId;

        } catch (\Exception $e) {
            $db->rollBack();
            throw new \Exception("Erro no banco de dados: " . $e->getMessage());
        }
    }

    public function atualizar($id, $dados, $itens) {
        $db = Database::connect();
        try {
            $db->beginTransaction();
            
            // Devolve estoque antes de apagar (agora devolve ingredientes se for combo)
            $this->estornarEstoque($db, $id, $dados['empresa_id']);
            
            $db->prepare("DELETE FROM pedido_itens WHERE pedido_id = ?")->execute([$id]);

            $clienteId = $this->gerenciarCliente($db, $dados);

            $sql = "UPDATE pedidos SET 
                    cliente_id = :cid, cliente_nome = :nome, cliente_telefone = :tel,
                    tipo_entrega = :tipo, endereco_entrega = :end, numero = :num, 
                    bairro = :bairro, complemento = :comp, taxa_entrega = :taxa,
                    desconto = :desc, valor_produtos = :vprod, valor_total = :vtotal,
                    forma_pagamento = :pgto, troco_para = :troco, 
                    lat_entrega = :lat, lng_entrega = :lng
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'cid' => $clienteId, 'nome' => $dados['cliente_nome'], 'tel' => $dados['cliente_telefone'],
                'tipo' => $dados['tipo_entrega'], 'end' => $dados['endereco'], 'num' => $dados['numero'],
                'bairro' => $dados['bairro'], 'comp' => $dados['complemento'], 'taxa' => $dados['taxa_entrega'],
                'desc' => $dados['desconto'], 'vprod' => $dados['valor_produtos'], 'vtotal' => $dados['valor_total'],
                'pgto' => $dados['forma_pagamento'], 'troco' => $dados['troco_para'],
                'lat' => $dados['lat_entrega'], 'lng' => $dados['lng_entrega'],
                'id' => $id
            ]);

            $this->inserirItens($db, $id, $itens, $dados['empresa_id']);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

   private function gerenciarCliente($db, $dados) {
        $tel = $dados['cliente_telefone'];
        if (empty($tel) || strlen($tel) < 8) return null;

        $stmt = $db->prepare("SELECT id FROM clientes WHERE empresa_id = ? AND telefone = ? LIMIT 1");
        $stmt->execute([$dados['empresa_id'], $tel]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $db->prepare("UPDATE clientes SET nome = ?, endereco_ultimo = ?, numero_ultimo = ?, bairro_ultimo = ? WHERE id = ?")
               ->execute([$dados['cliente_nome'], $dados['endereco'], $dados['numero'], $dados['bairro'], $cliente['id']]);
            return $cliente['id'];
        } else {
            $db->prepare("INSERT INTO clientes (empresa_id, nome, telefone, endereco_ultimo, numero_ultimo, bairro_ultimo) VALUES (?, ?, ?, ?, ?, ?)")
               ->execute([$dados['empresa_id'], $dados['cliente_nome'], $tel, $dados['endereco'], $dados['numero'], $dados['bairro']]);
            return $db->lastInsertId();
        }
    }

    // --- FUNÇÃO DE INSERIR COM BAIXA INTELIGENTE DE ESTOQUE ---
    private function inserirItens($db, $pedidoId, $itens, $empresaId) {
        $stmtItem = $db->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, total, observacao_item) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Query para salvar complementos na nova tabela
        $stmtAdd = $db->prepare("INSERT INTO pedido_item_complementos (pedido_item_id, complemento_id, nome, preco) VALUES (?, ?, ?, ?)");

        // Queries de estoque
        $stmtIngredientes = $db->prepare("SELECT item_id, quantidade FROM produto_combos WHERE produto_pai_id = ?");
        $stmtEst = $db->prepare("UPDATE estoque_filial SET quantidade = quantidade - ? WHERE produto_id = ? AND filial_id = ?");

        foreach ($itens as $item) {
            $obs = $item['observacao'] ?? null;
            $precoUnitario = $item['preco']; // Preço unitário já com adicionais (conforme enviado pelo front)
            
            // 1. Salva Item
            $stmtItem->execute([
                $pedidoId, 
                $item['id'], 
                $item['qtd'], 
                $precoUnitario, 
                $precoUnitario * $item['qtd'],
                $obs
            ]);
            $itemId = $db->lastInsertId();

            // 2. Salva Complementos (Se houver e se a tabela existir)
            if (!empty($item['adicionais']) && is_array($item['adicionais'])) {
                foreach ($item['adicionais'] as $add) {
                    try {
                        $stmtAdd->execute([
                            $itemId,
                            $add['id'], // ID do opcional
                            $add['nome'],
                            $add['preco']
                        ]);
                    } catch (\Exception $e) {
                        // Ignora erro se tabela de complementos não existir ainda
                    }
                }
            }

            // 3. Baixa Estoque (Combo ou Simples)
            $stmtIngredientes->execute([$item['id']]);
            $ingredientes = $stmtIngredientes->fetchAll(PDO::FETCH_ASSOC);

            if (count($ingredientes) > 0) {
                foreach ($ingredientes as $ing) {
                    $qtdBaixa = $item['qtd'] * $ing['quantidade'];
                    $stmtEst->execute([$qtdBaixa, $ing['item_id'], $empresaId]);
                }
            } else {
                $stmtEst->execute([$item['qtd'], $item['id'], $empresaId]);
            }
        }
    }

    // --- FUNÇÃO DE ESTORNO COM DEVOLUÇÃO INTELIGENTE DE ESTOQUE ---
    private function estornarEstoque($db, $pedidoId, $empresaId) {
        // Pega os itens do pedido que será excluído/editado
        $stmt = $db->prepare("SELECT produto_id, quantidade FROM pedido_itens WHERE pedido_id = ?");
        $stmt->execute([$pedidoId]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtIngredientes = $db->prepare("SELECT item_id, quantidade FROM produto_combos WHERE produto_pai_id = ?");
        $stmtDevolve = $db->prepare("UPDATE estoque_filial SET quantidade = quantidade + ? WHERE produto_id = ? AND filial_id = ?");

        foreach($itens as $i) {
            // Verifica se o item devolvido é um combo
            $stmtIngredientes->execute([$i['produto_id']]);
            $ingredientes = $stmtIngredientes->fetchAll(PDO::FETCH_ASSOC);

            if (count($ingredientes) > 0) {
                // DEVOLVE INGREDIENTES DO COMBO
                foreach ($ingredientes as $ing) {
                    $qtdDevolve = $i['quantidade'] * $ing['quantidade'];
                    $stmtDevolve->execute([$qtdDevolve, $ing['item_id'], $empresaId]);
                }
            } else {
                // DEVOLVE PRODUTO SIMPLES
                $stmtDevolve->execute([$i['quantidade'], $i['produto_id'], $empresaId]);
            }
        }
    }

    public function atualizarStatus($id, $status) {
        $db = Database::connect();
        $db->prepare("UPDATE pedidos SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
    
    public function excluir($id) {
         $db = Database::connect();
         // Estorna estoque corretamente antes de excluir
         $this->estornarEstoque($db, $id, $_SESSION['empresa_id']);
         $db->prepare("DELETE FROM pedido_itens WHERE pedido_id = ?")->execute([$id]);
         $db->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$id]);
    }
}