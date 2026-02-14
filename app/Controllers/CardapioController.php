<?php
namespace App\Controllers;

use App\Models\Cardapio;
use App\Models\Pedido;
use App\Core\Database;

class CardapioController {
    
    // =========================================================================
    // 1. ROTA PRINCIPAL (VISUALIZAÇÃO DO CARDÁPIO)
    // =========================================================================
    public function index($empresa) {
        // ... (Lógica de detecção de API para não quebrar rotas diretas)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, 'detalhesProduto') !== false) { $this->detalhesProduto(); exit; }
        if (strpos($uri, 'calcularFrete') !== false) { $this->calcularFrete(); exit; }
        if (strpos($uri, 'frete_gps') !== false) { $this->calcularFreteGPS(); exit; }
        if (strpos($uri, 'buscarCliente') !== false) { $this->buscarCliente(); exit; }
        if (strpos($uri, 'salvarPedido') !== false) { $this->salvarPedido(); exit; }
        if (strpos($uri, 'historico') !== false) { $this->meusPedidos(); exit; }

        date_default_timezone_set('America/Sao_Paulo');
        $db = Database::connect();

        // 1. Dados da Filial e Configurações
        $stmt = $db->prepare("SELECT * FROM filiais WHERE empresa_id = :id ORDER BY id ASC LIMIT 1");
        $stmt->execute(['id' => $empresa['id']]);
        $filial = $stmt->fetch();
        if (!$filial) die("<h1>Loja Indisponível</h1>");

        $stmt = $db->prepare("SELECT * FROM configuracoes_filial WHERE filial_id = :id");
        $stmt->execute(['id' => $filial['id']]);
        $config = $stmt->fetch();

        // 2. Dados da Empresa (PIX)
        $stmtEmp = $db->prepare("SELECT chave_pix, telefone_suporte, nome_fantasia, endereco_completo, slug FROM empresas WHERE id = ?");
        $stmtEmp->execute([$empresa['id']]);
        $dadosEmpresa = $stmtEmp->fetch();
        
        $chavePix = $dadosEmpresa['chave_pix'] ?? '';
        $empresa['slug'] = $dadosEmpresa['slug'] ?? 'loja';

        // Prepara dados PIX
        $nomeLojaPix = $this->limparStringPix($dadosEmpresa['nome_fantasia'] ?? 'LOJA', 25);
        $cidadeLojaPix = 'CIDADE';
        if (!empty($dadosEmpresa['endereco_completo'])) {
            $partes = explode('-', $dadosEmpresa['endereco_completo']);
            $ultimaParte = end($partes);
            $cidadeLojaPix = $this->limparStringPix($ultimaParte, 15);
        }

        // 3. Horário de Funcionamento
        $diaSemana = date('w'); $horaAgora = date('H:i');
        $stmtHora = $db->prepare("SELECT * FROM horarios_funcionamento WHERE filial_id = ? AND dia_semana = ?");
        $stmtHora->execute([$filial['id'], $diaSemana]);
        $horarioHoje = $stmtHora->fetch();

        $lojaAberta = false;
        $msgHorario = "Fechado agora";
        
        if ($horarioHoje && $horarioHoje['fechado_hoje'] == 0) {
            if ($horaAgora >= $horarioHoje['abertura'] && $horaAgora <= $horarioHoje['fechamento']) {
                $lojaAberta = true; 
                $msgHorario = "Aberto até " . date('H:i', strtotime($horarioHoje['fechamento']));
            } else { 
                $msgHorario = "Fechado (Abre às " . date('H:i', strtotime($horarioHoje['abertura'])) . ")"; 
            }
        }

        // 4. Busca Produtos
        $model = new Cardapio();
        $combos = $model->buscarCombos($empresa['id'], $filial['id']);
        $categorias = $model->buscarCardapioCompleto($empresa['id'], $filial['id']);
        
        $filial_id = $filial['id'];
        $whatsappLoja = $filial['telefone_whatsapp'] ?? '';
        
        require __DIR__ . '/../Views/cardapio/home.php';
    }

    // =========================================================================
    // 2. LÓGICA DE MESA (INTELIGÊNCIA NOVA)
    // =========================================================================

    // Rota: /api/mesa/checkin
    public function checkinMesa() {
        header('Content-Type: application/json');
        $db = Database::connect();
        
        $mesaId = $_POST['mesa_id'] ?? 0;
        $nome = trim($_POST['nome'] ?? '');
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
        $tipoDivisao = $_POST['tipo_divisao'] ?? 'unica'; // 'unica' ou 'individual'
        
        if(!$mesaId || empty($nome)) {
            echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
            exit;
        }
        
        try {
            $db->beginTransaction();

            // 1. Verifica se já tem sessão aberta nesta mesa
            $stmtSessao = $db->prepare("SELECT id, tipo_divisao FROM mesa_sessoes WHERE mesa_id = ? AND status != 'encerrada' LIMIT 1");
            $stmtSessao->execute([$mesaId]);
            $sessao = $stmtSessao->fetch();

            if (!$sessao) {
                // --- MESA LIVRE (CRIAR NOVA SESSÃO) ---
                // Define se a conta será JUNTA ou SEPARADA com base na escolha do primeiro cliente
                $db->prepare("INSERT INTO mesa_sessoes (mesa_id, status, tipo_divisao) VALUES (?, 'aberta', ?)")
                   ->execute([$mesaId, $tipoDivisao]);
                $sessaoId = $db->lastInsertId();
                
                // Marca mesa como ocupada
                $db->prepare("UPDATE mesas SET status_atual = 'ocupada' WHERE id = ?")->execute([$mesaId]);
                $isLider = 1;
            } else {
                // --- MESA JÁ OCUPADA (ENTRAR NA SESSÃO) ---
                $sessaoId = $sessao['id'];
                $isLider = 0;
                $tipoDivisao = $sessao['tipo_divisao']; // Herda o tipo definido pelo líder
            }

            // 2. VERIFICAÇÃO DE DUPLICIDADE (Anti-Duplo)
            // Verifica se já existe participante com esse NOME na mesma SESSÃO
            $stmtCheck = $db->prepare("SELECT id FROM mesa_participantes WHERE sessao_id = ? AND nome = ?");
            $stmtCheck->execute([$sessaoId, $nome]);
            $participanteExistente = $stmtCheck->fetch();

            if ($participanteExistente) {
                // Se já existe, "recupera" o acesso (login) em vez de criar duplicata
                $participanteId = $participanteExistente['id'];
                
                // Atualiza telefone se foi informado agora
                if(!empty($telefone)) {
                    $db->prepare("UPDATE mesa_participantes SET telefone = ? WHERE id = ?")->execute([$telefone, $participanteId]);
                }
            } else {
                // Se não existe, cria novo participante
                // status_pagamento default é 'pendente' (definido no banco)
                $db->prepare("INSERT INTO mesa_participantes (sessao_id, nome, telefone, is_lider, status_pagamento) VALUES (?, ?, ?, ?, 'pendente')")
                   ->execute([$sessaoId, $nome, $telefone, $isLider]);
                $participanteId = $db->lastInsertId();
            }

            $db->commit();

            // Retorna dados para o front-end
            echo json_encode([
                'ok' => true, 
                'sessao_id' => $sessaoId, 
                'participante_id' => $participanteId,
                'nome' => $nome,
                'tipo_divisao' => $tipoDivisao
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }

    // Rota: /api/mesa/status (Nova função para verificar antes de abrir o modal)
    public function verificarStatusMesa() {
        header('Content-Type: application/json');
        $mesaId = $_POST['mesa_id'] ?? 0;
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT id FROM mesa_sessoes WHERE mesa_id = ? AND status != 'encerrada' LIMIT 1");
        $stmt->execute([$mesaId]);
        
        // Retorna true se estiver ocupada, false se livre
        echo json_encode(['ocupada' => $stmt->rowCount() > 0]);
        exit;
    }

    // Rota: /slug/mesa/hash (Acesso via QR Code)
    public function mesa($slug, $hash) {
        $db = \App\Core\Database::connect();
        
        // 1. Busca Mesa, Filial e Empresa
        $stmt = $db->prepare("SELECT m.*, f.empresa_id, e.id as empresa_id_real 
                              FROM mesas m 
                              JOIN filiais f ON m.filial_id = f.id 
                              JOIN empresas e ON f.empresa_id = e.id
                              WHERE m.hash_qr = ? AND e.slug = ? LIMIT 1");
        $stmt->execute([$hash, $slug]);
        $mesa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$mesa) die("<h1>Mesa não encontrada ou link inválido</h1>");

        // 2. Verifica se já existe uma sessão ABERTA para esta mesa
        $stmtSessao = $db->prepare("SELECT id, aprovado FROM mesa_sessoes WHERE mesa_id = ? AND status != 'encerrada' ORDER BY id DESC LIMIT 1");
        $stmtSessao->execute([$mesa['id']]);
        $sessao = $stmtSessao->fetch(\PDO::FETCH_ASSOC);

        // 3. Lógica de Criação/Verificação
        if (!$sessao) {
            // Nenhuma sessão aberta: CRIA UMA NOVA como "Pendente" (aprovado = 0)
            // Isso faz aparecer no painel do garçom para ele aprovar
            $stmtInsert = $db->prepare("INSERT INTO mesa_sessoes (mesa_id, status, aprovado, tipo_divisao, created_at) VALUES (?, 'aberta', 0, 'unica', NOW())");
            $stmtInsert->execute([$mesa['id']]);
            $sessaoId = $db->lastInsertId();
            $estaAprovado = false;
        } else {
            // Sessão já existe: verifica se já foi aprovada
            $sessaoId = $sessao['id'];
            $estaAprovado = ($sessao['aprovado'] == 1);
        }

        // 4. Se NÃO estiver aprovado, mostra a tela de espera
        if (!$estaAprovado) {
            // Variáveis para a view usar
            $hashMesa = $hash;
            $slugEmpresa = $slug;
            
            require __DIR__ . '/../Views/cardapio/aguardando_liberacao.php';
            exit; // Interrompe aqui, não define cookie ainda
        }

        // 5. Se estiver APROVADO, define o cookie e libera o acesso
        $dadosCookie = [
            'id' => $mesa['id'],
            'numero' => $mesa['numero'],
            'hash' => $hash,
            'filial_id' => $mesa['filial_id'],
            'sessao_id' => $sessaoId // Útil guardar o ID da sessão também
        ];
        
        setcookie("mesa_ativa", json_encode($dadosCookie), time() + 86400, "/");
        header("Location: " . BASE_URL . "/{$slug}");
        exit;
    }

    // API que a tela de espera chama a cada 3 segundos
    public function apiChecarStatus() {
        header('Content-Type: application/json');
        $hash = $_POST['hash'] ?? '';
        
        if (!$hash) { echo json_encode(['status' => 'erro']); exit; }

        $db = \App\Core\Database::connect();
        
        // Busca a sessão ativa dessa mesa pelo hash da mesa
        $sql = "SELECT s.aprovado 
                FROM mesa_sessoes s
                JOIN mesas m ON s.mesa_id = m.id
                WHERE m.hash_qr = ? AND s.status != 'encerrada'
                ORDER BY s.id DESC LIMIT 1";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$hash]);
        $sessao = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($sessao && $sessao['aprovado'] == 1) {
            echo json_encode(['status' => 'liberado']);
        } else {
            echo json_encode(['status' => 'aguardando']);
        }
        exit;
    }

    // =========================================================================
    // 3. SALVAR PEDIDO
    // =========================================================================
    public function salvarPedido() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        date_default_timezone_set('America/Sao_Paulo');
        
        try { 
            $dados = $_POST; 
            if(empty($dados['itens_json'])) throw new \Exception('Carrinho vazio.'); 
            
            // =================================================================
            // 🛑 TRAVA DE SEGURANÇA (ADICIONADA AQUI)
            // =================================================================
            if (isset($dados['tipo_entrega']) && $dados['tipo_entrega'] === 'salao') {
                $partId = $dados['participante_id'] ?? null;
                
                if (empty($partId)) {
                    throw new \Exception("Erro de identificação. Tente sair e entrar na mesa novamente.");
                }

                $db = Database::connect();
                $stmtStatus = $db->prepare("SELECT status_pagamento, nome FROM mesa_participantes WHERE id = ?");
                $stmtStatus->execute([$partId]);
                $part = $stmtStatus->fetch(\PDO::FETCH_ASSOC);

                // Verifica se está pago
                if ($part && strtolower($part['status_pagamento']) === 'pago') {
                    throw new \Exception("⛔ CONTA ENCERRADA!\n\nA conta de {$part['nome']} já foi paga.\nPeça ao garçom para reabrir se quiser fazer novos pedidos.");
                }
            }
            // =================================================================

            $itens = json_decode($dados['itens_json'], true); 
            $modelPedido = new Pedido(); 
            
            $valorProd = 0;
            foreach($itens as $item) { $valorProd += ($item['qtd'] * $item['preco']); }

            $taxaEntrega = floatval($dados['taxa_entrega'] ?? 0);
            
            // Força taxa zero se for mesa
            if(isset($dados['tipo_entrega']) && $dados['tipo_entrega'] == 'salao') {
                $taxaEntrega = 0;
            }

            $valorTotal = $valorProd + $taxaEntrega;
            
            $troco = 0.00;
            if (isset($dados['forma_pagamento']) && $dados['forma_pagamento'] === 'dinheiro') {
                $valorPago = floatval(str_replace(',', '.', $dados['troco_para'] ?? 0));
                if ($valorPago > $valorTotal) { $troco = $valorPago - $valorTotal; }
            }

            $dadosPedido = [ 
                'empresa_id' => $dados['empresa_id'], 
                'cliente_nome' => $dados['cliente_nome'], 
                'cliente_telefone' => preg_replace('/[^0-9]/', '', $dados['cliente_telefone']), 
                'tipo_entrega' => $dados['tipo_entrega'], 
                'endereco' => $dados['endereco'] ?? ($dados['endereco_entrega'] ?? ''), 
                'numero' => $dados['numero'] ?? '', 
                'bairro' => $dados['bairro'] ?? '', 
                'complemento' => $dados['complemento'] ?? '', 
                'taxa_entrega' => $taxaEntrega, 
                'valor_produtos' => $valorProd, 
                'valor_total' => $valorTotal, 
                'forma_pagamento' => $dados['forma_pagamento'] ?? 'dinheiro', 
                'troco' => $troco,
                'lat_entrega' => !empty($dados['lat_entrega']) ? $dados['lat_entrega'] : null, 
                'lng_entrega' => !empty($dados['lng_entrega']) ? $dados['lng_entrega'] : null,
                'sessao_id' => !empty($dados['sessao_id']) ? $dados['sessao_id'] : null,
                'participante_id' => !empty($dados['participante_id']) ? $dados['participante_id'] : null
            ];
            
            if ($dadosPedido['tipo_entrega'] === 'entrega' && empty($dadosPedido['endereco'])) { 
                throw new \Exception('Endereço é obrigatório para delivery.'); 
            }

            // A função criar do Model já deve tratar o agrupamento se você atualizou o Model,
            // senão, o PedidoController que tratava. 
            // Se o CardapioController usa o Model direto, precisamos garantir que o Model
            // ou este controller faça a lógica de AGRUPAR pedidos de mesa.
            
            // --> Se você quiser garantir o agrupamento AQUI TAMBÉM (igual fizemos no outro controller):
            if ($dadosPedido['tipo_entrega'] === 'salao' && !empty($dadosPedido['sessao_id'])) {
                 $db = Database::connect();
                 
                 // Busca pedido aberto para agrupar
                 $sqlBusca = "SELECT id, valor_total, valor_produtos FROM pedidos 
                              WHERE sessao_id = ? AND status NOT IN ('finalizado', 'cancelado', 'entrega') AND empresa_id = ?";
                 $params = [$dadosPedido['sessao_id'], $dadosPedido['empresa_id']];
                 
                 if(!empty($dadosPedido['participante_id'])) {
                     $sqlBusca .= " AND participante_id = ?";
                     $params[] = $dadosPedido['participante_id'];
                 }
                 $sqlBusca .= " LIMIT 1";
                 
                 $stmtP = $db->prepare($sqlBusca);
                 $stmtP->execute($params);
                 $pedidoPai = $stmtP->fetch(\PDO::FETCH_ASSOC);
                 
                 if($pedidoPai) {
                     // ATUALIZA PEDIDO EXISTENTE
                     $novoTotal = $pedidoPai['valor_total'] + $valorTotal;
                     $novoTotalProd = $pedidoPai['valor_produtos'] + $valorProd;
                     
                     $db->prepare("UPDATE pedidos SET valor_total = ?, valor_produtos = ?, impresso = 0 WHERE id = ?")
                        ->execute([$novoTotal, $novoTotalProd, $pedidoPai['id']]);
                        
                     $idPedido = $pedidoPai['id'];
                     
                     // Insere Itens
                     foreach($itens as $item) {
                        $stmtItem = $db->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, total, observacao_item, status_item) VALUES (?, ?, ?, ?, ?, ?, 'fila')");
                        $tItem = $item['qtd'] * $item['preco'];
                        $stmtItem->execute([$idPedido, $item['id'], $item['qtd'], $item['preco'], $tItem, $item['observacao']??'']);
                        $itemId = $db->lastInsertId();
                        
                        if (!empty($item['adicionais'])) {
                            foreach($item['adicionais'] as $add) {
                                $db->prepare("INSERT INTO pedido_item_complementos (pedido_item_id, complemento_id, nome, preco) VALUES (?, ?, ?, ?)")
                                   ->execute([$itemId, $add['id'], $add['nome'], $add['preco']]);
                            }
                        }
                     }
                     
                     echo json_encode(['ok' => true, 'id' => $idPedido, 'whatsapp_loja' => $dados['whatsapp_loja'] ?? '', 'msg' => 'Agrupado com sucesso']);
                     exit;
                 }
            }

            // Se não agrupou, cria novo
            $idPedido = $modelPedido->criar($dadosPedido, $itens); 
            
            echo json_encode([
                'ok' => true, 
                'id' => $idPedido, 
                'whatsapp_loja' => $dados['whatsapp_loja'] ?? ''
            ]); 

        } catch (\Exception $e) { 
            echo json_encode(['ok' => false, 'erro' => "Erro: " . $e->getMessage()]); 
        } 
    }

    // =========================================================================
    // 4. MÉTODOS AUXILIARES E OUTRAS ROTAS API
    // =========================================================================

    public function detalhesProduto() {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = $_GET['id'] ?? 0;
            if (!$id) throw new \Exception("ID inválido.");
            $model = new Cardapio();
            echo json_encode(['ok' => true, 'complementos' => $model->getComplementos($id)]);
        } catch (\Exception $e) { echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); }
        exit;
    }

    public function calcularFrete() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        try { 
            $filialId = $_POST['filial_id'] ?? 0; $enderecoCliente = $_POST['endereco'] ?? ''; $numero = $_POST['numero'] ?? ''; 
            if(empty($enderecoCliente) || empty($numero)) throw new \Exception('Endereço incompleto'); 
            $db = Database::connect(); $stmtLoja = $db->prepare("SELECT lat, lng FROM configuracoes_filial WHERE filial_id = ?"); $stmtLoja->execute([$filialId]); $loja = $stmtLoja->fetch(); 
            if(!$loja || empty($loja['lat'])) throw new \Exception('Loja sem localização configurada.'); 
            $geoCliente = $this->geocodificar("$enderecoCliente, $numero"); 
            if(!$geoCliente) throw new \Exception('Endereço não encontrado.'); 
            $distancia = $this->calcularDistancia($loja['lat'], $loja['lng'], $geoCliente['lat'], $geoCliente['lon']); 
            $stmtTaxa = $db->prepare("SELECT valor FROM taxas_entrega_km WHERE filial_id = ? AND km_max >= ? AND ativo = 1 ORDER BY km_max ASC LIMIT 1"); 
            $stmtTaxa->execute([$filialId, $distancia]); $taxa = $stmtTaxa->fetch(); 
            if ($taxa) { echo json_encode(['ok' => true, 'valor' => floatval($taxa['valor']), 'distancia' => $distancia, 'lat' => $geoCliente['lat'], 'lng' => $geoCliente['lon'], 'endereco_sugerido' => $geoCliente['endereco_completo']]); } else { echo json_encode(['ok' => false, 'erro' => "Não entregamos nessa distância ($distancia km)."]); } 
        } catch (\Exception $e) { echo json_encode(['ok' => false, 'erro' => $e->getMessage()]); } 
    }

    public function buscarCliente() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        try { 
            $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''); $empresaId = $_POST['empresa_id'] ?? 0; 
            if (strlen($telefone) < 8) throw new \Exception('Telefone inválido'); 
            $db = Database::connect(); $stmt = $db->prepare("SELECT nome, endereco_ultimo, numero_ultimo, bairro_ultimo FROM clientes WHERE telefone = ? AND empresa_id = ? LIMIT 1"); 
            $stmt->execute([$telefone, $empresaId]); $cliente = $stmt->fetch(\PDO::FETCH_ASSOC); 
            echo json_encode(['encontrado' => !!$cliente, 'dados' => $cliente]); 
        } catch (\Exception $e) { echo json_encode(['encontrado' => false]); } 
    }

    public function calcularFreteGPS() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8'); 
        try {
            $filialId = $_POST['filial_id'] ?? 0; $latCli = $_POST['lat'] ?? 0; $lngCli = $_POST['lng'] ?? 0;
            $db = Database::connect(); $stmtLoja = $db->prepare("SELECT lat, lng FROM configuracoes_filial WHERE filial_id = ?");
            $stmtLoja->execute([$filialId]); $loja = $stmtLoja->fetch();
            if(!$loja || empty($loja['lat'])) throw new \Exception("Loja sem GPS");
            $distancia = $this->calcularDistancia($loja['lat'], $loja['lng'], $latCli, $lngCli);
            $stmtTaxa = $db->prepare("SELECT valor FROM taxas_entrega_km WHERE filial_id = ? AND km_max >= ? AND ativo = 1 ORDER BY km_max ASC LIMIT 1");
            $stmtTaxa->execute([$filialId, $distancia]); $taxa = $stmtTaxa->fetch();
            if ($taxa) echo json_encode(['ok' => true, 'valor' => floatval($taxa['valor']), 'endereco_sugerido' => null]);
            else echo json_encode(['ok' => false, 'erro' => "Fora da área ($distancia km)"]);
        } catch (\Exception $e) { echo json_encode(['ok'=>false, 'erro'=>$e->getMessage()]); }
    }

    public function meusPedidos() { 
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json'); 
        try {
            $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''); $empresaId = $_POST['empresa_id'] ?? 0;
            $model = new Pedido();
            if(method_exists($model, 'buscarPorTelefone')) {
                $pedidos = $model->buscarPorTelefone($empresaId, $telefone);
                $lista = [];
                foreach($pedidos as $p) {
                    $lista[] = ['id' => str_pad($p['id'], 4, '0', STR_PAD_LEFT), 'total' => number_format($p['valor_total'],2,',','.'), 'status' => ucfirst($p['status']), 'data' => date('d/m H:i', strtotime($p['created_at']))]; 
                }
                echo json_encode(['ok'=>true, 'pedidos'=>$lista]);
            } else { echo json_encode(['ok'=>true, 'pedidos'=>[]]); }
        } catch(\Exception $e) { echo json_encode(['ok'=>false]); }
    }
    // VALIDA SE A SESSÃO DO NAVEGADOR AINDA É VÁLIDA
    public function validarSessao() {
        header('Content-Type: application/json');
        // Limpa qualquer lixo de buffer
        while (ob_get_level()) { ob_end_clean(); }
        
        $sessaoId = $_POST['sessao_id'] ?? 0;
        $db = Database::connect();
        
        // Verifica se a sessão existe E se ainda está 'aberta'
        $stmt = $db->prepare("SELECT id FROM mesa_sessoes WHERE id = ? AND status = 'aberta'");
        $stmt->execute([$sessaoId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['valid' => true]);
        } else {
            echo json_encode(['valid' => false]);
        }
        exit;
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

    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) { 
        $earthRadius = 6371; $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1); 
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2); 
        $c = 2 * atan2(sqrt($a), sqrt(1-$a)); return round($earthRadius * $c, 1); 
    }

    private function geocodificar($endereco) {
        $apiKey = "AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U"; 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key=" . $apiKey; 
        $json = @file_get_contents($url); $data = json_decode($json, true); 
        if (isset($data['status']) && $data['status'] === 'OK') { 
            $comp = $data['results'][0]['address_components'];
            $rua = ''; $bairro = '';
            foreach($comp as $c) {
                if(in_array('route', $c['types'])) $rua = $c['long_name'];
                if(in_array('sublocality', $c['types'])) $bairro = $c['long_name'];
            }
            return ['lat' => $data['results'][0]['geometry']['location']['lat'], 'lon' => $data['results'][0]['geometry']['location']['lng'], 'endereco_completo' => ['rua' => $rua, 'bairro' => $bairro]]; 
        } 
        return null; 
    }

    // --- NOVO: TELA DE PERFIL DO CLIENTE ---
    public function perfil($empresa) {
        // Carrega configurações básicas igual ao index
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM filiais WHERE empresa_id = :id ORDER BY id ASC LIMIT 1");
        $stmt->execute(['id' => $empresa['id']]);
        $filial = $stmt->fetch();
        
        $whatsappLoja = $filial['telefone_whatsapp'] ?? '';
        
        require __DIR__ . '/../Views/cardapio/perfil.php';
    }

    // --- NOVO: API PARA BUSCAR DADOS COMPLETOS DO CLIENTE ---
    public function getDadosClienteCompleto() {
        header('Content-Type: application/json');
        
        // Limpa buffer de saída para evitar sujeira no JSON
        while (ob_get_level()) { ob_end_clean(); }

        try {
            $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
            $empresaId = $_POST['empresa_id'] ?? 0;

            if (strlen($telefone) < 8) throw new \Exception('Telefone inválido');

            $db = Database::connect();

            // 1. Busca Cadastro do Cliente (Para a aba "Meus Dados")
            // Tenta achar na tabela de clientes de delivery
            $stmt = $db->prepare("SELECT * FROM clientes WHERE telefone = ? AND empresa_id = ? LIMIT 1");
            $stmt->execute([$telefone, $empresaId]);
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Se não achar no delivery, tenta pegar o nome mais recente usado em mesa
            if (!$cliente) {
                $stmtMesa = $db->prepare("SELECT nome FROM mesa_participantes WHERE telefone = ? ORDER BY id DESC LIMIT 1");
                $stmtMesa->execute([$telefone]);
                $dadosMesa = $stmtMesa->fetch(\PDO::FETCH_ASSOC);
                
                // Monta um objeto cliente "fake" só para exibir o nome na tela
                $cliente = [
                    'nome' => $dadosMesa['nome'] ?? 'Cliente',
                    'endereco_ultimo' => '',
                    'numero_ultimo' => '',
                    'bairro_ultimo' => ''
                ];
            }

            // 2. Busca Histórico de Pedidos (Delivery + Salão)
            // A chave mágica é buscar pelo TELEFONE, que existe nos dois casos
            $sqlPedidos = "
                SELECT id, valor_total, status, created_at, tipo_entrega, 
                       (SELECT COUNT(*) FROM pedido_itens WHERE pedido_id = pedidos.id) as qtd_itens
                FROM pedidos 
                WHERE cliente_telefone = ? 
                AND empresa_id = ?
                ORDER BY id DESC LIMIT 50
            ";
            
            $stmtPed = $db->prepare($sqlPedidos);
            $stmtPed->execute([$telefone, $empresaId]);
            $pedidos = $stmtPed->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'ok' => true, 
                'cliente' => $cliente, 
                'pedidos' => $pedidos
            ]);

        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        }
        exit;
    }
}