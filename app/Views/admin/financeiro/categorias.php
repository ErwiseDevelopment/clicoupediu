<?php $titulo = "Categorias Financeiras"; require __DIR__ . '/../../partials/header.php'; ?>
<div class="flex h-screen bg-gray-100">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>
    
    <main class="flex-1 p-8 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">Categorias Financeiras 🏷️</h1>
                <p class="text-gray-500 text-sm">Organize suas Entradas e Saídas</p>
            </div>
            <button onclick="abrirModal()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Nova Categoria
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    <tr>
                        <th class="p-5">Descrição</th>
                        <th class="p-5">Tipo</th>
                        <th class="p-5">Status</th>
                        <th class="p-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach($categorias as $c): ?>
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-5 font-bold text-gray-700"><?php echo $c['descricao']; ?></td>
                        <td class="p-5">
                            <?php if($c['tipo'] == 'entrada'): ?>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Entrada 📥</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Saída 📤</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5">
                            <?php if($c['ativo']): ?>
                                <span class="text-green-600 text-xs font-bold flex items-center gap-1"><i class="fas fa-check-circle"></i> Ativo</span>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs font-bold flex items-center gap-1"><i class="fas fa-times-circle"></i> Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 text-right">
                            <button onclick='editar(<?php echo json_encode($c); ?>)' class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition"><i class="fas fa-edit"></i></button>
                            <a href="<?php echo BASE_URL; ?>/admin/categorias-financeiro/excluir?id=<?php echo $c['id']; ?>" onclick="return confirm('Excluir esta categoria?')" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition ml-2"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="modalCategoria" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl animate-fade-in">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-black text-gray-800" id="tituloModal">Nova Categoria</h2>
            <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        
        <form action="<?php echo BASE_URL; ?>/admin/categorias-financeiro/salvar" method="POST">
            <input type="hidden" name="id" id="cat_id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Descrição</label>
                    <input type="text" name="descricao" id="cat_descricao" required class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 outline-none" placeholder="Ex: Gás, Compra de Frango...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tipo</label>
                        <select name="tipo" id="cat_tipo" class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 outline-none">
                            <option value="entrada">Entrada (Receita)</option>
                            <option value="saida">Saída (Despesa)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status</label>
                        <select name="ativo" id="cat_ativo" class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 outline-none">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="fecharModal()" class="flex-1 bg-gray-100 text-gray-500 py-3 rounded-xl font-bold">Cancelar</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalCategoria').classList.remove('hidden');
    document.getElementById('tituloModal').innerText = 'Nova Categoria';
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_descricao').value = '';
    document.getElementById('cat_tipo').value = 'saida'; // Padrão Saída
    document.getElementById('cat_ativo').value = '1';
}

function fecharModal() {
    document.getElementById('modalCategoria').classList.add('hidden');
}

function editar(cat) {
    abrirModal();
    document.getElementById('tituloModal').innerText = 'Editar Categoria';
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('cat_descricao').value = cat.descricao;
    document.getElementById('cat_tipo').value = cat.tipo;
    document.getElementById('cat_ativo').value = cat.ativo;
}
</script>