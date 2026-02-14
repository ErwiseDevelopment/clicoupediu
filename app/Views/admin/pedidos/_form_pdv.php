<style>
    /* CSS DO MODAL DE VENDAS */
    @media (max-width: 768px) {
        #modalVenda { padding: 0 !important; align-items: flex-start !important; }
        #modalVenda > div { height: 100vh !important; max-height: 100vh !important; width: 100vw !important; max-width: 100vw !important; border-radius: 0 !important; display: flex; flex-direction: column; }
        .custom-scroll::-webkit-scrollbar { width: 0px; background: transparent; }
        #grid_produtos { grid-template-columns: repeat(2, 1fr); gap: 10px; padding-bottom: 80px; }
        .prod-card h4 { font-size: 13px; line-height: 1.2; }
        .mobile-hidden { display: none !important; }
    }
    @media (min-width: 769px) {
        .mobile-only { display: none !important; }
        #modalVenda > div { height: 90vh; max-height: 800px; max-width: 1200px; }
    }
    
    #modalConfigProduto { z-index: 150; } 
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="flex flex-col h-full bg-gray-50 w-full relative">

    <div class="mobile-only flex shrink-0 bg-white border-b border-gray-200 shadow-sm z-40 h-12">
        <button onclick="mudarAba('produtos')" id="tab_prod" class="flex-1 font-bold text-xs text-blue-600 border-b-4 border-blue-600 transition bg-blue-50 flex items-center justify-center gap-2">
            <i class="fas fa-utensils"></i> PRODUTOS
        </button>
        <button onclick="mudarAba('carrinho')" id="tab_cart" class="flex-1 font-bold text-xs text-gray-500 border-b-4 border-transparent transition relative flex items-center justify-center gap-2">
            <i class="fas fa-shopping-cart"></i> CESTA
            <span id="badge_mobile" class="bg-red-600 text-white text-[9px] px-1.5 py-0.5 rounded-full hidden">0</span>
        </button>
    </div>

    <div class="flex flex-1 overflow-hidden md:flex-row flex-col h-full relative">

        <div id="view_produtos" class="flex-1 flex flex-col h-full bg-white border-r border-gray-200 w-full md:w-1/2 overflow-hidden transition-all">
            
            <div class="p-3 bg-white shadow-sm shrink-0 z-10 border-b border-gray-100">
                <div class="relative mb-2">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="busca_prod" onkeyup="filtrarProdutos()" placeholder="Buscar item..." 
                           class="w-full pl-10 p-2.5 bg-gray-100 border-0 rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div class="flex gap-2 overflow-x-auto pb-1 custom-scroll no-scrollbar">
                    <button type="button" onclick="filtrarCategoria('all')" class="cat-btn bg-gray-900 text-white px-3 py-1.5 rounded-md text-xs font-bold whitespace-nowrap shrink-0">Todos</button>
                    <?php foreach($categorias as $cat): ?>
                        <button type="button" onclick="filtrarCategoria(<?php echo $cat['id']; ?>)" class="cat-btn bg-white border border-gray-200 text-gray-600 px-3 py-1.5 rounded-md text-xs font-bold whitespace-nowrap hover:bg-gray-50 shrink-0" data-id="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-3 bg-gray-50 custom-scroll">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="grid_produtos">
                    <?php 
                    foreach($produtosPDV as $prod): 
                        $controlaEstoque = isset($prod['controle_estoque']) ? $prod['controle_estoque'] : 1;
                        $estoque = intval($prod['estoque_atual'] ?? 0);
                        $esgotado = ($controlaEstoque == 1 && $estoque <= 0);
                        
                        $precoBase = (float)($prod['preco_base'] ?? 0);
                        $precoPromo = (float)($prod['preco_promocional'] ?? 0);
                        $temPromo = ($precoPromo > 0 && $precoPromo < $precoBase);
                        $precoFinal = $temPromo ? $precoPromo : $precoBase;
                        $isInterno = (isset($prod['visivel_online']) && $prod['visivel_online'] == 0);
                        
                        // BLINDAGEM DE DADOS
                        $nomeProd = $prod['nome'] ?? 'Item sem nome';
                        $catId = $prod['categoria_id'] ?? 0;
                        $temAdicionais = $prod['tem_adicionais'] ?? 0; // Vindo do Controller
                        $imgUrl = !empty($prod['imagem_url']) ? $prod['imagem_url'] : '';
                        if ($imgUrl && strpos($imgUrl, 'http') === false) { $imgUrl = BASE_URL . '/' . $imgUrl; }

                        $prodJS = $prod;
                        $prodJS['nome'] = $nomeProd;
                        $prodJS['preco_real'] = $precoFinal;
                        $prodJS['sem_estoque'] = ($controlaEstoque == 0); 
                        $prodJS['tem_adicionais'] = $temAdicionais;
                    ?>
                    <div onclick='verificarProduto(<?php echo json_encode($prodJS); ?>)' 
                         data-cat="<?php echo $catId; ?>" 
                         data-nome="<?php echo strtolower($nomeProd); ?>"
                         class="prod-card bg-white p-2 rounded-xl shadow-sm border border-gray-100 relative active:scale-95 transition flex flex-col <?php echo $esgotado ? 'opacity-50 pointer-events-none' : ''; ?> cursor-pointer hover:border-blue-300">
                         
                        <div class="absolute top-2 left-2 z-10 flex flex-col gap-1 items-start">
                            <?php if(isset($prod['tipo']) && $prod['tipo'] == 'combo'): ?>
                                <span class="bg-orange-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">COMBO</span>
                            <?php endif; ?>
                            <?php if($temAdicionais == 1): ?>
                                <span class="bg-purple-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">+ OPÇÕES</span>
                            <?php endif; ?>
                        </div>

                        <div class="aspect-video w-full bg-gray-200 rounded-lg mb-2 relative overflow-hidden shrink-0">
                            <?php if($imgUrl): ?>
                                <img src="<?php echo $imgUrl; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-300"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            
                            <?php if($esgotado): ?>
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center text-white text-[10px] font-bold uppercase">Esgotado</div>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-col flex-1 justify-between">
                            <h4 class="font-bold text-gray-700 mb-1 leading-tight line-clamp-2 text-xs md:text-sm"><?php echo $nomeProd; ?></h4>
                            
                            <?php if($controlaEstoque == 1): ?>
                                <div class="text-[10px] font-bold px-1.5 py-0.5 rounded inline-block w-max mb-1 <?php echo $estoque > 0 ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100'; ?>">
                                    Estoque: <?php echo $estoque; ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-green-600 font-black text-sm">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></span>
                                <div class="bg-blue-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] shadow"><i class="fas fa-plus"></i></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button onclick="mudarAba('carrinho')" class="mobile-only absolute bottom-4 left-4 right-4 bg-green-600 text-white py-3 rounded-xl shadow-xl flex justify-between px-6 font-bold animate-pulse z-40">
                <span>Ver Carrinho</span>
                <span id="resumo_mobile">R$ 0,00</span>
            </button>
        </div>

        <div id="view_carrinho" class="mobile-hidden flex-1 flex flex-col h-full bg-white w-full md:w-[450px] md:max-w-[450px] shrink-0 border-l border-gray-200 shadow-2xl z-20">
            
            <form id="formPedido" class="flex-1 flex flex-col overflow-hidden" onsubmit="return false;">
                <input type="hidden" name="pedido_id" id="edit_id">
                <input type="hidden" name="itens_json" id="itens_json">
                <input type="hidden" name="valor_produtos" id="input_valor_produtos">
                <input type="hidden" name="valor_total" id="input_valor_total">
                <input type="hidden" name="lat_entrega" id="lat_entrega_hidden">
                <input type="hidden" name="lng_entrega" id="lng_entrega_hidden">
                <input type="hidden" name="desconto" id="desconto" value="0.00">

                <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50 custom-scroll">
                    
                    <div class="bg-white p-1 rounded-lg border border-gray-200 flex shadow-sm">
                        <button type="button" onclick="mudarTipo('entrega')" id="btn_entrega" class="flex-1 py-2 text-xs font-bold rounded-md bg-blue-100 text-blue-700 transition"><i class="fas fa-motorcycle"></i> ENTREGA</button>
                        <button type="button" onclick="mudarTipo('retirada')" id="btn_retirada" class="flex-1 py-2 text-xs font-bold rounded-md text-gray-500 hover:bg-gray-100 transition"><i class="fas fa-store"></i> RETIRADA</button>
                        <input type="hidden" name="tipo_entrega" id="tipo_entrega" value="entrega">
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <div class="grid grid-cols-12 gap-2">
                            <div class="col-span-5">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">WhatsApp</label>
                                <input type="tel" name="cliente_telefone" id="telefone" onblur="buscarCliente()" class="w-full border border-gray-300 rounded p-2 text-sm font-bold bg-gray-50 focus:bg-white" placeholder="Só números">
                            </div>
                            <div class="col-span-7">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Nome</label>
                                <input type="text" name="cliente_nome" id="nome_cliente" class="w-full border border-gray-300 rounded p-2 text-sm bg-gray-50 focus:bg-white" placeholder="Nome do Cliente">
                            </div>
                        </div>
                    </div>

                    <div id="area_endereco" class="bg-white p-3 rounded-xl border border-blue-200 shadow-sm relative">
                        <label class="text-[10px] font-bold text-blue-600 uppercase mb-1 block">Endereço de Entrega</label>
                        <div class="relative mb-2">
                            <input type="text" id="busca_endereco" placeholder="Buscar no Google Maps..." class="w-full border border-gray-300 rounded pl-2 pr-8 py-2 text-xs" autocomplete="off">
                            <i class="fas fa-map-marker-alt absolute right-2 top-2.5 text-red-500"></i>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <input type="text" name="endereco_entrega" id="logradouro" placeholder="Rua" class="col-span-3 border border-gray-200 rounded p-2 text-xs bg-gray-50 text-gray-500" readonly>
                            <input type="text" name="numero" id="numero" placeholder="Nº" class="col-span-1 border border-blue-300 rounded p-2 text-xs font-bold text-gray-900" onblur="calcularFrete()">
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <input type="text" name="bairro" id="bairro" placeholder="Bairro" class="border border-gray-200 rounded p-2 text-xs bg-gray-50 text-gray-500" readonly>
                            <div class="relative">
                                <span class="absolute left-2 top-2 text-xs text-gray-400">Taxa R$</span>
                                <input type="text" name="taxa_entrega" id="input_taxa_entrega" placeholder="0,00" class="w-full border border-gray-200 rounded p-2 pl-14 text-xs font-bold text-right bg-white focus:border-blue-500" onkeyup="mascaraMoeda(this); atualizarTotais(null);">
                            </div>
                        </div>
                        <input type="text" name="complemento" id="complemento" placeholder="Complemento (Apto, Bloco...)" class="w-full border border-gray-200 rounded p-2 text-xs bg-gray-50 focus:bg-white">
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-2 border-b border-gray-100 pb-1">
                            <span class="text-xs font-bold text-gray-500 uppercase">Itens do Pedido</span>
                            <button type="button" onclick="mudarAba('produtos')" class="text-[10px] font-bold text-blue-600 mobile-only py-1 px-2 rounded hover:bg-blue-50">+ Adicionar</button>
                        </div>
                        <div id="lista_carrinho" class="min-h-[50px] space-y-2"></div>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm mb-10">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Pagamento</label>
                                <select name="forma_pagamento" id="forma_pagamento" class="w-full border border-gray-300 rounded p-2 text-xs bg-white" onchange="verificarTroco()">
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX (QR Code)</option>
                                    <option value="cartao">Cartão</option>
                                    <option value="fiado">Fiado / A Prazo</option>
                                </select>
                            </div>
                            
                            <div id="div_troco">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Troco p/</label>
                                <input type="text" name="troco_para" id="troco_para" class="w-full border border-gray-300 rounded p-2 text-xs" placeholder="R$ 0,00" onkeyup="mascaraMoeda(this)">
                            </div>
                        </div>

                        <div id="container_pix" class="hidden mt-3 p-3 bg-blue-50 rounded border border-blue-100 text-center">
                            <div class="flex items-center justify-center gap-2 mb-2 pb-2 border-b border-blue-200">
                                <input type="checkbox" id="check_gerar_qrcode" checked class="h-4 w-4 text-blue-600 rounded" onchange="toggleQrCode()">
                                <label for="check_gerar_qrcode" class="text-xs font-bold text-blue-800 cursor-pointer">Gerar QR Code Agora</label>
                            </div>
                            <div id="area_qrcode_visual">
                                <?php if(!empty($chavePixLoja)): ?>
                                    <p class="text-[10px] text-gray-500 mb-1">Chave: <strong class="text-gray-700"><?php echo $chavePixLoja; ?></strong></p>
                                    <div id="qrcode_pix" class="bg-white p-2 inline-block mb-2 rounded shadow-sm"></div>
                                    <textarea id="pix_copia_cola" readonly class="w-full text-[9px] p-2 rounded border border-gray-300 h-12 resize-none bg-white text-gray-500"></textarea>
                                    <button type="button" onclick="copiarPix()" class="mt-1 w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded transition shadow-md active:scale-95">
                                        <i class="far fa-copy mr-1"></i> COPIAR CÓDIGO
                                    </button>
                                <?php else: ?>
                                    <div class="text-xs text-red-500 font-bold py-2">
                                        <i class="fas fa-exclamation-circle"></i> Nenhuma chave PIX cadastrada.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border-t border-gray-200 z-30 shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
                    <div class="flex justify-between items-end mb-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total a Pagar</span>
                        <div class="flex items-center text-gray-800">
                            <span class="text-sm font-bold mr-1 text-gray-400">R$</span>
                            <input type="text" id="display_total_input" class="text-3xl font-black bg-transparent border-none outline-none text-right w-36 p-0" value="0,00" readonly>
                        </div>
                    </div>
                    
                    <div id="aviso_fora_area" class="hidden mb-2 text-center text-xs font-bold text-red-600 bg-red-50 p-2 rounded">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Endereço fora da área de entrega!
                    </div>

                    <button type="button" onclick="salvarPedido()" class="w-full bg-green-600 hover:bg-green-700 text-white py-3.5 rounded-xl font-bold text-base shadow-lg transition active:scale-95 flex items-center justify-center gap-2">
                        <span>FINALIZAR PEDIDO</span> <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalConfigProduto" class="fixed inset-0 bg-black/70 hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-gray-800" id="modal_config_titulo">Configurar Item</h3>
            <button onclick="fecharModalConfig()" class="w-8 h-8 rounded-full bg-white text-gray-500 flex items-center justify-center hover:text-red-500 font-bold shadow-sm">✕</button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 custom-scroll" id="modal_config_conteudo">
            <div class="text-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i></div>
        </div>

        <div class="p-4 border-t border-gray-100 bg-white">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Observação do Item</label>
            <textarea id="modal_config_obs" rows="2" class="w-full border border-gray-300 rounded-lg p-2 text-sm mb-3 focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Ex: Sem cebola..."></textarea>
            
            <div class="flex gap-3">
                <div class="flex items-center border border-gray-200 rounded-lg px-2">
                    <button onclick="ajustarQtdConfig(-1)" class="w-8 h-full font-bold text-gray-500 hover:text-red-500">-</button>
                    <span id="modal_config_qtd" class="w-8 text-center font-bold">1</span>
                    <button onclick="ajustarQtdConfig(1)" class="w-8 h-full font-bold text-blue-600 hover:text-blue-700">+</button>
                </div>
                <button onclick="confirmarConfigProduto()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow-md flex justify-between px-4 items-center">
                    <span>ADICIONAR</span>
                    <span id="modal_config_total">R$ 0,00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var carrinho = [];
    var entregaPermitida = true;
    var chavePixLoja = "<?php echo $chavePixLoja ?? ''; ?>";
    var nomeLojaPix  = "<?php echo $nomeLojaPix ?? 'LOJA'; ?>";
    var cidadeLojaPix = "<?php echo $cidadeLojaPix ?? 'CIDADE'; ?>";
    var produtoEmEdicao = null;

    function mudarAba(aba) {
        const viewProd = document.getElementById('view_produtos');
        const viewCart = document.getElementById('view_carrinho');
        const tabProd = document.getElementById('tab_prod');
        const tabCart = document.getElementById('tab_cart');

        if (aba === 'produtos') {
            viewProd.classList.remove('mobile-hidden');
            viewCart.classList.add('mobile-hidden');
            tabProd.className = "flex-1 font-bold text-sm text-blue-600 border-b-4 border-blue-600 bg-blue-50 flex items-center justify-center gap-2";
            tabCart.className = "flex-1 font-bold text-sm text-gray-500 border-b-4 border-transparent flex items-center justify-center gap-2";
        } else {
            viewProd.classList.add('mobile-hidden');
            viewCart.classList.remove('mobile-hidden');
            viewCart.classList.add('flex');
            tabProd.className = "flex-1 font-bold text-sm text-gray-500 border-b-4 border-transparent flex items-center justify-center gap-2";
            tabCart.className = "flex-1 font-bold text-sm text-blue-600 border-b-4 border-blue-600 bg-blue-50 flex items-center justify-center gap-2";
        }
    }

    function abrirModalVenda() {
        const form = document.getElementById('formPedido');
        if (form) form.reset();

        if(document.getElementById('edit_id')) document.getElementById('edit_id').value = '';
        if(document.getElementById('lista_carrinho')) document.getElementById('lista_carrinho').innerHTML = '';
        if(document.getElementById('display_total_input')) document.getElementById('display_total_input').value = '0,00';
        
        carrinho = [];
        verificarTroco();
        
        const m = document.getElementById('modalVenda');
        if (m) {
            m.classList.remove('hidden'); 
            m.classList.add('flex');
        }
        
        mudarAba('produtos');
    }

    function fecharModalVenda() {
        document.getElementById('modalVenda').classList.add('hidden');
        document.getElementById('modalVenda').classList.remove('flex');
    }

    // --- CARREGA DADOS PARA EDIÇÃO ---
    function editarPedido(id) {
        abrirModalVenda();
        
        fetch('<?= BASE_URL ?>/admin/pedidos/getDadosPedido?id=' + id)
            .then(r => r.json())
            .then(d => {
                if(d.pedido) {
                    const p = d.pedido;
                    document.getElementById('edit_id').value = p.id;
                    document.getElementById('nome_cliente').value = p.cliente_nome;
                    document.getElementById('telefone').value = p.cliente_telefone;
                    
                    document.getElementById('tipo_entrega').value = p.tipo_entrega;
                    mudarTipo(p.tipo_entrega);

                    document.getElementById('logradouro').value = p.endereco_entrega;
                    document.getElementById('numero').value = p.numero;
                    document.getElementById('bairro').value = p.bairro;
                    document.getElementById('complemento').value = p.complemento;
                    document.getElementById('input_taxa_entrega').value = parseFloat(p.taxa_entrega).toFixed(2).replace('.', ',');
                    
                    document.getElementById('forma_pagamento').value = p.forma_pagamento;
                    
                    if (p.forma_pagamento === 'dinheiro' && parseFloat(p.troco_para) > 0) {
                        document.getElementById('troco_para').value = parseFloat(p.troco_para).toFixed(2).replace('.', ',');
                    }
                    verificarTroco();

                    if (d.itensCarrinho) {
                        carrinho = d.itensCarrinho.map(item => {
                            if (!item.adicionais) item.adicionais = [];
                            return item;
                        });
                        renderCarrinho();
                    }
                }
            });
    }

    // --- LÓGICA DE PRODUTOS ---
    function verificarProduto(prod) {
        // Se a flag tem_adicionais for 1, abre modal.
        if (prod.tem_adicionais == 1) {
            abrirConfiguracaoProduto(prod);
        } else {
            addCarrinho(prod);
        }
    }

    function abrirConfiguracaoProduto(prod) {
        produtoEmEdicao = JSON.parse(JSON.stringify(prod));
        produtoEmEdicao.qtd_temp = 1;
        produtoEmEdicao.adicionais_temp = [];
        
        document.getElementById('modalConfigProduto').classList.remove('hidden');
        document.getElementById('modalConfigProduto').classList.add('flex');
        document.getElementById('modal_config_titulo').innerText = prod.nome;
        document.getElementById('modal_config_obs').value = '';
        document.getElementById('modal_config_qtd').innerText = '1';
        
        const container = document.getElementById('modal_config_conteudo');
        container.innerHTML = '<div class="text-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><br>Carregando...</div>';
        
        atualizarTotalConfig();

        // URL de Debug
        const url = '<?= BASE_URL ?>/admin/pedidos/buscaradicionais?id=' + prod.id;
        console.log("Tentando acessar URL:", url);

        fetch(url)
            .then(r => r.text()) 
            .then(texto => {
                // --- DEBUG AQUI ---
                console.log("RESPOSTA BRUTA DO SERVIDOR:", texto);
                // ------------------

                try {
                    // Tenta ler como JSON
                    const d = JSON.parse(texto.trim().replace(/^\uFEFF/, ''));
                    if(d.ok) {
                        renderizarComplementosModal(d.grupos);
                    } else {
                        container.innerHTML = '<div class="p-4 text-center text-red-500 font-bold">Erro no JSON: ' + (d.erro || 'Desconhecido') + '</div>';
                    }
                } catch (e) {
                    // SE CAIR AQUI, É PORQUE VEIO HTML
                    // Vamos mostrar os primeiros 100 caracteres do erro na tela para você saber o que é
                    let previaErro = texto.substring(0, 200).replace(/</g, "&lt;");
                    
                    container.innerHTML = `
                        <div class="p-4 bg-red-50 border border-red-200 rounded text-center text-xs text-left">
                            <h4 class="text-red-700 font-bold mb-2 text-center">Erro: Resposta não é JSON</h4>
                            <p><strong>O que chegou do servidor:</strong></p>
                            <pre class="bg-white p-2 border mt-1" style="white-space: pre-wrap;">${previaErro}...</pre>
                            <p class="mt-2 text-gray-500 text-center">Abra o Console (F12) para ver a resposta completa.</p>
                        </div>
                    `;
                }
            })
            .catch(e => {
                console.error(e);
                container.innerHTML = '<div class="text-center text-red-500 py-4 font-bold">Erro de Rede/Internet</div>';
            });
    }

    function renderizarComplementosModal(grupos) {
        const container = document.getElementById('modal_config_conteudo');
        let html = '';
        grupos.forEach(g => {
            const min = parseInt(g.minimo); const max = parseInt(g.maximo);
            html += `<div class="mb-4 bg-gray-50 rounded-lg border border-gray-100 group-adicional" data-min="${min}" data-max="${max}" data-id="${g.id}"><div class="bg-gray-100 px-3 py-2 rounded-t-lg border-b border-gray-200 flex justify-between items-center"><div><h4 class="font-bold text-sm text-gray-700 uppercase">${g.nome}</h4><p class="text-[10px] text-gray-500">Escolha até ${max} opções ${min > 0 ? '(Mínimo '+min+')' : ''}</p></div>${g.obrigatorio == 1 ? '<span class="bg-gray-800 text-white text-[9px] px-2 py-0.5 rounded font-bold">OBRIGATÓRIO</span>' : ''}</div><div class="p-2 space-y-1">`;
            g.itens.forEach(item => {
                let inputType = max == 1 ? 'radio' : 'checkbox'; let inputName = `add_${g.id}`; let precoTxt = parseFloat(item.preco) > 0 ? '+ R$ ' + parseFloat(item.preco).toFixed(2).replace('.',',') : 'Grátis';
                let nomeSafe = item.nome.replace(/"/g, '&quot;');
                html += `<label class="flex justify-between items-center p-2 bg-white border border-gray-100 rounded cursor-pointer hover:border-blue-300 transition"><div class="flex items-center gap-2"><input type="${inputType}" name="${inputName}" value="${item.id}" data-preco="${item.preco}" data-nome="${nomeSafe}" class="text-blue-600 focus:ring-blue-500" onchange="calcularConfigAdicionais()"><span class="text-sm font-medium text-gray-700">${item.nome}</span></div><span class="text-xs font-bold text-gray-500">${precoTxt}</span></label>`;
            });
            html += `</div></div>`;
        });
        container.innerHTML = html;
    }
    function calcularConfigAdicionais() {
        let totalAdicionais = 0;
        produtoEmEdicao.adicionais_temp = [];

        document.querySelectorAll('#modal_config_conteudo input:checked').forEach(input => {
            let preco = parseFloat(input.dataset.preco);
            let nome = input.dataset.nome;
            let id = input.value;
            
            totalAdicionais += preco;
            produtoEmEdicao.adicionais_temp.push({
                id: id,
                nome: nome,
                preco: preco
            });
        });
        
        atualizarTotalConfig(totalAdicionais);
    }

    function atualizarTotalConfig(totalAdicionais = 0) {
        if(!produtoEmEdicao) return;
        
        if(totalAdicionais === 0 && produtoEmEdicao.adicionais_temp.length > 0) {
             produtoEmEdicao.adicionais_temp.forEach(a => totalAdicionais += a.preco);
        }

        let totalUnitario = parseFloat(produtoEmEdicao.preco_real) + totalAdicionais;
        let totalFinal = totalUnitario * produtoEmEdicao.qtd_temp;
        
        document.getElementById('modal_config_total').innerText = 'R$ ' + totalFinal.toFixed(2).replace('.', ',');
    }

    function ajustarQtdConfig(delta) {
        let novaQtd = produtoEmEdicao.qtd_temp + delta;
        if(novaQtd < 1) novaQtd = 1;
        produtoEmEdicao.qtd_temp = novaQtd;
        document.getElementById('modal_config_qtd').innerText = novaQtd;
        calcularConfigAdicionais();
    }

    function fecharModalConfig() {
        document.getElementById('modalConfigProduto').classList.add('hidden');
        document.getElementById('modalConfigProduto').classList.remove('flex');
        produtoEmEdicao = null;
    }

    function confirmarConfigProduto() {
        let erro = false;
        document.querySelectorAll('.group-adicional').forEach(grupo => {
            let min = parseInt(grupo.dataset.min);
            let checked = grupo.querySelectorAll('input:checked').length;
            if(checked < min) {
                alert(`Selecione pelo menos ${min} opção(ões) em "${grupo.querySelector('h4').innerText}"`);
                erro = true;
            }
        });
        
        if(erro) return;

        let totalAdicionais = 0;
        produtoEmEdicao.adicionais_temp.forEach(a => totalAdicionais += a.preco);
        
        let itemCarrinho = {
            id: produtoEmEdicao.id,
            name: produtoEmEdicao.nome,
            preco: parseFloat(produtoEmEdicao.preco_real) + totalAdicionais,
            qtd: produtoEmEdicao.qtd_temp,
            estoque: produtoEmEdicao.sem_estoque ? 99999 : parseInt(produtoEmEdicao.estoque_atual),
            observacao: document.getElementById('modal_config_obs').value,
            adicionais: produtoEmEdicao.adicionais_temp
        };

        adicionarItemAoCarrinho(itemCarrinho);
        fecharModalConfig();
    }

    function addCarrinho(prod) {
        let itemCarrinho = {
            id: prod.id,
            name: prod.nome,
            preco: parseFloat(prod.preco_real),
            qtd: 1,
            estoque: prod.sem_estoque ? 99999 : parseInt(prod.estoque_atual),
            observacao: '',
            adicionais: []
        };
        adicionarItemAoCarrinho(itemCarrinho);
    }

    function adicionarItemAoCarrinho(novoItem) {
        let indexExistente = -1;
        
        if (!novoItem.adicionais) novoItem.adicionais = [];

        if (novoItem.adicionais.length === 0 && (!novoItem.observacao || novoItem.observacao.trim() === '')) {
            indexExistente = carrinho.findIndex(i => {
                let ads = i.adicionais || [];
                return i.id === novoItem.id && ads.length === 0 && (!i.observacao || i.observacao.trim() === '');
            });
        }

        if (indexExistente >= 0) {
            if (carrinho[indexExistente].qtd + novoItem.qtd > novoItem.estoque) { 
                alert("Limite de estoque atingido!"); return; 
            }
            carrinho[indexExistente].qtd += novoItem.qtd;
        } else {
            if (novoItem.estoque < 1) { alert("Produto esgotado!"); return; }
            carrinho.push(novoItem);
        }
        
        renderCarrinho();
        if(window.innerWidth < 768) mudarAba('carrinho');
    }

    function alterarPrecoItem(index, novoValor) {
        // Converte "27,50" para 27.50
        let v = parseFloat(novoValor.replace(/\./g, '').replace(',', '.'));
        
        if (isNaN(v) || v < 0) {
            // Se digitou errado, volta o preço original
            renderCarrinho();
            return;
        }
        
        // Atualiza o preço no array do carrinho
        carrinho[index].preco = v;
        
        // Redesenha o carrinho para atualizar os totais
        renderCarrinho();
    }

    function renderCarrinho() {
        let html = '';
        let total = 0;
        let qtdTotal = 0;

        carrinho.forEach((item, idx) => {
            let sub = item.qtd * item.preco;
            total += sub;
            qtdTotal += item.qtd;
            
            let detalhesHtml = '';
            let ads = item.adicionais || [];
            
            if(ads.length > 0) {
                detalhesHtml += '<div class="mt-1 pl-2 border-l-2 border-blue-100 text-[10px] text-gray-500">';
                ads.forEach(add => {
                    let p = parseFloat(add.preco) > 0 ? ` (+ R$ ${parseFloat(add.preco).toFixed(2).replace('.',',')})` : '';
                    detalhesHtml += `<div>+ ${add.nome}${p}</div>`;
                });
                detalhesHtml += '</div>';
            }
            
            if(item.observacao && item.observacao.trim() !== '') {
                detalhesHtml += `<div class="text-[10px] text-orange-600 mt-1 italic"><i class="fas fa-comment-dots"></i> "${item.observacao}"</div>`;
            }

            html += `
            <div class="flex justify-between items-start bg-gray-50 p-2 rounded border border-gray-100 text-xs mt-1 animate-fade-in group hover:bg-blue-50 transition">
                <div class="flex-1 pr-2">
                    <div class="font-bold text-gray-700 line-clamp-2">${item.name}</div>
                    ${detalhesHtml} 
                    
                    <div class="flex items-center mt-1 gap-1">
                        <span class="text-gray-400 text-[10px]">R$</span>
                        <input type="text" 
                               value="${item.preco.toFixed(2).replace('.',',')}" 
                               class="w-16 bg-transparent border-b border-gray-300 text-gray-600 font-bold text-xs focus:border-blue-500 focus:text-blue-600 outline-none transition"
                               onkeyup="mascaraMoeda(this)" 
                               onblur="alterarPrecoItem(${idx}, this.value)">
                        <span class="text-gray-400 text-[10px]">un</span>
                    </div>
                    </div>
                <div class="flex flex-col items-end gap-1">
                    <div class="font-bold text-gray-800">R$ ${sub.toFixed(2).replace('.', ',')}</div>
                    <div class="flex items-center gap-1 bg-white border border-gray-200 rounded shadow-sm">
                        <button type="button" onclick="alterarQtd(${idx}, -1)" class="w-6 h-6 flex items-center justify-center hover:bg-red-50 text-red-500 font-bold transition">-</button>
                        <span class="w-6 text-center font-bold text-gray-700">${item.qtd}</span>
                        <button type="button" onclick="alterarQtd(${idx}, 1)" class="w-6 h-6 flex items-center justify-center hover:bg-green-50 text-green-600 font-bold transition">+</button>
                    </div>
                </div>
            </div>`;
        });

        document.getElementById('lista_carrinho').innerHTML = html || '<div class="text-center text-gray-400 py-8 text-xs italic flex flex-col items-center gap-2"><i class="fas fa-shopping-basket text-2xl text-gray-300"></i> Cesta vazia</div>';
        document.getElementById('itens_json').value = JSON.stringify(carrinho);
        
        const badge = document.getElementById('badge_mobile');
        if(badge) {
            badge.innerText = qtdTotal;
            badge.style.display = qtdTotal > 0 ? 'inline-flex' : 'none';
        }
        
        atualizarTotais(total);
    }

    function alterarQtd(i, delta) {
        const item = carrinho[i];
        if (delta > 0 && item.qtd + delta > item.estoque) { alert("Limite de estoque!"); return; }
        item.qtd += delta;
        if (item.qtd <= 0) carrinho.splice(i, 1);
        renderCarrinho();
    }

    function atualizarTotais(subtotal = null) {
        if (subtotal === null) subtotal = carrinho.reduce((a, b) => a + (b.qtd * b.preco), 0);
        let taxa = parseFloat(document.getElementById('input_taxa_entrega').value.replace(',', '.') || 0);
        let desc = parseFloat(document.getElementById('desconto').value.replace(',', '.') || 0);
        let total = (subtotal + taxa) - desc;
        if(total < 0) total = 0;

        const totalFmt = total.toFixed(2).replace('.', ',');
        document.getElementById('display_total_input').value = totalFmt;
        document.getElementById('input_valor_produtos').value = subtotal.toFixed(2);
        document.getElementById('input_valor_total').value = total.toFixed(2);
        
        const btnMob = document.getElementById('resumo_mobile');
        if(btnMob) btnMob.innerText = 'R$ ' + totalFmt;

        if (document.getElementById('forma_pagamento').value === 'pix') atualizarPayloadPix();
    }

    function verificarTroco() {
        const elForma = document.getElementById('forma_pagamento');
        const divPix = document.getElementById('container_pix');
        const divTroco = document.getElementById('div_troco');
        const inpTroco = document.getElementById('troco_para');
        
        if (!elForma) return;
        const tipo = elForma.value;
        
        if (tipo === 'pix') { 
            if(divPix) divPix.classList.remove('hidden'); 
            if(divTroco) divTroco.classList.add('hidden'); 
            if(inpTroco) inpTroco.value = ''; 
            if(typeof atualizarPayloadPix === 'function') atualizarPayloadPix(); 
        } else if (tipo === 'dinheiro') {
            if(divPix) divPix.classList.add('hidden'); 
            if(divTroco) divTroco.classList.remove('hidden'); 
            if(inpTroco) inpTroco.disabled = false;
        } else {
            if(divPix) divPix.classList.add('hidden'); 
            if(divTroco) divTroco.classList.add('hidden'); 
            if(inpTroco) inpTroco.value = '';
        }
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        verificarTroco();
    });

    function salvarPedido() {
        if (document.getElementById('tipo_entrega').value === 'entrega') {
            if(!document.getElementById('numero').value) { alert("Preencha o número."); return; }
            if(!entregaPermitida) { alert("Fora da área!"); return; }
        }
        if(carrinho.length === 0) { alert('Carrinho vazio.'); return; }
        const formData = new FormData(document.getElementById('formPedido'));
        fetch('<?= BASE_URL ?>/admin/pedidos/salvar', { method: 'POST', body: formData }).then(r => r.json()).then(d => { if(d.ok) location.reload(); else alert(d.erro || 'Erro ao salvar'); });
    }
    
    function toggleQrCode() { const check = document.getElementById('check_gerar_qrcode'); document.getElementById('area_qrcode_visual').style.display = check.checked ? 'block' : 'none'; if(check.checked) atualizarPayloadPix(); }
    function formatarChavePix(chave) { const limpa = chave.replace(/[^a-zA-Z0-9@.+]/g, ""); if (limpa.includes('@')) return limpa; if (/^[0-9]+$/.test(limpa) && limpa.length >= 10 && limpa.length <= 14) { if (!limpa.startsWith('55')) return '+55' + limpa; return '+' + limpa; } return limpa; }
    function gerarPayloadPix(valor) { if (!chavePixLoja) return ""; const chaveFormatada = formatarChavePix(chavePixLoja); const v = parseFloat(valor).toFixed(2); const nome = nomeLojaPix; const cidade = cidadeLojaPix; const txtId = "***"; const f = (id, conteudo) => { const c = String(conteudo); return id + c.length.toString().padStart(2, '0') + c; }; let payload = "000201"; payload += f("26", f("00", "BR.GOV.BCB.PIX") + f("01", chaveFormatada)); payload += "52040000"; payload += "5303986"; payload += f("54", v); payload += "5802BR"; payload += f("59", nome); payload += f("60", cidade); payload += f("62", f("05", txtId)); payload += "6304"; function getCRC16(data) { let crc = 0xFFFF; const polynomial = 0x1021; for (let i = 0; i < data.length; i++) { crc ^= (data.charCodeAt(i) << 8); for (let j = 0; j < 8; j++) { if ((crc & 0x8000) !== 0) crc = (crc << 1) ^ polynomial; else crc <<= 1; } } return (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0'); } return payload + getCRC16(payload); }
    function atualizarPayloadPix() { if(!document.getElementById('check_gerar_qrcode').checked) return; const total = parseFloat(document.getElementById('input_valor_total').value) || 0; if (total <= 0) return; const payload = gerarPayloadPix(total); if(!payload) return; document.getElementById('pix_copia_cola').value = payload; const divQr = document.getElementById('qrcode_pix'); divQr.innerHTML = ''; try { new QRCode(divQr, { text: payload, width: 120, height: 120, colorDark : "#000000", colorLight : "#ffffff", correctLevel : QRCode.CorrectLevel.L }); } catch(e) {} }
    function copiarPix() { const txt = document.getElementById('pix_copia_cola'); if(!txt.value) return; txt.select(); navigator.clipboard.writeText(txt.value).then(()=>alert("Copiado!")); }
    function mudarTipo(t) { document.getElementById('tipo_entrega').value = t; const area = document.getElementById('area_endereco'); const aviso = document.getElementById('aviso_fora_area'); if(t==='retirada') { area.classList.add('opacity-50','pointer-events-none'); entregaPermitida = true; aviso.classList.add('hidden'); document.getElementById('btn_retirada').className = "flex-1 py-2 text-xs font-bold rounded-md bg-blue-100 text-blue-700 transition"; document.getElementById('btn_entrega').className = "flex-1 py-2 text-xs font-bold rounded-md text-gray-500 hover:bg-gray-100 transition"; } else { area.classList.remove('opacity-50','pointer-events-none'); document.getElementById('btn_entrega').className = "flex-1 py-2 text-xs font-bold rounded-md bg-blue-100 text-blue-700 transition"; document.getElementById('btn_retirada').className = "flex-1 py-2 text-xs font-bold rounded-md text-gray-500 hover:bg-gray-100 transition"; if(document.getElementById('numero').value) calcularFrete(); } atualizarTotais(null); }
    function calcularFrete() { let rua = document.getElementById('logradouro').value; let num = document.getElementById('numero').value; let bairro = document.getElementById('bairro').value; const aviso = document.getElementById('aviso_fora_area'); if(!rua || !num) { entregaPermitida = false; return; } aviso.classList.add('hidden'); fetch('<?= BASE_URL ?>/admin/pedidos/calcularFreteAjax', { method: 'POST', body: new URLSearchParams({ rua, numero: num, bairro }) }).then(r => r.json()).then(data => { if (data.lat_cliente) { document.getElementById('lat_entrega_hidden').value = data.lat_cliente; document.getElementById('lng_entrega_hidden').value = data.lng_cliente; } if (data.ok) { entregaPermitida = true; document.getElementById('input_taxa_entrega').value = data.valor.toFixed(2).replace('.', ','); } else { entregaPermitida = false; document.getElementById('input_taxa_entrega').value = '0,00'; if(data.motivo === 'fora_area') aviso.classList.remove('hidden'); } atualizarTotais(); }); }
    function filtrarCategoria(catId) { document.querySelectorAll('.cat-btn').forEach(btn => { const active = (btn.getAttribute('onclick').includes(catId)); btn.className = active ? "cat-btn bg-gray-900 text-white px-3 py-1.5 rounded-md text-xs font-bold whitespace-nowrap shrink-0" : "cat-btn bg-white border border-gray-200 text-gray-600 px-3 py-1.5 rounded-md text-xs font-bold whitespace-nowrap hover:bg-gray-50 shrink-0"; }); document.querySelectorAll('.prod-card').forEach(card => { card.style.display = (catId === 'all' || card.getAttribute('data-cat') == catId) ? 'flex' : 'none'; }); }
    function filtrarProdutos() { const term = document.getElementById('busca_prod').value.toLowerCase(); document.querySelectorAll('.prod-card').forEach(card => { card.style.display = card.getAttribute('data-nome').includes(term) ? 'flex' : 'none'; }); }
    document.addEventListener("DOMContentLoaded", function() { const inp = document.getElementById('busca_endereco'); if (inp && typeof google !== 'undefined') { const ac = new google.maps.places.Autocomplete(inp, { componentRestrictions: {country:'br'}, fields: ['geometry','address_components'] }); ac.addListener('place_changed', () => { const p = ac.getPlace(); if(!p.geometry) return; document.getElementById('lat_entrega_hidden').value = p.geometry.location.lat(); document.getElementById('lng_entrega_hidden').value = p.geometry.location.lng(); let rua='', num='', bairro=''; p.address_components.forEach(c => { if(c.types.includes('route')) rua = c.long_name; if(c.types.includes('street_number')) num = c.long_name; if(c.types.includes('sublocality_level_1')) bairro = c.long_name; }); document.getElementById('logradouro').value = rua; document.getElementById('bairro').value = bairro; document.getElementById('numero').value = num; if(num) calcularFrete(); else document.getElementById('numero').focus(); }); } });
    function mascaraMoeda(i) { var v = i.value.replace(/\D/g,''); v = (v/100).toFixed(2) + ''; v = v.replace(".", ","); v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,"); v = v.replace(/(\d)(\d{3}),/g, "$1.$2,"); i.value = v; }
    function buscarCliente() { 
        let t = document.getElementById('telefone').value.replace(/\D/g,''); 
        if(t.length < 8) return; 
        
        const f = new FormData();
        f.append('telefone', t);

        fetch('<?= BASE_URL ?>/admin/pedidos/buscarClienteAjax', { 
            method: 'POST', 
            body: f 
        })
        .then(r => r.json())
        .then(d => { 
            if(d.encontrado && d.dados) { 
                // 1. Preenche os campos
                if (document.getElementById('nome_cliente') && d.dados.nome) {
                    document.getElementById('nome_cliente').value = d.dados.nome;
                }
                if (document.getElementById('logradouro') && d.dados.logradouro) {
                    document.getElementById('logradouro').value = d.dados.logradouro;
                }
                if (document.getElementById('numero') && d.dados.numero) {
                    document.getElementById('numero').value = d.dados.numero;
                }
                if (document.getElementById('bairro') && d.dados.bairro) {
                    document.getElementById('bairro').value = d.dados.bairro;
                }
                if (document.getElementById('complemento') && d.dados.complemento) {
                    document.getElementById('complemento').value = d.dados.complemento;
                }
                
                // 2. Garante que a tela mude para "Entrega" (caso estivesse em Retirada)
                if (typeof mudarTipo === 'function') {
                    mudarTipo('entrega');
                }
                
                // 3. Dá um delay de 300 milissegundos e força o cálculo do frete!
                if(d.dados.numero && d.dados.logradouro && typeof calcularFrete === 'function') {
                    setTimeout(() => {
                        calcularFrete();
                    }, 300);
                }
            } 
        })
        .catch(e => console.error("Erro ao buscar cliente:", e));
    }
</script>