<?php $titulo = "Mesa " . $sessao['num_mesa']; require __DIR__ . '/../../partials/header.php'; ?>

<style>
    .prod-card { cursor: pointer; transition: all 0.1s; user-select: none; }
    .prod-card:active { transform: scale(0.95); }
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    /* Checkbox Customizado */
    .chk-custom { appearance: none; -webkit-appearance: none; }
    .chk-custom + div { border: 2px solid #d1d5db; border-radius: 6px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
    .chk-custom:checked + div { background-color: #16a34a; border-color: #16a34a; }
    .chk-custom:checked + div::after { content: '✔'; font-size: 12px; color: white; font-weight: bold; }
</style>

<div class="flex h-screen bg-gray-100 overflow-hidden">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <div class="bg-white border-b border-gray-200 px-6 py-3 shrink-0 flex justify-between items-center shadow-sm z-20">
            <div class="flex items-center gap-4">
                <a href="<?php echo BASE_URL; ?>/admin/salao" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition"><i class="fas fa-arrow-left"></i></a>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 leading-none">Mesa <?php echo $sessao['num_mesa']; ?></h2>
                    <p class="text-xs text-gray-500 font-bold mt-0.5"><span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded uppercase text-[10px]">Aberto às <?php echo date('H:i', strtotime($sessao['data_abertura'])); ?></span></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right mr-2">
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Total da Mesa</p>
                    <p class="text-2xl font-black text-gray-800">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></p>
                </div>
                
                <button onclick="imprimirConta(<?php echo $sessao['id']; ?>)" class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center shadow-sm transition" title="Imprimir Conta para Cliente">
                    <i class="fas fa-receipt"></i>
                </button>

                <button onclick="abrirModalFechamento()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow flex items-center gap-2 transition">
                    <i class="fas fa-check-circle"></i> Fechar Conta
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            
            <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col z-10">
                <div class="p-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-600 text-sm uppercase"><i class="fas fa-list-ul mr-2"></i> Consumo Agrupado</h3>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scroll">
                    <?php if(empty($participantes)): ?><div class="text-center py-10 text-gray-400 italic">Nenhum pedido ainda.</div><?php endif; ?>

                    <?php foreach($participantes as $p): $itens = $itensPorPessoa[$p['id']] ?? []; ?>
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-gray-700"><?php echo $p['nome']; ?></span>
                                <?php if($p['is_lider']): ?><i class="fas fa-crown text-yellow-500 text-xs" title="Responsável"></i><?php endif; ?>
                                
                                <?php if(isset($p['status_pagamento']) && $p['status_pagamento'] == 'pago'): ?>
                                    <span class="bg-green-100 text-green-600 text-[9px] font-black px-1.5 rounded flex items-center gap-1">
                                        <i class="fas fa-lock"></i> PAGO
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-1">
                                <?php 
                                    $totalPessoa = 0;
                                    foreach($itens as $it) $totalPessoa += $it['total_final'];
                                    
                                    // SE ESTIVER PAGO: Mostra botão de REABRIR (Desbloquear)
                                    if(isset($p['status_pagamento']) && $p['status_pagamento'] == 'pago'): 
                                ?>
                                    <button onclick="reabrirParticipante(<?php echo $p['id']; ?>, '<?php echo $p['nome']; ?>')" 
                                            class="bg-white border border-gray-300 text-gray-400 hover:text-blue-600 hover:border-blue-400 w-6 h-6 rounded-full flex items-center justify-center transition shadow-sm" 
                                            title="Reabrir comanda (Destravar)">
                                        <i class="fas fa-unlock"></i>
                                    </button>

                                <?php elseif($totalPessoa > 0): // SE NÃO PAGO: Botão de PAGAR ?>
                                    <button onclick="abrirModalPagamentoIndividual(<?php echo $p['id']; ?>, '<?php echo $p['nome']; ?>', <?php echo $totalPessoa; ?>)" 
                                            class="bg-white border border-green-200 text-green-600 hover:bg-green-600 hover:text-white w-6 h-6 rounded-full flex items-center justify-center transition shadow-sm" 
                                            title="Pagar conta">
                                        <i class="fas fa-dollar-sign text-xs"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <table class="w-full text-xs">
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach($itens as $item): ?>
                                <td class="px-3 py-2 align-top">
                                    <div class="font-black text-gray-800 uppercase text-xs">
                                        <?php echo $item['produto_nome']; ?>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <?php if($item['status_item'] == 'cancelado'): ?>
                                            <span class="bg-red-100 text-red-600 border border-red-200 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wide">
                                                <i class="fas fa-ban"></i> Cancelado
                                            </span>
                                        <?php elseif($item['status_item'] == 'entregue'): ?>
                                            <span class="bg-green-100 text-green-700 border border-green-200 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wide">
                                                <i class="fas fa-check-double"></i> Entregue
                                            </span>
                                        <?php elseif($item['status_item'] == 'fila'): ?>
                                            <span class="bg-gray-200 text-gray-600 border border-gray-300 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wide animate-pulse">
                                                <i class="fas fa-hourglass-start"></i> Na Fila
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-orange-100 text-orange-600 border border-orange-200 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wide">
                                                <i class="fas fa-fire"></i> Preparando
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-gray-700 align-top pt-3 flex flex-col items-end gap-1">
                                    <?php if($item['status_item'] == 'cancelado'): ?>
                                        <span class="text-red-400 line-through text-xs">R$ <?php echo number_format($item['total_final'], 2, ',', '.'); ?></span>
                                    <?php else: ?>
                                        <span>R$ <?php echo number_format($item['total_final'], 2, ',', '.'); ?></span>
                                        
                                        <?php if($item['status_item'] != 'entregue'): ?>
                                            <button onclick="cancelarItem(<?php echo $item['id']; ?>)" class="text-red-400 hover:text-red-600 text-[10px] bg-red-50 px-2 py-1 rounded border border-red-100 hover:bg-red-100 transition">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>   
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex-1 flex flex-col bg-gray-100 relative">
                
                <div class="p-3 bg-white shadow-sm shrink-0 flex flex-col gap-2">
                    <div class="relative">
                        <input type="text" id="busca_prod" onkeyup="filtrarProdutos()" placeholder="Buscar produto..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                    </div>
                    <div class="flex gap-2 overflow-x-auto pb-1 custom-scroll">
                        <button onclick="filtrarCategoria('all')" class="cat-btn bg-gray-800 text-white px-3 py-1 rounded text-xs font-bold whitespace-nowrap">Todos</button>
                        <?php foreach($categorias as $cat): ?>
                            <button onclick="filtrarCategoria(<?php echo $cat['id']; ?>)" class="cat-btn bg-white border border-gray-300 text-gray-600 px-3 py-1 rounded text-xs font-bold whitespace-nowrap hover:bg-gray-50" data-id="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 custom-scroll pb-24">
                    <div class="grid grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3">
                        <?php foreach($produtos as $prod): 
                             $preco = (float)($prod['preco_promocional'] > 0 ? $prod['preco_promocional'] : $prod['preco_base']);
                             $estoque = intval($prod['estoque_atual']);
                             $controlaEstoque = ($prod['controle_estoque'] ?? 1);
                             $esgotado = ($controlaEstoque == 1 && $estoque <= 0);
                             $prodJS = $prod; $prodJS['preco_real'] = $preco; $prodJS['nome'] = $prod['nome'];
                        ?>
                        <div onclick='verificarProduto(<?php echo htmlspecialchars(json_encode($prodJS), ENT_QUOTES, 'UTF-8'); ?>)' 
                             class="prod-card bg-white p-2 rounded-xl shadow-sm border border-gray-200 hover:border-purple-400 relative overflow-hidden group flex flex-col <?php echo $esgotado ? 'opacity-50 grayscale pointer-events-none' : ''; ?>"
                             data-cat="<?php echo $prod['categoria_id']; ?>" data-nome="<?php echo strtolower($prod['nome']); ?>">
                            <div class="aspect-video w-full bg-gray-100 rounded-lg mb-2 relative overflow-hidden shrink-0">
                                <?php if(!empty($prod['imagem_url'])): ?><img src="<?php echo $prod['imagem_url']; ?>" class="w-full h-full object-cover"><?php else: ?><div class="flex items-center justify-center h-full text-gray-300"><i class="fas fa-image"></i></div><?php endif; ?>
                            </div>
                            <div class="flex flex-col flex-1 justify-between">
                                <h4 class="font-bold text-gray-700 text-xs leading-tight mb-1 line-clamp-2 h-8"><?php echo $prod['nome']; ?></h4>
                                <div class="flex justify-between items-end"><span class="text-sm font-black text-green-600">R$ <?php echo number_format($preco, 2, ',', '.'); ?></span><div class="w-6 h-6 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition"><i class="fas fa-plus text-xs"></i></div></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="barra_lancamento" class="bg-white border-t border-gray-200 p-4 shadow-lg absolute bottom-0 w-full transform translate-y-full transition-transform duration-300 z-30">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold text-gray-800 text-sm"><i class="fas fa-shopping-basket text-purple-600 mr-2"></i> Itens para lançar</h4>
                        <button onclick="limparCarrinhoMesa()" class="text-xs text-red-500 hover:underline">Limpar</button>
                    </div>
                    <div id="lista_itens_temp" class="flex gap-2 overflow-x-auto pb-2 mb-2 custom-scroll max-h-24"></div>
                    <div class="flex gap-3">
                        <select id="select_participante" class="bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5">
                            <?php foreach($participantes as $p): 
                                $bloqueado = (isset($p['status_pagamento']) && $p['status_pagamento'] == 'pago');
                            ?>
                                <option value="<?php echo $p['id']; ?>" 
                                        <?php echo $p['is_lider'] && !$bloqueado ? 'selected' : ''; ?>
                                        <?php echo $bloqueado ? 'disabled' : ''; ?>>
                                    <?php echo $bloqueado ? '🔒 ' : ''; ?>Para: <?php echo $p['nome']; ?> <?php echo $bloqueado ? '(Pago)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button onclick="enviarPedidoMesa()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg flex items-center gap-2 whitespace-nowrap">
                            <span>LANÇAR</span> <span id="total_temp" class="bg-white/20 px-2 py-0.5 rounded text-xs">R$ 0,00</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="modalConfigProduto" class="fixed inset-0 bg-black/70 hidden items-center justify-center p-4 backdrop-blur-sm z-50">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden animate-fade-in flex flex-col max-h-[90vh]">
        <div class="bg-gray-50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 id="modal_config_titulo" class="font-black text-lg text-gray-800">Nome Produto</h3>
            <button onclick="fecharModalConfig()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-5 flex-1 overflow-y-auto custom-scroll">
             <div id="modal_config_conteudo"></div>
             <div class="mt-4">
                 <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Observação</label>
                 <textarea id="modal_config_obs" class="w-full bg-gray-50 border border-gray-200 rounded-lg text-sm p-3 focus:ring-1 focus:ring-purple-500" rows="2" placeholder="Ex: Sem cebola..."></textarea>
             </div>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3 items-center">
             <div class="flex items-center bg-white border border-gray-200 rounded-lg h-10">
                 <button onclick="ajustarQtdConfig(-1)" class="w-8 h-full text-gray-400 hover:text-red-500 font-bold">-</button>
                 <span id="modal_config_qtd" class="w-8 text-center font-bold text-gray-800 text-sm">1</span>
                 <button onclick="ajustarQtdConfig(1)" class="w-8 h-full text-green-600 hover:text-green-700 font-bold">+</button>
             </div>
             <button onclick="confirmarConfigProduto()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold h-10 rounded-lg shadow flex justify-between items-center px-4">
                 <span>Adicionar</span>
                 <span id="modal_config_total">R$ 0,00</span>
             </button>
        </div>
    </div>
</div>

<div id="modal-fechamento" class="fixed inset-0 bg-black/80 z-[60] hidden items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-fade-in">
        
        <div class="bg-gray-50 border-b border-gray-100 p-4 flex justify-between items-center">
            <div>
                <h3 class="font-black text-xl text-gray-800">Fechar Mesa</h3>
                <p class="text-xs text-gray-500 font-bold">Resumo financeiro</p>
            </div>
            <button onclick="document.getElementById('modal-fechamento').classList.add('hidden')" class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-600 transition flex items-center justify-center">✕</button>
        </div>

        <div class="p-6 space-y-4">
            <div class="space-y-2">
                <div class="flex justify-between items-center text-gray-500 text-sm font-bold">
                    <span>Subtotal</span>
                    <span>R$ <span id="lbl-subtotal">0,00</span></span>
                </div>
                
                <div class="flex justify-between items-center bg-red-50 p-2 rounded-lg border border-red-100">
                    <span class="text-red-600 font-bold text-xs uppercase"><i class="fas fa-tag"></i> Desconto</span>
                    <div class="flex items-center gap-1">
                        <span class="text-red-600 font-bold">- R$</span>
                        <input type="number" id="input-desconto" value="" placeholder="0,00" class="w-20 bg-transparent text-right font-bold text-red-600 outline-none border-b border-red-200 focus:border-red-500" step="0.01" onkeyup="calcularTotais()">
                    </div>
                </div>

                <div class="flex justify-between items-end border-t border-dashed border-gray-300 pt-3">
                    <span class="text-gray-800 font-black text-lg">TOTAL A PAGAR</span>
                    <span class="text-3xl font-black text-green-600">R$ <span id="lbl-total-final">0,00</span></span>
                </div>
            </div>

            <div class="bg-blue-50 p-3 rounded-xl border border-blue-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="mudarPessoas(-1)" class="w-8 h-8 bg-white text-blue-600 rounded-lg font-bold shadow-sm hover:bg-blue-100">-</button>
                    <div class="text-center">
                        <span class="block text-xs text-blue-400 font-bold uppercase">Dividir p/</span>
                        <span class="block font-black text-blue-700"><span id="qtd-pessoas">1</span> Pessoas</span>
                    </div>
                    <button onclick="mudarPessoas(1)" class="w-8 h-8 bg-white text-blue-600 rounded-lg font-bold shadow-sm hover:bg-blue-100">+</button>
                </div>
                <div class="text-right">
                    <span class="block text-xs text-blue-400 font-bold uppercase">Por pessoa</span>
                    <span class="block font-black text-xl text-blue-700">R$ <span id="lbl-por-pessoa">0,00</span></span>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Forma de Pagamento</p>
                <div class="grid grid-cols-4 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="pgto" value="Dinheiro" class="peer hidden" onchange="toggleTroco(true)">
                        <div class="h-16 rounded-xl border-2 border-gray-100 flex flex-col items-center justify-center text-gray-400 hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-600 transition">
                            <i class="fas fa-money-bill-wave text-xl mb-1"></i>
                            <span class="text-[10px] font-bold">DINHEIRO</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="pgto" value="Pix" class="peer hidden" onchange="toggleTroco(false)" checked>
                        <div class="h-16 rounded-xl border-2 border-gray-100 flex flex-col items-center justify-center text-gray-400 hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-600 transition">
                            <i class="fab fa-pix text-xl mb-1"></i>
                            <span class="text-[10px] font-bold">PIX</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="pgto" value="Debito" class="peer hidden" onchange="toggleTroco(false)">
                        <div class="h-16 rounded-xl border-2 border-gray-100 flex flex-col items-center justify-center text-gray-400 hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-600 transition">
                            <i class="fas fa-credit-card text-xl mb-1"></i>
                            <span class="text-[10px] font-bold">DÉBITO</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="pgto" value="Credito" class="peer hidden" onchange="toggleTroco(false)">
                        <div class="h-16 rounded-xl border-2 border-gray-100 flex flex-col items-center justify-center text-gray-400 hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-600 transition">
                            <i class="fas fa-credit-card text-xl mb-1"></i>
                            <span class="text-[10px] font-bold">CRÉDITO</span>
                        </div>
                    </label>
                </div>
            </div>

            <div id="area-troco" class="hidden bg-yellow-50 p-3 rounded-xl border border-yellow-200">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-bold text-yellow-800">Valor Recebido:</label>
                    <div class="flex items-center bg-white rounded border border-yellow-300 px-2">
                        <span class="text-yellow-700 font-bold mr-1">R$</span>
                        <input type="number" id="valor-recebido" class="w-24 p-1 outline-none font-bold text-gray-700 text-right" placeholder="0,00" onkeyup="calcularTroco()">
                    </div>
                </div>
                <div class="flex justify-between items-center border-t border-yellow-200 pt-2">
                    <span class="text-sm font-bold text-yellow-800 uppercase">Troco:</span>
                    <span class="text-xl font-black text-yellow-700">R$ <span id="lbl-troco">0,00</span></span>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-gray-100 bg-gray-50 flex gap-3">
            <button onclick="document.getElementById('modal-fechamento').classList.add('hidden')" class="flex-1 py-3 font-bold text-gray-500 hover:bg-gray-200 rounded-xl transition">Cancelar</button>
            <button onclick="confirmarFechamento()" class="flex-[2] bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-check"></i> FINALIZAR MESA
            </button>
        </div>
    </div>
</div>

<div id="modal-pagamento-individual" class="fixed inset-0 bg-black/80 z-[70] hidden items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden animate-fade-in">
        <div class="bg-green-50 p-4 border-b border-green-100">
            <h3 class="font-black text-lg text-green-800">Pagamento Individual</h3>
            <p class="text-xs text-green-600">Baixar conta de <span id="lbl-nome-pagador" class="font-bold uppercase">...</span></p>
        </div>
        <div class="p-6">
            <div class="text-center mb-6">
                <p class="text-gray-500 text-xs font-bold uppercase mb-1">Valor Total da Pessoa</p>
                <p class="text-3xl font-black text-gray-800">R$ <span id="lbl-valor-individual">0,00</span></p>
            </div>
            
            <p class="text-xs font-bold text-gray-400 uppercase mb-2">Forma de Pagamento</p>
            <div class="grid grid-cols-3 gap-2 mb-4">
                <label class="cursor-pointer text-center">
                    <input type="radio" name="pgto_ind" value="Pix" checked class="peer hidden">
                    <div class="border border-gray-200 rounded-lg p-2 peer-checked:bg-green-100 peer-checked:border-green-500 font-bold text-xs text-gray-600">Pix</div>
                </label>
                <label class="cursor-pointer text-center">
                    <input type="radio" name="pgto_ind" value="Dinheiro" class="peer hidden">
                    <div class="border border-gray-200 rounded-lg p-2 peer-checked:bg-green-100 peer-checked:border-green-500 font-bold text-xs text-gray-600">Dinheiro</div>
                </label>
                <label class="cursor-pointer text-center">
                    <input type="radio" name="pgto_ind" value="Cartao" class="peer hidden">
                    <div class="border border-gray-200 rounded-lg p-2 peer-checked:bg-green-100 peer-checked:border-green-500 font-bold text-xs text-gray-600">Cartão</div>
                </label>
            </div>

            <button onclick="confirmarPagamentoIndividual()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow-lg">CONFIRMAR PAGAMENTO</button>
            <button onclick="document.getElementById('modal-pagamento-individual').classList.add('hidden')" class="w-full mt-2 py-2 text-gray-400 text-xs font-bold hover:text-gray-600">Cancelar</button>
        </div>
    </div>
</div>

<script>
    // --- VARIÁVEIS GLOBAIS ---
    let carrinhoMesa = [];
    let produtoEmEdicao = null;
    
    // Dados vindos do PHP
    const sessaoId = <?php echo $sessao['id']; ?>;
    let totalMesa = <?php echo $totalGeral ?? 0; ?>; 
    let pessoas = 1;


    // --- 1. FILTROS ---
    function filtrarProdutos() {
        const term = document.getElementById('busca_prod').value.toLowerCase();
        document.querySelectorAll('.prod-card').forEach(card => {
            card.style.display = card.dataset.nome.includes(term) ? 'flex' : 'none';
        });
    }

    let pagadorAtual = null;

    function abrirModalPagamentoIndividual(id, nome, valor) {
        pagadorAtual = { id: id, valor: valor };
        document.getElementById('lbl-nome-pagador').innerText = nome;
        document.getElementById('lbl-valor-individual').innerText = valor.toFixed(2).replace('.', ',');
        document.getElementById('modal-pagamento-individual').classList.remove('hidden');
        document.getElementById('modal-pagamento-individual').classList.add('flex');
    }
    function cancelarItem(id) {
        if(!confirm("⚠️ Deseja CANCELAR este item da comanda?")) return;
        
        const f = new FormData();
        f.append('item_id', id);
        f.append('status', 'cancelado');
        
        fetch('<?= BASE_URL ?>/admin/salao/mudarStatusItem', { method: 'POST', body: f })
            .then(r => r.json())
            .then(d => {
                if(d.ok) location.reload();
                else alert("Erro: " + (d.erro || 'Desconhecido'));
            });
    }
    function reabrirParticipante(id, nome) {
        if(!confirm(`Deseja reabrir a comanda de ${nome} para novos pedidos?`)) return;
        
        const f = new FormData();
        f.append('participante_id', id);
        
        fetch('<?= BASE_URL ?>/admin/salao/reabrirParticipante', { method: 'POST', body: f })
            .then(r => r.json())
            .then(d => {
                if(d.ok) location.reload();
                else alert(d.erro);
            });
    }

    function confirmarPagamentoIndividual() {
        if(!confirm("Confirmar recebimento deste valor?")) return;
        
        const forma = document.querySelector('input[name="pgto_ind"]:checked').value;
        const btn = event.target;
        btn.innerHTML = 'Processando...'; btn.disabled = true;

        const f = new FormData();
        f.append('participante_id', pagadorAtual.id);
        f.append('valor', pagadorAtual.valor);
        f.append('forma_pagamento', forma);

        fetch('<?= BASE_URL ?>/admin/salao/pagarParticipante', { method: 'POST', body: f })
            .then(r => r.json())
            .then(d => {
                if(d.ok) location.reload();
                else { alert(d.erro); btn.disabled = false; btn.innerHTML = 'CONFIRMAR PAGAMENTO'; }
            });
    }

    function filtrarCategoria(catId) {
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.className = "cat-btn bg-white border border-gray-300 text-gray-600 px-3 py-1 rounded text-xs font-bold whitespace-nowrap hover:bg-gray-50";
            if((catId === 'all' && btn.innerText === 'Todos') || btn.dataset.id == catId) {
                btn.className = "cat-btn bg-gray-800 text-white px-3 py-1 rounded text-xs font-bold whitespace-nowrap";
            }
        });
        document.querySelectorAll('.prod-card').forEach(card => {
            card.style.display = (catId === 'all' || card.dataset.cat == catId) ? 'flex' : 'none';
        });
    }

    // --- 2. PRODUTOS ---
    function verificarProduto(prod) { abrirConfiguracaoProduto(prod); }

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
        fetch('<?= BASE_URL ?>/admin/pedidos/buscaradicionais?id=' + prod.id).then(r => r.json()).then(d => {
            if(d.ok && d.grupos.length > 0) renderizarComplementosModal(d.grupos);
            else container.innerHTML = '<div class="text-center text-gray-400 py-4 italic text-sm">Sem adicionais.<br>Use o campo abaixo para observações.</div>';
        }).catch(e => container.innerHTML = '<div class="text-center text-gray-400 py-4">Erro ao carregar opções.</div>');
    }

    function renderizarComplementosModal(grupos) {
        const container = document.getElementById('modal_config_conteudo');
        let html = '';
        grupos.forEach(g => {
            const min = parseInt(g.minimo); const max = parseInt(g.maximo);
            html += `<div class="mb-4 bg-gray-50 rounded-lg border border-gray-100 group-adicional" data-min="${min}" data-max="${max}" data-id="${g.id}"><div class="bg-gray-100 px-3 py-2 rounded-t-lg border-b border-gray-200 flex justify-between items-center"><div><h4 class="font-bold text-sm text-gray-700 uppercase">${g.nome}</h4><p class="text-[10px] text-gray-500">Escolha até ${max}</p></div>${g.obrigatorio == 1 ? '<span class="bg-gray-800 text-white text-[9px] px-2 py-0.5 rounded font-bold">OBRIGATÓRIO</span>' : ''}</div><div class="p-2 space-y-1">`;
            g.itens.forEach(item => {
                let type = max == 1 ? 'radio' : 'checkbox';
                let name = `add_${g.id}`;
                let precoTxt = parseFloat(item.preco) > 0 ? '+ R$ ' + parseFloat(item.preco).toFixed(2).replace('.',',') : 'Grátis';
                html += `<label class="flex justify-between items-center p-2 bg-white border border-gray-100 rounded cursor-pointer hover:border-blue-300 transition"><div class="flex items-center gap-2"><input type="${type}" name="${name}" value="${item.id}" data-preco="${item.preco}" data-nome="${item.nome}" class="chk-custom" onchange="calcularConfigAdicionais()"><div></div><span class="text-sm font-medium text-gray-700">${item.nome}</span></div><span class="text-xs font-bold text-gray-500">${precoTxt}</span></label>`;
            });
            html += `</div></div>`;
        });
        container.innerHTML = html;
    }

    function calcularConfigAdicionais() {
        let totalAdicionais = 0; produtoEmEdicao.adicionais_temp = [];
        document.querySelectorAll('#modal_config_conteudo input:checked').forEach(input => {
            let p = parseFloat(input.dataset.preco); totalAdicionais += p;
            produtoEmEdicao.adicionais_temp.push({ id: input.value, nome: input.dataset.nome, preco: p });
        });
        atualizarTotalConfig(totalAdicionais);
    }
    function atualizarTotalConfig(addTotal = 0) {
        if(!produtoEmEdicao) return;
        if(addTotal === 0 && produtoEmEdicao.adicionais_temp.length > 0) produtoEmEdicao.adicionais_temp.forEach(a => addTotal += a.preco);
        let total = (parseFloat(produtoEmEdicao.preco_real) + addTotal) * produtoEmEdicao.qtd_temp;
        document.getElementById('modal_config_total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
    function ajustarQtdConfig(d) {
        let n = produtoEmEdicao.qtd_temp + d; if(n < 1) n = 1; produtoEmEdicao.qtd_temp = n;
        document.getElementById('modal_config_qtd').innerText = n; calcularConfigAdicionais();
    }
    function fecharModalConfig() { document.getElementById('modalConfigProduto').classList.add('hidden'); document.getElementById('modalConfigProduto').classList.remove('flex'); produtoEmEdicao = null; }
    function confirmarConfigProduto() {
        let erro = false;
        document.querySelectorAll('.group-adicional').forEach(g => { if(g.querySelectorAll('input:checked').length < parseInt(g.dataset.min)) { alert('Selecione os obrigatórios.'); erro = true; }});
        if(erro) return;
        calcularConfigAdicionais();
        let totalAdds = 0; produtoEmEdicao.adicionais_temp.forEach(a => totalAdds += a.preco);
        carrinhoMesa.push({ id: produtoEmEdicao.id, nome: produtoEmEdicao.nome, preco: parseFloat(produtoEmEdicao.preco_real) + totalAdds, qtd: produtoEmEdicao.qtd_temp, observacao: document.getElementById('modal_config_obs').value, adicionais: produtoEmEdicao.adicionais_temp });
        renderizarCarrinhoMesa(); fecharModalConfig();
    }

    // --- 3. CARRINHO ---
    function renderizarCarrinhoMesa() {
        const div = document.getElementById('lista_itens_temp'); const barra = document.getElementById('barra_lancamento'); const totalEl = document.getElementById('total_temp');
        div.innerHTML = ''; let total = 0;
        if(carrinhoMesa.length === 0) { barra.classList.add('translate-y-full'); return; }
        barra.classList.remove('translate-y-full');
        carrinhoMesa.forEach((item, idx) => {
            total += item.preco * item.qtd;
            div.innerHTML += `<div class="bg-gray-100 border border-gray-300 rounded px-3 py-1.5 shrink-0 flex items-center gap-2 shadow-sm min-w-[100px]"><div class="text-xs font-bold text-gray-700 flex flex-col"><span>${item.qtd}x ${item.nome}</span><span class="text-[9px] text-gray-500">R$ ${item.preco.toFixed(2)}</span></div><button onclick="removerItemMesa(${idx})" class="text-red-400 hover:text-red-600 font-bold ml-auto px-1">✕</button></div>`;
        });
        totalEl.innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
    function removerItemMesa(i) { carrinhoMesa.splice(i, 1); renderizarCarrinhoMesa(); }
    function limparCarrinhoMesa() { carrinhoMesa = []; renderizarCarrinhoMesa(); }
    function enviarPedidoMesa() {
        if(carrinhoMesa.length === 0) return;
        const btn = document.querySelector('button[onclick="enviarPedidoMesa()"]'); const txt = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        const f = new FormData(); f.append('sessao_id', sessaoId); f.append('participante_id', document.getElementById('select_participante').value); f.append('itens', JSON.stringify(carrinhoMesa));
        fetch('<?= BASE_URL ?>/admin/salao/adicionarPedidoMesa', { method: 'POST', body: f }).then(r=>r.json()).then(d=>{ if(d.ok) location.reload(); else { alert(d.erro); btn.disabled=false; btn.innerHTML=txt; } }).catch(()=>{ alert('Erro'); btn.disabled=false; btn.innerHTML=txt; });
    }

    // --- 4. IMPRESSÃO ---
    // Função Genérica de Impressão via Iframe (SEM forçar print no JS)
    function imprimirViaIframe(url, idFrame) {
        let oldFrame = document.getElementById(idFrame);
        if(oldFrame) oldFrame.remove();

        const iframe = document.createElement('iframe');
        iframe.id = idFrame;
        iframe.style.position = 'fixed';
        iframe.style.left = '-9999px'; // Esconde visualmente
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        
        document.body.appendChild(iframe);
        
        // Apenas carrega a URL.
        // O comando de impressão virá do próprio arquivo PHP carregado (window.print() no onload do HTML gerado)
        iframe.src = url;
    }

    // Funções Específicas
    function imprimirConferencia(sid) {
        // Usa um ID único para o frame da conferência
        imprimirViaIframe('<?= BASE_URL ?>/admin/salao/imprimirConferencia?id=' + sid, 'frame_print_conferencia');
    }

    function imprimirConta(sid) {
        // Usa um ID único para o frame da conta
        imprimirViaIframe('<?= BASE_URL ?>/admin/salao/imprimirConta?id=' + sid, 'frame_print_conta');
    }
    // --- 5. FECHAMENTO ---
    function abrirModalFechamento() {
        document.getElementById('modal-fechamento').classList.remove('hidden');
        document.getElementById('modal-fechamento').classList.add('flex');
        document.getElementById('lbl-subtotal').innerText = totalMesa.toFixed(2).replace('.', ',');
        calcularTotais();
    }
    function mudarPessoas(d) { pessoas += d; if(pessoas < 1) pessoas = 1; document.getElementById('qtd-pessoas').innerText = pessoas; calcularTotais(); }
    function toggleTroco(show) { 
        const div = document.getElementById('area-troco'); 
        if(show) { div.classList.remove('hidden'); setTimeout(() => document.getElementById('valor-recebido').focus(), 100); } else { div.classList.add('hidden'); }
    }
    function calcularTotais() {
        const desc = parseFloat(document.getElementById('input-desconto').value.replace(',', '.')) || 0;
        const final = Math.max(0, totalMesa - desc);
        document.getElementById('lbl-total-final').innerText = final.toFixed(2).replace('.', ',');
        document.getElementById('lbl-por-pessoa').innerText = (final / pessoas).toFixed(2).replace('.', ',');
        calcularTroco();
    }
    function calcularTroco() {
        const total = parseFloat(document.getElementById('lbl-total-final').innerText.replace('.', '').replace(',', '.'));
        const recebido = parseFloat(document.getElementById('valor-recebido').value.replace(',', '.')) || 0;
        document.getElementById('lbl-troco').innerText = Math.max(0, recebido - total).toFixed(2).replace('.', ',');
    }
    function confirmarFechamento() {
        if(!confirm("Encerrar mesa?")) return;
        const btn = event.target; btn.innerHTML = 'Processando...'; btn.disabled = true;
        
        const desc = parseFloat(document.getElementById('input-desconto').value.replace(',', '.')) || 0;
        const forma = document.querySelector('input[name="pgto"]:checked').value;
        const total = parseFloat(document.getElementById('lbl-total-final').innerText.replace('.', '').replace(',', '.'));
        const recebido = parseFloat(document.getElementById('valor-recebido').value.replace(',', '.')) || 0;
        const troco = parseFloat(document.getElementById('lbl-troco').innerText.replace('.', '').replace(',', '.'));

        const f = new FormData();
        f.append('sessao_id', sessaoId);
        f.append('forma_pagamento', forma);
        f.append('desconto', desc);
        f.append('valor_pago', (forma === 'Dinheiro' && recebido > 0) ? recebido : total);
        f.append('troco', troco);

        fetch('<?= BASE_URL ?>/admin/salao/encerrarMesa', { method: 'POST', body: f })
            .then(r => r.json()).then(d => {
                if(d.ok) window.location.href = '<?= BASE_URL ?>/admin/salao';
                else { alert("Erro: " + d.erro); btn.disabled = false; btn.innerHTML = 'FINALIZAR MESA'; }
            }).catch(() => { alert("Erro conexão"); btn.disabled = false; btn.innerHTML = 'FINALIZAR MESA'; });
    }
</script>