<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $empresa['nome_fantasia']; ?> | Cardápio Digital</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json?v=2">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.png?v=2" type="image/png">
    
    <meta name="theme-color" content="#ea1d2c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; -webkit-tap-highlight-color: transparent; }
        .hide-scroll::-webkit-scrollbar { display: none; } 
        .animate-slide-up { animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .radio-custom, .chk-custom { appearance: none; -webkit-appearance: none; }
        .radio-custom + div { border: 2px solid #d1d5db; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .radio-custom:checked + div { border-color: #ea1d2c; } 
        .radio-custom:checked + div::after { content: ''; width: 12px; height: 12px; background: #ea1d2c; border-radius: 50%; }
        .chk-custom + div { border: 2px solid #d1d5db; border-radius: 6px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .chk-custom:checked + div { background-color: #16a34a; border-color: #16a34a; }
        .chk-custom:checked + div::after { content: '✔'; font-size: 14px; color: white; font-weight: bold; }
        textarea:focus { outline: none; border-color: #ea1d2c; }
    </style>
</head>
<body class="text-gray-800 antialiased bg-gray-50 pb-24">

    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <img src="<?php echo $config['logo_url'] ?: 'https://ui-avatars.com/api/?name=Loja&background=random'; ?>" class="w-10 h-10 rounded-full object-cover border border-gray-100">
                    <div class="absolute bottom-0 right-0 w-3 h-3 <?php echo $lojaAberta ? 'bg-green-500' : 'bg-red-500'; ?> border-2 border-white rounded-full"></div>
                </div>
                <div>
                    <h1 class="text-sm font-black leading-tight text-gray-800 line-clamp-1"><?php echo $empresa['nome_fantasia']; ?></h1>
                    <p class="text-[10px] <?php echo $lojaAberta ? 'text-green-600' : 'text-red-500'; ?> font-bold flex items-center gap-1">
                        <?php echo $lojaAberta ? "Aberto agora" : "Fechado agora • $msgHorario"; ?>
                    </p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/<?= $empresa['slug'] ?>/perfil" class="w-9 h-9 rounded-full bg-gray-50 text-gray-600 flex items-center justify-center border border-gray-200 hover:bg-gray-100 transition" title="Minha Conta">
                <i class="fas fa-user text-sm"></i>
            </a>
        </div>
        
        <div class="px-4 pb-3 bg-white">
            <input type="text" id="input-busca-cardapio" onkeyup="filtrarCardapio()" placeholder="Buscar itens..." class="w-full pl-4 pr-4 py-2 bg-gray-100 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-gray-200 outline-none transition placeholder-gray-400">
        </div>

        <div id="barra-topo-endereco" class="bg-white px-4 py-2 border-t border-gray-100 hidden flex justify-between items-center cursor-pointer active:bg-gray-50 transition" onclick="abrirCheckout()">
            <div class="flex items-center gap-2 text-gray-500 truncate max-w-[70%]">
                <div class="bg-red-50 text-red-500 w-5 h-5 rounded-full flex items-center justify-center shrink-0"><i class="fas fa-map-marker-alt text-[10px]"></i></div>
                <span id="lbl-topo-rua" class="text-xs font-bold text-gray-700 truncate">Selecionar endereço...</span>
            </div>
            <span id="lbl-topo-valor" class="text-xs font-black text-green-600">Calculando</span>
        </div>

        <div id="barra-status-mesa" class="bg-purple-50 px-4 py-3 border-t border-purple-100 hidden flex justify-between items-center cursor-pointer hover:bg-purple-100 transition shadow-inner" onclick="verContaMesa()">
            <div class="flex items-center gap-3">
                <div class="bg-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fas fa-clipboard-list text-sm"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-purple-600 font-bold uppercase tracking-wide leading-none mb-0.5">Minha Comanda</span>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span class="font-bold">Eu: <span id="resumo-eu" class="text-gray-900">R$ 0,00</span></span>
                        <span class="text-gray-300">|</span>
                        <span>Mesa: <span id="resumo-mesa">R$ 0,00</span></span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-purple-700 bg-white border border-purple-200 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1">
                    Ver Detalhes <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
            </div>
        </div>

        <div class="flex gap-2 overflow-x-auto hide-scroll px-4 py-2 bg-white border-b border-gray-100 sticky top-[68px] z-20" id="nav-categorias">
            <?php if(!empty($combos)): ?>
                <a href="#secao-combos" class="px-3 py-1.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 whitespace-nowrap scroll-smooth"><i class="fas fa-fire"></i> Combos</a>
            <?php endif; ?>
            <?php foreach($categorias as $cat): ?>
                <a href="#cat-<?php echo $cat['id']; ?>" class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-800 hover:text-white transition whitespace-nowrap scroll-smooth"><?php echo $cat['nome']; ?></a>
            <?php endforeach; ?>
        </div>
    </header>

    <main class="max-w-md mx-auto space-y-6 mt-4 pb-10">
        <?php if(!empty($combos)): ?>
            <section id="secao-combos" class="secao-cardapio pl-4">
                <h2 class="text-base font-black text-gray-800 mb-3">🔥 Ofertas & Combos</h2>
                <div class="flex gap-3 overflow-x-auto hide-scroll snap-x-mandatory pr-4 pb-2">
                    <?php foreach($combos as $prod): 
                        $controla = $prod['controle_estoque'] ?? 1; $qtd = intval($prod['estoque_atual'] ?? 0); $esgotado = ($controla == 1 && $qtd <= 0);
                        $precoBase = (float)$prod['preco_base']; $precoPromo = (float)($prod['preco_promocional'] ?? 0);
                        $temDesconto = ($precoPromo > 0 && $precoPromo < $precoBase); $precoFinal = $temDesconto ? $precoPromo : $precoBase;
                        $prodJS = $prod; $prodJS['preco_real'] = $precoFinal;
                    ?>
                        <div onclick='abrirModalProduto(<?php echo htmlspecialchars(json_encode($prodJS), ENT_QUOTES, 'UTF-8'); ?>)' 
                             class="item-cardapio snap-center shrink-0 w-[280px] bg-white rounded-xl shadow-sm border border-gray-100 relative overflow-hidden active:scale-95 transition <?php echo $esgotado ? 'opacity-60 pointer-events-none' : ''; ?>" data-nome="<?php echo strtolower($prod['nome']); ?>">
                            <div class="h-36 w-full bg-gray-200 relative"><img src="<?php echo $prod['imagem_url'] ?: 'https://via.placeholder.com/400x300'; ?>" class="w-full h-full object-cover">
                                <?php if($temDesconto): ?><div class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded shadow-sm">OFERTA</div><?php endif; ?>
                            </div>
                            <div class="p-3">
                                <h3 class="font-bold text-gray-800 text-sm mb-1 truncate"><?php echo $prod['nome']; ?></h3>
                                <div class="flex justify-between items-center">
                                    <span class="text-green-700 font-black text-base">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></span>
                                    <div class="bg-gray-50 w-7 h-7 rounded-full flex items-center justify-center text-gray-800 border border-gray-200"><i class="fas fa-plus text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php foreach($categorias as $cat): ?>
            <section id="cat-<?php echo $cat['id']; ?>" class="secao-cardapio px-4 pt-2">
                <h2 class="text-base font-black text-gray-800 mb-3 sticky top-0 bg-gray-50 z-10 py-2"><?php echo $cat['nome']; ?></h2>
                <div class="flex flex-col gap-3">
                    <?php foreach($cat['itens'] as $prod): 
                        $controla = $prod['controle_estoque'] ?? 1; 
                        $qtd = intval($prod['estoque_atual'] ?? 0); 
                        $esgotado = ($controla == 1 && $qtd <= 0);
                        $poucoEstoque = ($controla == 1 && $qtd > 0 && $qtd <= 5);

                        $precoBase = (float)$prod['preco_base']; 
                        $precoPromo = (float)($prod['preco_promocional'] ?? 0);
                        $temDesconto = ($precoPromo > 0 && $precoPromo < $precoBase); 
                        $precoFinal = $temDesconto ? $precoPromo : $precoBase;
                        
                        $prodJS = $prod; 
                        $prodJS['preco_real'] = $precoFinal;
                    ?>
                        <div <?php if(!$esgotado): ?>onclick='abrirModalProduto(<?php echo htmlspecialchars(json_encode($prodJS), ENT_QUOTES, 'UTF-8'); ?>)'<?php endif; ?> 
                            class="item-cardapio flex justify-between bg-white p-3 rounded-lg border border-gray-100 shadow-sm transition relative overflow-hidden 
                            <?php echo $esgotado ? 'grayscale bg-gray-50 cursor-not-allowed opacity-80' : 'active:bg-gray-50 cursor-pointer'; ?>" 
                            data-nome="<?php echo strtolower($prod['nome']); ?>">
                            
                            <div class="flex-1 pr-3 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-sm text-gray-800 mb-1 leading-tight"><?php echo $prod['nome']; ?></h3>
                                    <p class="text-xs text-gray-500 line-clamp-2 mb-2"><?php echo $prod['descricao']; ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold <?php echo $esgotado ? 'text-gray-400' : 'text-green-700'; ?>">
                                        R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?>
                                    </span>
                                    <?php if($poucoEstoque): ?>
                                        <span class="text-[9px] bg-orange-100 text-orange-700 px-1.5 rounded font-bold">Restam <?php echo $qtd; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if(!empty($prod['imagem_url'])): ?>
                                <div class="w-24 h-24 rounded-lg bg-gray-100 overflow-hidden shrink-0 relative">
                                    <img src="<?php echo $prod['imagem_url']; ?>" class="w-full h-full object-cover">
                                    <?php if($esgotado): ?>
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                            <span class="text-white text-[10px] font-black uppercase border border-white px-2 py-1 rounded">ESGOTADO</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <div id="barra-carrinho" class="fixed bottom-0 w-full bg-white border-t border-gray-100 p-3 shadow-lg z-40 transform translate-y-full transition-transform duration-300">
        <div class="max-w-md mx-auto flex justify-between items-center gap-3">
            <div><p class="text-[10px] text-gray-500 uppercase font-bold">Total</p><p class="font-black text-lg text-gray-800 leading-none" id="total-barra">R$ 0,00</p></div>
            <button onclick="abrirCheckout()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold flex items-center gap-2 shadow-lg active:scale-95 transition text-sm flex-1 justify-center"><span>Ver Sacola</span> <span id="badge-qtd" class="bg-white/20 px-2 rounded text-xs">0</span></button>
        </div>
    </div>

    <div id="modal-produto" class="fixed inset-0 z-[60] hidden">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" onclick="fecharModalProduto()"></div>
        <div class="fixed inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none">
            <div class="bg-white w-full md:max-w-lg md:rounded-xl rounded-t-2xl h-[94vh] md:h-[90vh] flex flex-col pointer-events-auto animate-slide-up shadow-2xl relative overflow-hidden">
                <button onclick="fecharModalProduto()" class="absolute top-4 left-4 z-20 bg-black/40 text-white w-9 h-9 rounded-full flex items-center justify-center backdrop-blur hover:bg-black/60 transition"><i class="fas fa-chevron-down"></i></button>
                <div class="flex-1 overflow-y-auto custom-scroll bg-gray-50 pb-4">
                    <div class="relative h-64 w-full bg-gray-200 shrink-0">
                        <img id="modal-prod-img" src="" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                    </div>
                    <div class="bg-white px-5 py-5 border-b border-gray-100 -mt-6 relative rounded-t-2xl z-10">
                        <h3 id="modal-prod-nome" class="font-black text-2xl text-gray-800 mb-2 leading-tight"></h3>
                        <p id="modal-prod-desc" class="text-sm text-gray-500 mb-4 leading-relaxed"></p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400 font-bold">A partir de</span>
                            <span id="modal-prod-preco" class="text-2xl font-black text-green-600"></span>
                        </div>
                    </div>
                    <div id="container-adicionais" class="flex flex-col gap-2 mt-2"></div>
                    <div class="bg-white p-5 mt-2 border-t border-gray-100">
                        <h4 class="font-bold text-gray-700 text-sm mb-2 flex items-center gap-2"><i class="far fa-comment-dots"></i> Alguma observação?</h4>
                        <textarea id="modal-prod-obs" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-lg text-sm p-3 focus:ring-1 focus:ring-gray-400 placeholder-gray-400" placeholder="Ex: Tirar cebola, ponto da carne..."></textarea>
                    </div>
                </div>
                <div class="p-4 bg-white border-t border-gray-100 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center border border-gray-200 rounded-lg h-12 bg-white px-1">
                            <button onclick="mudarQtdModal(-1)" class="w-10 h-full text-gray-400 hover:text-red-500 font-black text-2xl disabled:opacity-30" id="btn-menos">-</button>
                            <span id="modal-prod-qtd" class="w-8 text-center font-bold text-gray-800 text-lg">1</span>
                            <button onclick="mudarQtdModal(1)" class="w-10 h-full text-red-500 hover:text-red-600 font-black text-2xl">+</button>
                        </div>
                        <button onclick="adicionarAoCarrinhoComComplementos()" class="flex-1 bg-red-600 hover:bg-red-700 text-white h-12 rounded-lg font-bold flex justify-between items-center px-5 shadow-lg active:scale-95 transition">
                            <span>Adicionar</span>
                            <span id="modal-btn-total">R$ 0,00</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-checkout" class="fixed inset-0 bg-black/60 z-[60] hidden items-end sm:items-center justify-center backdrop-blur-sm p-0 sm:p-4">
        <div class="bg-white w-full sm:max-w-md h-[95vh] sm:h-auto rounded-t-2xl sm:rounded-2xl flex flex-col animate-slide-up shadow-2xl relative">
            
            <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                <h3 class="font-bold text-lg text-gray-800" id="titulo-checkout">Sua Sacola</h3>
                <button onclick="fecharCheckout()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 custom-scroll bg-gray-50">
                <div id="lista-resumo" class="space-y-3 mb-6"></div>
                
                <form id="form-pedido" onsubmit="return false;" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <input type="hidden" name="empresa_id" value="<?php echo $empresa['id']; ?>">
                    <input type="hidden" name="filial_id" value="<?php echo $filial_id; ?>">
                    <input type="hidden" name="whatsapp_loja" value="<?php echo $whatsappLoja; ?>">
                    <input type="hidden" name="itens_json" id="input_itens_json">
                    <input type="hidden" name="valor_produtos" id="input_v_prod">
                    <input type="hidden" name="valor_total" id="input_v_total">
                    
                    <div id="area_dados_cliente" class="space-y-4">
                        <input type="tel" id="cliente_telefone" name="cliente_telefone" placeholder="Seu WhatsApp" onblur="buscarCliente()" class="w-full border border-gray-200 rounded-lg p-3 text-sm font-bold outline-none bg-gray-50" required>
                        <input type="text" id="cliente_nome" name="cliente_nome" placeholder="Seu Nome" class="w-full border border-gray-200 rounded-lg p-3 text-sm font-bold outline-none bg-gray-50" required>
                    </div>

                    <div id="area_entrega_box" class="border-t pt-3 mt-4">
                            <p class="text-xs font-bold text-gray-400 mb-2">ENTREGA</p>
                            <div id="seletor_tipo_entrega" class="flex bg-gray-100 p-1 rounded-lg mb-2">
                            <label class="flex-1 text-center cursor-pointer"><input type="radio" name="tipo_entrega" id="radio_entrega" value="entrega" checked class="hidden peer" onchange="toggleEntrega(true)"><span class="block py-2 text-xs font-bold text-gray-500 rounded peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm">Delivery</span></label>
                            <label class="flex-1 text-center cursor-pointer"><input type="radio" name="tipo_entrega" value="retirada" class="hidden peer" onchange="toggleEntrega(false)"><span class="block py-2 text-xs font-bold text-gray-500 rounded peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm">Retirada</span></label>
                            </div>
                            <div id="box-endereco">
                            <button onclick="usarLocalizacaoAtual()" class="w-full bg-blue-50 text-blue-600 font-bold text-xs py-3 rounded-lg mb-3 flex items-center justify-center gap-2 hover:bg-blue-100 transition border border-blue-100 border-dashed"><i class="fas fa-location-arrow"></i> Usar localização atual</button>
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="endereco_entrega" id="input_end" placeholder="Endereço" class="flex-1 border border-gray-200 rounded-lg p-3 text-sm bg-gray-50">
                                <input type="text" name="numero" id="input_num" placeholder="Nº" class="w-20 border border-gray-200 rounded-lg p-3 text-sm text-center bg-gray-50" onblur="consultarFreteManual()">
                            </div>
                                <div class="flex gap-2">
                                    <input type="text" name="bairro" id="input_bairro" placeholder="Bairro" class="flex-1 border border-gray-200 rounded-lg p-3 text-sm bg-gray-50">
                                    <input type="text" name="complemento" id="input_comp" placeholder="Complemento" class="w-2/5 border border-gray-200 rounded-lg p-3 text-sm bg-gray-50">
                                </div>
                            <input type="hidden" name="taxa_entrega" id="input_taxa_entrega" value="0.00">
                            <input type="hidden" name="lat_entrega" id="input_lat">
                            <input type="hidden" name="lng_entrega" id="input_lng">
                            </div>
                    </div>

                    <div id="container-pagamento" class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-bold text-gray-400 mb-2">PAGAMENTO</p>
                        <div class="grid grid-cols-2 gap-2"> 
                            <label class="cursor-pointer group">
                                <input type="radio" name="forma_pagamento" value="cartao" class="peer hidden" onchange="checkTroco()">
                                <div class="border border-gray-200 rounded-lg p-3 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition font-bold text-sm text-gray-500 flex flex-col items-center gap-1 hover:bg-gray-50">
                                    <i class="fas fa-credit-card text-lg"></i> Cartão
                                </div>
                            </label>
                            
                            <label class="cursor-pointer group">
                                <input type="radio" name="forma_pagamento" value="dinheiro" class="peer hidden" onchange="checkTroco()">
                                <div class="border border-gray-200 rounded-lg p-3 text-center peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 transition font-bold text-sm text-gray-500 flex flex-col items-center gap-1 hover:bg-gray-50">
                                    <i class="fas fa-money-bill-wave text-lg"></i> Dinheiro
                                </div>
                            </label>
                        </div>

                        <div id="box-troco" class="hidden mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-3 animate-fade-in">
                            <label class="block text-xs font-bold text-yellow-800 uppercase mb-1">Precisa de troco para quanto?</label>
                            <div class="flex items-center bg-white border border-yellow-300 rounded px-3 py-2">
                                <span class="text-yellow-600 font-bold mr-2">R$</span>
                                <input type="text" name="troco_para" id="input_troco" placeholder="0,00" class="w-full text-lg font-bold text-gray-800 outline-none placeholder-gray-300">
                            </div>
                            <p class="text-[10px] text-yellow-700 mt-1 italic">* Deixe vazio se tiver o valor exato.</p>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-4 border-t border-gray-100 bg-white">
                <div class="flex justify-between items-center mb-1 text-xs text-gray-500">
                    <span>Taxa de Entrega</span>
                    <span id="lbl-taxa-entrega" class="font-bold">Grátis</span>
                </div>
                <div class="flex justify-between items-end mb-2"><span class="text-xs text-gray-500 font-bold uppercase">Total Final</span><span class="text-2xl font-black text-gray-800" id="total-final-modal">R$ 0,00</span></div>
                <button onclick="enviarPedido()" id="btn-enviar" class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-bold shadow-lg text-lg flex items-center justify-center gap-2"><span>Confirmar Pedido</span> <i class="fab fa-whatsapp"></i></button>
            </div>
        </div>
    </div>
    
    <div id="tela-sucesso" class="fixed inset-0 bg-white z-[70] hidden flex-col items-center justify-center p-6 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600 text-4xl animate-bounce"><i class="fas fa-check"></i></div>
        <h2 class="text-2xl font-black mb-2">Pedido Enviado!</h2>
        <p class="text-gray-500 mb-6 text-sm">Acompanhe pelo WhatsApp.</p>
        <a id="btn-zap-comprovante" href="#" target="_blank" class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold w-full max-w-xs block mb-3">Abrir WhatsApp</a>
        <button onclick="location.reload()" class="text-gray-400 text-sm font-bold">Voltar ao Cardápio</button>
    </div>

    <div id="modal-meus-pedidos" class="fixed inset-0 bg-black/60 z-[60] hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-sm rounded-2xl p-5 shadow-2xl relative animate-slide-up">
            <button onclick="fecharMeusPedidos()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            <h3 class="font-bold text-lg mb-4 text-gray-800">Meus Pedidos</h3>
            <div id="area-login-pedidos" class="text-center">
                <p class="text-sm text-gray-500 mb-3">Informe seu telefone para consultar</p>
                <div class="flex gap-2">
                    <input type="tel" id="input-tel-busca" placeholder="DDD + Número" class="flex-1 border border-gray-200 rounded-lg p-3 text-sm font-bold outline-none bg-gray-50">
                    <button onclick="buscarMeusPedidosManual()" class="bg-gray-800 text-white px-4 rounded-lg font-bold"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div id="loading-pedidos" class="hidden text-center py-5"><i class="fas fa-spinner fa-spin text-gray-400"></i></div>
            <div id="lista-meus-pedidos" class="mt-4 space-y-2 max-h-60 overflow-y-auto custom-scroll"></div>
        </div>
    </div>

    <div id="modal-conta" class="fixed inset-0 bg-black/60 z-[70] hidden items-end sm:items-center justify-center backdrop-blur-sm p-0 sm:p-4">
        <div class="bg-white w-full sm:max-w-md h-[90vh] sm:h-auto rounded-t-2xl sm:rounded-2xl flex flex-col animate-slide-up shadow-2xl relative">
            
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl sticky top-0 z-10">
                <div>
                    <h3 class="font-black text-lg text-gray-800 flex items-center gap-2"><i class="fas fa-file-invoice-dollar text-purple-600"></i> Consumo</h3>
                </div>
                <button onclick="document.getElementById('modal-conta').classList.add('hidden')" class="w-8 h-8 rounded-full bg-white text-gray-500 border border-gray-200 flex items-center justify-center font-bold">✕</button>
            </div>

            <div id="aviso-conta-paga" class="hidden bg-green-50 border-l-4 border-green-500 p-4 m-4 rounded shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
                    <div>
                        <p class="font-bold text-green-800 text-sm uppercase">Conta Encerrada</p>
                        <p class="text-xs text-green-700">Você já pagou sua parte. Novos pedidos estão bloqueados.</p>
                    </div>
                </div>
            </div>

            <div id="abas-conta" class="px-5 pt-2 hidden">
                <div class="flex bg-gray-100 p-1 rounded-xl">
                    <button onclick="mudarAbaConta('minha')" id="tab-minha" class="flex-1 py-2 text-xs font-bold rounded-lg transition text-gray-500">MINHA PARTE</button>
                    <button onclick="mudarAbaConta('mesa')" id="tab-mesa" class="flex-1 py-2 text-xs font-bold rounded-lg transition text-gray-500">MESA TOTAL</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-5 custom-scroll bg-white" id="lista-consumo">
                <div class="text-center py-10"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i></div>
            </div>
            
            <div class="p-5 border-t border-gray-100 bg-gray-50">
                <div class="flex justify-between items-end mb-4">
                    <span class="text-sm text-gray-500 font-bold uppercase" id="lbl-total-legenda">Total</span>
                    <span class="text-3xl font-black text-gray-800" id="total-conta">R$ 0,00</span>
                </div>
                <button onclick="document.getElementById('modal-conta').classList.add('hidden')" class="w-full bg-gray-800 text-white py-3 rounded-xl font-bold shadow-lg">Voltar ao Cardápio</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
   <script>
    // --- VARIÁVEIS GLOBAIS ---
    var carrinho = [];
    var produtoAtual = null;
    var lojaAberta = <?php echo $lojaAberta ? 'true' : 'false'; ?>;
    var chavePixLoja = "<?php echo $chavePix; ?>";
    const BASE_URL = '<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>';
    
    // Dados para PIX
    var nomeLojaPix = "<?php echo $nomeLojaPix ?? 'LOJA'; ?>";
    var cidadeLojaPix = "<?php echo $cidadeLojaPix ?? 'CIDADE'; ?>";

    <?php 
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $slugLoja = $empresa['slug'] ?? 'teste'; 
        // Rotas API
        $urlApiDetalhes = "{$baseUrl}/{$slugLoja}/cardapio/detalhesProduto";
        $urlApiFrete = "{$baseUrl}/{$slugLoja}/cardapio/calcularFrete";
        $urlApiGps = "{$baseUrl}/{$slugLoja}/cardapio/frete_gps";
        $urlApiCliente = "{$baseUrl}/{$slugLoja}/cardapio/buscarCliente";
        $urlApiSalvar = "{$baseUrl}/{$slugLoja}/cardapio/salvarPedido";
        $urlApiHistorico = "{$baseUrl}/{$slugLoja}/cardapio/historico";
        $urlApiCheckin = "{$baseUrl}/api/mesa/checkin";
        $urlApiConta = "{$baseUrl}/admin/salao/getConsumoAjax";
    ?>
    const ROTA_DETALHES = "<?= $urlApiDetalhes ?>";
    const ROTA_FRETE = "<?= $urlApiFrete ?>";
    const ROTA_FRETE_GPS = "<?= $urlApiGps ?>";
    const ROTA_CLIENTE = "<?= $urlApiCliente ?>";
    const ROTA_SALVAR = "<?= $urlApiSalvar ?>";
    const ROTA_HISTORICO = "<?= $urlApiHistorico ?>";
    const ROTA_CHECKIN = "<?= $urlApiCheckin ?>";
    const ROTA_VER_CONTA = "<?= $urlApiConta ?>";

    var dadosMesa = null;
    
    // Variáveis para Conta da Mesa
    let dadosContaCache = null;
    let abaAtual = 'minha';

    // --- FUNÇÕES DE PRODUTO ---
    function abrirModalProduto(prod) {
        if(!lojaAberta) { alert("Loja Fechada."); return; }
        
        produtoAtual = JSON.parse(JSON.stringify(prod));
        produtoAtual.qtd = 1; produtoAtual.adicionais = [];

        // Reset UI
        document.getElementById('modal-prod-img').src = prod.imagem_url || 'https://via.placeholder.com/400x300';
        document.getElementById('modal-prod-nome').innerText = prod.nome;
        document.getElementById('modal-prod-desc').innerText = prod.descricao || '';
        document.getElementById('modal-prod-preco').innerText = formatar(prod.preco_real);
        document.getElementById('modal-prod-qtd').innerText = 1;
        document.getElementById('modal-prod-obs').value = '';
        document.getElementById('btn-menos').disabled = true;
        
        const container = document.getElementById('container-adicionais');
        container.innerHTML = '<div class="py-10 text-center text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="text-xs mt-2">Carregando opções...</p></div>';
        
        document.getElementById('modal-produto').classList.remove('hidden');
        atualizarTotalModal();

        fetch(ROTA_DETALHES + "?id=" + prod.id)
            .then(r => r.text())
            .then(texto => {
                try {
                    const d = JSON.parse(texto.trim().replace(/^\uFEFF/, ''));
                    if(d.ok && d.complementos && d.complementos.length > 0) {
                        renderizarComplementos(d.complementos);
                    } else {
                        container.innerHTML = '<div class="p-4 text-center text-gray-400 text-xs italic">Sem adicionais.</div>';
                    }
                } catch (e) {
                    console.error("Erro JSON:", e);
                    container.innerHTML = '<div class="p-4 text-center text-red-400 text-xs">Erro ao carregar opções.</div>';
                }
            })
            .catch(e => container.innerHTML = '');
    }

    function renderizarComplementos(grupos) {
        const container = document.getElementById('container-adicionais');
        let html = '';
        grupos.forEach(g => {
            const min = parseInt(g.minimo); const max = parseInt(g.maximo);
            const isObrigatorio = g.obrigatorio == 1;
            html += `
            <div class="bg-white group-adicional mb-2" data-id="${g.id}" data-min="${min}" data-max="${max}">
                <div class="px-5 py-3 bg-gray-100 flex justify-between items-center sticky top-0 z-10 border-y border-gray-200">
                    <div>
                        <h4 class="font-bold text-gray-700 text-sm uppercase tracking-wide">${g.nome}</h4>
                        <p class="text-[10px] text-gray-500 font-bold mt-0.5">${isObrigatorio ? '<span class="text-red-600">OBRIGATÓRIO</span> • ' : ''} Escolha até ${max}</p>
                    </div>
                    <span class="text-xs font-bold text-gray-400 bg-white px-2 py-1 rounded border border-gray-200 shadow-sm" id="badge-count-${g.id}">0/${min > 0 ? min : max}</span>
                </div>
                <div class="divide-y divide-gray-50">`;
            if(g.itens && g.itens.length > 0) {
                g.itens.forEach(item => {
                    let tipo = max == 1 ? 'radio' : 'checkbox';
                    let name = `add_${g.id}`;
                    let precoTxt = parseFloat(item.preco) > 0 ? `+ ${formatar(item.preco)}` : 'Grátis';
                    let idInput = `opt_${g.id}_${item.id}`;
                    html += `<label class="flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 transition" for="${idInput}">
                            <div class="flex-1 pr-4"><span class="block text-sm font-semibold text-gray-700">${item.nome}</span><span class="block text-xs font-bold text-gray-500 mt-0.5">${precoTxt}</span></div>
                            <div class="relative flex items-center"><input type="${tipo}" id="${idInput}" name="${name}" value="${item.id}" data-preco="${item.preco}" data-nome="${item.nome}" data-grupo="${g.id}" class="${tipo === 'radio' ? 'radio-custom' : 'chk-custom'} peer w-5 h-5 opacity-0 absolute z-10" onchange="checkAdicional(this, ${max}, '${name}', ${g.id})"><div></div></div>
                        </label>`;
                });
            }
            html += `</div><div class="hidden bg-red-50 text-red-600 text-xs font-bold p-3 text-center msg-erro">Selecione pelo menos ${min} opção(ões)</div></div>`;
        });
        container.innerHTML = html;
    }

    function checkAdicional(input, max, name, gId) {
        if(input.type === 'checkbox') {
            const marcados = document.querySelectorAll(`input[name="${name}"]:checked`);
            if(marcados.length > max) { input.checked = false; alert(`Máximo de ${max} opções.`); return; }
        }
        const marcados = document.querySelectorAll(`input[name="${name}"]:checked`).length;
        const badge = document.getElementById(`badge-count-${gId}`);
        const grupoDiv = document.querySelector(`.group-adicional[data-id="${gId}"]`);
        if(badge) {
            const min = grupoDiv.dataset.min;
            badge.innerText = `${marcados}/${min > 0 ? min : max}`;
            if(min > 0 && marcados >= min) { badge.classList.add('text-green-600', 'border-green-200'); badge.classList.remove('text-gray-400'); }
        }
        grupoDiv.querySelector(`.msg-erro`).classList.add('hidden');
        atualizarTotalModal();
    }

    function mudarQtdModal(d) {
        if(!produtoAtual) return;
        const n = produtoAtual.qtd + d;
        if(n >= 1) { produtoAtual.qtd = n; document.getElementById('modal-prod-qtd').innerText = n; document.getElementById('btn-menos').disabled = n===1; atualizarTotalModal(); }
    }

    function atualizarTotalModal() {
        let addTotal = 0;
        document.querySelectorAll('#container-adicionais input:checked').forEach(i => addTotal += parseFloat(i.dataset.preco));
        const final = (parseFloat(produtoAtual.preco_real) + addTotal) * produtoAtual.qtd;
        document.getElementById('modal-btn-total').innerText = formatar(final);
    }

   function adicionarAoCarrinhoComComplementos() {
        let erro = false; 
        let primeiroErro = null;
        let adds = []; 
        let addsTotal = 0;

        document.querySelectorAll('.group-adicional').forEach(g => {
            const min = parseInt(g.dataset.min);
            const nomeGrupo = g.querySelector('h4').innerText;
            const checked = g.querySelectorAll('input:checked');
            
            // Validação Rigorosa
            if(checked.length < min) { 
                const msg = g.querySelector('.msg-erro');
                msg.classList.remove('hidden');
                msg.innerHTML = `<i class="fas fa-exclamation-circle"></i> Escolha pelo menos ${min} opção(ões)`;
                
                // Destaque visual
                g.classList.add('border-red-500', 'border-2');
                
                if(!primeiroErro) primeiroErro = g;
                erro = true; 
            } else {
                g.querySelector('.msg-erro').classList.add('hidden');
                g.classList.remove('border-red-500', 'border-2');
                
                checked.forEach(i => {
                    adds.push({
                        id: i.value, 
                        nome: i.dataset.nome,
                        preco: parseFloat(i.dataset.preco)
                    });
                    addsTotal += parseFloat(i.dataset.preco);
                });
            }
        });

        if(erro) {
            if(primeiroErro) {
                primeiroErro.scrollIntoView({behavior: 'smooth', block: 'center'});
                // Vibração para celular
                if(navigator.vibrate) navigator.vibrate(200);
            }
            return; // Bloqueia adição
        }

        // Se passou, adiciona
        produtoAtual.adicionais = adds;
        produtoAtual.observacao = document.getElementById('modal-prod-obs').value;
        produtoAtual.preco = parseFloat(produtoAtual.preco_real) + addsTotal; 
        
        carrinho.push(produtoAtual);
        atualizarUI(); 
        fecharModalProduto();
    }

    function carregarGoogleMaps() {
        if (typeof google === 'object' && typeof google.maps === 'object') {
            iniciarAutocomplete();
            return;
        }
        // Usando a chave que já está no seu sistema
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U&libraries=places&callback=iniciarAutocomplete`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    function iniciarAutocomplete() {
        const input = document.getElementById('input_end');
        if (!input) return;

        // Limita a busca ao Brasil e apenas endereços
        const options = {
            componentRestrictions: { country: "br" },
            fields: ["address_components", "geometry", "formatted_address"],
            types: ["address"],
        };

        const autocomplete = new google.maps.places.Autocomplete(input, options);

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return; // Usuário não selecionou nada válido
            preencherEndereco(place);
        });
    }
    function preencherEndereco(place) {
        let rua = "";
        let numero = "";
        let bairro = "";
        
        // Extrai os dados corretos do Google (Lógica melhorada para Bairro)
        for (const component of place.address_components) {
            const types = component.types; // Pega todos os tipos, não só o primeiro
            
            if (types.includes("route")) {
                rua = component.long_name;
            }
            if (types.includes("street_number")) {
                numero = component.long_name;
            }
            // Verifica todas as possibilidades de bairro no Brasil
            if (types.includes("sublocality_level_1") || types.includes("sublocality") || types.includes("neighborhood")) {
                bairro = component.long_name;
            }
        }

        // Preenche os campos visuais
        document.getElementById('input_end').value = rua;
        document.getElementById('input_bairro').value = bairro; // Agora vai preencher
        document.getElementById('input_num').value = numero;

        // Salva coordenadas ocultas (Importante para o frete preciso)
        if(place.geometry && place.geometry.location) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            document.getElementById('input_lat').value = lat;
            document.getElementById('input_lng').value = lng;

            // Se o Google não trouxe o número, foca nele pro cliente digitar
            if (numero === "") {
                document.getElementById('input_num').focus();
                calcularFretePorCoordenadas(lat, lng, rua, ''); 
            } else {
                // Se trouxe tudo, foca no complemento (agora com ID correto)
                if(document.getElementById('input_comp')) {
                    document.getElementById('input_comp').focus();
                }
                calcularFretePorCoordenadas(lat, lng, rua, numero);
            }
        }
    }
    function calcularFretePorCoordenadas(lat, lng, rua, num) {
        const filialId = document.querySelector('input[name="filial_id"]').value;
        const btnTopo = document.getElementById('lbl-topo-valor');
        if(btnTopo) btnTopo.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        let f = new FormData();
        f.append('filial_id', filialId);
        f.append('lat', lat);
        f.append('lng', lng);

        fetch(ROTA_FRETE_GPS, { method: 'POST', body: f })
            .then(r => r.json())
            .then(d => {
                if(d.ok){
                    document.getElementById('input_taxa_entrega').value = d.valor;
                    salvarEnderecoLocal(rua, num, '', lat, lng, d.valor);
                    calcularTotalFinal();
                } else {
                    alert("Atenção: " + d.erro);
                    document.getElementById('lbl-topo-valor').innerText = '---';
                }
            })
            .catch(() => consultarFreteManual()); // Fallback
    }


    function atualizarUI() {
        const total = carrinho.reduce((a,b)=>a+(b.preco*b.qtd),0);
        document.getElementById('total-barra').innerText = formatar(total);
        document.getElementById('badge-qtd').innerText = carrinho.length;
        const b = document.getElementById('barra-carrinho');
        carrinho.length ? b.classList.remove('translate-y-full') : b.classList.add('translate-y-full');
    }

    function fecharModalProduto() { document.getElementById('modal-produto').classList.add('hidden'); }
    function formatar(v) { return parseFloat(v).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}); }
    
    // --- CHECKOUT DO SALÃO ---
    function abrirCheckout() { 
        document.getElementById('modal-checkout').classList.remove('hidden'); 
        document.getElementById('modal-checkout').classList.add('flex'); 
        renderizarCheckout(); 
        
        if(dadosMesa) {
            console.log("Modo Mesa: Pulando carregamento de endereço");
            configurarVisualSalao();
        } else {
            carregarEnderecoSalvo(); 
            const t=localStorage.getItem('delivery_telefone'); 
            if(t){ document.getElementById('cliente_telefone').value=t; buscarCliente(); } 
        }
    }

    function configurarVisualSalao() {
        // Esconde áreas desnecessárias para mesa
        document.getElementById('area_entrega_box').style.display = 'none';
        
        // Esconde pagamento (pois mesa paga no final)
        const divPagamento = document.getElementById('container-pagamento');
        if(divPagamento) divPagamento.style.display = 'none';

        // Esconde dados do cliente e REMOVE OBRIGATORIEDADE
        const divDados = document.getElementById('area_dados_cliente');
        divDados.style.display = 'none';
        
        document.getElementById('cliente_nome').removeAttribute('required');
        document.getElementById('cliente_telefone').removeAttribute('required');

        // Ajusta botão e título
        const btn = document.getElementById('btn-enviar');
        btn.innerHTML = '<span>ENVIAR PARA COZINHA</span> <i class="fas fa-utensils"></i>';
        document.getElementById('titulo-checkout').innerText = 'Seu Pedido (Mesa ' + dadosMesa.numero + ')';
        
        const radio = document.getElementById('radio_entrega');
        if(radio) radio.checked = true;
        document.getElementById('lbl-taxa-entrega').innerText = '---';
    }

    function fecharCheckout() { document.getElementById('modal-checkout').classList.add('hidden'); document.getElementById('modal-checkout').classList.remove('flex'); }
    
    function renderizarCheckout() {
        let h = ''; let total = 0;
        carrinho.forEach((p,i) => {
            let pt = p.preco * p.qtd; total += pt;
            let addsHtml = p.adicionais.length ? `<div class="text-[10px] text-gray-500 border-l border-gray-300 pl-2 mt-1">${p.adicionais.map(a=>`+ ${a.nome}`).join('<br>')}</div>` : '';
            if(p.observacao) addsHtml += `<div class="text-[10px] text-orange-600 mt-1 italic">"${p.observacao}"</div>`;
            h += `<div class="flex justify-between p-3 border-b border-gray-50"><div class="flex-1"><span class="bg-gray-100 text-xs font-bold px-1 rounded mr-1">${p.qtd}x</span> <span class="text-sm font-bold text-gray-800">${p.nome}</span> ${addsHtml} <button onclick="carrinho.splice(${i},1);atualizarUI();renderizarCheckout()" class="text-[10px] text-red-500 block mt-1">Remover</button></div><span class="text-sm font-bold text-gray-700">${formatar(pt)}</span></div>`;
        });
        document.getElementById('lista-resumo').innerHTML = h;
        document.getElementById('input_itens_json').value = JSON.stringify(carrinho);
        document.getElementById('input_v_prod').value = total.toFixed(2);
        calcularTotalFinal(total);
    }
    
    function calcularTotalFinal(subtotal = null) {
        if(subtotal === null) subtotal = parseFloat(document.getElementById('input_v_prod').value);
        let frete = 0;
        if (!dadosMesa) {
            frete = parseFloat(document.getElementById('input_taxa_entrega').value.replace(',', '.') || 0);
        } else {
             document.getElementById('input_taxa_entrega').value = 0;
        }
        const total = subtotal + frete;
        if(dadosMesa) document.getElementById('lbl-taxa-entrega').innerText = 'Mesa';
        else document.getElementById('lbl-taxa-entrega').innerText = (frete > 0 ? formatar(frete) : 'Grátis');
        document.getElementById('total-final-modal').innerText = formatar(total);
        document.getElementById('input_v_total').value = total.toFixed(2);
        if(!dadosMesa && document.querySelector('input[name="forma_pagamento"]:checked')?.value === 'pix') gerarQRPix(total);
    }

   function toggleEntrega(d) { 
        if(dadosMesa) return;
        const b=document.getElementById('box-endereco'), t=document.getElementById('barra-topo-endereco');
        if(d){ 
            b.classList.remove('hidden'); 
            t.classList.remove('hidden'); 
            t.classList.add('flex'); 
            
            // --- ADICIONADO: Inicia o Google Maps ---
            iniciarAutocomplete();
            
            if(document.getElementById('input_num').value) consultarFreteManual(); 
        }
        else { 
            b.classList.add('hidden'); 
            t.classList.add('hidden'); 
            t.classList.remove('flex'); 
            document.getElementById('input_taxa_entrega').value=0; 
            calcularTotalFinal(); 
        }
    }
    
    function checkTroco() { 
        const p = document.querySelector('input[name="forma_pagamento"]:checked');
        const box = document.getElementById('box-troco');
        
        if (p && p.value === 'dinheiro') {
            box.classList.remove('hidden');
            setTimeout(() => document.getElementById('input_troco').focus(), 100);
        } else {
            box.classList.add('hidden');
            document.getElementById('input_troco').value = ''; 
        }
    }

    function formatarChavePix(chave) { const limpa = chave.replace(/[^a-zA-Z0-9@.+]/g, ""); if (limpa.includes('@')) return limpa; if (/^[0-9]+$/.test(limpa) && limpa.length >= 10 && limpa.length <= 14) { if (!limpa.startsWith('55')) return '+55' + limpa; return '+' + limpa; } return limpa; }
    function gerarPayloadPix(valor) { if (!chavePixLoja) return ""; const chaveFormatada = formatarChavePix(chavePixLoja); const v = parseFloat(valor).toFixed(2); const nome = nomeLojaPix; const cidade = cidadeLojaPix; const txtId = "***"; const f = (id, conteudo) => { const c = String(conteudo); return id + c.length.toString().padStart(2, '0') + c; }; let payload = "000201"; payload += f("26", f("00", "BR.GOV.BCB.PIX") + f("01", chaveFormatada)); payload += "52040000"; payload += "5303986"; payload += f("54", v); payload += "5802BR"; payload += f("59", nome); payload += f("60", cidade); payload += f("62", f("05", txtId)); payload += "6304"; function getCRC16(data) { let crc = 0xFFFF; const polynomial = 0x1021; for (let i = 0; i < data.length; i++) { crc ^= (data.charCodeAt(i) << 8); for (let j = 0; j < 8; j++) { if ((crc & 0x8000) !== 0) crc = (crc << 1) ^ polynomial; else crc <<= 1; } } return (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0'); } return payload + getCRC16(payload); }
    function atualizarPayloadPix() { if(!dadosMesa && document.querySelector('input[name="forma_pagamento"]:checked')?.value !== 'pix') return; const total = parseFloat(document.getElementById('input_v_total').value) || 0; if (total <= 0) return; const payload = gerarPayloadPix(total); if(!payload) return; document.getElementById('pix_copia_cola').value = payload; }
    function copiarPix() { const txt = document.getElementById('pix_copia_cola'); if(!txt.value) return; txt.select(); navigator.clipboard.writeText(txt.value).then(()=>alert("Copiado!")); }

    function buscarCliente() {
        let t = document.getElementById('cliente_telefone').value.replace(/\D/g,''); 
        if(t.length < 8) return;
        
        localStorage.setItem('delivery_telefone', t);
        
        const f = new FormData();
        f.append('telefone', t);
        f.append('empresa_id', document.querySelector('input[name="empresa_id"]').value);
        
        fetch(ROTA_CLIENTE, { method: 'POST', body: f })
            .then(r => r.json())
            .then(texto => {
                try { 
                    const d = typeof texto === 'string' ? JSON.parse(texto) : texto; 
                    
                    if (d.encontrado && !dadosMesa) { 
                        // Preenche Nome
                        document.getElementById('cliente_nome').value = d.dados.nome; 
                        
                        // Preenche Endereço Completo
                        if(d.dados.endereco_ultimo) document.getElementById('input_end').value = d.dados.endereco_ultimo;
                        if(d.dados.numero_ultimo) document.getElementById('input_num').value = d.dados.numero_ultimo;
                        if(d.dados.bairro_ultimo) document.getElementById('input_bairro').value = d.dados.bairro_ultimo;
                        
                        // Preenche Complemento (se o campo existir no HTML)
                        if(d.dados.complemento_ultimo && document.getElementById('input_comp')) {
                            document.getElementById('input_comp').value = d.dados.complemento_ultimo;
                        }

                        // Recalcula o frete com os dados preenchidos
                        consultarFreteManual(); 
                    } 
                } catch(e){ console.error(e); }
            });
    }

    function enviarPedido() {
        const b = document.getElementById('btn-enviar'); 
        b.disabled = true; 

        // 1. CRIA O FORMDATA PRIMEIRO
        const f = new FormData(document.getElementById('form-pedido'));
        const telLimpo = document.getElementById('cliente_telefone').value.replace(/\D/g,'');
        f.set('cliente_telefone', telLimpo);

        // 2. VERIFICAÇÃO DE MESA (CRÍTICO)
        if(dadosMesa) {
            const jsonSessao = localStorage.getItem('mesa_sessao_' + dadosMesa.id);
            let sessao = null;
            
            try { 
                sessao = JSON.parse(jsonSessao); 
            } catch(e) {}

            // Se não tiver sessão válida, BLOQUEIA O ENVIO
            if(!sessao || !sessao.sessao_id || !sessao.participante_id) {
                alert("⚠️ Sessão inválida ou expirada.\nO sistema irá recarregar para você entrar novamente.");
                localStorage.removeItem('mesa_sessao_' + dadosMesa.id); 
                location.reload(); 
                return; // PARE TUDO AQUI
            }

            // INJEÇÃO DOS DADOS OBRIGATÓRIOS PARA A TRAVA FUNCIONAR
            f.set('tipo_entrega', 'salao'); 
            f.append('sessao_id', sessao.sessao_id);
            f.append('participante_id', sessao.participante_id); // <--- SEM ISSO A TRAVA NÃO FUNCIONA
            f.append('cliente_nome', sessao.nome); 
            f.append('forma_pagamento', 'dinheiro'); 

        } else {
            // Validação Delivery
            if (document.getElementById('radio_entrega').checked && !document.getElementById('input_num').value) { 
                alert("Informe o número do endereço."); 
                b.disabled = false; 
                return; 
            }
        }

        b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';

        fetch(ROTA_SALVAR, { method: 'POST', body: f })
            .then(r => r.json()) // Mudado para .json() direto para pegar erro formatado
            .then(d => {
                if(d.ok){
                    fecharCheckout(); 
                    if(dadosMesa) {
                        alert('✅ Pedido enviado para a cozinha!');
                    } else {
                        document.getElementById('tela-sucesso').classList.remove('hidden'); 
                        document.getElementById('tela-sucesso').classList.add('flex');
                        const zap = (f.get('whatsapp_loja') || '').replace(/\D/g,'');
                        const link = `https://wa.me/55${zap}?text=${encodeURIComponent(`Pedido #${d.id} realizado!`)}`;
                        document.getElementById('btn-zap-comprovante').href = link;
                    }
                    carrinho=[]; atualizarUI();
                    b.disabled = false;
                    b.innerHTML = '<span>ENVIAR PARA COZINHA</span> <i class="fas fa-utensils"></i>';
                } else { 
                    // AQUI VAI APARECER O ERRO "CONTA ENCERRADA"
                    alert(d.erro); 
                    b.disabled=false; 
                    b.innerHTML='Tentar Novamente'; 
                }
            })
            .catch((err)=>{ 
                console.error(err); 
                // Tente ler como texto se o JSON falhar (erro de servidor)
                alert("Erro ao processar. Verifique se a conta já não foi paga."); 
                b.disabled=false; b.innerHTML='Tentar Novamente'; 
            });
    }
    // --- FRETE (Só Delivery) ---
    function salvarEnderecoLocal(r,n,b,la,lo,f) { localStorage.setItem('end_salvo',JSON.stringify({r,n,b,la,lo,f})); atualizarTopo(r,n,f); }
    function carregarEnderecoSalvo() { const s=JSON.parse(localStorage.getItem('end_salvo')); if(s){ document.getElementById('input_end').value=s.r||''; document.getElementById('input_num').value=s.n||''; document.getElementById('input_bairro').value=s.b||''; document.getElementById('input_taxa_entrega').value=s.f||0; atualizarTopo(s.r,s.n,s.f); } }
    function atualizarTopo(r,n,f) { if(dadosMesa) return; if(r){ const b=document.getElementById('barra-topo-endereco'); b.classList.remove('hidden'); b.classList.add('flex'); document.getElementById('lbl-topo-rua').innerText=`${r}, ${n}`; document.getElementById('lbl-topo-valor').innerText=`R$ ${parseFloat(f).toFixed(2)}`; } }
    function consultarFreteManual() { if(dadosMesa) return; const rua = document.getElementById('input_end').value; const num = document.getElementById('input_num').value; const filialId = document.querySelector('input[name="filial_id"]').value; if (rua && num) { document.getElementById('lbl-topo-valor').innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; const f = new FormData(); f.append('filial_id', filialId); f.append('endereco', rua); f.append('numero', num); fetch(ROTA_FRETE, { method: 'POST', body: f }).then(r => r.json()).then(d => { if (d.ok) { document.getElementById('input_taxa_entrega').value = d.valor; salvarEnderecoLocal(rua, num, '', d.lat, d.lng, d.valor); calcularTotalFinal(); } else { document.getElementById('lbl-topo-valor').innerText = '---'; } }).catch(e => console.error(e)); } }
    function filtrarCardapio() { let t = document.getElementById('input-busca-cardapio').value.toLowerCase(); document.querySelectorAll('.secao-cardapio').forEach(s => { let show = false; s.querySelectorAll('.item-cardapio').forEach(i => { if(i.dataset.nome.includes(t)) { i.classList.remove('hidden'); show = true; } else i.classList.add('hidden'); }); s.style.display = show ? 'block' : 'none'; }); document.getElementById('nav-categorias').style.display = t ? 'none' : 'flex'; }
    function getCookie(name) { const v = `; ${document.cookie}`; const p = v.split(`; ${name}=`); if (p.length === 2) return p.pop().split(';').shift(); }
    
    function ativarModoSalao() {
        // ESCONDE coisas de Delivery
        const barraEnd = document.getElementById('barra-topo-endereco');
        if(barraEnd) barraEnd.classList.add('hidden');
        if(barraEnd) barraEnd.classList.remove('flex');

        // MOSTRA Barra da Mesa
        const barraMesa = document.getElementById('barra-status-mesa');
        if(barraMesa) {
            barraMesa.classList.remove('hidden');
            barraMesa.classList.add('flex');
            
            // Atualiza título inicial se tiver dados
            if(dadosMesa) {
                // Chama a conta silenciosamente para atualizar os valores na barra
                atualizarResumoValores();
            }
        }

        // Validação de Sessão (Código existente...)
        const sessaoLocal = localStorage.getItem('mesa_sessao_' + dadosMesa.id);
        if (sessaoLocal) {
            try {
                const s = JSON.parse(sessaoLocal);
                const f = new FormData(); f.append('sessao_id', s.sessao_id);
                fetch('<?= BASE_URL ?>/api/mesa/validar', { method: 'POST', body: f })
                    .then(r => r.json())
                    .then(d => { if (!d.valid) { localStorage.removeItem('mesa_sessao_' + dadosMesa.id); location.reload(); } })
                    .catch(e => console.error("Erro validar", e));
            } catch(e) { localStorage.removeItem('mesa_sessao_' + dadosMesa.id); }
        } else {
            const f = new FormData(); f.append('mesa_id', dadosMesa.id);
            fetch('<?= BASE_URL ?>/api/mesa/status', { method: 'POST', body: f })
                .then(r => r.json())
                .then(d => { abrirModalCheckin(d.ocupada); })
                .catch(() => abrirModalCheckin(true));
        }
    }

    function atualizarResumoValores() {
        if(!dadosMesa) return;
        const s = JSON.parse(localStorage.getItem('mesa_sessao_' + dadosMesa.id));
        if(!s) return;

        const f = new FormData(); 
        f.append('sessao_id', s.sessao_id); 
        f.append('participante_id', s.participante_id);

        fetch(ROTA_VER_CONTA, { method: 'POST', body: f })
            .then(r => r.json())
            .then(d => {
                if(d.ok) {
                    // Atualiza a barra de topo
                    document.getElementById('resumo-eu').innerText = formatar(d.total_usuario);
                    document.getElementById('resumo-mesa').innerText = formatar(d.total_mesa);
                    
                    // Se o modal estiver aberto, atualiza ele também (cache)
                    dadosContaCache = d;
                    renderizarAbaAtual();
                }
            })
            .catch(e => console.error("Erro ao atualizar resumo", e));
    }
    
    function sairMesa() { if(confirm("Sair da mesa?")) { localStorage.removeItem('mesa_sessao_'+dadosMesa.id); location.reload(); } }
    function abrirModalCheckin(mesaOcupada) {
        const htmlTipoConta = !mesaOcupada ? `<div class="mb-4 bg-purple-50 p-3 rounded-lg border border-purple-100"><label class="block text-xs font-bold text-purple-700 uppercase mb-2">Como será a conta?</label><div class="flex gap-2"><label class="flex-1 cursor-pointer"><input type="radio" name="tipo_divisao" value="unica" checked class="peer hidden"><div class="border border-purple-200 bg-white text-gray-500 rounded-lg p-2 text-center text-xs font-bold peer-checked:bg-purple-600 peer-checked:text-white transition">JUNTOS (1 Conta)</div></label><label class="flex-1 cursor-pointer"><input type="radio" name="tipo_divisao" value="individual" class="peer hidden"><div class="border border-purple-200 bg-white text-gray-500 rounded-lg p-2 text-center text-xs font-bold peer-checked:bg-purple-600 peer-checked:text-white transition">SEPARADO</div></label></div></div>` : `<input type="hidden" name="tipo_divisao" value="individual"><p class="text-xs text-center text-purple-600 font-bold mb-4 bg-purple-50 p-2 rounded">🍽️ Junte-se à mesa!</p>`;
        const html = `<div id="modal-checkin" class="fixed inset-0 bg-black/80 z-[80] flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in"><div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl"><div class="text-center mb-5"><div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner"><i class="fas fa-utensils"></i></div><h2 class="text-2xl font-black text-gray-800 leading-tight">Mesa ${dadosMesa.numero}</h2><p class="text-gray-500 text-sm mt-1">Para começar, identifique-se:</p></div><form onsubmit="realizarCheckin(event)"><label class="block text-xs font-bold text-gray-400 uppercase mb-1">Seu Nome</label><input type="text" id="checkin_nome" class="w-full border border-gray-300 rounded-xl p-3 mb-4 font-bold outline-none focus:border-purple-500 uppercase" placeholder="Ex: RAPHAEL" required>${htmlTipoConta}<label class="block text-xs font-bold text-gray-400 uppercase mb-1">WhatsApp (Opcional)</label><input type="tel" id="checkin_tel" class="w-full border border-gray-300 rounded-xl p-3 mb-6 font-bold outline-none focus:border-purple-500" placeholder="11 99999-9999"><button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl shadow-lg transition active:scale-95">ABRIR CARDÁPIO</button></form></div></div>`;
        document.body.insertAdjacentHTML('beforeend', html);
    }
    function realizarCheckin(e) {
        e.preventDefault();
        const nome = document.getElementById('checkin_nome').value; const tel = document.getElementById('checkin_tel').value;
        const radio = document.querySelector('input[name="tipo_divisao"]:checked'); const tipo = radio ? radio.value : 'unica';
        const btn = e.target.querySelector('button'); btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';
        const f = new FormData(); f.append('mesa_id', dadosMesa.id); f.append('nome', nome); f.append('telefone', tel); f.append('tipo_divisao', tipo);
        fetch(ROTA_CHECKIN, { method: 'POST', body: f }).then(r => r.json()).then(d => { if(d.ok) { localStorage.setItem('mesa_sessao_' + dadosMesa.id, JSON.stringify({ sessao_id: d.sessao_id, participante_id: d.participante_id, nome: d.nome, telefone: tel })); document.getElementById('modal-checkin').remove(); } else { alert('Erro: ' + (d.erro || 'Falha')); btn.disabled = false; btn.innerHTML = 'TENTAR NOVAMENTE'; } }).catch(err => { alert('Erro de conexão.'); btn.disabled = false; btn.innerHTML = 'TENTAR NOVAMENTE'; });
    }
    function usarLocalizacaoAtual() { const btn=document.querySelector('button[onclick="usarLocalizacaoAtual()"]'); btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>'; if(!navigator.geolocation) { alert("GPS não suportado"); return; } navigator.geolocation.getCurrentPosition(p=>{ const lat=p.coords.latitude, lng=p.coords.longitude; document.getElementById('input_lat').value=lat; document.getElementById('input_lng').value=lng; let f=new FormData(); f.append('filial_id',document.querySelector('input[name="filial_id"]').value); f.append('lat',lat); f.append('lng',lng); fetch(ROTA_FRETE_GPS, { method: 'POST', body: f }).then(r=>r.json()).then(d=>{ if(d.ok){ btn.innerHTML='<i class="fas fa-check"></i> Localizado'; document.getElementById('input_taxa_entrega').value=d.valor; if(d.endereco_sugerido) { document.getElementById('input_end').value=d.endereco_sugerido.rua; document.getElementById('input_num').value=d.endereco_sugerido.numero; } salvarEnderecoLocal(d.endereco_sugerido?.rua,d.endereco_sugerido?.numero,'',lat,lng,d.valor); calcularTotalFinal(); } else { alert(d.erro); btn.innerHTML='Usar localização atual'; } }); }, ()=>{ alert("Permita o GPS"); btn.innerHTML='Usar localização atual'; }); }

    // --- NOVA FUNÇÃO DE CONTA (COM ABAS SEMPRE ATIVAS E TOTAIS NO RODAPÉ) ---
    function verContaMesa() {
        if(!dadosMesa) return;
        
        // Abre o modal
        document.getElementById('modal-conta').classList.remove('hidden');
        document.getElementById('lista-consumo').innerHTML = '<div class="text-center py-10"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i></div>';
        
        // Chama a atualização (que vai preencher a barra e o modal)
        atualizarResumoValores();
        
        // Loop para manter atualizado a cada 10 segundos enquanto estiver na mesa (Opcional, mas legal)
        if(!window.intervaloConta) {
            window.intervaloConta = setInterval(atualizarResumoValores, 15000);
        }
    }


    function mudarAbaConta(aba) {
        abaAtual = aba;
        renderizarAbaAtual();
    }

    function renderizarAbaAtual() {
        if (!dadosContaCache) return;

        const btnMinha = document.getElementById('tab-minha');
        const btnMesa = document.getElementById('tab-mesa');
        
        // Atualiza Estilo das Abas
        if (abaAtual === 'minha') {
            btnMinha.className = 'flex-1 py-2 text-xs font-bold rounded-lg transition bg-white text-purple-700 shadow-sm border border-gray-200';
            btnMesa.className = 'flex-1 py-2 text-xs font-bold rounded-lg transition text-gray-400 hover:text-gray-600';
            
            // ATUALIZA TOTAIS NO RODAPÉ (Corrigido para evitar erro null)
            const lblLegenda = document.getElementById('lbl-total-legenda');
            const lblTotal = document.getElementById('total-conta');
            
            if(lblLegenda && lblTotal) {
                lblLegenda.innerText = "Minha Parte";
                lblTotal.innerText = formatar(dadosContaCache.total_usuario);
            }
        } else {
            btnMinha.className = 'flex-1 py-2 text-xs font-bold rounded-lg transition text-gray-400 hover:text-gray-600';
            btnMesa.className = 'flex-1 py-2 text-xs font-bold rounded-lg transition bg-white text-purple-700 shadow-sm border border-gray-200';
            
            const lblLegenda = document.getElementById('lbl-total-legenda');
            const lblTotal = document.getElementById('total-conta');
            
            if(lblLegenda && lblTotal) {
                lblLegenda.innerText = "Total da Mesa";
                lblTotal.innerText = formatar(dadosContaCache.total_mesa);
            }
        }

        const listaFiltrada = dadosContaCache.itens.filter(item => {
            if (abaAtual === 'mesa') return true; 
            return item.is_me; 
        });

        renderizarListaItens(listaFiltrada);
    }

    function renderizarListaItens(itens) {
        const div = document.getElementById('lista-consumo');
        
        if(itens.length === 0) {
            div.innerHTML = `
                <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                    <i class="fas fa-receipt text-4xl mb-2 opacity-20"></i>
                    <p class="text-sm font-bold">Nenhum item nesta visão.</p>
                </div>`;
            return;
        }

        let html = '<div class="space-y-4">';
        
        itens.forEach(i => {
            let statusBadge = '';
            let textClass = 'text-gray-800'; // Cor padrão do texto
            let priceClass = 'text-gray-800'; // Cor padrão do preço

            // --- LÓGICA VISUAL DOS STATUS ---
            if(i.status === 'cancelado') {
                statusBadge = '<span class="text-[9px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-bold uppercase"><i class="fas fa-ban"></i> Cancelado</span>';
                textClass = 'text-gray-400 line-through'; // Riscado
                priceClass = 'text-gray-400 line-through text-xs'; // Riscado e menor
            } 
            else if(i.status === 'fila' || i.status === 'preparo') {
                statusBadge = '<span class="text-[9px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded font-bold uppercase"><i class="fas fa-fire"></i> Cozinha</span>';
            } 
            else if(i.status === 'entregue') {
                statusBadge = '<span class="text-[9px] bg-green-100 text-green-600 px-2 py-0.5 rounded font-bold uppercase"><i class="fas fa-check"></i> Entregue</span>';
            }

            let addsHtml = '';
            if(i.adicionais && i.adicionais.length > 0) {
                addsHtml = `<div class="text-xs text-gray-500 mt-1 pl-2 border-l-2 border-gray-200">${i.adicionais.map(a => `+ ${a.nome}`).join('<br>')}</div>`;
            }

            let obsHtml = i.obs ? `<div class="text-[10px] text-orange-600 italic mt-1 bg-orange-50 p-1 rounded inline-block"><i class="far fa-comment"></i> ${i.obs}</div>` : '';

            // Se for visão da mesa e o item não for meu, mostra quem pediu
            let donoHtml = '';
            let estiloItem = '';
            
            if (abaAtual === 'mesa' && !i.is_me) {
                donoHtml = `<div class="text-[9px] font-bold text-purple-600 mb-1 uppercase bg-purple-50 inline-block px-1.5 rounded"><i class="fas fa-user"></i> ${i.quem_pediu}</div>`;
                estiloItem = 'opacity-75'; 
            }

            html += `
            <div class="flex justify-between items-start border-b border-gray-50 pb-3 last:border-0 ${estiloItem}">
                <div class="flex-1">
                    ${donoHtml}
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-gray-100 text-gray-800 text-xs font-black px-1.5 py-0.5 rounded">${i.qtd}x</span>
                        <span class="text-sm font-bold uppercase leading-tight ${textClass}">${i.nome}</span>
                    </div>
                    ${addsHtml}
                    ${obsHtml}
                    <div class="mt-1">${statusBadge}</div>
                </div>
                <div class="text-right">
                    <span class="block text-sm font-black ${priceClass}">${formatar(i.total)}</span>
                </div>
            </div>`;
        });

        html += '</div>';
        div.innerHTML = html;
    }

    // Inicialização
   window.onload = function() {
        const cookieMesa = getCookie('mesa_ativa');
        if (cookieMesa) {
            try {
                dadosMesa = JSON.parse(decodeURIComponent(cookieMesa));
                ativarModoSalao();
            } catch(e) { console.error("Erro cookie mesa", e); }
        } else {
            carregarEnderecoSalvo();
            carregarGoogleMaps(); // <--- NOVO: Carrega o mapa para delivery
        }
    }
</script>
</body>
</html>