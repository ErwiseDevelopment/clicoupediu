<?php 
$titulo = "Controle de Estoque";
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Estoque de Produtos</h1>
                <p class="text-gray-50">Gerencie o estoque manual e visualize a disponibilidade de combos.</p>
            </div>
            
            <button onclick="document.getElementById('formEstoque').submit()" class="hidden md:block bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition">
                <i class="fas fa-save mr-2"></i> Salvar Alterações
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <form id="formEstoque" action="<?php echo BASE_URL; ?>/admin/estoque/salvarAjuste" method="POST">
                
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Produto</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Categoria</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest w-48">Qtd. Disponível</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                   <tbody class="bg-white divide-y divide-gray-100">
                        <?php foreach($produtos as $prod): 
                            $isCombo = ($prod['tipo'] === 'combo'); // Identifica se é combo
                            $qtd = intval($prod['quantidade'] ?? 0);
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 flex items-center">
                                <div class="relative">
                                    <img src="<?= (strpos($prod['imagem_url'], 'http') === false) ? BASE_URL.'/'.$prod['imagem_url'] : $prod['imagem_url']; ?>" class="h-10 w-10 rounded-lg object-cover mr-3 border border-gray-200">
                                    <?php if($isCombo): ?>
                                        <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-[8px] px-1 rounded-full border border-white">COMBO</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900"><?= $prod['nome']; ?></span>
                                    <?php if($isCombo): ?>
                                        <span class="block text-[10px] text-gray-400 italic">Disponibilidade montada pelos itens do combo</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= $prod['categoria']; ?></td>
                            <td class="px-6 py-4">
                                <?php if($isCombo): ?>
                                    <div class="flex items-center text-gray-500 font-bold bg-gray-100 w-24 p-2 rounded-lg justify-center gap-2" title="Estoque calculado pelos ingredientes">
                                        <i class="fas fa-info-circle text-xs"></i> <?= $qtd ?>
                                    </div>
                                <?php else: ?>
                                    <input type="number" name="estoque[<?= $prod['id']; ?>]" value="<?= $qtd ?>" 
                                        class="w-24 border border-gray-300 rounded-lg p-2 text-center focus:ring-2 focus:ring-blue-500">
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $qtd > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $qtd > 0 ? 'Disponível' : 'Esgotado' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </form>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>