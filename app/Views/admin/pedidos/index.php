<?php 
    // Mudamos o título para deixar claro que essa tela NÃO é do salão
    $titulo = "Monitor KDS (Delivery & Retirada)"; 
    require __DIR__ . '/../../partials/header.php'; 

    // --- FILTRO: REMOVE PEDIDOS DE MESA/SALÃO ---
    // Garante que o KDS mostre apenas logística de entrega e retirada
    $filtroSemSalao = function($p) {
        return isset($p['tipo_entrega']) && $p['tipo_entrega'] !== 'salao';
    };

    // Aplica o filtro nas 4 listas que vêm do Controller
    $analise     = array_filter($analise, $filtroSemSalao);
    $preparo     = array_filter($preparo, $filtroSemSalao);
    $entrega     = array_filter($entrega, $filtroSemSalao);
    $finalizados = array_filter($finalizados, $filtroSemSalao);

    // --- TOTALIZADOR (Calculado APÓS o filtro) ---
    $totalDia = array_sum(array_column($analise, 'valor_total')) + 
                array_sum(array_column($preparo, 'valor_total')) + 
                array_sum(array_column($entrega, 'valor_total')) + 
                array_sum(array_column($finalizados, 'valor_total'));

    // --- LÓGICA DE DATAS (Mantida igual) ---
    $dataAtual = $dataFiltro ?? date('Y-m-d');
    $dataAnterior = date('Y-m-d', strtotime('-1 day', strtotime($dataAtual)));
    $dataProxima = date('Y-m-d', strtotime('+1 day', strtotime($dataAtual)));
    $dataFormatada = date('d/m/Y', strtotime($dataAtual));
    $isHoje = ($dataAtual == date('Y-m-d'));
    ?>

    <style>
        /* Ajustes de rolagem e Kanban */
        .kanban-col { min-height: 200px; transition: 0.2s; }
        .custom-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        
        .kanban-card { border-radius: 12px; border: 1px solid #f3f4f6; margin-bottom: 12px; background: white; padding: 14px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s; cursor: grab; }
        .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-color: #e2e8f0; }
        .kanban-card:active { cursor: grabbing; }
        
        .card-id { font-weight: 800; font-size: 1rem; color: #111827; letter-spacing: -0.5px; }
        .card-time { font-size: 0.7rem; color: #9ca3af; font-weight: 600; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
        .card-client-name { font-weight: 700; color: #374151; font-size: 0.9rem; margin-bottom: 2px; }
        .card-address { font-size: 0.75rem; color: #6b7280; line-height: 1.3; }
        
        .btn-action { font-weight: 700; font-size: 0.8rem; padding: 8px; border-radius: 8px; transition: all 0.2s; width: 100%; text-align: center; margin-top: 8px; border: none; cursor: pointer; }
        .btn-proximo { background: #10b981; color: white; }
        .btn-proximo:hover { background: #059669; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3); }

        /* Oculta scrollbar horizontal no mobile para o slider ficar limpo */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        @media print {
            @page { margin: 0; size: 58mm auto; }
            body { background: white; }
            .flex.h-screen, #modalVenda, #modalMotoboy, .bg-white.px-6 { display: none !important; }
            #area_cupom { display: block !important; width: 58mm; padding: 2mm; font-family: 'Courier New', monospace; font-size: 11px; color: #000; }
            .cupom-line { border-bottom: 1px dashed #000; margin: 4px 0; display: block; width: 100%; }
            .text-center { text-align: center; }
        }
    </style>

    <div class="flex h-screen overflow-hidden bg-gray-100 font-sans">
        <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            
            <div class="bg-white px-4 md:px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shrink-0 z-20 shadow-sm">
                
                <div class="flex flex-col md:flex-row items-center gap-3 w-full md:flex-1">
                    <div class="relative w-full md:w-1/3">
                        <input type="text" placeholder="Buscar (Nome, Tel ou Código)" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 outline-none focus:ring-2 focus:ring-blue-100 transition text-sm">
                        <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    </div>

                    <div class="flex items-center justify-between md:justify-center w-full md:w-auto bg-gray-50 rounded-xl border border-gray-200 p-1">
                        <a href="?data=<?php echo $dataAnterior; ?>" class="w-10 h-10 md:w-8 md:h-8 flex items-center justify-center text-gray-500 hover:bg-white hover:shadow-sm rounded-lg transition" title="Dia Anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        
                        <form action="" method="GET" class="flex items-center mx-2 flex-1 justify-center">
                            <input type="date" name="data" value="<?php echo $dataAtual; ?>" class="bg-transparent border-none text-sm md:text-sm font-bold text-gray-700 outline-none cursor-pointer text-center w-full" onchange="this.form.submit()">
                        </form>

                        <a href="?data=<?php echo $dataProxima; ?>" class="w-10 h-10 md:w-8 md:h-8 flex items-center justify-center text-gray-500 hover:bg-white hover:shadow-sm rounded-lg transition" title="Próximo Dia">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <?php if(!$isHoje): ?>
                        <a href="?data=<?php echo date('Y-m-d'); ?>" class="hidden md:inline-block text-xs font-bold text-blue-600 hover:underline bg-blue-50 px-3 py-1.5 rounded-lg">
                            Ir para Hoje
                        </a>
                    <?php endif; ?>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button onclick="location.reload()" class="w-12 md:w-10 h-12 md:h-10 rounded-xl bg-gray-50 border border-gray-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition flex items-center justify-center shrink-0">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button onclick="abrirModalVenda()" class="flex-1 md:flex-none bg-red-600 text-white px-4 md:px-6 py-2.5 rounded-xl font-bold hover:bg-red-700 shadow-lg shadow-red-200 flex items-center justify-center gap-2 transition transform active:scale-95">
                        <i class="fas fa-plus"></i> Novo pedido
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col p-4 md:p-6 bg-gray-100">
                
                <div class="mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-2 shrink-0">
                    <h2 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                        <i class="far fa-calendar-alt text-gray-400"></i> 
                        Pedidos de <?php echo $dataFormatada; ?>
                    </h2>
                    <div class="text-sm font-bold text-gray-500 bg-white px-3 py-2 md:py-1 rounded-lg border border-gray-200 shadow-sm w-full md:w-auto text-center md:text-left">
                        Total do Dia: <span class="text-green-600">R$ <?php echo number_format($totalDia, 2, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="flex flex-nowrap md:flex-row gap-4 md:gap-6 overflow-x-auto no-scrollbar md:custom-scroll snap-x snap-mandatory flex-1 md:min-w-[1200px] pb-24 md:pb-0 items-start">
                    
                    <div class="w-[85vw] md:w-1/4 shrink-0 snap-center flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-[calc(100vh-280px)] md:h-full">
                        <div class="p-3 md:p-4 border-b border-gray-200 flex justify-between items-center bg-white rounded-t-2xl shrink-0">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <span class="font-bold text-gray-700 text-sm md:text-base">Análise</span>
                                <span class="bg-gray-100 text-gray-600 text-[10px] md:text-xs font-bold px-2 py-0.5 rounded-md"><?php echo count($analise); ?></span>
                            </div>
                            <div class="text-[10px] md:text-xs font-bold text-gray-400">R$ <?php echo number_format(array_sum(array_column($analise, 'valor_total')), 2, ',', '.'); ?></div>
                        </div>
                        <div id="col_analise" class="flex-1 overflow-y-auto p-3 space-y-3 custom-scroll" 
                            ondrop="drop(event, 'analise')" ondragover="allowDrop(event)">
                            <?php foreach($analise as $p): renderCard($p, 'analise'); endforeach; ?>
                        </div>
                    </div>

                    <div class="w-[85vw] md:w-1/4 shrink-0 snap-center flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-[calc(100vh-280px)] md:h-full">
                        <div class="p-3 md:p-4 border-b border-gray-200 flex justify-between items-center bg-white rounded-t-2xl shrink-0">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-orange-500 animate-pulse"></div>
                                <span class="font-bold text-gray-700 text-sm md:text-base">Preparo</span>
                                <span class="bg-gray-100 text-gray-600 text-[10px] md:text-xs font-bold px-2 py-0.5 rounded-md"><?php echo count($preparo); ?></span>
                            </div>
                        </div>
                        <div id="col_preparo" class="flex-1 overflow-y-auto p-3 space-y-3 custom-scroll"
                            ondrop="drop(event, 'preparo')" ondragover="allowDrop(event)">
                            <?php foreach($preparo as $p): renderCard($p, 'preparo'); endforeach; ?>
                        </div>
                    </div>

                    <div class="w-[85vw] md:w-1/4 shrink-0 snap-center flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-[calc(100vh-280px)] md:h-full">
                        <div class="p-3 md:p-4 border-b border-gray-200 flex justify-between items-center bg-white rounded-t-2xl shrink-0">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="font-bold text-gray-700 text-sm md:text-base">Entrega/Pronto</span>
                                <span class="bg-gray-100 text-gray-600 text-[10px] md:text-xs font-bold px-2 py-0.5 rounded-md"><?php echo count($entrega); ?></span>
                            </div>
                        </div>
                        <div id="col_entrega" class="flex-1 overflow-y-auto p-3 space-y-3 custom-scroll"
                            ondrop="drop(event, 'entrega')" ondragover="allowDrop(event)">
                            <?php foreach($entrega as $p): renderCard($p, 'entrega'); endforeach; ?>
                        </div>
                    </div>

                    <div class="w-[85vw] md:w-1/4 shrink-0 snap-center flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-[calc(100vh-280px)] md:h-full opacity-80">
                        <div class="p-3 md:p-4 border-b border-gray-200 flex justify-between items-center bg-white rounded-t-2xl shrink-0">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                                <span class="font-bold text-gray-600 text-sm md:text-base">Finalizados</span>
                                <span class="bg-gray-100 text-gray-600 text-[10px] md:text-xs font-bold px-2 py-0.5 rounded-md"><?php echo count($finalizados); ?></span>
                            </div>
                        </div>
                        <div id="col_finalizado" class="flex-1 overflow-y-auto p-3 space-y-3 custom-scroll"
                            ondrop="drop(event, 'finalizado')" ondragover="allowDrop(event)">
                            <?php foreach($finalizados as $p): renderCard($p, 'finalizado'); endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <div id="modalMotoboy" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-motorcycle text-orange-500"></i> Selecionar Motoboy</h3>
            <input type="hidden" id="pedido_avancar_id">
            <select id="select_motoboy_avancar" class="w-full border rounded-lg p-4 mb-5 outline-none font-bold text-gray-700 bg-gray-50 text-sm">
                <option value="">Sem motoboy (Em aberto)</option>
                <?php foreach($motoboys as $m): ?>
                    <option value="<?php echo $m['id']; ?>"><?php echo $m['nome']; ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex gap-2">
                <button onclick="document.getElementById('modalMotoboy').classList.add('hidden')" class="flex-1 bg-gray-100 py-3.5 rounded-xl font-bold text-gray-500 text-sm">CANCELAR</button>
                <button onclick="confirmarAvancoEntrega()" class="flex-1 bg-green-600 py-3.5 rounded-xl font-bold text-white shadow-lg text-sm">CONFIRMAR</button>
            </div>
        </div>
    </div>

    <div id="modalVenda" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center p-0 md:p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-[1200px] h-full md:h-[90vh] md:rounded-3xl shadow-2xl relative flex flex-col overflow-hidden animate-fade-in">
            <button onclick="fecharModalVenda()" class="absolute top-2 right-2 md:top-4 md:right-4 z-50 bg-white text-gray-500 hover:bg-red-50 hover:text-red-500 rounded-full w-10 h-10 font-black transition flex items-center justify-center shadow-md border border-gray-100 text-xl">✕</button>
            <?php require __DIR__ . '/_form_pdv.php'; ?>
        </div>
    </div>

    <div id="area_cupom" style="display:none;"></div>

    <?php 
    // Função para Renderizar Card (PHP)
    function renderCard($p, $status) {
        if ($status == 'preparo' && $p['tipo_entrega'] == 'retirada') {
            $btnLabel = 'Finalizar (Pronto)';
        } else {
            $btnLabel = match($status) {
                'analise' => 'Aceitar Pedido',
                'preparo' => 'Enviar Entrega',
                'entrega' => 'Finalizar',
                default => ''
            };
        }
        
        $isFinalizado = ($status == 'finalizado');
        ?>
        <div class="kanban-card group" id="pedido_<?php echo $p['id']; ?>" draggable="true" ondragstart="drag(event)">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <span class="card-id">#<?php echo str_pad($p['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    <span class="card-time"><?php echo date('H:i', strtotime($p['created_at'])); ?></span>
                </div>
                
                <div class="flex gap-1 md:opacity-0 group-hover:opacity-100 transition duration-200">
                    <?php if($isFinalizado): ?>
                        <button onclick="imprimirCupom(<?php echo $p['id']; ?>)" class="w-8 h-8 md:w-7 md:h-7 rounded bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center" title="Reimprimir"><i class="fas fa-print text-sm md:text-xs"></i></button>
                    <?php else: ?>
                        <button onclick="editarPedido(<?php echo $p['id']; ?>)" class="w-8 h-8 md:w-7 md:h-7 rounded bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center" title="Editar"><i class="fas fa-pen text-sm md:text-xs"></i></button>
                        <button onclick="imprimirCupom(<?php echo $p['id']; ?>)" class="w-8 h-8 md:w-7 md:h-7 rounded bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center" title="Imprimir"><i class="fas fa-print text-sm md:text-xs"></i></button>
                        <button onclick="chamarZap('<?php echo $p['cliente_telefone']; ?>', <?php echo $p['id']; ?>)" class="w-8 h-8 md:w-7 md:h-7 rounded bg-green-50 hover:bg-green-100 text-green-600 flex items-center justify-center" title="WhatsApp"><i class="fab fa-whatsapp text-sm md:text-xs"></i></button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <div class="card-client-name truncate"><?php echo $p['cliente_nome'] ?: 'Consumidor'; ?></div>
                <?php if($p['tipo_entrega'] == 'entrega'): ?>
                    <div class="card-address flex items-start gap-1">
                        <i class="fas fa-map-marker-alt text-[10px] text-gray-400 mt-0.5 shrink-0"></i>
                        <span class="line-clamp-2"><?php echo $p['bairro']; ?>, <?php echo $p['endereco_entrega']; ?>, <?php echo $p['numero']; ?></span>
                    </div>
                <?php else: ?>
                    <div class="text-xs font-bold text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded mt-1">RETIRADA</div>
                <?php endif; ?>
            </div>

            <div class="flex justify-between items-end border-t border-gray-50 pt-3 mt-1">
                <div>
                    <div class="text-sm md:text-base font-black text-gray-800">R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></div>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <span class="text-[9px] font-bold text-gray-500 uppercase bg-gray-100 px-1.5 py-0.5 rounded"><?php echo $p['forma_pagamento']; ?></span>
                        <?php if($p['forma_pagamento'] == 'dinheiro' && $p['troco_para'] > 0): ?>
                            <span class="text-[9px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">Troco: R$ <?php echo number_format($p['troco_para'], 2, ',', '.'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if(!$isFinalizado): ?>
                <div class="flex gap-2 w-1/2 justify-end">
                    <?php if($status == 'analise'): ?>
                        <button onclick="excluirPedido(<?php echo $p['id']; ?>)" class="w-10 h-10 md:w-8 md:h-8 shrink-0 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition"><i class="fas fa-trash-alt"></i></button>
                    <?php endif; ?>
                    
                    <button onclick="<?php 
                        if($status == 'analise') {
                            echo "aceitarPedido(".$p['id'].")";
                        }
                        elseif($status == 'preparo') {
                            echo ($p['tipo_entrega'] == 'retirada') 
                                ? "moverStatus(".$p['id'].", 'finalizado')" 
                                : "avancarParaEntrega(".$p['id'].")";
                        }
                        else {
                            echo "moverStatus(".$p['id'].", 'finalizado')";
                        }
                    ?>" class="btn-action btn-proximo flex-1 text-xs md:text-xs !mt-0 h-10 md:h-8 flex items-center justify-center py-0"><?php echo $btnLabel; ?></button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php } ?>

    <script>
        // --- LÓGICA DE KANBAN / STATUS / MOTOBOY ---
        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev) { ev.dataTransfer.setData("text", ev.target.id); }
        function drop(ev, status) {
            ev.preventDefault();
            var data = ev.dataTransfer.getData("text");
            var pedidoId = data.split('_')[1];
            
            // Atualiza visualmente (opcional, pois o reload atualiza)
            ev.currentTarget.appendChild(document.getElementById(data));

            // Atualiza no banco
            fetch('<?php echo BASE_URL; ?>/admin/pedidos/mudarStatus', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + pedidoId + '&status=' + status
            }).then(() => location.reload());
        }

        function aceitarPedido(id) {
            fetch('<?php echo BASE_URL; ?>/admin/pedidos/mudarStatus', {
                method: 'POST',
                body: new URLSearchParams({id: id, status: 'preparo'})
            }).then(r => r.json()).then(d => {
                if(d.ok) {
                    imprimirCupom(id);
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }

        function avancarParaEntrega(id) {
            document.getElementById('pedido_avancar_id').value = id;
            document.getElementById('modalMotoboy').classList.remove('hidden');
        }

        function confirmarAvancoEntrega() {
            const id = document.getElementById('pedido_avancar_id').value;
            const motoboyId = document.getElementById('select_motoboy_avancar').value;
            let formData = new FormData();
            formData.append('pedido_id', id);
            formData.append('motoboy_id', motoboyId);

            fetch('<?php echo BASE_URL; ?>/admin/pedidos/vincularMotoboyAjax', { method: 'POST', body: formData })
            .then(() => moverStatus(id, 'entrega'));
        }

        function moverStatus(id, status) {
            fetch('<?php echo BASE_URL; ?>/admin/pedidos/mudarStatus', {
                method: 'POST',
                body: new URLSearchParams({id: id, status: status})
            }).then(() => location.reload());
        }

        function excluirPedido(id) {
            if(confirm('Deseja realmente excluir este pedido?')) {
                fetch('<?php echo BASE_URL; ?>/admin/pedidos/excluir', {
                    method: 'POST', 
                    body: new URLSearchParams({id: id})
                }).then(() => location.reload());
            }
        }

        function chamarZap(t, id) { 
            window.open(`https://wa.me/55${t.replace(/\D/g, '')}?text=Pedido%20#${id}`, '_blank'); 
        }

        function imprimirCupom(id) {
            const idFrame = 'frame_impressao_oculto';
            let oldFrame = document.getElementById(idFrame);
            if (oldFrame) oldFrame.remove();

            const iframe = document.createElement('iframe');
            iframe.id = idFrame;
            iframe.style.position = 'absolute';
            iframe.style.top = '-10000px';
            iframe.style.left = '-10000px';
            iframe.style.width = '1px';
            iframe.style.height = '1px';
            
            iframe.src = '<?php echo BASE_URL; ?>/admin/pedidos/imprimir?id=' + id;
            document.body.appendChild(iframe);

            iframe.onload = function() {
                setTimeout(() => {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.open(iframe.src, '_blank');
                    }
                }, 500);
            };
        }
    </script>