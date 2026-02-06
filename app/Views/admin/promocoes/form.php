<?php $titulo = "Gerenciar Promoção"; require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen bg-gray-50 font-sans">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8 overflow-y-auto">
        <h1 class="text-2xl font-black text-gray-800 mb-6 flex items-center gap-2">
            <i class="fas fa-tags text-blue-600"></i> 
            <?php echo isset($combo['id']) ? 'Editar Promoção' : 'Nova Promoção'; ?>
        </h1>

        <form action="<?php echo BASE_URL; ?>/admin/promocoes/salvar" method="POST" enctype="multipart/form-data" onsubmit="prepararEnvio(event)" class="h-full pb-20">
            <input type="hidden" name="id" value="<?php echo $combo['id'] ?? ''; ?>">
            <input type="hidden" name="itens_json" id="itens_json">
            <input type="hidden" name="imagem_atual" value="<?php echo $combo['imagem_url'] ?? ''; ?>">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-full">
                
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <div class="flex justify-between items-start mb-4 border-b pb-2">
                            <h3 class="font-bold text-gray-700">Dados do Combo</h3>
                            
                            <div class="flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="visivel_online" value="1" class="sr-only peer" <?php echo (!isset($combo['visivel_online']) || $combo['visivel_online'] == 1) ? 'checked' : ''; ?>>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-2 text-xs font-bold text-gray-500">Exibir no Site</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4 mb-4">
                            <div class="relative w-24 h-24 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden shrink-0 group">
                                <?php 
                                    $displayImg = '';
                                    if (!empty($combo['imagem_url'])) {
                                        $displayImg = (strpos($combo['imagem_url'], 'http') === 0) 
                                                    ? $combo['imagem_url'] 
                                                    : BASE_URL . '/' . $combo['imagem_url'];
                                    }
                                ?>
                                <img id="img_preview" src="<?php echo $displayImg; ?>" class="w-full h-full object-cover <?php echo empty($displayImg) ? 'hidden' : ''; ?>">
                                
                                <div id="img_placeholder" class="text-gray-400 text-center <?php echo !empty($displayImg) ? 'hidden' : ''; ?>">
                                    <i class="fas fa-camera text-2xl"></i>
                                    <span class="text-[10px] block mt-1">Foto</span>
                                </div>
                                <input type="file" name="imagem" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewImagem(this)">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nome do Combo</label>
                                <input type="text" name="nome" value="<?php echo $combo['nome'] ?? ''; ?>" class="w-full border rounded-xl p-3 font-bold text-gray-700 outline-none focus:border-blue-500" placeholder="Ex: Combo Família" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Preço de Venda (R$)</label>
                                <input type="text" id="input_preco_venda" name="preco" value="<?php echo isset($combo['preco_base']) ? number_format($combo['preco_base'], 2, ',', '.') : ''; ?>" class="w-full border rounded-xl p-3 font-black text-gray-700 text-lg outline-none focus:border-blue-500" placeholder="0,00" onkeyup="mascaraMoeda(this); calcularEconomia();" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-green-600 uppercase mb-1">Preço Promocional (Opcional)</label>
                                <input type="text" name="preco_promocional" value="<?php echo (isset($combo['preco_promocional']) && $combo['preco_promocional'] > 0) ? number_format($combo['preco_promocional'], 2, ',', '.') : ''; ?>" class="w-full border rounded-xl p-3 font-black text-green-600 text-lg outline-none focus:border-green-500" placeholder="0,00" onkeyup="mascaraMoeda(this)">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Categoria</label>
                                <select name="categoria_id" class="w-full border rounded-xl p-3 text-sm bg-white outline-none" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($combo['categoria_id']) && $combo['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo $cat['nome']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-center pt-6">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="controle_estoque" value="1" class="sr-only peer" <?php echo (!isset($combo['controle_estoque']) || $combo['controle_estoque'] == 1) ? 'checked' : ''; ?>>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-2 text-xs font-bold text-gray-500">Controlar Estoque</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descrição</label>
                            <textarea name="descricao" rows="2" class="w-full border rounded-xl p-3 text-sm outline-none resize-none" placeholder="Descrição do cardápio..."><?php echo $combo['descricao'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-gray-700 mb-1 text-sm">Disponibilidade</h3>
                        <p class="text-[10px] text-gray-400 mb-4">Visível apenas nos períodos abaixo.</p>
                        
                        <button type="button" onclick="abrirModalDisponibilidade()" class="w-full border border-blue-500 text-blue-500 text-center py-2 rounded-xl font-bold text-xs hover:bg-blue-50 transition mb-4">
                            + Adicionar período
                        </button>

                        <div id="lista_periodos_visual" class="space-y-2"></div>
                        <div id="container_periodos_inputs" class="hidden"></div>
                    </div>

                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100">
                        <h4 class="font-bold text-blue-800 text-sm mb-3 border-b border-blue-200 pb-2">Resumo Financeiro</h4>
                        <div class="flex justify-between text-sm mb-1 text-gray-600">
                            <span>Soma dos Produtos:</span>
                            <span class="font-bold" id="txt_total_original">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2 text-green-700">
                            <span>Preço do Combo:</span>
                            <span class="font-bold" id="txt_preco_combo">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-blue-100 shadow-sm">
                            <span class="text-xs font-bold text-gray-500 uppercase">Economia</span>
                            <div class="text-right">
                                <span class="block font-black text-lg text-blue-600" id="txt_economia_valor">R$ 0,00</span>
                                <span class="text-[10px] font-bold text-white bg-blue-500 px-1.5 py-0.5 rounded" id="txt_economia_porc">0% OFF</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-6 h-full">
                     <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col max-h-[40vh]">
                        <h3 class="font-bold text-gray-700 text-sm mb-2">Itens do Combo (Baixa Estoque)</h3>
                        <div class="flex-1 bg-gray-50 rounded-xl border border-gray-200 overflow-hidden relative">
                            <div class="overflow-y-auto h-full custom-scroll">
                                <table class="w-full text-left text-sm">
                                    <tbody id="lista_itens"></tbody>
                                </table>
                            </div>
                            <div id="msg_vazio" class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm flex-col pointer-events-none">
                                <span class="font-medium">Nenhum item adicionado</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col flex-1 min-h-[40vh]">
                        <div class="mb-4 relative shrink-0">
                            <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="text" id="busca_prod_grid" onkeyup="filtrarGrid()" placeholder="Buscar produto..." 
                                   class="w-full pl-12 border border-gray-200 rounded-xl p-3 outline-none transition text-sm font-medium focus:border-blue-500">
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scroll pr-1">
                            <div class="grid grid-cols-2 xl:grid-cols-3 gap-3">
                                <?php if(!empty($produtosSimples)): ?>
                                    <?php foreach($produtosSimples as $prod): 
                                        $pId = $prod['id'] ?? 0;
                                        $pNome = $prod['nome'] ?? 'Sem Nome';
                                        $pPreco = (float)($prod['preco_base'] ?? 0);
                                        $pImg = $prod['imagem_url'] ?? '';
                                    ?>
                                        <div onclick="adicionarItem(<?php echo $pId; ?>, '<?php echo addslashes($pNome); ?>', <?php echo $pPreco; ?>)" 
                                             class="prod-item bg-white border border-gray-100 rounded-xl p-2 cursor-pointer shadow-sm hover:border-blue-500 hover:shadow-md transition active:scale-95 group relative flex flex-col"
                                             data-nome="<?php echo strtolower($pNome); ?>">
                                            
                                            <div class="aspect-[4/3] w-full bg-gray-100 rounded-lg mb-2 overflow-hidden relative">
                                                <?php if(!empty($pImg)): ?>
                                                    <img src="<?php echo $pImg; ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center h-full text-gray-300"><i class="fas fa-image text-2xl"></i></div>
                                                <?php endif; ?>
                                                <div class="absolute bottom-1 right-1 bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </div>
                                            </div>

                                            <h4 class="font-bold text-gray-700 text-xs leading-tight mb-1 line-clamp-2 min-h-[2.5em]"><?php echo $pNome; ?></h4>
                                            <p class="text-[10px] text-gray-400 font-medium">R$ <?php echo number_format($pPreco, 2, ',', '.'); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-6 right-8 z-50">
                <button type="submit" class="bg-gray-900 text-white px-8 py-4 rounded-full font-bold shadow-2xl hover:bg-black transition transform hover:-translate-y-1 hover:scale-105 flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i> <span>SALVAR PROMOÇÃO</span>
                </button>
            </div>
        </form>
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
                <button type="button" onclick="salvarPeriodoModal()" class="px-6 py-3 rounded-xl bg-slate-200 text-slate-400 font-bold hover:bg-blue-600 hover:text-white transition" id="btn_salvar_modal">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Recupera dados
    let itens = <?php echo isset($combo['itens']) ? json_encode($combo['itens']) : '[]'; ?>;
    let disponibilidade = <?php echo isset($combo['disponibilidade']) ? json_encode($combo['disponibilidade']) : '[]'; ?>;

    itens = itens.map(i => ({
        id: parseInt(i.id || i.item_id),
        nome: i.nome,
        preco_original: parseFloat(i.preco_base || 0),
        qtd: parseInt(i.qtd || i.quantidade)
    }));

    // Inicialização
    document.addEventListener('DOMContentLoaded', () => {
        renderizarItens();
        calcularEconomia();
        carregarDisponibilidade();
    });

    // --- FUNÇÕES DE DISPONIBILIDADE ---
    const diasNomes = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
    let diasSelecionadosModal = [];

    function carregarDisponibilidade() {
        if(disponibilidade && disponibilidade.length > 0) {
            disponibilidade.forEach(p => {
                const dia = p.dia_semana;
                const inicio = p.horario_inicio.substring(0, 5);
                const fim = p.horario_fim.substring(0, 5);
                inserirPeriodoVisual(dia, inicio, fim);
            });
        }
    }

    function abrirModalDisponibilidade() {
        diasSelecionadosModal = [];
        document.querySelectorAll('.btn-dia').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
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

    document.querySelectorAll('.btn-dia').forEach(btn => {
        btn.addEventListener('click', function() {
            const dia = parseInt(this.getAttribute('data-dia'));
            if (diasSelecionadosModal.includes(dia)) {
                diasSelecionadosModal = diasSelecionadosModal.filter(d => d !== dia);
                this.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                this.classList.add('border-slate-200', 'text-slate-500');
            } else {
                diasSelecionadosModal.push(dia);
                this.classList.remove('border-slate-200', 'text-slate-500');
                this.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            }
            atualizarBtnSalvar();
        });
    });

    function atualizarBtnSalvar() {
        const btn = document.getElementById('btn_salvar_modal');
        if (diasSelecionadosModal.length > 0) {
            btn.classList.remove('bg-slate-200', 'text-slate-400');
            btn.classList.add('bg-blue-600', 'text-white');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-slate-200', 'text-slate-400');
        }
    }

    function salvarPeriodoModal() {
        if (diasSelecionadosModal.length === 0) return;
        const inicio = document.getElementById('modal_hora_inicio').value;
        const fim = document.getElementById('modal_hora_fim').value;

        diasSelecionadosModal.forEach(dia => {
            inserirPeriodoVisual(dia, inicio, fim);
        });
        fecharModalDisponibilidade();
    }

    function inserirPeriodoVisual(dia, inicio, fim) {
        const idUnico = 'p_' + Math.random().toString(36).substr(2, 9);
        const listaVisual = document.getElementById('lista_periodos_visual');
        const containerInputs = document.getElementById('container_periodos_inputs');

        const htmlVisual = `
            <div id="vis_${idUnico}" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 animate-fade-in text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-black bg-white px-2 py-1 rounded border border-slate-200 text-blue-600">${diasNomes[dia]}</span>
                    <span class="text-slate-600 font-medium">${inicio} - ${fim}</span>
                </div>
                <button type="button" onclick="removerPeriodo('${idUnico}')" class="text-slate-400 hover:text-red-500"><i class="fas fa-trash-alt"></i></button>
            </div>`;
        listaVisual.insertAdjacentHTML('beforeend', htmlVisual);

        const htmlInputs = `
            <div id="inp_${idUnico}">
                <input type="hidden" name="dia_semana[]" value="${dia}">
                <input type="hidden" name="horario_inicio[]" value="${inicio}">
                <input type="hidden" name="horario_fim[]" value="${fim}">
            </div>`;
        containerInputs.insertAdjacentHTML('beforeend', htmlInputs);
    }

    function removerPeriodo(id) {
        document.getElementById('vis_' + id).remove();
        document.getElementById('inp_' + id).remove();
    }

    // --- FUNÇÕES DE ITENS E COMBOS ---
    function adicionarItem(id, nome, preco) {
        let existente = itens.find(i => i.id === id);
        if(existente) {
            existente.qtd++;
        } else {
            itens.push({ id: id, nome: nome, preco_original: preco, qtd: 1 });
        }
        renderizarItens();
        calcularEconomia();
    }

    function removerItem(index) {
        itens.splice(index, 1);
        renderizarItens();
        calcularEconomia();
    }
    
    function alterarQtd(index, delta) {
        const item = itens[index];
        item.qtd += delta;
        if(item.qtd <= 0) itens.splice(index, 1);
        renderizarItens();
        calcularEconomia();
    }

    function renderizarItens() {
        const tbody = document.getElementById('lista_itens');
        const msg = document.getElementById('msg_vazio');
        tbody.innerHTML = '';

        if(itens.length === 0) {
            msg.style.display = 'flex';
        } else {
            msg.style.display = 'none';
            itens.forEach((item, index) => {
                let subtotal = item.qtd * item.preco_original;
                tbody.innerHTML += `
                    <tr class="border-b border-gray-100 last:border-0 bg-white hover:bg-gray-50 transition animate-fade-in">
                        <td class="p-3 w-20">
                            <div class="flex items-center gap-1 bg-gray-100 rounded-lg px-1 py-0.5">
                                <button type="button" onclick="alterarQtd(${index}, -1)" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-red-500 font-bold">-</button>
                                <span class="text-xs font-black w-4 text-center">${item.qtd}</span>
                                <button type="button" onclick="alterarQtd(${index}, 1)" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-blue-500 font-bold">+</button>
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-gray-700 text-xs">${item.nome}</div>
                            <div class="text-[10px] text-gray-400">Orig: R$ ${subtotal.toFixed(2).replace('.',',')}</div>
                        </td>
                        <td class="p-3 text-right">
                            <button type="button" onclick="removerItem(${index})" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function calcularEconomia() {
        let totalOriginal = itens.reduce((acc, item) => acc + (item.qtd * item.preco_original), 0);
        let precoComboStr = document.getElementById('input_preco_venda').value;
        let precoCombo = parseFloat(precoComboStr.replace(/\./g, '').replace(',', '.')) || 0;

        let economia = totalOriginal - precoCombo;
        let porc = totalOriginal > 0 ? (economia / totalOriginal) * 100 : 0;

        document.getElementById('txt_total_original').innerText = 'R$ ' + totalOriginal.toFixed(2).replace('.', ',');
        document.getElementById('txt_preco_combo').innerText = 'R$ ' + precoCombo.toFixed(2).replace('.', ',');
        
        const elEcoValor = document.getElementById('txt_economia_valor');
        const elEcoPorc = document.getElementById('txt_economia_porc');

        if (economia > 0) {
            elEcoValor.innerText = 'R$ ' + economia.toFixed(2).replace('.', ',');
            elEcoValor.className = "block font-black text-lg text-blue-600";
            elEcoPorc.innerText = porc.toFixed(0) + '% OFF';
            elEcoPorc.className = "text-[10px] font-bold text-white bg-blue-500 px-1.5 py-0.5 rounded";
        } else {
            elEcoValor.innerText = 'R$ 0,00';
            elEcoValor.className = "block font-black text-lg text-gray-400";
            elEcoPorc.innerText = 'Sem desconto';
            elEcoPorc.className = "text-[10px] font-bold text-gray-500 bg-gray-200 px-1.5 py-0.5 rounded";
        }
    }

    function previewImagem(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img_preview').src = e.target.result;
                document.getElementById('img_preview').classList.remove('hidden');
                document.getElementById('img_placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function prepararEnvio(e) {
        if(itens.length === 0) { e.preventDefault(); alert("Adicione produtos ao combo!"); return; }
        document.getElementById('itens_json').value = JSON.stringify(itens);
    }

    function filtrarGrid() {
        const term = document.getElementById('busca_prod_grid').value.toLowerCase();
        document.querySelectorAll('.prod-item').forEach(el => {
            el.style.display = el.getAttribute('data-nome').includes(term) ? 'flex' : 'none';
        });
    }

    function mascaraMoeda(i) {
        var v = i.value.replace(/\D/g,'');
        v = (v/100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
        v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
        i.value = v;
    }
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>