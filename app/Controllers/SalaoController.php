<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Promocao;

class SalaoController {

    // =========================================================================
    // TELA 1: MAPA DE MESAS
    // =========================================================================
    public function index() {
        $this->verificarLogin();
        $db = Database::connect();
        $empresaId = $_SESSION['empresa_id'];

        // Busca Filial
        $stmtFilial = $db->prepare("SELECT id FROM filiais WHERE empresa_id = ? LIMIT 1");
        $stmtFilial->execute([$empresaId]);
        $filial = $stmtFilial->fetch();
        $filialId = $filial['id'];

        // Lista Mesas com Status
        $sql = "SELECT m.*, 
                       s.id as sessao_id, 
                       s.status as status_sessao, 
                       s.tipo_divisao, 
                       s.created_at as data_abertura, 
                       (SELECT COUNT(*) FROM mesa_participantes WHERE sessao_id = s.id) as qtd_pessoas,
                       (SELECT SUM(valor_total) FROM pedidos WHERE sessao_id = s.id AND status != 'cancelado') as total_consumido
                FROM mesas m
                LEFT JOIN mesa_sessoes s ON s.mesa_id = m.id AND s.status != 'encerrada'
                WHERE m.filial_id = ?
                ORDER BY CAST(REGEXP_REPLACE(m.numero, '[^0-9]', '') AS UNSIGNED), m.numero";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$filialId]);
        $mesas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/salao/mapa.php';
    }

    public function imprimirConta() {
        $this->verificarLogin();
        $sessaoId = $_GET['id'] ?? 0;
        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();
        
        // 1. Dados Gerais
        $stmt = $db->prepare("SELECT m.numero, e.nome_fantasia 
                              FROM mesa_sessoes s 
                              JOIN mesas m ON s.mesa_id = m.id 
                              JOIN empresas e ON m.filial_id = (SELECT id FROM filiais WHERE empresa_id = e.id LIMIT 1)
                              WHERE s.id = ?");
        $stmt->execute([$sessaoId]);
        $mesa = $stmt->fetch(\PDO::FETCH_ASSOC);
        $empresa = ['nome_fantasia' => $mesa['nome_fantasia']];

        // 2. Busca Participantes
        $stmtP = $db->prepare("SELECT id, nome, status_pagamento FROM mesa_participantes WHERE sessao_id = ? ORDER BY nome ASC");
        $stmtP->execute([$sessaoId]);
        $participantes = $stmtP->fetchAll(\PDO::FETCH_ASSOC);

        $dadosParticipantes = [];
        $totalGeral = 0;

        // 3. Monta Itens
        foreach($participantes as $p) {
            $sqlItens = "SELECT pi.quantidade, pi.total, p.nome, pi.id, pi.preco_unitario
                         FROM pedido_itens pi
                         JOIN pedidos ped ON pi.pedido_id = ped.id
                         LEFT JOIN produtos p ON pi.produto_id = p.id
                         WHERE ped.sessao_id = ? AND ped.participante_id = ? 
                         AND ped.status != 'cancelado' 
                         AND pi.status_item != 'cancelado'"; // <--- FILTRO IMPORTANTE
            
            $stmtI = $db->prepare($sqlItens);
            $stmtI->execute([$sessaoId, $p['id']]);
            $itensBrutos = $stmtI->fetchAll(\PDO::FETCH_ASSOC);

            // Agrupa...
            $itensAgrupados = [];
            $subtotal = 0;

            foreach($itensBrutos as $item) {
                $stmtAdd = $db->prepare("SELECT nome, preco FROM pedido_item_complementos WHERE pedido_item_id = ?");
                $stmtAdd->execute([$item['id']]);
                $adds = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC);
                
                $nomesAdd = []; $valAdd = 0;
                foreach($adds as $a) { $nomesAdd[] = $a['nome']; $valAdd += $a['preco']; }
                
                $chave = $item['nome'] . '|' . implode(',', $nomesAdd);
                $totalItem = $item['total'] + ($valAdd * $item['quantidade']);

                if(!isset($itensAgrupados[$chave])) {
                    $itensAgrupados[$chave] = ['nome' => $item['nome'], 'qtd' => 0, 'total' => 0, 'adds' => $nomesAdd];
                }
                $itensAgrupados[$chave]['qtd'] += $item['quantidade'];
                $itensAgrupados[$chave]['total'] += $totalItem;
                
                $subtotal += $totalItem;
                $totalGeral += $totalItem;
            }

            $dadosParticipantes[] = [
                'nome' => $p['nome'],
                'status_pagamento' => $p['status_pagamento'],
                'itens' => array_values($itensAgrupados),
                'subtotal' => $subtotal
            ];
        }

        require __DIR__ . '/../Views/admin/salao/cupom_mesa.php';
    }
    // =========================================================================
    // AÇÃO: PAGAR CONTA INDIVIDUAL (PARTICIPANTE)
    // =========================================================================
    public function pagarParticipante() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        $participanteId = $_POST['participante_id'];
        $formaPagamento = $_POST['forma_pagamento'];
        $valorPago = str_replace(',', '.', $_POST['valor']); // Valor calculado no front
        $empresaId = $_SESSION['empresa_id'];

        $db = Database::connect();
        try {
            $db->beginTransaction();

            // 1. Busca nome para o histórico
            $stmtP = $db->prepare("SELECT nome, sessao_id FROM mesa_participantes WHERE id = ?");
            $stmtP->execute([$participanteId]);
            $part = $stmtP->fetch();

            // 2. Lança no Financeiro
            $sqlFin = "INSERT INTO contas_receber 
                (empresa_id, cliente_nome, valor, data_vencimento, status, forma_pagamento, categoria, created_at)
                VALUES (?, ?, ?, CURDATE(), 'pago', ?, 'Venda Parcial', NOW())";
            
            $desc = "Parcial Mesa - " . $part['nome'];
            $db->prepare($sqlFin)->execute([$empresaId, $desc, $valorPago, $formaPagamento]);

            // 3. Marca Participante como PAGO
            $db->prepare("UPDATE mesa_participantes SET status_pagamento = 'pago' WHERE id = ?")
               ->execute([$participanteId]);

            $db->commit();
            echo json_encode(['ok' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    // =========================================================================
    // TELA 2: DETALHES DA MESA (COM CARDÁPIO PDV)
    // =========================================================================
    public function detalhes() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? 0;
        $db = Database::connect();
        $empresaId = $_SESSION['empresa_id'];
        
        $stmt = $db->prepare("SELECT m.numero as num_mesa, m.id as mesa_id, s.*, s.created_at as data_abertura 
                              FROM mesas m JOIN mesa_sessoes s ON s.mesa_id = m.id 
                              WHERE m.id = ? AND s.status != 'encerrada'");
        $stmt->execute([$id]);
        $sessao = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$sessao) { header('Location: ' . BASE_URL . '/admin/salao'); exit; }

        $stmtP = $db->prepare("SELECT * FROM mesa_participantes WHERE sessao_id = ? ORDER BY is_lider DESC, nome ASC");
        $stmtP->execute([$sessao['id']]);
        $participantes = $stmtP->fetchAll(\PDO::FETCH_ASSOC);

        $itensPorPessoa = [];
        $totalGeral = 0;

        foreach($participantes as $p) {
            $sqlItens = "SELECT pi.*, p.nome as produto_nome, ped.status as status_pedido
                         FROM pedido_itens pi
                         JOIN pedidos ped ON pi.pedido_id = ped.id
                         LEFT JOIN produtos p ON pi.produto_id = p.id
                         WHERE ped.participante_id = ? AND ped.sessao_id = ? AND ped.status != 'cancelado'
                         ORDER BY pi.id DESC";
            
            $stmtI = $db->prepare($sqlItens);
            $stmtI->execute([$p['id'], $sessao['id']]);
            $itensBrutos = $stmtI->fetchAll(\PDO::FETCH_ASSOC);
            
            $itensAgrupados = [];

            foreach($itensBrutos as $item) {
                $stmtAdd = $db->prepare("SELECT nome, preco FROM pedido_item_complementos WHERE pedido_item_id = ?");
                $stmtAdd->execute([$item['id']]);
                $adds = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC);
                
                $nomesAdd = array_map(function($a) { return $a['nome']; }, $adds);
                sort($nomesAdd); 
                $valorAdds = 0;
                foreach($adds as $a) $valorAdds += $a['preco'];

                $chave = $item['produto_id'] . '|' . trim($item['observacao_item']) . '|' . implode(',', $nomesAdd) . '|' . $item['status_item'];

                $totalItemBruto = $item['total'] + ($valorAdds * $item['quantidade']); 

                // SE CANCELADO, ZERA O VALOR PARA NÃO SOMAR
                if ($item['status_item'] == 'cancelado') {
                    $totalItemBruto = 0;
                }

                if(isset($itensAgrupados[$chave])) {
                    $itensAgrupados[$chave]['quantidade'] += $item['quantidade'];
                    $itensAgrupados[$chave]['total_final'] += $totalItemBruto;
                } else {
                    $itensAgrupados[$chave] = [
                        'id' => $item['id'],
                        'produto_nome' => $item['produto_nome'],
                        'quantidade' => $item['quantidade'],
                        'total_final' => $totalItemBruto,
                        'observacao_item' => $item['observacao_item'],
                        'status_item' => $item['status_item'], 
                        'complementos_ja_pedidos' => $nomesAdd
                    ];
                }
                $totalGeral += $totalItemBruto;
            }
            $itensPorPessoa[$p['id']] = array_values($itensAgrupados);
        }

        // ... Resto da função (busca produtos, etc) mantem igual ...
        $prodModel = new Produto();
        $todosProdutos = $prodModel->listar($empresaId);
        $stmtTemAdd = $db->prepare("SELECT DISTINCT produto_id FROM produto_complementos WHERE ativo = 1");
        $stmtTemAdd->execute();
        $idsComAdicionais = $stmtTemAdd->fetchAll(\PDO::FETCH_COLUMN);
        
        $produtos = [];
        $stmtEst = $db->prepare("SELECT quantidade FROM estoque_filial WHERE produto_id = ? LIMIT 1");
        foreach($todosProdutos as $p) {
            $p['tem_adicionais'] = in_array($p['id'], $idsComAdicionais) ? 1 : 0;
            $stmtEst->execute([$p['id']]);
            $est = $stmtEst->fetch(\PDO::FETCH_ASSOC);
            $p['estoque_atual'] = ($est) ? $est['quantidade'] : 0;
            $produtos[] = $p;
        }
        $catModel = new Categoria();
        $categorias = $catModel->listar($empresaId);

        require __DIR__ . '/../Views/admin/salao/detalhes.php';
    }


    // --- NOVO: ABRIR MESA MANUALMENTE PELO ADMIN ---
   // --- 1. ABRIR MESA MANUALMENTE ---
    public function abrirMesaManual() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        $mesaId = $_POST['mesa_id'];
        $nomeCliente = $_POST['nome_cliente'] ?: 'Cliente Balcão';
        
        $db = Database::connect();
        try {
            $db->beginTransaction();
            
            // Cria Sessão
            $db->prepare("INSERT INTO mesa_sessoes (mesa_id, status, tipo_divisao) VALUES (?, 'aberta', 'unica')")
               ->execute([$mesaId]);
            $sessaoId = $db->lastInsertId();
            
            // Ocupa a Mesa
            $db->prepare("UPDATE mesas SET status_atual = 'ocupada' WHERE id = ?")->execute([$mesaId]);
            
            // Cria Participante Líder
            $db->prepare("INSERT INTO mesa_participantes (sessao_id, nome, is_lider, status_pagamento) VALUES (?, ?, 1, 'pendente')")
               ->execute([$sessaoId, $nomeCliente]);
               
            $db->commit();
            echo json_encode(['ok' => true]);
            
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    // --- 2. IMPRIMIR CONFERÊNCIA (ESTILO CUPOM) ---
   // --- IMPRIMIR PARA COZINHA (COMANDA DE PRODUÇÃO) ---
    public function imprimirConferencia() {
        $this->verificarLogin();
        $sessaoId = $_GET['id'] ?? 0;
        $empresaId = $_SESSION['empresa_id'];
        
        $db = Database::connect();
        
        // 1. Busca Dados da Mesa
        $stmt = $db->prepare("SELECT m.numero, s.created_at, m.id as mesa_id
                              FROM mesa_sessoes s 
                              JOIN mesas m ON s.mesa_id = m.id 
                              WHERE s.id = ?");
        $stmt->execute([$sessaoId]);
        $mesa = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if(!$mesa) die("Mesa não encontrada.");

        // 2. Busca Dados da Loja (CORRIGIDO: Busca telefone na tabela filiais)
        $stmtEmp = $db->prepare("SELECT e.nome_fantasia, f.telefone_whatsapp 
                                 FROM empresas e 
                                 LEFT JOIN filiais f ON f.empresa_id = e.id 
                                 WHERE e.id = ? LIMIT 1");
        $stmtEmp->execute([$empresaId]);
        $loja = $stmtEmp->fetch(\PDO::FETCH_ASSOC);

        // 3. Busca TODOS os itens ativos (sem agrupar no SQL para não perder obs)
        $sqlItens = "SELECT pi.id, pi.produto_id, pi.quantidade, pi.observacao_item, p.nome
                     FROM pedido_itens pi
                     JOIN pedidos ped ON pi.pedido_id = ped.id
                     LEFT JOIN produtos p ON pi.produto_id = p.id
                     WHERE ped.sessao_id = ? AND ped.status != 'cancelado'
                     ORDER BY pi.id ASC"; // Ordem de chegada
        
        $stmtI = $db->prepare($sqlItens);
        $stmtI->execute([$sessaoId]);
        $itensBrutos = $stmtI->fetchAll(\PDO::FETCH_ASSOC);

        // 4. Agrupamento Inteligente (PHP)
        $itensAgrupados = [];

        foreach ($itensBrutos as $item) {
            // Busca Complementos
            $stmtAdd = $db->prepare("SELECT nome FROM pedido_item_complementos WHERE pedido_item_id = ? ORDER BY nome ASC");
            $stmtAdd->execute([$item['id']]);
            $adicionais = $stmtAdd->fetchAll(\PDO::FETCH_COLUMN);

            $obsLimpa = trim($item['observacao_item'] ?? '');
            $addsLimpa = implode(' + ', $adicionais);
            
            // Chave única: Se mudar Obs ou Adicional, cria nova linha
            $chave = $item['produto_id'] . '|' . $obsLimpa . '|' . $addsLimpa;

            if (!isset($itensAgrupados[$chave])) {
                $itensAgrupados[$chave] = [
                    'nome' => $item['nome'],
                    'qtd' => 0,
                    'observacao' => $obsLimpa,
                    'complementos' => $adicionais
                ];
            }

            $itensAgrupados[$chave]['qtd'] += $item['quantidade'];
        }

        // Converte para lista simples
        $itens = array_values($itensAgrupados);

        require __DIR__ . '/../Views/admin/salao/cupom_mesa.php';
    }

    

    // =========================================================================
    // AÇÃO: ADICIONAR ITEM NA MESA (Salva pedido)
    // =========================================================================
   public function adicionarPedidoMesa() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        try {
            $sessaoId = $_POST['sessao_id'];
            $participanteId = $_POST['participante_id'];
            $itens = json_decode($_POST['itens'], true);
            $empresaId = $_SESSION['empresa_id'];

            if(empty($itens)) throw new \Exception("Nenhum item selecionado.");

            $db = Database::connect();
            
            // --- TRAVA DE SEGURANÇA ---
            // Verifica se o participante já pagou a conta
            $stmtStatus = $db->prepare("SELECT status_pagamento, nome FROM mesa_participantes WHERE id = ?");
            $stmtStatus->execute([$participanteId]);
            $part = $stmtStatus->fetch(\PDO::FETCH_ASSOC);

            if ($part && $part['status_pagamento'] == 'pago') {
                throw new \Exception("A conta de {$part['nome']} já foi encerrada. Reabra a comanda para adicionar novos itens.");
            }
            // ---------------------------

            $db->beginTransaction();

            // ... (O RESTANTE DO CÓDIGO CONTINUA IGUAL AO ANTERIOR) ...
            // Copie o restante da função adicionarPedidoMesa que te passei antes daqui para baixo
            
            // 1. Descobre dados da Sessão...
            $stmtSessao = $db->prepare("SELECT s.tipo_divisao, m.numero FROM mesa_sessoes s JOIN mesas m ON s.mesa_id = m.id WHERE s.id = ?");
            $stmtSessao->execute([$sessaoId]);
            $sessao = $stmtSessao->fetch(\PDO::FETCH_ASSOC);

            // 2. Busca Pedido Existente...
            $sqlBusca = "SELECT id, valor_total, valor_produtos FROM pedidos WHERE sessao_id = ? AND status NOT IN ('finalizado', 'cancelado') AND empresa_id = ?";
            $paramsBusca = [$sessaoId, $empresaId];

            if ($sessao['tipo_divisao'] == 'individual') {
                $sqlBusca .= " AND participante_id = ?";
                $paramsBusca[] = $participanteId;
            } else {
                $sqlBusca .= " LIMIT 1";
            }

            $stmtPed = $db->prepare($sqlBusca);
            $stmtPed->execute($paramsBusca);
            $pedidoExistente = $stmtPed->fetch(\PDO::FETCH_ASSOC);

            // 3. Calcula totais...
            $valorNovosItens = 0;
            foreach($itens as $item) { $valorNovosItens += ($item['preco'] * $item['qtd']); }

            if ($pedidoExistente) {
                $pedidoId = $pedidoExistente['id'];
                $novoTotal = $pedidoExistente['valor_total'] + $valorNovosItens;
                $novoTotalProd = $pedidoExistente['valor_produtos'] + $valorNovosItens;
                $db->prepare("UPDATE pedidos SET valor_total = ?, valor_produtos = ?, impresso = 0 WHERE id = ?")->execute([$novoTotal, $novoTotalProd, $pedidoId]);
            } else {
                $stmtPart = $db->prepare("SELECT nome, telefone FROM mesa_participantes WHERE id = ?");
                $stmtPart->execute([$participanteId]);
                $part = $stmtPart->fetch(\PDO::FETCH_ASSOC);
                $nomeCliente = $sessao['tipo_divisao'] == 'unica' ? "Mesa " . $sessao['numero'] . " (" . $part['nome'] . ")" : $part['nome'];

                $db->prepare("INSERT INTO pedidos (empresa_id, cliente_nome, cliente_telefone, tipo_entrega, endereco_entrega, taxa_entrega, valor_produtos, valor_total, forma_pagamento, sessao_id, participante_id, status, impresso, created_at) VALUES (?, ?, ?, 'salao', 'Mesa', 0, ?, ?, 'dinheiro', ?, ?, 'analise', 0, NOW())")
                   ->execute([$empresaId, $nomeCliente, $part['telefone']??'', $valorNovosItens, $valorNovosItens, $sessaoId, $participanteId]);
                $pedidoId = $db->lastInsertId();
            }

            foreach($itens as $item) {
                $totalItem = ($item['preco'] * $item['qtd']);
                $db->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, total, observacao_item, status_item) VALUES (?, ?, ?, ?, ?, ?, 'fila')")
                   ->execute([$pedidoId, $item['id'], $item['qtd'], $item['preco'], $totalItem, $item['obs'] ?? '']);
                $itemId = $db->lastInsertId();
                if (!empty($item['adicionais'])) {
                    foreach($item['adicionais'] as $add) {
                        $db->prepare("INSERT INTO pedido_item_complementos (pedido_item_id, complemento_id, nome, preco) VALUES (?, ?, ?, ?)")->execute([$itemId, $add['id'], $add['nome'], $add['preco']]);
                    }
                }
            }

            $db->commit();
            echo json_encode(['ok' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    // =========================================================================
    // AÇÃO: REABRIR COMANDA (Permitir novos pedidos)
    // =========================================================================
    public function reabrirParticipante() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        $id = $_POST['participante_id'];
        
        $db = Database::connect();
        $db->prepare("UPDATE mesa_participantes SET status_pagamento = 'pendente' WHERE id = ?")->execute([$id]);
        
        echo json_encode(['ok' => true]);
        exit;
    }

   public function getConsumoAjax() {
        header('Content-Type: application/json');
        
        try {
            $sessaoId = $_POST['sessao_id'] ?? 0;
            $participanteId = $_POST['participante_id'] ?? 0;
            
            if(!$sessaoId) throw new \Exception("Sessão inválida.");

            $db = Database::connect();

            // 1. Busca dados da sessão
            $stmtS = $db->prepare("SELECT tipo_divisao, status FROM mesa_sessoes WHERE id = ?");
            $stmtS->execute([$sessaoId]);
            $sessao = $stmtS->fetch(\PDO::FETCH_ASSOC);

            if(!$sessao) throw new \Exception("Mesa não encontrada.");

            // 2. BUSCA TUDO
            $sql = "SELECT pi.*, p.nome as produto_nome, p.imagem_url, ped.participante_id, part.nome as nome_participante
                    FROM pedido_itens pi
                    JOIN pedidos ped ON pi.pedido_id = ped.id
                    LEFT JOIN mesa_participantes part ON ped.participante_id = part.id
                    LEFT JOIN produtos p ON pi.produto_id = p.id
                    WHERE ped.sessao_id = ? 
                    AND ped.status != 'cancelado'
                    ORDER BY pi.id DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$sessaoId]);
            $itensRaw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 3. Processa e Marca 'is_me'
            $itensFormatados = [];
            $totalMesa = 0;
            $totalUsuario = 0;

            foreach($itensRaw as $item) {
                // Busca complementos
                $stmtAdd = $db->prepare("SELECT nome, preco FROM pedido_item_complementos WHERE pedido_item_id = ?");
                $stmtAdd->execute([$item['id']]);
                $adicionais = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC);

                $valorAdds = 0;
                foreach($adicionais as $add) {
                    $valorAdds += ($add['preco'] * $item['quantidade']);
                }
                
                $totalItemReal = (float)$item['total'] + $valorAdds;
                
                $isMe = ($item['participante_id'] == $participanteId);
                
                // --- CORREÇÃO AQUI: NÃO SOMA SE TIVER CANCELADO ---
                if ($item['status_item'] != 'cancelado') {
                    $totalMesa += $totalItemReal;
                    if($isMe) $totalUsuario += $totalItemReal;
                }
                // --------------------------------------------------

                $itensFormatados[] = [
                    'id' => $item['id'],
                    'nome' => $item['produto_nome'],
                    'quem_pediu' => $item['nome_participante'] ?: 'Alguém',
                    'is_me' => $isMe,
                    'qtd' => (int)$item['quantidade'],
                    'preco_unitario' => (float)$item['preco_unitario'],
                    'total' => $totalItemReal,
                    'obs' => $item['observacao_item'],
                    'status' => $item['status_item'], // Envia o status para o JS pintar de vermelho
                    'adicionais' => $adicionais
                ];
            }

            echo json_encode([
                'ok' => true, 
                'itens' => $itensFormatados, 
                'total_mesa' => $totalMesa,
                'total_usuario' => $totalUsuario,
                'tipo_divisao' => $sessao['tipo_divisao'],
                'meu_status_pagamento' => '' // Se precisar verificar se eu já paguei
            ]);

        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
    
    // =========================================================================
    // AÇÃO: MUDAR STATUS DO ITEM (Entregar na mesa)
    // =========================================================================
    public function mudarStatusItem() {
        $this->verificarLogin();
        
        $itemId = $_POST['item_id'];
        $status = $_POST['status']; 
        
        $db = Database::connect();
        
        // 1. Atualiza Status
        $db->prepare("UPDATE pedido_itens SET status_item = ? WHERE id = ?")->execute([$status, $itemId]);
        
        // 2. Recalcula Valor Total do Pedido Pai (EXCLUINDO CANCELADOS)
        $stmtP = $db->prepare("SELECT pedido_id FROM pedido_itens WHERE id = ?");
        $stmtP->execute([$itemId]);
        $pedidoId = $stmtP->fetchColumn();
        
        if ($pedidoId) {
            $stmtSoma = $db->prepare("SELECT SUM(total) FROM pedido_itens WHERE pedido_id = ? AND status_item != 'cancelado'");
            $stmtSoma->execute([$pedidoId]);
            $novoTotal = $stmtSoma->fetchColumn() ?: 0.00;
            
            // Atualiza o pedido
            $db->prepare("UPDATE pedidos SET valor_total = ?, valor_produtos = ? WHERE id = ?")
               ->execute([$novoTotal, $novoTotal, $pedidoId]);
        }
        
        echo json_encode(['ok' => true]);
        exit;
    }

    // =========================================================================
    // AÇÃO: ENCERRAR MESA E GERAR FINANCEIRO
    // =========================================================================
    // AÇÃO: ENCERRAR MESA E GERAR FINANCEIRO
    public function encerrarMesa() {
        $this->verificarLogin();
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $sessaoId = $_POST['sessao_id'];
        $formaPagamento = $_POST['forma_pagamento'] ?? 'Dinheiro';
        $desconto = floatval($_POST['desconto'] ?? 0);
        $valorPago = floatval($_POST['valor_pago'] ?? 0); // O que entrou no caixa
        $troco = floatval($_POST['troco'] ?? 0);
        $empresaId = $_SESSION['empresa_id'];

        $db = Database::connect();
        
        try {
            $db->beginTransaction();
            
            // 1. Busca e Recalcula Total
            $stmt = $db->prepare("SELECT s.*, m.numero FROM mesa_sessoes s JOIN mesas m ON s.mesa_id = m.id WHERE s.id = ?");
            $stmt->execute([$sessaoId]);
            $sessao = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Busca total dos itens ativos
            $sqlTotal = "SELECT SUM(pi.total) + 
                        (SELECT COALESCE(SUM(pic.preco), 0) FROM pedido_item_complementos pic 
                         JOIN pedido_itens pi2 ON pic.pedido_item_id = pi2.id 
                         JOIN pedidos p2 ON pi2.pedido_id = p2.id 
                         WHERE p2.sessao_id = ? AND p2.status != 'cancelado') 
                         as total_geral
                         FROM pedido_itens pi
                         JOIN pedidos p ON pi.pedido_id = p.id
                         WHERE p.sessao_id = ? AND p.status != 'cancelado'";
            
            // Simplificando: Vamos confiar no 'valor_total' dos pedidos se estiver atualizado, 
            // mas o ideal é recalcular. Vamos usar o valor enviado pelo front ou recalcular simples:
            $stmtTotal = $db->prepare("SELECT SUM(valor_total) FROM pedidos WHERE sessao_id = ? AND status != 'cancelado'");
            $stmtTotal->execute([$sessaoId]);
            $totalBruto = $stmtTotal->fetchColumn() ?: 0.00;

            $totalLiquido = $totalBruto - $desconto;

            if ($totalLiquido > 0) {
                // 2. GERA O FINANCEIRO
                $sqlFin = "INSERT INTO contas_receber 
                    (empresa_id, cliente_nome, valor, data_vencimento, status, forma_pagamento, categoria, observacoes, created_at)
                    VALUES (?, ?, ?, CURDATE(), 'pago', ?, 'Venda', ?, NOW())";
                
                $obs = "Mesa " . $sessao['numero'];
                if($desconto > 0) $obs .= " | Desconto: R$ " . number_format($desconto, 2);
                if($troco > 0) $obs .= " | Troco: R$ " . number_format($troco, 2);
                
                // O valor que entra no financeiro é o VALOR PAGO real (sem o troco) ou o Total Líquido?
                // Geralmente lança-se o valor da venda (Liquido). O caixa físico que lida com o troco.
                $db->prepare($sqlFin)->execute([
                    $empresaId,
                    "Mesa " . $sessao['numero'],
                    $totalLiquido,
                    $formaPagamento,
                    $obs
                ]);
            }
            
            // 3. Fecha a Sessão
            $db->prepare("UPDATE mesa_sessoes SET status = 'encerrada', encerrada_em = NOW(), total_consumido = ? WHERE id = ?")
               ->execute([$totalLiquido, $sessaoId]); // Salva o valor final cobrado
            
            // 4. Libera Mesa e Finaliza Pedidos
            $db->prepare("UPDATE mesas SET status_atual = 'livre' WHERE id = ?")->execute([$sessao['mesa_id']]);
            $db->prepare("UPDATE pedidos SET status = 'finalizado', desconto = ? WHERE sessao_id = ? AND status != 'cancelado'")
               ->execute([$desconto, $sessaoId]); // Rateio do desconto seria ideal, mas aqui aplicamos no update geral

            $db->commit();
            echo json_encode(['ok' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }

}