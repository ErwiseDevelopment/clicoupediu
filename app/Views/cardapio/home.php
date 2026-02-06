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
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png?v=2" type="image/png">
    
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/favicon.png?v=2">
    
    <meta name="theme-color" content="#ea1d2c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ClicouPediu">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; -webkit-tap-highlight-color: transparent; }
        .hide-scroll::-webkit-scrollbar { display: none; } 
        .modal-open { overflow: hidden; }
        .snap-x-mandatory { scroll-snap-type: x mandatory; }
        .snap-center { scroll-snap-align: center; }
        .shadow-card { box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
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
            <button onclick="abrirMeusPedidos()" class="w-9 h-9 rounded-full bg-gray-50 text-gray-600 flex items-center justify-center border border-gray-200 hover:bg-gray-100 transition">
                <i class="fas fa-receipt text-sm"></i>
            </button>
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
                        <div onclick='abrirModalProduto(<?php echo json_encode($prodJS); ?>)' class="item-cardapio snap-center shrink-0 w-[280px] bg-white rounded-xl shadow-card border border-gray-100 relative overflow-hidden active:scale-95 transition <?php echo $esgotado ? 'opacity-60 pointer-events-none' : ''; ?>" data-nome="<?php echo strtolower($prod['nome']); ?>">
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
                         $controla = $prod['controle_estoque'] ?? 1; $qtd = intval($prod['estoque_atual'] ?? 0); $esgotado = ($controla == 1 && $qtd <= 0);
                         $precoBase = (float)$prod['preco_base']; $precoPromo = (float)($prod['preco_promocional'] ?? 0);
                         $temDesconto = ($precoPromo > 0 && $precoPromo < $precoBase); $precoFinal = $temDesconto ? $precoPromo : $precoBase;
                         $prodJS = $prod; $prodJS['preco_real'] = $precoFinal;
                    ?>
                         <div onclick='abrirModalProduto(<?php echo json_encode($prodJS); ?>)' class="item-cardapio flex justify-between bg-white p-3 rounded-lg border border-gray-100 shadow-sm active:bg-gray-50 transition relative overflow-hidden <?php echo $esgotado ? 'opacity-60 pointer-events-none' : ''; ?>" data-nome="<?php echo strtolower($prod['nome']); ?>">
                            <div class="flex-1 pr-3 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-sm text-gray-800 mb-1 leading-tight"><?php echo $prod['nome']; ?></h3>
                                    <p class="text-xs text-gray-500 line-clamp-2 mb-2"><?php echo $prod['descricao']; ?></p>
                                </div>
                                <span class="text-sm font-bold text-green-700">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></span>
                            </div>
                            <?php if(!empty($prod['imagem_url'])): ?>
                                <div class="w-24 h-24 rounded-lg bg-gray-100 overflow-hidden shrink-0 relative">
                                    <img src="<?php echo $prod['imagem_url']; ?>" class="w-full h-full object-cover">
                                    <?php if($esgotado): ?><div class="absolute inset-0 bg-black/50 flex items-center justify-center text-white text-[9px] font-bold uppercase">Esgotado</div><?php endif; ?>
                                </div>
                            <?php endif; ?>
                         </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <div id="barra-carrinho" class="fixed bottom-0 w-full bg-white border-t border-gray-100 p-3 shadow-float z-40 transform translate-y-full transition-transform duration-300">
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
                <h3 class="font-bold text-lg text-gray-800">Sua Sacola</h3>
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
                    <div class="space-y-4">
                        <input type="tel" id="cliente_telefone" name="cliente_telefone" placeholder="Seu WhatsApp" onblur="buscarCliente()" class="w-full border border-gray-200 rounded-lg p-3 text-sm font-bold outline-none bg-gray-50" required>
                        <input type="text" id="cliente_nome" name="cliente_nome" placeholder="Seu Nome" class="w-full border border-gray-200 rounded-lg p-3 text-sm font-bold outline-none bg-gray-50" required>
                        <div class="border-t pt-3">
                             <p class="text-xs font-bold text-gray-400 mb-2">ENTREGA</p>
                             <div class="flex bg-gray-100 p-1 rounded-lg mb-2">
                                <label class="flex-1 text-center cursor-pointer"><input type="radio" name="tipo_entrega" value="entrega" checked class="hidden peer" onchange="toggleEntrega(true)"><span class="block py-2 text-xs font-bold text-gray-500 rounded peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm">Delivery</span></label>
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
                                    <input type="text" name="complemento" placeholder="Comp." class="w-24 border border-gray-200 rounded-lg p-3 text-sm bg-gray-50">
                                </div>
                                <input type="hidden" name="taxa_entrega" id="input_taxa_entrega" value="0.00">
                                <input type="hidden" name="lat_entrega" id="input_lat">
                                <input type="hidden" name="lng_entrega" id="input_lng">
                             </div>
                        </div>
                        <div class="border-t pt-3">
                             <p class="text-xs font-bold text-gray-400 mb-2">PAGAMENTO</p>
                             <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer group"><input type="radio" name="forma_pagamento" value="pix" class="peer hidden" onchange="checkTroco()"><div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50"><i class="fas fa-qrcode"></i> PIX</div></label>
                                <label class="cursor-pointer group"><input type="radio" name="forma_pagamento" value="cartao" class="peer hidden" onchange="checkTroco()"><div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50"><i class="fas fa-credit-card"></i> Cartão</div></label>
                                <label class="cursor-pointer group"><input type="radio" name="forma_pagamento" value="dinheiro" class="peer hidden" onchange="checkTroco()"><div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50"><i class="fas fa-money-bill"></i> Dinheiro</div></label>
                             </div>
                             <div id="box-troco" class="hidden mt-2"><input type="text" name="troco_para" placeholder="Troco para quanto?" class="w-full border border-gray-200 rounded p-2 text-sm"></div>
                             <div id="box-pix-pagamento" class="hidden mt-2"><textarea id="pix_copia_cola" readonly class="w-full text-xs p-2 border rounded bg-gray-50 h-16"></textarea><button onclick="copiarPix()" class="bg-blue-600 text-white text-xs px-3 py-1 rounded mt-1">Copiar</button></div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        var carrinho = [];
        var produtoAtual = null;
        var lojaAberta = <?php echo $lojaAberta ? 'true' : 'false'; ?>;
        var chavePixLoja = "<?php echo $chavePix; ?>";
        const BASE_URL = '<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>';
        
        <?php 
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $slugLoja = $empresa['slug'] ?? 'teste'; 
            $urlApiDetalhes = "{$baseUrl}/{$slugLoja}/cardapio/detalhesProduto";
            $urlApiFrete = "{$baseUrl}/{$slugLoja}/cardapio/calcularFrete";
            $urlApiGps = "{$baseUrl}/{$slugLoja}/cardapio/frete_gps";
            $urlApiCliente = "{$baseUrl}/{$slugLoja}/cardapio/buscarCliente";
            $urlApiSalvar = "{$baseUrl}/{$slugLoja}/cardapio/salvarPedido";
            $urlApiHistorico = "{$baseUrl}/{$slugLoja}/cardapio/historico";
        ?>
        const ROTA_DETALHES = "<?php echo $urlApiDetalhes; ?>";
        const ROTA_FRETE = "<?php echo $urlApiFrete; ?>";
        const ROTA_FRETE_GPS = "<?php echo $urlApiGps; ?>";
        const ROTA_CLIENTE = "<?php echo $urlApiCliente; ?>";
        const ROTA_SALVAR = "<?php echo $urlApiSalvar; ?>";
        const ROTA_HISTORICO = "<?php echo $urlApiHistorico; ?>";

        function abrirModalProduto(prod) {
            if(!lojaAberta) { alert("Loja Fechada."); return; }
            
            produtoAtual = JSON.parse(JSON.stringify(prod));
            produtoAtual.qtd = 1; produtoAtual.adicionais = [];

            // UI Reset
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

            // Fetch Blindado
            fetch(ROTA_DETALHES + "?id=" + prod.id)
                .then(r => r.text())
                .then(texto => {
                    texto = texto.trim().replace(/^\uFEFF/, '');
                    try {
                        const d = JSON.parse(texto);
                        if(d.ok && d.complementos && d.complementos.length > 0) {
                            renderizarComplementos(d.complementos);
                        } else {
                            container.innerHTML = '<div class="p-4 text-center text-gray-400 text-xs italic">Sem adicionais.</div>';
                        }
                    } catch (e) {
                        console.error("Erro Parse JSON:", e);
                        container.innerHTML = '<div class="p-4 text-center text-red-400 text-xs">Erro ao carregar opções.</div>';
                    }
                })
                .catch(e => {
                    console.error("Erro Conexão:", e);
                    container.innerHTML = '';
                });
        }

        function renderizarComplementos(grupos) {
            const container = document.getElementById('container-adicionais');
            let html = '';
            grupos.forEach(g => {
        if(g.nome === 'ERRO SQL') { html += `<div class="text-red-500 font-bold p-4 bg-red-50 rounded">ERRO SQL: ${g.itens[0].nome}</div>`; return; }
        
        const max = parseInt(g.maximo);
        let min = parseInt(g.minimo); // Mudamos de const para let
        const isObrigatorio = g.obrigatorio == 1;

        // CORREÇÃO: Se for obrigatório, forçamos o mínimo ser 1
        if (isObrigatorio && min < 1) {
            min = 1;
        }
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
                        html += `
                            <label class="flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 transition" for="${idInput}">
                                <div class="flex-1 pr-4"><span class="block text-sm font-semibold text-gray-700">${item.nome}</span><span class="block text-xs font-bold text-gray-500 mt-0.5">${precoTxt}</span></div>
                                <div class="relative flex items-center"><input type="${tipo}" id="${idInput}" name="${name}" value="${item.id}" data-preco="${item.preco}" data-nome="${item.nome}" data-grupo="${g.id}" class="${tipo === 'radio' ? 'radio-custom' : 'chk-custom'} peer w-5 h-5 opacity-0 absolute z-10" onchange="checkAdicional(this, ${max}, '${name}', ${g.id})"><div></div></div>
                            </label>`;
                    });
                } else { html += '<div class="p-4 text-xs text-gray-400">Nenhuma opção cadastrada neste grupo.</div>'; }
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
            const min = document.querySelector(`.group-adicional[data-id="${gId}"]`).dataset.min;
            const badge = document.getElementById(`badge-count-${gId}`);
            if(badge) {
                badge.innerText = `${marcados}/${min > 0 ? min : max}`;
                if(min > 0 && marcados >= min) { badge.classList.add('text-green-600', 'border-green-200'); badge.classList.remove('text-gray-400'); }
            }
            document.querySelector(`.group-adicional[data-id="${gId}"] .msg-erro`).classList.add('hidden');
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
            let erro = false; let adds = []; let addsTotal = 0;
            document.querySelectorAll('.group-adicional').forEach(g => {
                const min = parseInt(g.dataset.min);
                const checked = g.querySelectorAll('input:checked');
                if(checked.length < min) { g.querySelector('.msg-erro').classList.remove('hidden'); erro = true; g.scrollIntoView({behavior:'smooth', block:'center'}); }
                else {
                    g.querySelector('.msg-erro').classList.add('hidden');
                    checked.forEach(i => {
                        adds.push({
                            id: i.value, // O valor do input é o ID do complemento
                            nome: i.dataset.nome,
                            preco: parseFloat(i.dataset.preco)
                        });
                        addsTotal += parseFloat(i.dataset.preco);
                    });
                }
            });
            if(erro) return;
            produtoAtual.adicionais = adds;
            produtoAtual.observacao = document.getElementById('modal-prod-obs').value;
            produtoAtual.preco = parseFloat(produtoAtual.preco_real) + addsTotal; 
            carrinho.push(produtoAtual);
            atualizarUI(); fecharModalProduto();
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
        
        function abrirCheckout() { document.getElementById('modal-checkout').classList.remove('hidden'); document.getElementById('modal-checkout').classList.add('flex'); renderizarCheckout(); carregarEnderecoSalvo(); const t=localStorage.getItem('delivery_telefone'); if(t){ document.getElementById('cliente_telefone').value=t; buscarCliente(); } }
        function fecharCheckout() { document.getElementById('modal-checkout').classList.add('hidden'); document.getElementById('modal-checkout').classList.remove('flex'); }
        function renderizarCheckout() {
            let h = ''; let total = 0;
            carrinho.forEach((p,i) => {
                let pt = p.preco * p.qtd; total += pt;
                let addsHtml = p.adicionais.length ? `<div class="text-[10px] text-gray-500 border-l border-gray-300 pl-2 mt-1">${p.adicionais.map(a=>`+ ${a.nome}`).join('<br>')}</div>` : '';
                h += `<div class="flex justify-between p-3 border-b border-gray-50"><div class="flex-1"><span class="bg-gray-100 text-xs font-bold px-1 rounded mr-1">${p.qtd}x</span> <span class="text-sm font-bold text-gray-800">${p.nome}</span> ${addsHtml} <button onclick="carrinho.splice(${i},1);atualizarUI();renderizarCheckout()" class="text-[10px] text-red-500 block mt-1">Remover</button></div><span class="text-sm font-bold text-gray-700">${formatar(pt)}</span></div>`;
            });
            document.getElementById('lista-resumo').innerHTML = h;
            document.getElementById('input_itens_json').value = JSON.stringify(carrinho);
            document.getElementById('input_v_prod').value = total.toFixed(2);
            calcularTotalFinal(total);
        }
        
        function calcularTotalFinal(subtotal = null) {
            if(subtotal === null) subtotal = parseFloat(document.getElementById('input_v_prod').value);
            const frete = parseFloat(document.getElementById('input_taxa_entrega').value) || 0;
            const total = subtotal + frete;
            document.getElementById('lbl-taxa-entrega').innerText = frete > 0 ? formatar(frete) : 'Grátis';
            document.getElementById('total-final-modal').innerText = formatar(total);
            document.getElementById('input_v_total').value = total.toFixed(2);
            if(document.querySelector('input[name="forma_pagamento"]:checked')?.value === 'pix') gerarQRPix(total);
        }

        function toggleEntrega(d) { 
            const b=document.getElementById('box-endereco'), t=document.getElementById('barra-topo-endereco');
            if(d){ b.classList.remove('hidden'); t.classList.remove('hidden'); t.classList.add('flex'); if(document.getElementById('input_num').value) consultarFreteManual(); }
            else { b.classList.add('hidden'); t.classList.add('hidden'); t.classList.remove('flex'); document.getElementById('input_taxa_entrega').value=0; calcularTotalFinal(); }
        }
        
        function checkTroco() { 
            const p=document.querySelector('input[name="forma_pagamento"]:checked')?.value;
            document.getElementById('box-troco').classList.toggle('hidden', p !== 'dinheiro');
            document.getElementById('box-pix-pagamento').classList.toggle('hidden', p !== 'pix');
            if(p==='pix') calcularTotalFinal();
        }

        function gerarQRPix(v) {
            if(!chavePixLoja) return;
            const payload = `00020126360014BR.GOV.BCB.PIX0114${chavePixLoja}520400005303986540${v.toFixed(2).length}${v.toFixed(2)}5802BR5904LOJA6006CIDADE62070503***6304`; 
            document.getElementById('pix_copia_cola').value = payload;
        }
        function copiarPix() { navigator.clipboard.writeText(document.getElementById('pix_copia_cola').value).then(()=>alert("Copiado!")); }

        function buscarCliente() {
            let t=document.getElementById('cliente_telefone').value.replace(/\D/g,''); if(t.length<8)return;
            localStorage.setItem('delivery_telefone',t);
            const f = new FormData();
            f.append('telefone', t);
            f.append('empresa_id', document.querySelector('input[name="empresa_id"]').value);
            fetch(ROTA_CLIENTE, { method: 'POST', body: f }).then(r => r.text()).then(texto => {
                texto = texto.trim().replace(/^\uFEFF/, '');
                try {
                    const d = JSON.parse(texto);
                    if (d.encontrado) { 
                        document.getElementById('cliente_nome').value = d.dados.nome; 
                        if (!localStorage.getItem('end_salvo')) consultarFreteManual(); 
                    }
                } catch(e) { console.error(e); }
            });
        }

        function enviarPedido() {
            const b=document.getElementById('btn-enviar'); b.disabled=true; b.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
            let f=new FormData(document.getElementById('form-pedido'));
            f.append('cliente_telefone',document.getElementById('cliente_telefone').value.replace(/\D/g,''));
            
            fetch(ROTA_SALVAR, { method: 'POST', body: f })
                .then(r => r.text())
                .then(texto => {
                    texto = texto.trim().replace(/^\uFEFF/, '');
                    try {
                        const d = JSON.parse(texto);
                        if(d.ok){
                            fecharCheckout(); document.getElementById('tela-sucesso').classList.remove('hidden'); document.getElementById('tela-sucesso').classList.add('flex');
                            const zap = f.get('whatsapp_loja').replace(/\D/g,'');
                            const link = `https://wa.me/55${zap}?text=${encodeURIComponent(`Pedido #${d.id} realizado!`)}`;
                            document.getElementById('btn-zap-comprovante').href = link;
                            carrinho=[]; atualizarUI();
                        } else { alert(d.erro); b.disabled=false; b.innerHTML='Confirmar Pedido'; }
                    } catch(e) { 
                        alert("Erro de processamento."); 
                        b.disabled=false; b.innerHTML='Confirmar Pedido'; 
                    }
                })
                .catch(()=>{ alert("Erro conexão"); b.disabled=false; b.innerHTML='Confirmar Pedido'; });
        }

        function abrirMeusPedidos() { document.getElementById('modal-meus-pedidos').classList.remove('hidden'); document.getElementById('modal-meus-pedidos').classList.add('flex'); let t=localStorage.getItem('delivery_telefone'); if(t) carregarPedidosAPI(t); else document.getElementById('area-login-pedidos').classList.remove('hidden'); }
        function fecharMeusPedidos() { document.getElementById('modal-meus-pedidos').classList.add('hidden'); document.getElementById('modal-meus-pedidos').classList.remove('flex'); }
        function buscarMeusPedidosManual() { let t=document.getElementById('input-tel-busca').value.replace(/\D/g,''); if(t.length<8)return; localStorage.setItem('delivery_telefone',t); carregarPedidosAPI(t); }
        function carregarPedidosAPI(t) {
            document.getElementById('area-login-pedidos').classList.add('hidden'); document.getElementById('loading-pedidos').classList.remove('hidden'); document.getElementById('lista-meus-pedidos').innerHTML='';
            let f=new FormData(); f.append('telefone',t); f.append('empresa_id',document.querySelector('input[name="empresa_id"]').value);
            fetch(ROTA_HISTORICO, { method: 'POST', body: f }).then(r => r.json()).then(d => {
                document.getElementById('loading-pedidos').classList.add('hidden');
                if(d.ok && d.pedidos.length>0) {
                    let h=''; d.pedidos.forEach(p=>{ h+=`<div class="bg-gray-50 p-3 rounded border border-gray-100 flex justify-between items-center"><div><span class="text-xs font-bold text-gray-500">#${p.id} • ${p.data}</span><span class="text-[10px] font-bold ${p.cor} px-2 py-0.5 rounded ml-2 uppercase">${p.status}</span></div><span class="font-bold text-sm text-gray-800">R$ ${p.total}</span></div>`; });
                    document.getElementById('lista-meus-pedidos').innerHTML=h;
                } else document.getElementById('lista-meus-pedidos').innerHTML='<p class="text-center text-gray-400 py-4 text-xs">Nenhum pedido encontrado.</p>';
            });
        }
        function salvarEnderecoLocal(r,n,b,la,lo,f) { localStorage.setItem('end_salvo',JSON.stringify({r,n,b,la,lo,f})); atualizarTopo(r,n,f); }
        function carregarEnderecoSalvo() { const s=JSON.parse(localStorage.getItem('end_salvo')); if(s){ document.getElementById('input_end').value=s.r||''; document.getElementById('input_num').value=s.n||''; document.getElementById('input_bairro').value=s.b||''; document.getElementById('input_taxa_entrega').value=s.f||0; atualizarTopo(s.r,s.n,s.f); } }
        function atualizarTopo(r,n,f) { if(r){ const b=document.getElementById('barra-topo-endereco'); b.classList.remove('hidden'); b.classList.add('flex'); document.getElementById('lbl-topo-rua').innerText=`${r}, ${n}`; document.getElementById('lbl-topo-valor').innerText=`R$ ${parseFloat(f).toFixed(2)}`; } }
        
        function consultarFreteManual() {
            const rua = document.getElementById('input_end').value;
            const num = document.getElementById('input_num').value;
            const filialId = document.querySelector('input[name="filial_id"]').value;

            if (rua && num) {
                document.getElementById('lbl-topo-valor').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                const f = new FormData();
                f.append('filial_id', filialId);
                f.append('endereco', rua);
                f.append('numero', num);

                fetch(ROTA_FRETE, { method: 'POST', body: f })
                    .then(r => r.text()) 
                    .then(texto => {
                        const jsonClean = texto.trim().replace(/^\uFEFF/, '');
                        return JSON.parse(jsonClean);
                    })
                    .then(d => {
                        if (d.ok) {
                            document.getElementById('input_taxa_entrega').value = d.valor;
                            
                            if(d.endereco_sugerido && d.endereco_sugerido.bairro) {
                                document.getElementById('input_bairro').value = d.endereco_sugerido.bairro;
                            }
                            if(d.endereco_sugerido && d.endereco_sugerido.rua) {
                                document.getElementById('input_end').value = d.endereco_sugerido.rua;
                            }

                            salvarEnderecoLocal(rua, num, '', d.lat, d.lng, d.valor);
                            calcularTotalFinal();
                        } else {
                            alert(d.erro || "Não foi possível calcular o frete para este endereço.");
                            document.getElementById('lbl-topo-valor').innerText = '---';
                        }
                    })
                    .catch(e => {
                        console.error("Erro frete:", e);
                    });
            }
        }

        function usarLocalizacaoAtual() {
            const btn=document.querySelector('button[onclick="usarLocalizacaoAtual()"]'); btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
            if(!navigator.geolocation) { alert("GPS não suportado"); return; }
            navigator.geolocation.getCurrentPosition(p=>{
                const lat=p.coords.latitude, lng=p.coords.longitude;
                document.getElementById('input_lat').value=lat; document.getElementById('input_lng').value=lng;
                let f=new FormData(); f.append('filial_id',document.querySelector('input[name="filial_id"]').value); f.append('lat',lat); f.append('lng',lng);
                fetch(ROTA_FRETE_GPS, { method: 'POST', body: f }).then(r=>r.json()).then(d=>{
                    if(d.ok){ btn.innerHTML='<i class="fas fa-check"></i> Localizado'; document.getElementById('input_taxa_entrega').value=d.valor; if(d.endereco_sugerido) { document.getElementById('input_end').value=d.endereco_sugerido.rua; document.getElementById('input_num').value=d.endereco_sugerido.numero; } salvarEnderecoLocal(d.endereco_sugerido?.rua,d.endereco_sugerido?.numero,'',lat,lng,d.valor); calcularTotalFinal(); }
                    else { alert(d.erro); btn.innerHTML='Usar localização atual'; }
                });
            }, ()=>{ alert("Permita o GPS"); btn.innerHTML='Usar localização atual'; });
        }
        function filtrarCardapio() {
            let t = document.getElementById('input-busca-cardapio').value.toLowerCase();
            document.querySelectorAll('.secao-cardapio').forEach(s => {
                let show = false;
                s.querySelectorAll('.item-cardapio').forEach(i => {
                    if(i.dataset.nome.includes(t)) { i.classList.remove('hidden'); show = true; } else i.classList.add('hidden');
                });
                s.style.display = show ? 'block' : 'none';
            });
            document.getElementById('nav-categorias').style.display = t ? 'none' : 'flex';
        }
        window.onload = function() { carregarEnderecoSalvo(); }
    </script>
</body>
</html>