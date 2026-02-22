<?php $titulo = "Financeiro - Contas a Receber"; require __DIR__ . '/../../partials/header.php'; ?>
<div class="flex h-screen bg-gray-50 font-sans">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>
    
    <main class="flex-1 p-6 md:p-8 overflow-y-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Contas a Receber <span class="text-blue-500">.</span></h1>
                <p class="text-gray-500 text-sm font-medium">Gestão de fiados e fluxo de caixa</p>
            </div>
            
             <div class="flex gap-2 w-full md:w-auto">
                <form class="flex gap-2 bg-white p-1 rounded-xl shadow-sm border border-gray-200 w-full md:w-auto">
                    <input type="date" name="inicio" value="<?php echo $_GET['inicio'] ?? date('Y-m-d'); ?>" class="bg-transparent text-xs font-bold text-gray-600 outline-none px-2 py-2 border-r border-gray-100">
                    <input type="date" name="fim" value="<?php echo $_GET['fim'] ?? date('Y-m-d'); ?>" class="bg-transparent text-xs font-bold text-gray-600 outline-none px-2 py-2">
                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 rounded-lg"><i class="fas fa-search"></i></button>
                </form>
                <button onclick="abrirModalForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Novo Recebimento
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-gray-400 text-[10px] font-black uppercase tracking-widest">A Receber (Fiado)</span>
                    <p class="text-3xl font-black text-red-500 mt-1">R$ <?php echo number_format($resumo['total_fiado'] ?? 0, 2, ',', '.'); ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <span class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Recebido Total</span>
                <p class="text-3xl font-black text-green-600 mt-1">R$ <?php echo number_format($resumo['total_pago'] ?? 0, 2, ',', '.'); ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <span class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Previsão Total</span>
                <p class="text-3xl font-black text-blue-800 mt-1">R$ <?php echo number_format(($resumo['total_pago'] ?? 0) + ($resumo['total_pendente'] ?? 0), 2, ',', '.'); ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <tr>
                        <th class="p-5">Cliente</th>
                        <th class="p-5">Valor</th>
                        <th class="p-5">Pagamento</th>
                        <th class="p-5">Status</th>
                        <th class="p-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if(empty($titulos)): ?>
                        <tr><td colspan="5" class="p-10 text-center text-gray-400 text-sm font-medium">Nenhum lançamento encontrado.</td></tr>
                    <?php else: foreach($titulos as $t): ?>
                    <tr class="hover:bg-gray-50/80 transition group">
                        <td class="p-5">
                            <div class="flex flex-col">
                                <span class="font-bold <?php echo $t['status'] == 'cancelado' ? 'text-gray-400 line-through' : 'text-gray-800'; ?> text-sm"><?php echo $t['cliente_nome']; ?></span>
                                <?php $tel = $t['cliente_whatsapp'] ?? $t['telefone'] ?? ''; ?>
                                <span class="text-[10px] text-gray-400 font-medium"><?php echo $tel ? $tel : 'S/ Telefone'; ?></span>
                            </div>
                        </td>
                        <td class="p-5 font-black <?php echo $t['status'] == 'cancelado' ? 'text-gray-400' : 'text-gray-900'; ?>">R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?></td>
                        <td class="p-5">
                            <div class="flex flex-col gap-1">
                                <span class="px-2 py-1 rounded w-fit text-[10px] font-bold uppercase <?php echo $t['forma_pagamento'] == 'fiado' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'; ?>">
                                    <?php echo $t['forma_pagamento']; ?>
                                </span>
                                <?php if($t['observacoes']): ?>
                                    <span class="text-[10px] text-gray-400 truncate max-w-[150px]"><?php echo $t['observacoes']; ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="p-5">
                            <?php if($t['status'] == 'pago'): ?>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-green-100 text-green-600">PAGO</span>
                            <?php elseif($t['status'] == 'cancelado'): ?>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-gray-200 text-gray-500">CANCELADO</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-orange-100 text-orange-600">PENDENTE</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 text-right">
                            <div class="flex gap-2 justify-end items-center opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                
                                <button onclick='editarTitulo(<?php echo json_encode($t); ?>)' 
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition" title="Editar">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                </button>
                                
                                <button onclick="excluirTitulo(<?php echo $t['id']; ?>)" 
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition" title="Excluir">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>

                                <?php if($t['status'] == 'pendente'): ?>
                                    <button onclick="cancelarTitulo(<?php echo $t['id']; ?>)" 
                                            class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition" title="Cancelar Título">
                                        <i class="fas fa-ban text-xs"></i>
                                    </button>

                                    <?php $zap = preg_replace('/\D/', '', $tel); if($zap): ?>
                                    <a href="https://wa.me/55<?php echo $zap; ?>?text=Olá+<?php echo urlencode($t['cliente_nome']); ?>,+lembrete+do+débito+de+R$+<?php echo number_format($t['valor'], 2, ',', '.'); ?>." target="_blank" class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition" title="Cobrar WhatsApp">
                                        <i class="fab fa-whatsapp text-xs"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="baixarTitulo(<?php echo $t['id']; ?>)" class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold hover:bg-black transition shadow-lg ml-2">
                                        BAIXAR
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="modalForm" class="fixed inset-0 bg-slate-900/40 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm transition-opacity">
    <div class="bg-white w-full max-w-lg rounded-3xl p-8 shadow-2xl transform transition-all scale-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-black text-gray-800 tracking-tight" id="modalTitulo">NOVO RECEBIMENTO</h2>
            <button onclick="fecharModalForm()" class="text-gray-300 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        
        <form id="formTitulo" onsubmit="salvarTitulo(event)">
            <input type="hidden" name="id" id="input_id">
            <input type="hidden" name="cliente_id" id="input_cliente_id">

            <div class="space-y-5">
                <div class="relative">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Cliente</label>
                    <input type="text" id="busca_cliente" name="cliente_nome" autocomplete="off" required
                           class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:bg-white outline-none font-bold text-gray-700 transition"
                           placeholder="Buscar..." onkeyup="buscarClienteAPI(this.value)">
                    <div id="lista_clientes" class="absolute w-full bg-white border border-gray-100 shadow-xl rounded-xl mt-1 hidden z-50 max-h-48 overflow-y-auto"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Telefone</label>
                        <input type="text" name="telefone" id="input_telefone" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none font-medium text-gray-600">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Valor</label>
                        <input type="text" name="valor" id="input_valor" required onkeyup="mascaraMoedaManual(this)" placeholder="0,00" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm font-black text-gray-800 focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Categoria</label>
                        <select name="categoria" id="input_categoria" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none font-bold text-gray-600">
                            <option value="Venda">Venda</option>
                            <option value="Serviço">Serviço</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Vencimento</label>
                        <input type="date" name="data_vencimento" id="input_vencimento" required value="<?php echo date('Y-m-d'); ?>" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none font-bold text-gray-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Forma Pagto</label>
                        <select name="forma_pagamento" id="input_forma" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none font-bold text-gray-600">
                            <option value="fiado">Fiado</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">Pix</option>
                            <option value="cartao">Cartão</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Status</label>
                        <select name="status" id="input_status" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none font-bold text-gray-600">
                            <option value="pendente">Pendente</option>
                            <option value="pago">Já Pago</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Observações</label>
                    <textarea name="observacoes" id="input_obs" rows="2" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none font-medium text-gray-600 resize-none"></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="fecharModalForm()" class="flex-1 bg-white border-2 border-gray-100 text-gray-500 py-3 rounded-xl font-bold hover:bg-gray-50 transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-xl shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalForm(editar = false) {
    document.getElementById('modalForm').classList.remove('hidden');
    if(!editar) {
        document.getElementById('formTitulo').reset();
        document.getElementById('input_id').value = '';
        document.getElementById('modalTitulo').innerText = "NOVO RECEBIMENTO";
    }
}
function fecharModalForm() { document.getElementById('modalForm').classList.add('hidden'); }

function editarTitulo(t) {
    document.getElementById('input_id').value = t.id;
    document.getElementById('input_cliente_id').value = t.cliente_id;
    document.getElementById('busca_cliente').value = t.cliente_nome;
    
    let tel = t.cliente_whatsapp || t.telefone || '';
    document.getElementById('input_telefone').value = tel;

    let valor = parseFloat(t.valor).toFixed(2).replace('.', ',');
    document.getElementById('input_valor').value = valor;
    
    document.getElementById('input_vencimento').value = t.data_vencimento;
    document.getElementById('input_categoria').value = t.categoria;
    document.getElementById('input_status').value = t.status;
    document.getElementById('input_forma').value = t.forma_pagamento;
    document.getElementById('input_obs').value = t.observacoes;

    document.getElementById('modalTitulo').innerText = "EDITAR RECEBIMENTO";
    abrirModalForm(true);
}

function salvarTitulo(e) {
    e.preventDefault();
    fetch('<?php echo BASE_URL; ?>/admin/financeiro/salvar', {
        method: 'POST', body: new FormData(e.target)
    }).then(r => r.json()).then(d => {
        if(d.ok) location.reload(); else alert('Erro: ' + d.erro);
    });
}

function baixarTitulo(id) {
    if(!confirm('Confirmar recebimento deste valor?')) return;
    const fd = new FormData(); fd.append('id', id);
    fetch('<?php echo BASE_URL; ?>/admin/financeiro/baixarPagamento', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(d => { if(d.ok) location.reload(); });
}

function cancelarTitulo(id) {
    if(!confirm('Tem certeza que deseja cancelar esta cobrança?')) return;
    const fd = new FormData(); fd.append('id', id);
    fetch('<?php echo BASE_URL; ?>/admin/financeiro/cancelar', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(d => { if(d.ok) location.reload(); });
}

function excluirTitulo(id) {
    if(!confirm('Tem certeza que deseja EXCLUIR este registro permanentemente?')) return;
    const fd = new FormData(); fd.append('id', id);
    fetch('<?php echo BASE_URL; ?>/admin/financeiro/excluir', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(d => { if(d.ok) location.reload(); });
}

let timeoutBusca = null;
function buscarClienteAPI(termo) {
    const lista = document.getElementById('lista_clientes');
    if(document.getElementById('busca_cliente').value !== termo) document.getElementById('input_cliente_id').value = '';
    
    clearTimeout(timeoutBusca);
    if(termo.length < 2) { lista.classList.add('hidden'); return; }

    timeoutBusca = setTimeout(() => {
        fetch(`<?php echo BASE_URL; ?>/admin/financeiro/buscarClientes?q=${encodeURIComponent(termo)}`)
        .then(r => r.json())
        .then(data => {
            lista.innerHTML = '';
            if(data.length > 0) {
                data.forEach(c => {
                    const item = document.createElement('div');
                    item.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-50 flex justify-between items-center text-sm';
                    item.innerHTML = `<span class="font-bold text-gray-700">${c.nome}</span> <span class="text-xs text-gray-400">${c.telefone || ''}</span>`;
                    item.onclick = () => {
                        document.getElementById('busca_cliente').value = c.nome;
                        document.getElementById('input_telefone').value = c.telefone;
                        document.getElementById('input_cliente_id').value = c.id;
                        lista.classList.add('hidden');
                    };
                    lista.appendChild(item);
                });
                lista.classList.remove('hidden');
            } else {
                lista.innerHTML = `<div class="p-3 text-xs text-gray-500 italic">Novo: <strong>${termo}</strong></div>`;
                lista.classList.remove('hidden');
            }
        });
    }, 300);
}

document.addEventListener('click', function(e) { if (!document.getElementById('busca_cliente').contains(e.target)) { document.getElementById('lista_clientes').classList.add('hidden'); } });

function mascaraMoedaManual(i) {
    let v = i.value.replace(/\D/g,'');
    v = (v/100).toFixed(2) + ''; 
    v = v.replace(".", ",");
    v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
    v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
    i.value = v;
}
</script>