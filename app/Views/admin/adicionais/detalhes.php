<?php 
// Verifica se o grupo existe, senão volta
if(!isset($grupo)) { echo "Grupo não encontrado"; exit; }

$titulo = "Opções: " . $grupo['nome'];
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        
        <div class="flex items-center gap-4 mb-8">
            <a href="<?php echo BASE_URL; ?>/admin/adicionais" 
               class="bg-white border px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <?php echo $grupo['nome']; ?>
                    <span class="text-sm font-normal text-gray-500 bg-gray-200 px-2 py-1 rounded-full">ID: <?php echo $grupo['id']; ?></span>
                </h1>
                <p class="text-gray-500">Cadastre as opções que o cliente poderá escolher.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-plus-circle text-green-600 mr-2"></i> Adicionar Opção
                    </h3>
                    
                    <form action="<?php echo BASE_URL; ?>/admin/adicionais/salvarItem" method="POST">
                        <input type="hidden" name="grupo_id" value="<?php echo $grupo['id']; ?>">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Opção</label>
                                <input type="text" name="nome" required 
                                       placeholder="Ex: Catupiry Original" 
                                       class="w-full border-gray-300 rounded-lg p-2 border focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preço Adicional (R$)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input type="text" name="preco" required 
                                           placeholder="0,00" 
                                           class="w-full pl-10 border-gray-300 rounded-lg p-2 border focus:ring-green-500" 
                                           onkeyup="mascaraMoeda(this)">
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Deixe 0,00 se for grátis.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição (Opcional)</label>
                                <input type="text" name="descricao" 
                                       placeholder="Ex: Borda bem recheada" 
                                       class="w-full border-gray-300 rounded-lg p-2 border">
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full bg-green-600 text-white py-2.5 rounded-lg font-bold hover:bg-green-700 shadow transition">
                                <i class="fas fa-check mr-1"></i> Salvar Opção
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nome da Opção</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Valor Extra</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(empty($itens)): ?>
                                <tr>
                                    <td colspan="3" class="p-12 text-center text-gray-500">
                                        <div class="mb-2"><i class="fas fa-box-open text-3xl text-gray-300"></i></div>
                                        Nenhuma opção cadastrada neste grupo ainda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($itens as $item): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-800 text-base"><?php echo $item['nome']; ?></div>
                                            <?php if(!empty($item['descricao'])): ?>
                                                <div class="text-xs text-gray-500"><?php echo $item['descricao']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($item['preco'] > 0): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-bold border border-green-200">
                                                    + R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-sm font-bold border border-gray-200">
                                                    Grátis
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="<?php echo BASE_URL; ?>/admin/adicionais/excluirItem?id=<?php echo $item['id']; ?>" 
                                               onclick="return confirm('Remover esta opção?')" 
                                               class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" 
                                               title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
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