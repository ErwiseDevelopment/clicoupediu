<?php
namespace App\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Categoria;
use App\Core\Database;
use App\Models\Promocao;
use App\Models\Cardapio;

class PedidoController {

    // 1. TELA PRINCIPAL (Monitor KDS + PDV)
    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $dataFiltro = $_GET['data'] ?? date('Y-m-d');

        $db = \App\Core\Database::connect();
        
        // --- DADOS DA EMPRESA (CABEÇALHO) ---
        $stmtEmp = $db->prepare("SELECT chave_pix, nome_fantasia, endereco_completo FROM empresas WHERE id = ? LIMIT 1");
        $stmtEmp->execute([$empresaId]);
        $empresaDados = $stmtEmp->fetch(\PDO::FETCH_ASSOC);

        $chavePixLoja = $empresaDados['chave_pix'] ?? '';
        $nomeLojaPix  = $this->limparStringPix($empresaDados['nome_fantasia'] ?? 'LOJA', 25);
        
        $cidadeLojaPix = 'CIDADE'; 
        if (!empty($empresaDados['endereco_completo'])) {
            $partes = explode('-', $empresaDados['endereco_completo']);
            $ultimaParte = end($partes); 
            $cidadeLojaPix = $this->limparStringPix(count($partes) > 1 && strlen(trim($ultimaParte)) <= 2 ? prev($partes) : $ultimaParte, 15);
        }

        // --- LISTAGEM DE PEDIDOS (KANBAN) ---
        $model = new Pedido();
        $analise = $model->listarPorStatus($empresaId, 'analise', $dataFiltro);
        $preparo = $model->listarPorStatus($empresaId, 'preparo', $dataFiltro);
        $entrega = $model->listarPorStatus($empresaId, 'entrega', $dataFiltro);
        $finalizados = $model->listarPorStatus($empresaId, 'finalizado', $dataFiltro);

        // --- BUSCA PRODUTOS E COMBOS ---
        $prodModel = new Produto();
        $promoModel = new Promocao();
        
        $todosProdutos = $prodModel->listar($empresaId);
        $todosCombos = $promoModel->listarCombos($empresaId);
        
        // Verifica quais produtos têm adicionais (para o modal)
        $stmtTemAdd = $db->prepare("SELECT DISTINCT produto_id FROM produto_complementos WHERE ativo = 1");
        $stmtTemAdd->execute();
        $idsComAdicionais = $stmtTemAdd->fetchAll(\PDO::FETCH_COLUMN);

        $produtosPDV = [];

        // 1. Processa Produtos Simples (Estoque Direto)
        $stmtEst = $db->prepare("SELECT quantidade FROM estoque_filial WHERE produto_id = ? LIMIT 1");

        foreach($todosProdutos as $p) {
            $p['tipo_item'] = 'produto';
            $p['tem_adicionais'] = in_array($p['id'], $idsComAdicionais) ? 1 : 0;
            
            $stmtEst->execute([$p['id']]);
            $est = $stmtEst->fetch(\PDO::FETCH_ASSOC);
            $p['estoque_atual'] = ($est) ? $est['quantidade'] : 0;

            $produtosPDV[] = $p;
        }

        // 2. Processa Combos (CÁLCULO DO ESTOQUE VIRTUAL)
        $stmtIngredientes = $db->prepare("
            SELECT pc.quantidade as qtd_necessaria, 
                   COALESCE(ef.quantidade, 0) as estoque_real
            FROM produto_combos pc
            LEFT JOIN estoque_filial ef ON pc.item_id = ef.produto_id
            WHERE pc.produto_pai_id = ?
        ");

        foreach($todosCombos as $c) {
            $c['tipo_item'] = 'combo';
            $c['tem_adicionais'] = 0; 
            if(!isset($c['categoria_id'])) $c['categoria_id'] = 0;
            
            $stmtIngredientes->execute([$c['id']]);
            $ingredientes = $stmtIngredientes->fetchAll(\PDO::FETCH_ASSOC);

            if (count($ingredientes) == 0) {
                $c['estoque_atual'] = 999;
            } else {
                $maxCombosPossiveis = 99999;
                foreach($ingredientes as $ing) {
                    $qtdNecessaria = intval($ing['qtd_necessaria']);
                    if ($qtdNecessaria <= 0) continue; 
                    $estoqueReal = intval($ing['estoque_real']);
                    $possivelComEsteItem = floor($estoqueReal / $qtdNecessaria);
                    if ($possivelComEsteItem < $maxCombosPossiveis) {
                        $maxCombosPossiveis = $possivelComEsteItem;
                    }
                }
                $c['estoque_atual'] = $maxCombosPossiveis;
            }
            $produtosPDV[] = $c;
        }

        $catModel = new Categoria();
        $categorias = $catModel->listar($empresaId);

        $stmtM = $db->prepare("SELECT id, nome FROM motoboys WHERE empresa_id = ? AND ativo = 1");
        $stmtM->execute([$empresaId]);
        $motoboys = $stmtM->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/pedidos/index.php';
    }

    // 2. BUSCA ADICIONAIS
    public function buscaradicionais() {
        $this->verificarLogin(); 
        while (ob_get_level()) { ob_end_clean(); } 
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = $_GET['id'] ?? 0;
            $empresaId = $_SESSION['empresa_id'];

            if (!$id) throw new \Exception("ID inválido");

            $db = Database::connect();
            
            $sql = "SELECT g.* FROM grupos_adicionais g
                    INNER JOIN produto_complementos pc ON g.id = pc.grupo_id
                    WHERE pc.produto_id = :prod_id 
                      AND pc.ativo = 1
                      AND g.empresa_id = :emp_id
                    ORDER BY g.obrigatorio DESC, g.id ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['prod_id' => $id, 'emp_id' => $empresaId]);
            $grupos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($grupos)) {
                foreach ($grupos as &$g) {
                    $sqlOpcoes = "SELECT * FROM opcionais WHERE grupo_id = ? ORDER BY preco ASC, nome ASC";
                    $stmtOp = $db->prepare($sqlOpcoes);
                    $stmtOp->execute([$g['id']]);
                    $g['itens'] = $stmtOp->fetchAll(\PDO::FETCH_ASSOC);
                }
            } else {
                $grupos = [];
            }
            
            echo json_encode(['ok' => true, 'grupos' => $grupos]);

        } catch (\Throwable $e) { 
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); 
        }
        exit;
    }

    // 3. SALVAR PEDIDO
   public function salvar() {
        $this->verificarLogin(); 
        if(ob_get_contents()) ob_clean();
        header('Content-Type: application/json');

        try {
            $itens = json_decode($_POST['itens_json'] ?? '[]', true);
            if(empty($itens)) throw new \Exception('Carrinho vazio.');

            $dados = [
                'empresa_id'       => $_SESSION['empresa_id'],
                'pedido_id'        => $_POST['pedido_id'] ?? '',
                'cliente_nome'     => $_POST['cliente_nome'] ?? 'Consumidor',
                'cliente_telefone' => preg_replace('/[^0-9]/', '', $_POST['cliente_telefone'] ?? ''),
                'tipo_entrega'     => $_POST['tipo_entrega'],
                'endereco'         => $_POST['endereco_entrega'] ?? '', 
                'numero'           => $_POST['numero'] ?? '',
                'bairro'           => $_POST['bairro'] ?? '',
                'complemento'      => $_POST['complemento'] ?? '',
                'taxa_entrega'     => $this->moedaParaFloat($_POST['taxa_entrega'] ?? '0'),
                'desconto'         => $this->moedaParaFloat($_POST['desconto'] ?? '0'),
                'valor_total'      => $this->moedaParaFloat($_POST['valor_total'] ?? '0'),
                'forma_pagamento'  => $_POST['forma_pagamento'],
                'troco_para'       => $this->moedaParaFloat($_POST['troco_para'] ?? '0'),
                'lat_entrega'      => $_POST['lat_entrega'] ?? null,
                'lng_entrega'      => $_POST['lng_entrega'] ?? null,
                'sessao_id'        => $_POST['sessao_id'] ?? null,
                'participante_id'  => $_POST['participante_id'] ?? null
            ];

            $valorProd = 0;
            foreach($itens as $item) {
                $valorProd += ($item['qtd'] * $item['preco']);
            }
            $dados['valor_produtos'] = $valorProd;

            if ($dados['tipo_entrega'] === 'entrega' && empty($dados['endereco'])) {
                throw new \Exception('Endereço é obrigatório para entrega.');
            }

            $db = \App\Core\Database::connect();

            if ($dados['tipo_entrega'] === 'salao' && !empty($dados['sessao_id']) && empty($dados['pedido_id'])) {
                
                $sqlBusca = "SELECT id, valor_total, valor_produtos FROM pedidos 
                             WHERE sessao_id = ? AND status NOT IN ('finalizado', 'cancelado', 'entrega') AND empresa_id = ?";
                $params = [$dados['sessao_id'], $dados['empresa_id']];
                
                if (!empty($dados['participante_id'])) {
                    $sqlBusca .= " AND participante_id = ?";
                    $params[] = $dados['participante_id'];
                }
                $sqlBusca .= " LIMIT 1";

                $stmtBusca = $db->prepare($sqlBusca);
                $stmtBusca->execute($params);
                $pedidoExistente = $stmtBusca->fetch(\PDO::FETCH_ASSOC);

                if ($pedidoExistente) {
                    $idPedido = $pedidoExistente['id'];
                    $novoTotal = $pedidoExistente['valor_total'] + $dados['valor_total'];
                    $novoTotalProd = $pedidoExistente['valor_produtos'] + $dados['valor_produtos'];

                    $db->prepare("UPDATE pedidos SET valor_total = ?, valor_produtos = ?, impresso = 0 WHERE id = ?")
                       ->execute([$novoTotal, $novoTotalProd, $idPedido]);

                    foreach($itens as $item) {
                        $stmtItem = $db->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, total, observacao_item, status_item) VALUES (?, ?, ?, ?, ?, ?, 'fila')");
                        $totalItem = $item['qtd'] * $item['preco'];
                        $stmtItem->execute([
                            $idPedido, 
                            $item['id'], 
                            $item['qtd'], 
                            $item['preco'], 
                            $totalItem, 
                            $item['observacao'] ?? ''
                        ]);
                        $itemId = $db->lastInsertId();

                        if (!empty($item['adicionais'])) {
                            foreach($item['adicionais'] as $add) {
                                $db->prepare("INSERT INTO pedido_item_complementos (pedido_item_id, complemento_id, nome, preco) VALUES (?, ?, ?, ?)")
                                   ->execute([$itemId, $add['id'], $add['nome'], $add['preco']]);
                            }
                        }
                    }
                    echo json_encode(['ok' => true, 'msg' => 'Itens adicionados à comanda!', 'id' => $idPedido]);
                    exit; 
                }
            }

            $model = new Pedido();
            if ($dados['pedido_id']) {
                $model->atualizar($dados['pedido_id'], $dados, $itens);
                echo json_encode(['ok' => true, 'msg' => 'Pedido atualizado!']);
            } else {
                $idNovo = $model->criar($dados, $itens);
                echo json_encode(['ok' => true, 'msg' => 'Pedido criado!', 'id' => $idNovo]);
            }

        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    public function verificarMesa() {
        header('Content-Type: application/json');
        $hash = $_GET['h'] ?? ''; 
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT * FROM mesas WHERE hash_qr = ? LIMIT 1");
        $stmt->execute([$hash]);
        $mesa = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if(!$mesa) {
            echo json_encode(['ok' => false, 'msg' => 'Mesa não encontrada']);
            exit;
        }

        $stmtSessao = $db->prepare("SELECT * FROM mesa_sessoes WHERE mesa_id = ? AND status != 'encerrada' ORDER BY id DESC LIMIT 1");
        $stmtSessao->execute([$mesa['id']]);
        $sessao = $stmtSessao->fetch(\PDO::FETCH_ASSOC);

        if(!$sessao) {
            echo json_encode(['ok' => true, 'estado' => 'LIVRE', 'mesa' => $mesa]);
        } else {
            echo json_encode(['ok' => true, 'estado' => 'OCUPADA', 'mesa' => $mesa, 'sessao' => $sessao]);
        }
        exit;
    }

    public function abrirOuEntrarMesa() {
        header('Content-Type: application/json');
        $db = Database::connect();
        
        $mesaId = $_POST['mesa_id'];
        $nome = $_POST['nome'];
        $telefone = $_POST['telefone'];
        $acao = $_POST['acao']; 
        
        try {
            $db->beginTransaction();

            $sessaoId = null;

            if ($acao == 'abrir') {
                $tipoDivisao = $_POST['tipo_divisao'] ?? 'unica'; 
                $stmt = $db->prepare("INSERT INTO mesa_sessoes (mesa_id, tipo_divisao, status) VALUES (?, ?, 'aberta')");
                $stmt->execute([$mesaId, $tipoDivisao]);
                $sessaoId = $db->lastInsertId();
                $db->prepare("UPDATE mesas SET status_atual = 'ocupada' WHERE id = ?")->execute([$mesaId]);
                $isLider = 1;
            } else {
                $sessaoId = $_POST['sessao_id'];
                $isLider = 0;
            }

            $stmtP = $db->prepare("INSERT INTO mesa_participantes (sessao_id, nome, telefone, is_lider) VALUES (?, ?, ?, ?)");
            $stmtP->execute([$sessaoId, $nome, $telefone, $isLider]);
            $participanteId = $db->lastInsertId();

            $db->commit();
            
            echo json_encode([
                'ok' => true, 
                'sessao_id' => $sessaoId, 
                'participante_id' => $participanteId,
                'nome' => $nome
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    public function imprimir() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? 0;
        $empresaId = $_SESSION['empresa_id'];
        
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, tipo_entrega, sessao_id FROM pedidos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $empresaId]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$pedido) die("Pedido não encontrado.");

        if ($pedido['tipo_entrega'] == 'salao' && !empty($pedido['sessao_id'])) {
            header("Location: " . BASE_URL . "/admin/salao/imprimirConferencia?id=" . $pedido['sessao_id']);
            exit;
        }

        $stmtFull = $db->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmtFull->execute([$id]);
        $pedido = $stmtFull->fetch(\PDO::FETCH_ASSOC);

        $stmtEmp = $db->prepare("SELECT nome_fantasia, telefone_suporte FROM empresas WHERE id = ?");
        $stmtEmp->execute([$empresaId]);
        $loja = $stmtEmp->fetch(\PDO::FETCH_ASSOC);
        
        $sqlItens = "SELECT pi.*, p.nome as p_nome FROM pedido_itens pi LEFT JOIN produtos p ON pi.produto_id = p.id WHERE pi.pedido_id = ?";
        $stmtI = $db->prepare($sqlItens);
        $stmtI->execute([$id]);
        $itens = $stmtI->fetchAll(\PDO::FETCH_ASSOC);

        foreach($itens as &$i) {
            $stmtAdd = $db->prepare("SELECT nome FROM pedido_item_complementos WHERE pedido_item_id = ?");
            $stmtAdd->execute([$i['id']]);
            $i['complementos'] = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC); 
        }

        require __DIR__ . '/../Views/admin/pedidos/cupom.php';
    }

    public function getDadosPedido() {
        $this->verificarLogin(); 
        error_reporting(0); 
        ini_set('display_errors', 0);
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = $_GET['id'] ?? 0;
            $db = Database::connect();
            
            $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $_SESSION['empresa_id']]);
            $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if(!$pedido) throw new \Exception("Pedido não encontrado");

            $stmtI = $db->prepare("SELECT pi.*, p.nome as p_nome, p.tem_adicionais 
                                   FROM pedido_itens pi 
                                   LEFT JOIN produtos p ON pi.produto_id = p.id 
                                   WHERE pi.pedido_id = ?");
            $stmtI->execute([$id]);
            
            $itensCarrinho = [];
            foreach($stmtI->fetchAll(\PDO::FETCH_ASSOC) as $i) {
                $adicionais = [];
                try {
                    $stmtAdd = $db->prepare("SELECT id, nome, preco FROM pedido_item_complementos WHERE pedido_item_id = ?");
                    $stmtAdd->execute([$i['id']]); 
                    $adicionais = $stmtAdd->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $t) { $adicionais = []; }

                $itensCarrinho[] = [
                    'id' => $i['produto_id'],
                    'name' => $i['p_nome'] ?? 'Produto Excluído',
                    'preco' => floatval($i['preco_unitario']),
                    'qtd' => intval($i['quantidade']),
                    'observacao' => $i['observacao_item'] ?? '',
                    'estoque' => 999,
                    'tem_adicionais' => $i['tem_adicionais'] ?? 0,
                    'adicionais' => $adicionais
                ];
            }
            echo json_encode(['pedido' => $pedido, 'itensCarrinho' => $itensCarrinho]); 

        } catch (\Throwable $e) {
            echo json_encode(['erro' => $e->getMessage()]);
        }
        exit;
    }

    public function calcularFreteAjax() {
        if(ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        try {
            $this->verificarLogin();
            $rua = $_POST['rua'] ?? ''; $numero = $_POST['numero'] ?? ''; $bairro = $_POST['bairro'] ?? '';
            $empresaId = $_SESSION['empresa_id'];

            if (empty($rua) || empty($numero)) throw new \Exception('Endereço incompleto.');

            $db = Database::connect();
            $stmt = $db->prepare("SELECT f.id as filial_id, cf.lat, cf.lng FROM filiais f JOIN configuracoes_filial cf ON f.id = cf.filial_id WHERE f.empresa_id = ? LIMIT 1");
            $stmt->execute([$empresaId]);
            $loja = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$loja || empty($loja['lat'])) throw new \Exception('Loja sem coordenadas.');

            $geo = $this->geocodificar("$rua, $numero - $bairro"); 
            if (!$geo) throw new \Exception('Endereço não encontrado no Maps.');

            $distancia = $this->calcularDistancia($loja['lat'], $loja['lng'], $geo['lat'], $geo['lon']);

            $stmtTaxa = $db->prepare("SELECT valor FROM taxas_entrega_km WHERE filial_id = ? AND km_max >= ? AND ativo = 1 ORDER BY km_max ASC LIMIT 1");
            $stmtTaxa->execute([$loja['filial_id'], $distancia]);
            $taxa = $stmtTaxa->fetch(\PDO::FETCH_ASSOC);

            if ($taxa) {
                echo json_encode(['ok' => true, 'valor' => floatval($taxa['valor']), 'lat_cliente' => $geo['lat'], 'lng_cliente' => $geo['lon']]);
            } else {
                echo json_encode(['ok' => false, 'motivo' => 'fora_area', 'distancia' => $distancia]);
            }
        } catch (\Exception $e) { echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); }
        exit;
    }

    public function historico() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $dataInicio = $_GET['inicio'] ?? date('Y-m-01');
        $dataFim    = $_GET['fim'] ?? date('Y-m-d');
        
        $model = new Pedido();
        $pedidos = $model->listarHistorico($empresaId, $dataInicio, $dataFim);
        
        $totalFaturado = 0;
        $totalPedidos = count($pedidos);
        $totalDelivery = 0;
        foreach($pedidos as $p) { 
            if($p['status']!='cancelado') {
                $totalFaturado += $p['valor_total']; 
                $totalDelivery += $p['taxa_entrega'];
            }
        }
        $ticketMedio = $totalPedidos > 0 ? $totalFaturado / $totalPedidos : 0;
        
        require __DIR__ . '/../Views/admin/pedidos/historico.php';
    }

    public function mudarStatus() {
        $this->verificarLogin(); 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        
        try {
            $id = $_POST['id'] ?? 0;
            $novoStatus = $_POST['status'] ?? '';
            $empresaId = $_SESSION['empresa_id'];

            if (!$id || !$novoStatus) throw new \Exception("Dados inválidos.");
            
            $db = Database::connect();
            $db->prepare("UPDATE pedidos SET status = ? WHERE id = ?")->execute([$novoStatus, $id]);

            if ($novoStatus == 'entrega' || $novoStatus == 'finalizado') {
                $db->prepare("UPDATE pedido_itens SET status_item = 'entregue' WHERE pedido_id = ?")->execute([$id]);
            } elseif ($novoStatus == 'preparo') {
                 $db->prepare("UPDATE pedido_itens SET status_item = 'preparo' WHERE pedido_id = ? AND status_item = 'fila'")->execute([$id]);
            }

            if ($novoStatus == 'finalizado') {
                $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ? AND empresa_id = ?");
                $stmt->execute([$id, $empresaId]);
                $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($pedido) {
                    $stmtCheck = $db->prepare("SELECT id FROM contas_receber WHERE pedido_id = ?");
                    $stmtCheck->execute([$id]);
                    
                    if ($stmtCheck->rowCount() == 0) {
                        $isFiado = stripos($pedido['forma_pagamento'], 'fiado') !== false;
                        $statusConta = $isFiado ? 'pendente' : 'pago';
                        $dataVencimento = date('Y-m-d'); 

                        $sqlFin = "INSERT INTO contas_receber 
                            (empresa_id, pedido_id, cliente_id, cliente_nome, valor, data_vencimento, status, forma_pagamento, categoria, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Venda', NOW())";
                        
                        $stmtFin = $db->prepare($sqlFin);
                        $stmtFin->execute([
                            $pedido['empresa_id'],
                            $pedido['id'],
                            $pedido['cliente_id'],
                            $pedido['cliente_nome'],
                            $pedido['valor_total'],
                            $dataVencimento,
                            $statusConta,
                            $pedido['forma_pagamento']
                        ]);
                    }
                }
            }

            echo json_encode(['ok' => true]); 

        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    public function excluir() {
        $this->verificarLogin(); ob_clean();
        (new Pedido())->excluir($_POST['id']);
        echo json_encode(['ok' => true]); exit;
    }

    public function buscarClienteAjax() {
        $this->verificarLogin(); ob_clean();
        $tel = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
        $db = Database::connect();
        $stmt = $db->prepare("SELECT nome FROM clientes WHERE telefone LIKE ? AND empresa_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(["%$tel%", $_SESSION['empresa_id']]);
        echo json_encode(['encontrado' => ($stmt->rowCount() > 0), 'dados' => $stmt->fetch(\PDO::FETCH_ASSOC)]); exit;
    }
    
    public function getItensPedido() {
        $this->verificarLogin(); 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = $_GET['id'] ?? 0; 
            $db = \App\Core\Database::connect(); 
            
            $stmtP = $db->prepare("SELECT * FROM pedidos WHERE id = ?"); 
            $stmtP->execute([$id]); 
            $pedido = $stmtP->fetch(\PDO::FETCH_ASSOC); 
            
            $stmtI = $db->prepare("SELECT i.*, COALESCE(p.nome, 'Item Excluído') as produto_nome 
                                   FROM pedido_itens i 
                                   LEFT JOIN produtos p ON i.produto_id = p.id 
                                   WHERE i.pedido_id = ?"); 
            $stmtI->execute([$id]); 
            $itens = $stmtI->fetchAll(\PDO::FETCH_ASSOC); 
            
            echo json_encode(['pedido' => $pedido, 'itens' => $itens]); 

        } catch (\Throwable $e) {
            echo json_encode(['erro' => $e->getMessage()]);
        }
        exit; 
    }
    
    public function vincularMotoboyAjax() {
        $this->verificarLogin(); ob_clean();
        $db = Database::connect(); 
        $db->prepare("UPDATE pedidos SET motoboy_id = ? WHERE id = ?")->execute([$_POST['motoboy_id'] ?: null, $_POST['pedido_id']]); 
        echo json_encode(['ok' => true]); exit; 
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }

    private function moedaParaFloat($v) {
        if (is_numeric($v)) return (float)$v;
        return (float) str_replace(',', '.', str_replace(['R$', ' ', '.'], '', $v));
    }

    private function limparStringPix($str, $limit) {
        if (empty($str)) return 'LOJA';
        $str = preg_replace(
            ["/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"], 
            explode(" ","a A e E i I o O u U n N c C"), 
            $str
        );
        $str = preg_replace('/[^a-zA-Z0-9 ]/', '', $str);
        return strtoupper(substr(trim($str), 0, $limit));
    }

    private function geocodificar($endereco) {
        $apiKey = "AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U"; 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key=" . $apiKey;
        $json = @file_get_contents($url);
        $data = json_decode($json, true);
        if (isset($data['status']) && $data['status'] === 'OK') {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'], 
                'lon' => $data['results'][0]['geometry']['location']['lng']
            ];
        }
        return null; 
    }
    
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 1);
    }

    // --- API: VERIFICA IMPRESSÃO AUTOMÁTICA ---
    public function verificarFilaImpressao() {
        $this->verificarLogin();
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        
        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();
        
        // BLOQUEIO ATIVADO: Só puxa pedidos que tiverem pelo menos 1 item que precisa ir pra cozinha.
        $sql = "SELECT p.id, p.tipo_entrega, p.sessao_id 
                FROM pedidos p 
                WHERE p.empresa_id = ? 
                AND p.impresso = 0 
                AND p.status IN ('novo', 'analise', 'preparo') 
                AND p.created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
                AND EXISTS (
                    SELECT 1 FROM pedido_itens pi_check
                    LEFT JOIN produtos pr_check ON pi_check.produto_id = pr_check.id
                    WHERE pi_check.pedido_id = p.id 
                    AND (pr_check.precisa_preparo = 1 OR pr_check.precisa_preparo IS NULL)
                )
                ORDER BY p.id ASC LIMIT 1";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($pedido) {
            echo json_encode([
                'tem_pedido' => true, 
                'id' => $pedido['id'],
                'tipo' => $pedido['tipo_entrega'],
                'sessao_id' => $pedido['sessao_id']
            ]);
        } else {
            echo json_encode(['tem_pedido' => false]);
        }
        exit;
    }
   
    public function marcarComoImpresso() {
        $this->verificarLogin();
        $id = $_POST['id'];
        $db = Database::connect();
        $db->prepare("UPDATE pedidos SET impresso = 1 WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    public function monitorUnificado() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $db = \App\Core\Database::connect();

        // 1. FILTRO DE PEDIDOS: Só pega pedidos que tenham pelo menos UM item que precisa de preparo
        $sql = "SELECT p.id, p.cliente_nome, p.tipo_entrega, p.status, p.created_at, 
                       p.sessao_id, p.bairro, m.numero as num_mesa
                FROM pedidos p
                LEFT JOIN mesa_sessoes s ON p.sessao_id = s.id
                LEFT JOIN mesas m ON s.mesa_id = m.id
                WHERE p.empresa_id = ? 
                AND p.status IN ('novo', 'analise', 'preparo', 'entrega') 
                AND DATE(p.created_at) = CURDATE()
                AND EXISTS (
                    SELECT 1 FROM pedido_itens pi_check
                    LEFT JOIN produtos pr_check ON pi_check.produto_id = pr_check.id
                    WHERE pi_check.pedido_id = p.id 
                    AND (pr_check.precisa_preparo = 1 OR pr_check.precisa_preparo IS NULL)
                )
                ORDER BY p.id ASC"; 
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        $todosPedidos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($todosPedidos as &$p) {
            // 2. FILTRO DE ITENS: Dentro do card, só mostra os itens que vão ao fogo/preparo
            $stmtItens = $db->prepare("SELECT pi.id, pi.quantidade, COALESCE(prod.nome, 'Item Removido') as nome, 
                                              pi.observacao_item, pi.status_item 
                                       FROM pedido_itens pi 
                                       LEFT JOIN produtos prod ON pi.produto_id = prod.id 
                                       WHERE pi.pedido_id = ?
                                       AND (prod.precisa_preparo = 1 OR prod.precisa_preparo IS NULL)");
            $stmtItens->execute([$p['id']]);
            $itens = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);

            foreach($itens as &$i) {
                $stmtAdd = $db->prepare("SELECT nome FROM pedido_item_complementos WHERE pedido_item_id = ?");
                $stmtAdd->execute([$i['id']]);
                $i['complementos'] = $stmtAdd->fetchAll(\PDO::FETCH_COLUMN);
            }
            $p['itens'] = $itens;
        }

        $analise = array_filter($todosPedidos, fn($p) => $p['status'] == 'novo' || $p['status'] == 'analise');
        $preparo = array_filter($todosPedidos, fn($p) => $p['status'] == 'preparo');
        $entrega = array_filter($todosPedidos, fn($p) => $p['status'] == 'entrega');

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            echo json_encode([
                'analise' => array_values($analise),
                'preparo' => array_values($preparo),
                'entrega' => array_values($entrega)
            ]);
            exit;
        }

        require __DIR__ . '/../Views/admin/pedidos/monitor_unificado.php';
    }

    // --- IMPRIMIR COZINHA ---
    public function imprimirCozinha() {
        $this->verificarLogin();
        $id = $_GET['id'] ?? 0;
        
        $db = \App\Core\Database::connect();
        
        $stmt = $db->prepare("SELECT p.*, m.numero as num_mesa 
                              FROM pedidos p
                              LEFT JOIN mesa_sessoes s ON p.sessao_id = s.id
                              LEFT JOIN mesas m ON s.mesa_id = m.id
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$pedido) die("Pedido não encontrado.");

        // BLOQUEIO ATIVADO: Não puxa itens de bebidas e etc para o papel da cozinha.
        $stmtI = $db->prepare("SELECT pi.*, COALESCE(p.nome, 'Item Removido') as produto_nome 
                               FROM pedido_itens pi 
                               LEFT JOIN produtos p ON pi.produto_id = p.id 
                               WHERE pi.pedido_id = ? 
                               AND (pi.status_item != 'cancelado' OR pi.status_item IS NULL)
                               AND (p.precisa_preparo = 1 OR p.precisa_preparo IS NULL)");
        $stmtI->execute([$id]);
        $itens = $stmtI->fetchAll(\PDO::FETCH_ASSOC);

        foreach($itens as &$i) {
            $stmtAdd = $db->prepare("SELECT nome FROM pedido_item_complementos WHERE pedido_item_id = ?");
            $stmtAdd->execute([$i['id']]);
            $i['complementos'] = $stmtAdd->fetchAll(\PDO::FETCH_COLUMN);
        }

        require __DIR__ . '/../Views/admin/pedidos/cupom_cozinha.php';
    }

    // =========================================================
    // API SPOOLER LOGIN (BLINDADO CONTRA ERRO 500)
    // =========================================================
    public function apiLoginSpooler() {
        header('Content-Type: application/json');
        
        try {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            
            $db = \App\Core\Database::connect();
            $stmt = $db->prepare("SELECT id, nome, senha, empresa_id FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user && password_verify($senha, $user['senha'])) {
                $stmtEmp = $db->prepare("SELECT nome_fantasia FROM empresas WHERE id = ?");
                $stmtEmp->execute([$user['empresa_id']]);
                $empresa = $stmtEmp->fetch(\PDO::FETCH_ASSOC);

                echo json_encode(['ok' => true, 'empresa_id' => $user['empresa_id'], 'nome_loja' => $empresa['nome_fantasia']]);
            } else {
                echo json_encode(['ok' => false, 'msg' => 'Email ou senha incorretos.']);
            }

        } catch (\Throwable $e) {
            // Se der erro SQL, enviamos status 200 pro Python não se assustar, 
            // mas enviamos a mensagem de erro real no JSON!
            http_response_code(200);
            echo json_encode(['ok' => false, 'msg' => 'Erro SQL: ' . $e->getMessage()]);
        }
        exit;
    }

    // --- API SPOOLER PYTHON ---
    public function apiVerificarFila() {
        header('Content-Type: application/json');
        $empresaId = $_POST['empresa_id'] ?? 0;
        
        if(!$empresaId) {
            echo json_encode(['tem_pedido' => false]);
            exit;
        }

        $db = \App\Core\Database::connect();
        
        // BLOQUEIO ATIVADO: Spooler só olha pedidos que tenham itens a ser preparados!
        $sql = "SELECT p.id, p.cliente_nome, p.tipo_entrega, p.status, p.created_at, 
                       p.sessao_id, p.bairro, m.numero as num_mesa
                FROM pedidos p
                LEFT JOIN mesa_sessoes s ON p.sessao_id = s.id
                LEFT JOIN mesas m ON s.mesa_id = m.id
                WHERE p.empresa_id = ? 
                AND p.status IN ('novo', 'preparo') 
                AND (p.impresso = 0 OR p.impresso IS NULL)
                AND EXISTS (
                    SELECT 1 FROM pedido_itens pi_check
                    LEFT JOIN produtos pr_check ON pi_check.produto_id = pr_check.id
                    WHERE pi_check.pedido_id = p.id 
                    AND (pr_check.precisa_preparo = 1 OR pr_check.precisa_preparo IS NULL)
                )
                ORDER BY p.id ASC 
                LIMIT 1"; 
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$empresaId]);
        $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($pedido) {
            // BLOQUEIO ATIVADO: Retorna apenas os itens que precisam de preparo
            $stmtItens = $db->prepare("SELECT pi.quantidade, pi.observacao_item, 
                                              COALESCE(prod.nome, 'Item Removido') as nome
                                       FROM pedido_itens pi 
                                       LEFT JOIN produtos prod ON pi.produto_id = prod.id 
                                       WHERE pi.pedido_id = ? 
                                       AND pi.status_item != 'cancelado'
                                       AND (prod.precisa_preparo = 1 OR prod.precisa_preparo IS NULL)");
            $stmtItens->execute([$pedido['id']]);
            $itens = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);

            foreach($itens as &$i) {
                $i['complementos'] = []; 
            }

            $pedido['itens'] = $itens;
            echo json_encode(['tem_pedido' => true, 'dados' => $pedido]);
        } else {
            echo json_encode(['tem_pedido' => false]);
        }
        exit;
    }

    public function apiConfirmarImpressao() {
        header('Content-Type: application/json');
        $pedidoId = $_POST['pedido_id'] ?? 0;

        if ($pedidoId) {
            $db = \App\Core\Database::connect();
            $db->prepare("UPDATE pedidos SET impresso = 1 WHERE id = ?")->execute([$pedidoId]);
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false]);
        }
        exit;
    }

    public function resetarImpressao() {
        $this->verificarLogin(); 
        $id = $_POST['id'] ?? 0;
        
        if($id) {
            $db = \App\Core\Database::connect();
            $db->prepare("UPDATE pedidos SET impresso = 0 WHERE id = ?")->execute([$id]);
            echo "OK";
        }
    }

    public function despachar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'erro' => 'Não logado']);
            exit;
        }

        header('Content-Type: application/json'); 

        $pedidoId = $_POST['pedido_id'] ?? 0;
        $motoboyId = $_POST['motoboy_id'] ?? null;

        if (!$pedidoId || !$motoboyId) {
            echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
            exit;
        }

        $db = \App\Core\Database::connect();
        try {
            $stmt = $db->prepare("UPDATE pedidos SET motoboy_id = ? WHERE id = ?");
            $stmt->execute([$motoboyId, $pedidoId]);
            
            echo json_encode(['ok' => true]);
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit; 
    }
}