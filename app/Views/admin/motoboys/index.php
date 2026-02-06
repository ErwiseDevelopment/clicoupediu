<?php $titulo = "Cadastro de Motoboys"; require __DIR__ . '/../../partials/header.php'; ?>
<div class="flex h-screen bg-gray-100">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>
    <main class="flex-1 p-8 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Motoboys</h1>
            <button onclick="abrirModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">+ Novo Motoboy</button>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4">Nome</th>
                        <th class="p-4">WhatsApp</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($motoboys as $m): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-medium"><?php echo $m['nome']; ?></td>
                        <td class="p-4"><?php echo $m['whatsapp']; ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold <?php echo $m['ativo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo $m['ativo'] ? 'ATIVO' : 'INATIVO'; ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <button onclick='editarMotoboy(<?php echo json_encode($m); ?>)' class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button>
                            <a href="<?php echo BASE_URL; ?>/admin/motoboys/excluir?id=<?php echo $m['id']; ?>" onclick="return confirm('Excluir?')" class="text-red-500"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="modalMotoboy" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-md rounded-xl p-6 shadow-2xl">
        <h2 id="modalTitle" class="text-xl font-bold mb-4">Novo Motoboy</h2>
        <form action="<?php echo BASE_URL; ?>/admin/motoboys/salvar" method="POST">
            <input type="hidden" name="id" id="moto_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Nome Completo</label>
                    <input type="text" name="nome" id="moto_nome" required class="w-full border rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">WhatsApp</label>
                    <input type="text" name="whatsapp" id="moto_whatsapp" class="w-full border rounded-lg p-2">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="moto_ativo" checked>
                    <label class="text-sm font-bold">Motoboy Ativo</label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="fecharModal()" class="flex-1 bg-gray-100 py-2 rounded-lg font-bold">Cancelar</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg font-bold">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalMotoboy').classList.remove('hidden');
    document.getElementById('moto_id').value = '';
    document.getElementById('modalTitle').innerText = 'Novo Motoboy';
}
function fecharModal() { document.getElementById('modalMotoboy').classList.add('hidden'); }
function editarMotoboy(m) {
    abrirModal();
    document.getElementById('modalTitle').innerText = 'Editar Motoboy';
    document.getElementById('moto_id').value = m.id;
    document.getElementById('moto_nome').value = m.nome;
    document.getElementById('moto_whatsapp').value = m.whatsapp;
    document.getElementById('moto_ativo').checked = m.ativo == 1;
}
</script>