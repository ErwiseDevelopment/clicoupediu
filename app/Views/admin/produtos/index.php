<?php 
$titulo = "Gerenciar Produtos";
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex flex-col md:flex-row h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Produtos do Cardápio</h1>
                <p class="text-sm text-slate-500">Gestão visual de itens, preços e disponibilidade.</p>
            </div>
            <button onclick="novoProduto()" class="w-full md:w-auto bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Novo Produto
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            <div class="xl:col-span-1 order-2 xl:order-1">
                <form action="<?php echo BASE_URL; ?>/admin/produtos/salvar" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" id="prod_id">
                    <input type="hidden" name="imagem_atual" id="imagem_atual">

                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="font-black text-slate-800 flex items-center gap-2" id="titulo_form">
                                <i class="fas fa-pen-to-square text-indigo-600"></i> Cadastro
                            </h3>
                            <div class="flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="visivel_online" id="prod_visivel" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-2 text-xs font-bold text-slate-500">No Site</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-6 text-center">
                            <input type="file" name="imagem" id="upload_img" class="hidden" accept="image/*" onchange="iniciarCorte(this)">
                            <div onclick="document.getElementById('upload_img').click()" class="cursor-pointer group relative inline-block">
                                <img id="preview_small" src="https://via.placeholder.com/150?text=Foto" 
                                     class="w-32 h-32 md:w-40 md:h-40 rounded-3xl object-cover border-4 border-slate-50 shadow-inner group-hover:border-indigo-100 transition-all bg-slate-50">
                                <div class="absolute inset-0 bg-black/40 rounded-3xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                    <i class="fas fa-camera text-white text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Nome do Item</label>
                                <input type="text" name="nome" id="prod_nome" required class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Ex: X-Bacon">
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Categoria</label>
                                <select name="categoria_id" id="prod_categoria" required class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Selecione...</option>
                                    <?php foreach($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4"> 
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Preço (R$)</label>
                                    <input type="text" name="preco" id="prod_preco" required onkeyup="mascaraMoeda(this)" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1 block">Promoção (R$)</label>
                                    <input type="text" name="preco_promocional" id="prod_promocional" onkeyup="mascaraMoeda(this)" placeholder="Opcional" class="w-full bg-emerald-50 border-none rounded-xl p-3 text-sm font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                            
                            <div class="pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="controle_estoque" id="prod_controle_estoque" value="1" class="sr-only peer" checked onchange="toggleEstoque()">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-2 text-xs font-bold text-slate-500">Controlar Estoque?</span>
                                </label>
                            </div>

                            <div id="div_estoque">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Qtd em Estoque</label>
                                <input type="number" name="estoque" id="prod_estoque" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Descrição</label>
                                <textarea name="descricao" id="prod_desc" rows="2" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Ingredientes, detalhes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                        <h3 class="font-black text-slate-800 mb-2">Complementos</h3>
                        <p class="text-xs text-slate-400 mb-4">Vincule adicionais (ex: bordas, bebidas) a este produto.</p>
                        
                        <div class="flex items-center justify-between bg-indigo-50 p-4 rounded-2xl border border-indigo-100 cursor-pointer hover:bg-indigo-100 transition" onclick="abrirModalComplementos()">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <span class="block font-bold text-indigo-900 text-sm">Selecionar Grupos</span>
                                    <span class="text-xs text-indigo-500" id="contador_complementos">Nenhum grupo vinculado</span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-indigo-400"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                        <h3 class="font-black text-slate-800 mb-1">Disponibilidade</h3>
                        <p class="text-[10px] text-slate-400 mb-4">Visível apenas nos períodos abaixo.</p>
                        
                        <button type="button" onclick="abrirModalDisponibilidade()" class="w-full border border-rose-500 text-rose-500 text-center py-2 rounded-xl font-bold text-xs hover:bg-rose-50 transition mb-4">
                            + Adicionar Horário
                        </button>

                        <div id="lista_periodos_visual" class="space-y-2"></div>
                        <div id="container_periodos_inputs" class="hidden"></div>
                    </div>

                    <div class="mt-8 space-y-3 pb-20 md:pb-0">
                        <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-black transition">Salvar Produto</button>
                        <button type="button" id="btn_cancelar" onclick="limparForm()" class="hidden w-full bg-slate-100 text-slate-500 py-3 rounded-2xl font-bold hover:bg-slate-200 transition">Cancelar</button>
                    </div>

                    <div id="modal_complementos" class="fixed inset-0 z-[70] hidden">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="fecharModalComplementos()"></div>
                        <div class="fixed inset-0 flex items-center justify-center p-4">
                            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg transform transition-all scale-100 flex flex-col max-h-[80vh]">
                                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-800">Vincular Complementos</h3>
                                        <p class="text-xs text-slate-400">Escolha quais grupos aparecem neste produto.</p>
                                    </div>
                                    <button type="button" onclick="fecharModalComplementos()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                                </div>
                                
                                <div class="p-6 overflow-y-auto custom-scroll">
                                    <?php if(empty($gruposAdicionais)): ?>
                                        <div class="text-center py-10">
                                            <i class="fas fa-folder-open text-4xl text-slate-200 mb-3"></i>
                                            <p class="text-slate-400 font-bold text-sm">Nenhum grupo cadastrado.</p>
                                            <a href="<?php echo BASE_URL; ?>/admin/adicionais" target="_blank" class="text-indigo-600 text-xs font-bold underline mt-2 block">Cadastrar Grupos</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach($gruposAdicionais as $g): ?>
                                                <div class="flex items-start justify-between p-4 border border-slate-100 rounded-2xl bg-slate-50 hover:border-indigo-200 hover:bg-white transition-all cursor-pointer shadow-sm" onclick="toggleCheckbox('grupo_<?php echo $g['id']; ?>')">
                                                    
                                                    <div class="flex-1 pr-4">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="block text-sm font-bold text-slate-800"><?php echo $g['nome']; ?></span>
                                                            <?php if($g['obrigatorio']): ?>
                                                                <span class="text-[9px] bg-rose-100 text-rose-600 px-1.5 py-0.5 rounded font-bold uppercase">Obrigatório</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <p class="text-[11px] text-slate-500 mb-2 leading-tight">
                                                            <?php echo $g['descricao'] ?: 'Sem descrição definida.'; ?>
                                                        </p>

                                                        <div class="bg-white border border-slate-100 rounded-lg p-2">
                                                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Opções deste grupo:</p>
                                                            <p class="text-xs text-indigo-600 font-medium leading-snug">
                                                                <?php 
                                                                    if (!empty($g['lista_itens'])) {
                                                                        echo str_replace(',', ' <span class="text-slate-300">•</span> ', $g['lista_itens']);
                                                                    } else {
                                                                        echo '<span class="text-slate-400 italic">Nenhuma opção cadastrada neste grupo.</span>';
                                                                    }
                                                                ?>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <label class="relative inline-flex items-center cursor-pointer pointer-events-none mt-1">
                                                        <input type="checkbox" name="grupos_complementos[]" value="<?php echo $g['id']; ?>" class="sr-only peer chk-complemento" id="grupo_<?php echo $g['id']; ?>" onchange="atualizarContadorComplementos()">
                                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-3xl">
                                    <button type="button" onclick="fecharModalComplementos()" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow hover:bg-indigo-700 transition">Confirmar Seleção</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="xl:col-span-2 order-1 xl:order-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3 gap-6">
                    <?php if(empty($produtos)): ?>
                        <div class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-bold">Nenhum produto cadastrado.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($produtos as $prod): 
                            $imgUrl = !empty($prod['imagem_url']) ? $prod['imagem_url'] : '';
                            if ($imgUrl && strpos($imgUrl, 'http') === false) { $imgUrl = BASE_URL . '/' . $imgUrl; }
                            
                            $isInterno = isset($prod['visivel_online']) && $prod['visivel_online'] == 0;
                            $temPromo = isset($prod['preco_promocional']) && $prod['preco_promocional'] > 0;
                            $controlaEstoque = isset($prod['controle_estoque']) ? $prod['controle_estoque'] : 1;
                        ?>
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden group hover:shadow-xl transition-all duration-300 relative">
                            <?php if($isInterno): ?>
                                <div class="absolute top-0 left-0 z-10 bg-slate-800 text-white text-[10px] font-bold px-3 py-1 rounded-br-xl shadow-md"><i class="fas fa-eye-slash mr-1"></i> INTERNO</div>
                            <?php endif; ?>

                            <div class="relative h-48 overflow-hidden bg-slate-100">
                                <img src="<?php echo $imgUrl ?: 'https://via.placeholder.com/400x300?text=Sem+Foto'; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 cursor-pointer" onclick='visualizarImagem("<?php echo $imgUrl; ?>")'>
                                <div class="absolute bottom-4 left-4"><span class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-lg shadow-lg uppercase tracking-widest"><?php echo $prod['nome_categoria'] ?? 'Geral'; ?></span></div>
                            </div>

                            <div class="p-6">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-black text-slate-800 text-lg leading-tight truncate pr-2"><?php echo $prod['nome']; ?></h3>
                                    <div class="text-right">
                                        <?php if($temPromo): ?>
                                            <span class="block text-[10px] text-slate-400 line-through">R$ <?php echo number_format($prod['preco_base'], 2, ',', '.'); ?></span>
                                            <span class="font-black text-emerald-500">R$ <?php echo number_format($prod['preco_promocional'], 2, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="font-black text-indigo-600">R$ <?php echo number_format($prod['preco_base'], 2, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 line-clamp-2 mb-6 h-8"><?php echo $prod['descricao']; ?></p>

                                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black text-slate-400 uppercase">Estoque</span>
                                        <?php if($controlaEstoque): ?>
                                            <span class="text-sm font-bold <?php echo ($prod['estoque_atual'] <= 5) ? 'text-rose-500' : 'text-slate-700'; ?>"><?php echo (int)$prod['estoque_atual']; ?> un</span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-emerald-500"><i class="fas fa-infinity"></i> Livre</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick='editar(<?php echo json_encode($prod); ?>)' class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-center transition"><i class="fas fa-edit"></i></button>
                                        <a href="<?php echo BASE_URL; ?>/admin/produtos/excluir?id=<?php echo $prod['id']; ?>" onclick="return confirm('Apagar item?')" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 flex items-center justify-center transition"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="modal_disp" class="fixed inset-0 z-[60] hidden">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="fecharModalDisponibilidade()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md transform transition-all scale-100 p-8">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-xl font-bold text-slate-800">Adicionar disponibilidade</h3>
                <button onclick="fecharModalDisponibilidade()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <p class="text-sm text-slate-500 mb-3">Selecione os dias da semana</p>
            <div class="flex justify-between gap-1 mb-6">
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="0">DOM</button>
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="1">SEG</button>
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="2">TER</button>
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="3">QUA</button>
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="4">QUI</button>
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="5">SEX</button>
                <button type="button" class="btn-dia w-11 h-11 rounded-xl text-xs font-bold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all focus:outline-none" data-dia="6">SÁB</button>
            </div>
            <p class="text-sm text-slate-500 mb-3">Selecione o horário</p>
            <div class="flex gap-4 mb-6">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1">De</label>
                    <input type="time" id="modal_hora_inicio" value="00:00" class="w-full rounded-xl border-slate-200 text-sm font-medium text-slate-700 pl-3 pr-8 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Até</label>
                    <input type="time" id="modal_hora_fim" value="23:59" class="w-full rounded-xl border-slate-200 text-sm font-medium text-slate-700 pl-3 pr-8 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="fecharModalDisponibilidade()" class="px-6 py-3 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50 transition">Cancelar</button>
                <button type="button" onclick="salvarPeriodoModal()" class="px-6 py-3 rounded-xl bg-slate-200 text-slate-400 font-bold hover:bg-indigo-600 hover:text-white transition" id="btn_salvar_modal">Salvar</button>
            </div>
        </div>
    </div>
</div>

<div id="modal_cropper" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl overflow-hidden w-full max-w-2xl shadow-2xl">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-black text-slate-800" id="modal_titulo">Recortar Imagem</h3>
                <button onclick="fecharModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <div class="bg-slate-50 rounded-2xl overflow-hidden h-96 flex items-center justify-center">
                    <img id="image_to_crop" src="" class="max-w-full">
                </div>
            </div>
            <div class="p-6 bg-slate-50 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <button onclick="fecharModal()" class="w-full sm:w-auto px-8 py-3 rounded-2xl font-bold text-slate-500 hover:bg-slate-200 transition">Cancelar</button>
                <button id="btn_recortar" class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition">Recortar e Usar</button>
                <button id="btn_fechar_visualizacao" onclick="fecharModal()" class="hidden w-full sm:w-auto px-8 py-3 rounded-2xl bg-indigo-600 text-white font-bold transition">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- FUNÇÕES DE COMPLEMENTOS (MODAL) ---
    function abrirModalComplementos() {
        document.getElementById('modal_complementos').classList.remove('hidden');
    }
    
    function fecharModalComplementos() {
        document.getElementById('modal_complementos').classList.add('hidden');
        atualizarContadorComplementos();
    }

    function toggleCheckbox(id) {
        const chk = document.getElementById(id);
        chk.checked = !chk.checked;
        atualizarContadorComplementos();
    }

    function atualizarContadorComplementos() {
        const total = document.querySelectorAll('.chk-complemento:checked').length;
        const texto = total === 0 ? "Nenhum grupo vinculado" : total + " grupo(s) vinculado(s)";
        document.getElementById('contador_complementos').innerText = texto;
        
        if (total > 0) {
            document.getElementById('contador_complementos').classList.remove('text-indigo-500');
            document.getElementById('contador_complementos').classList.add('text-green-600', 'font-bold');
        } else {
            document.getElementById('contador_complementos').classList.add('text-indigo-500');
            document.getElementById('contador_complementos').classList.remove('text-green-600', 'font-bold');
        }
    }

    // --- JS: CONTROLE DE ESTOQUE ---
    function toggleEstoque() {
        const chk = document.getElementById('prod_controle_estoque');
        const div = document.getElementById('div_estoque');
        chk.checked ? div.classList.remove('hidden') : div.classList.add('hidden');
    }

    // --- JS: MODAL DISPONIBILIDADE ---
    const diasNomes = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
    let diasSelecionadosModal = [];

    document.querySelectorAll('.btn-dia').forEach(btn => {
        btn.addEventListener('click', function() {
            const dia = parseInt(this.getAttribute('data-dia'));
            if (diasSelecionadosModal.includes(dia)) {
                diasSelecionadosModal = diasSelecionadosModal.filter(d => d !== dia);
                this.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600');
                this.classList.add('border-slate-200', 'text-slate-500');
            } else {
                diasSelecionadosModal.push(dia);
                this.classList.remove('border-slate-200', 'text-slate-500');
                this.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600');
            }
            atualizarBtnSalvar();
        });
    });

    function atualizarBtnSalvar() {
        const btn = document.getElementById('btn_salvar_modal');
        if (diasSelecionadosModal.length > 0) {
            btn.classList.remove('bg-slate-200', 'text-slate-400');
            btn.classList.add('bg-indigo-600', 'text-white');
        } else {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('bg-slate-200', 'text-slate-400');
        }
    }

    function abrirModalDisponibilidade() {
        diasSelecionadosModal = [];
        document.querySelectorAll('.btn-dia').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600');
            btn.classList.add('border-slate-200', 'text-slate-500');
        });
        document.getElementById('modal_hora_inicio').value = "00:00";
        document.getElementById('modal_hora_fim').value = "23:59";
        atualizarBtnSalvar();
        document.getElementById('modal_disp').classList.remove('hidden');
    }

    function fecharModalDisponibilidade() {
        document.getElementById('modal_disp').classList.add('hidden');
    }

    function salvarPeriodoModal() {
        if (diasSelecionadosModal.length === 0) return;
        const inicio = document.getElementById('modal_hora_inicio').value;
        const fim = document.getElementById('modal_hora_fim').value;
        const containerInputs = document.getElementById('container_periodos_inputs');
        const listaVisual = document.getElementById('lista_periodos_visual');

        diasSelecionadosModal.forEach(dia => {
            const idUnico = 'p_' + Math.random().toString(36).substr(2, 9);
            const htmlVisual = `
                <div id="vis_${idUnico}" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 animate-fade-in text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-black bg-white px-2 py-1 rounded border border-slate-200 text-indigo-600">${diasNomes[dia]}</span>
                        <span class="text-slate-600 font-medium">${inicio} - ${fim}</span>
                    </div>
                    <button type="button" onclick="removerPeriodo('${idUnico}')" class="text-slate-400 hover:text-rose-500"><i class="fas fa-trash-alt"></i></button>
                </div>`;
            listaVisual.insertAdjacentHTML('beforeend', htmlVisual);

            const htmlInputs = `
                <div id="inp_${idUnico}">
                    <input type="hidden" name="dia_semana[]" value="${dia}">
                    <input type="hidden" name="horario_inicio[]" value="${inicio}">
                    <input type="hidden" name="horario_fim[]" value="${fim}">
                </div>`;
            containerInputs.insertAdjacentHTML('beforeend', htmlInputs);
        });
        fecharModalDisponibilidade();
    }

    function removerPeriodo(id) {
        document.getElementById('vis_' + id).remove();
        document.getElementById('inp_' + id).remove();
    }

    // --- JS: CROPPING & GERAL ---
    let cropper; 
    const modal = document.getElementById('modal_cropper');
    const imageToCrop = document.getElementById('image_to_crop');
    
    function iniciarCorte(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('modal_titulo').innerText = 'Recortar Imagem';
                document.getElementById('btn_recortar').classList.remove('hidden');
                document.getElementById('btn_fechar_visualizacao').classList.add('hidden');
                imageToCrop.src = e.target.result;
                modal.classList.remove('hidden');
                if (cropper) { cropper.destroy(); }
                cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1, autoCropArea: 1 });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('btn_recortar').addEventListener('click', function() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 });
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            document.getElementById('preview_small').src = url;
            const file = new File([blob], "imagem_recortada.jpg", { type: "image/jpeg" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('upload_img').files = dataTransfer.files;
            fecharModal();
        }, 'image/jpeg', 0.9);
    });

    function fecharModal() {
        modal.classList.add('hidden');
        if (cropper) { cropper.destroy(); cropper = null; }
        imageToCrop.src = "";
    }

    function mascaraMoeda(i) {
        var v = i.value.replace(/\D/g,'');
        v = (v/100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
        v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
        i.value = v;
    }

    function editar(prod) {
        document.getElementById('prod_id').value = prod.id;
        document.getElementById('prod_nome').value = prod.nome;
        document.getElementById('prod_categoria').value = prod.categoria_id;
        document.getElementById('prod_preco').value = parseFloat(prod.preco_base).toFixed(2).replace('.', ',');
        
        if(prod.preco_promocional && prod.preco_promocional > 0) {
            document.getElementById('prod_promocional').value = parseFloat(prod.preco_promocional).toFixed(2).replace('.', ',');
        } else {
            document.getElementById('prod_promocional').value = '';
        }

        document.getElementById('prod_estoque').value = prod.estoque_atual ? parseInt(prod.estoque_atual) : 0;
        document.getElementById('prod_desc').value = prod.descricao;
        document.getElementById('imagem_atual').value = prod.imagem_url;

        document.getElementById('prod_visivel').checked = (prod.visivel_online != 0);
        document.getElementById('prod_controle_estoque').checked = (prod.controle_estoque != 0);
        toggleEstoque();

        let imgUrl = prod.imagem_url;
        if(imgUrl && !imgUrl.startsWith('http')) { imgUrl = '<?php echo BASE_URL; ?>/' + imgUrl; }
        document.getElementById('preview_small').src = imgUrl || 'https://via.placeholder.com/150?text=Foto';

        // Disponibilidade
        document.getElementById('container_periodos_inputs').innerHTML = '';
        document.getElementById('lista_periodos_visual').innerHTML = '';
        if(prod.disponibilidade && prod.disponibilidade.length > 0) {
            prod.disponibilidade.forEach(p => {
                const dia = p.dia_semana;
                const inicio = p.horario_inicio.substring(0, 5);
                const fim = p.horario_fim.substring(0, 5);
                const idUnico = 'p_' + Math.random().toString(36).substr(2, 9);
                const listaVisual = document.getElementById('lista_periodos_visual');
                const containerInputs = document.getElementById('container_periodos_inputs');

                listaVisual.insertAdjacentHTML('beforeend', `
                    <div id="vis_${idUnico}" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 animate-fade-in text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-black bg-white px-2 py-1 rounded border border-slate-200 text-indigo-600">${diasNomes[dia]}</span>
                            <span class="text-slate-600 font-medium">${inicio} - ${fim}</span>
                        </div>
                        <button type="button" onclick="removerPeriodo('${idUnico}')" class="text-slate-400 hover:text-rose-500"><i class="fas fa-trash-alt"></i></button>
                    </div>`);
                containerInputs.insertAdjacentHTML('beforeend', `
                    <div id="inp_${idUnico}">
                        <input type="hidden" name="dia_semana[]" value="${dia}">
                        <input type="hidden" name="horario_inicio[]" value="${inicio}">
                        <input type="hidden" name="horario_fim[]" value="${fim}">
                    </div>`);
            });
        }

        // Complementos (Preencher Modal)
        document.querySelectorAll('.chk-complemento').forEach(chk => chk.checked = false);
        if(prod.complementos && prod.complementos.length > 0) {
            prod.complementos.forEach(grupoId => {
                const el = document.getElementById('grupo_' + grupoId);
                if(el) el.checked = true;
            });
        }
        atualizarContadorComplementos();

        document.getElementById('titulo_form').innerHTML = '<i class="fas fa-pen-to-square text-indigo-600"></i> Editar Item';
        document.getElementById('btn_cancelar').classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function novoProduto() {
        limparForm();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limparForm() {
        document.getElementById('prod_id').value = '';
        document.getElementById('prod_nome').value = '';
        document.getElementById('prod_preco').value = '';
        document.getElementById('prod_promocional').value = '';
        document.getElementById('prod_estoque').value = '';
        document.getElementById('prod_desc').value = '';
        document.getElementById('prod_visivel').checked = true; 
        document.getElementById('prod_controle_estoque').checked = true;
        toggleEstoque();

        document.getElementById('preview_small').src = 'https://via.placeholder.com/150?text=Foto';
        document.getElementById('container_periodos_inputs').innerHTML = '';
        document.getElementById('lista_periodos_visual').innerHTML = '';
        document.querySelectorAll('.chk-complemento').forEach(chk => chk.checked = false);
        atualizarContadorComplementos();

        document.getElementById('btn_cancelar').classList.add('hidden');
        document.getElementById('titulo_form').innerHTML = '<i class="fas fa-pen-to-square text-indigo-600"></i> Cadastro';
    }

    function visualizarImagem(url) {
        if(!url) return;
        document.getElementById('modal_titulo').innerText = 'Visualizar';
        document.getElementById('btn_recortar').classList.add('hidden');
        document.getElementById('btn_fechar_visualizacao').classList.remove('hidden');
        imageToCrop.src = url;
        modal.classList.remove('hidden');
        if (cropper) { cropper.destroy(); }
    }
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>