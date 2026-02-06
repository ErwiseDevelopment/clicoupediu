<?php 
$titulo = "Grupos de Complementos";
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Complementos e Adicionais</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Novo Grupo</h3>
                    <form action="<?php echo BASE_URL; ?>/admin/adicionais/salvarGrupo" method="POST">
                        <input type="hidden" name="id" id="grupo_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="nome" required placeholder="Ex: Escolha a Borda" class="w-full border rounded-lg p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <input type="text" name="descricao" placeholder="Ajuda ao cliente" class="w-full border rounded-lg p-2">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Min</label>
                                    <input type="number" name="qtd_min" value="0" class="w-full border rounded-lg p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Max</label>
                                    <input type="number" name="qtd_max" value="1" class="w-full border rounded-lg p-2">
                                </div>
                            </div>
                            <div class="flex items-center bg-yellow-50 p-3 rounded border border-yellow-200">
                                <input type="checkbox" name="obrigatorio" value="1" class="h-4 w-4 text-yellow-600">
                                <label class="ml-2 text-sm text-yellow-800 font-bold">Obrigatório?</label>
                            </div>
                        </div>
                        <button type="submit" class="mt-4 w-full bg-blue-600 text-white p-2 rounded-lg font-bold">Salvar</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <?php if(empty($grupos)): ?>
                    <p class="text-gray-500 text-center mt-10">Nenhum grupo cadastrado.</p>
                <?php else: ?>
                    <?php foreach($grupos as $g): ?>
                        <div class="bg-white p-4 mb-3 rounded-xl shadow-sm border flex justify-between items-center">
                            <div>
                                <h4 class="font-bold"><?php echo $g['nome']; ?></h4>
                                
                                <p class="text-xs text-gray-500">Min: <?php echo $g['minimo']; ?> | Max: <?php echo $g['maximo']; ?></p>
                            
                            </div>
                            <div class="flex gap-2">
                                <a href="<?php echo BASE_URL; ?>/admin/adicionais/detalhes?id=<?php echo $g['id']; ?>" class="bg-blue-100 text-blue-700 px-3 py-1 rounded font-bold text-sm">Opções</a>
                                <a href="<?php echo BASE_URL; ?>/admin/adicionais/excluirGrupo?id=<?php echo $g['id']; ?>" class="text-red-500 px-2"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>
<?php require __DIR__ . '/../../partials/footer.php'; ?>