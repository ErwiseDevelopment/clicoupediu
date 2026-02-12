<?php $titulo = "Monitor KDS"; require __DIR__ . '/../../partials/header.php'; ?>

<audio id="som_alerta" src="https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3" preload="auto"></audio>

<style>
    /* LAYOUT CLARO (IGUAL AO SISTEMA) */
    body { background-color: #f3f4f6; } /* Cinza Claro */
    .kanban-col { min-height: calc(100vh - 180px); }
    
    .card-pedido {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s;
        border-left: 5px solid transparent;
        padding: 14px;
    }
    .card-pedido:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

    /* IDENTIFICAÇÃO */
    .borda-mesa { border-left-color: #9333ea; }     /* Roxo */
    .borda-delivery { border-left-color: #2563eb; } /* Azul */
    
    .badge-mesa { background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; }
    .badge-delivery { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

    /* TEXTOS */
    .text-titulo { color: #111827; font-weight: 800; }
    .text-sub { color: #6b7280; font-size: 0.75rem; }
</style>

<div class="flex h-screen overflow-hidden font-sans bg-gray-100">
    
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shrink-0 z-20 shadow-sm">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-black text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tv text-orange-500"></i> MONITOR DE COZINHA
                </h1>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full border border-green-200 flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div> AO VIVO
                </span>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 mr-4 text-xs font-bold text-gray-500">
                    <div class="flex items-center gap-1"><span class="w-3 h-3 bg-purple-600 rounded"></span> MESA</div>
                    <div class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-600 rounded"></span> DELIVERY</div>
                </div>
                
                <div id="relogio" class="text-xl font-mono font-bold text-gray-600">00:00</div>
                
                <button onclick="ativarSom()" id="btn-som" class="bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-200 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-volume-mute"></i> SOM OFF
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto p-6 bg-gray-100">
            <div class="flex gap-6 h-full min-w-[1000px]">
                
                <div class="w-1/3 flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-full">
                    <div class="p-4 border-b border-gray-200 bg-white rounded-t-2xl flex justify-between items-center">
                        <div class="flex items-center gap-2 font-black text-gray-700 uppercase tracking-wide">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div> A Aceitar
                        </div>
                        <span id="count-analise" class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded border border-gray-200">0</span>
                    </div>
                    <div id="col-analise" class="kanban-col flex-1 overflow-y-auto p-4 custom-scroll"></div>
                </div>

                <div class="w-1/3 flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-full">
                    <div class="p-4 border-b border-gray-200 bg-white rounded-t-2xl flex justify-between items-center">
                        <div class="flex items-center gap-2 font-black text-gray-700 uppercase tracking-wide">
                            <div class="w-3 h-3 rounded-full bg-orange-500"></div> Preparando
                        </div>
                        <span id="count-preparo" class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded border border-gray-200">0</span>
                    </div>
                    <div id="col-preparo" class="kanban-col flex-1 overflow-y-auto p-4 custom-scroll"></div>
                </div>

                <div class="w-1/3 flex flex-col bg-gray-50 rounded-2xl border border-gray-200 h-full">
                    <div class="p-4 border-b border-gray-200 bg-white rounded-t-2xl flex justify-between items-center">
                        <div class="flex items-center gap-2 font-black text-gray-700 uppercase tracking-wide">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div> Entrega / Pronto
                        </div>
                        <span id="count-entrega" class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded border border-gray-200">0</span>
                    </div>
                    <div id="col-entrega" class="kanban-col flex-1 overflow-y-auto p-4 custom-scroll"></div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
    let somAtivo = false;
    let ultimoId = 0;

    // --- FUNÇÃO ATUALIZADA: VISUALIZAR ITENS ENTREGUES ---
    function criarCardHtml(p, coluna) {
        const isMesa = p.tipo_entrega === 'salao';
        const bordaClass = isMesa ? 'borda-mesa' : 'borda-delivery';
        const badgeClass = isMesa ? 'badge-mesa' : 'badge-delivery';
        const icone = isMesa ? 'fas fa-chair' : 'fas fa-motorcycle';
        const textoBadge = isMesa ? `MESA ${p.num_mesa}` : 'DELIVERY';
        
        let listaItens = '';
        if(p.itens && p.itens.length > 0) {
            p.itens.forEach(i => {
                // Trata status nulo como 'fila' para garantir exibição
                const status = i.status_item || 'fila'; 

                // Pula itens cancelados
                if (status === 'cancelado') return;

                let styleDiv = 'hover:bg-gray-50 cursor-pointer border-b border-gray-100';
                let styleTexto = 'text-gray-800';
                let iconeStatus = '<i class="far fa-square text-gray-300"></i>';
                let proximoStatus = 'entregue'; 

                if (status === 'entregue') {
                    styleDiv = 'bg-gray-50 opacity-50 cursor-pointer border-b border-gray-100'; 
                    styleTexto = 'text-gray-400 line-through decoration-2';
                    iconeStatus = '<i class="fas fa-check-double text-green-500"></i>';
                    proximoStatus = 'fila'; 
                } 
                else if (status === 'pronto') {
                    styleTexto = 'text-green-700 font-bold';
                    iconeStatus = '<i class="fas fa-check text-green-600"></i>';
                    proximoStatus = 'entregue';
                }

                let htmlAdds = '';
                if(i.complementos && i.complementos.length > 0) {
                    i.complementos.forEach(add => { 
                        let nomeAdd = typeof add === 'string' ? add : add.nome;
                        htmlAdds += `<div class="text-[11px] text-gray-500 ml-7 font-bold">+ ${nomeAdd}</div>`; 
                    });
                }

                let htmlObs = '';
                if(i.observacao_item) {
                    htmlObs = `<div class="ml-7 mt-1"><span class="text-[10px] bg-red-100 text-red-600 border border-red-200 font-black px-1 rounded uppercase tracking-wide">OBS: ${i.observacao_item}</span></div>`;
                }

                listaItens += `
                <div class="py-2 px-1 transition ${styleDiv}" onclick="alternarItem(${i.id}, '${proximoStatus}')">
                    <div class="flex items-center gap-2">
                        <div class="w-5 text-center">${iconeStatus}</div>
                        <span class="font-black text-sm ${styleTexto}">${parseInt(i.quantidade)}x</span>
                        <span class="text-sm font-bold leading-tight uppercase flex-1 ${styleTexto}">${i.nome}</span>
                    </div>
                    ${htmlAdds} ${htmlObs}
                </div>`;
            });
        }
        
        // Se não tiver itens visíveis (tudo cancelado ou vazio), mostra aviso
        if(listaItens === '') {
            listaItens = '<div class="p-2 text-center text-xs text-gray-400 italic">Sem itens ativos</div>';
        }

        const min = Math.floor((new Date() - new Date(p.created_at)) / 60000);
        let corTempo = 'text-gray-400 bg-gray-100';
        if(min > 20) corTempo = 'text-orange-600 bg-orange-100';
        if(min > 40) corTempo = 'text-red-600 bg-red-100 animate-pulse';

        let btnAcao = '';
        if(coluna === 'analise') {
            btnAcao = `<button onclick="mover(${p.id}, 'preparo')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs flex-1 transition shadow-md">ACEITAR <i class="fas fa-check ml-1"></i></button>`;
        } else if(coluna === 'preparo') {
            btnAcao = `<button onclick="mover(${p.id}, 'entrega')" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded text-xs flex-1 transition shadow-md">PRONTO <i class="fas fa-arrow-right ml-1"></i></button>`;
        } else {
            btnAcao = `<button onclick="mover(${p.id}, 'finalizado')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-xs flex-1 transition shadow-md">FINALIZAR <i class="fas fa-check-double ml-1"></i></button>`;
        }

        return `
        <div class="card-pedido ${bordaClass} animate-fade-in relative group">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-lg font-black text-gray-800">#${p.id}</span>
                    <span class="text-[10px] font-black px-2 py-0.5 rounded flex items-center gap-1 ${badgeClass}">
                        <i class="${icone}"></i> ${textoBadge}
                    </span>
                </div>
                <div class="text-xs font-mono font-bold px-1.5 py-0.5 rounded ${corTempo}">${min} min</div>
            </div>

            <div class="mb-3">
                <div class="font-bold text-gray-700 text-sm truncate uppercase">${p.cliente_nome || 'Consumidor'}</div>
                ${!isMesa ? `<div class="text-[10px] text-gray-400 truncate"><i class="fas fa-map-marker-alt"></i> ${p.bairro || 'Balcão'}</div>` : ''}
            </div>

            <div class="bg-white rounded p-1 mb-3 border border-gray-100 shadow-inner">
                ${listaItens}
            </div>

            <div class="flex gap-2">
                <button onclick="imprimirCozinha(${p.id})" class="bg-gray-800 hover:bg-black text-white p-2 rounded transition shadow-sm" title="Imprimir para Cozinha">
                    <i class="fas fa-print"></i>
                </button>
                <button onclick="cancelarPedido(${p.id})" class="bg-white border border-gray-200 text-red-400 hover:text-red-600 hover:bg-red-50 p-2 rounded transition" title="Cancelar Pedido"><i class="fas fa-trash-alt"></i></button>
                ${btnAcao}
            </div>
        </div>`;
    }

    function cancelarPedido(id) {
        if(!confirm("⚠️ Tem certeza que deseja CANCELAR este pedido?\n\nEsta ação não pode ser desfeita.")) return;
        
        const f = new FormData(); 
        f.append('id', id); 
        f.append('status', 'cancelado');
        
        fetch('<?= BASE_URL ?>/admin/pedidos/mudarStatus', { method: 'POST', body: f })
            .then(() => atualizarTela())
            .catch(e => alert("Erro ao cancelar"));
    }

    // --- NOVA FUNÇÃO: ALTERNAR STATUS ITEM ---
    function alternarItem(itemId, novoStatus) {
        // Evita propagação se clicar em botões internos (se houver)
        event.stopPropagation();

        const f = new FormData();
        f.append('item_id', itemId);
        f.append('status', novoStatus);

        // Chama o método novo que criamos no controller
        fetch('<?= BASE_URL ?>/admin/pedidos/mudarStatusItem', { method: 'POST', body: f })
            .then(() => atualizarTela()) // Recarrega para ver a mudança
            .catch(e => console.error("Erro ao mudar item", e));
    }

    function atualizarTela() {
        fetch('<?= BASE_URL ?>/admin/pedidos/kds?ajax=1')
            .then(r => r.json())
            .then(d => {
                renderizarColuna('col-analise', 'count-analise', d.analise, 'analise');
                renderizarColuna('col-preparo', 'count-preparo', d.preparo, 'preparo');
                renderizarColuna('col-entrega', 'count-entrega', d.entrega, 'entrega');

                let novoMaiorId = 0;
                d.analise.forEach(p => { if(p.id > novoMaiorId) novoMaiorId = p.id; });
                if(novoMaiorId > ultimoId && ultimoId !== 0) tocarSom();
                if(ultimoId === 0 && novoMaiorId > 0) ultimoId = novoMaiorId;
            })
            .catch(e => console.error("Erro", e));
    }

    function renderizarColuna(idCol, idCount, lista, tipo) {
        document.getElementById(idCount).innerText = lista.length;
        let html = '';
        lista.forEach(p => html += criarCardHtml(p, tipo));
        document.getElementById(idCol).innerHTML = html;
    }

    function mover(id, status) {
        const f = new FormData(); f.append('id', id); f.append('status', status);
        fetch('<?= BASE_URL ?>/admin/pedidos/mudarStatus', { method: 'POST', body: f }).then(() => atualizarTela());
    }

function imprimirCozinha(id) {
        // Cria um iframe oculto se não existir
        let iframe = document.getElementById('frame_print_cozinha');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'frame_print_cozinha';
            iframe.style.position = 'fixed';
            iframe.style.left = '-9999px'; // Esconde fora da tela
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
        }
        
        // Carrega o cupom no iframe
        // O próprio arquivo cupom_cozinha.php já tem um script que roda window.print() ao carregar
        iframe.src = '<?= BASE_URL ?>/admin/pedidos/imprimirCozinha?id=' + id;
    }

    
    setInterval(() => { document.getElementById('relogio').innerText = new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'}); }, 1000);
    setInterval(atualizarTela, 5000);
    atualizarTela();
</script>