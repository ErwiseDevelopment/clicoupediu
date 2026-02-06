<?php 
$titulo = "Configurar Adicionais - " . $produto['nome'];
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        
        <div class="flex items-center gap-4 mb-8">
            <a href="<?php echo BASE_URL; ?>/admin/produtos" class="bg-white border px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Complementos do Produto</h1>
                <p class="text-gray-500">Selecione o que o cliente pode personalizar no <strong><?php echo $produto['nome']; ?></strong></p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <form action="<?php echo BASE_URL; ?>/admin/produtos/salvarVinculos" method="POST">
                <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700">Grupos de Adicionais Disponíveis</h3>
                        <a href="<?php echo BASE_URL; ?>/admin/adicionais" class="text-sm text-blue-600 hover:underline">Criar novo grupo</a>
                    </div>

                    <?php if(empty($grupos)): ?>
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-exclamation-triangle text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800">Nenhum Grupo Criado</h3>
                            <p class="text-gray-500 mb-4">Você precisa criar grupos (ex: Bordas, Bebidas) antes de vincular.</p>
                            <a href="<?php echo BASE_URL; ?>/admin/adicionais" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700">Ir para Adicionais</a>
                        </div>
                    <?php else: ?>
                        
                        <div class="divide-y divide-gray-100">
                            <?php foreach($grupos as $g): ?>
                                <label class="flex items-center p-5 hover:bg-blue-50 transition cursor-pointer group">
                                    <div class="flex items-center h-6">
                                        <input type="checkbox" name="grupos[]" value="<?php echo $g['id']; ?>" 
                                               <?php echo $g['vinculado'] ? 'checked' : ''; ?>
                                               class="w-6 h-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                    </div>
                                    
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-medium text-gray-900 group-hover:text-blue-700">
                                                <?php echo $g['nome']; ?>
                                            </span>
                                            
                                            <?php if($g['obrigatorio']): ?>
                                                <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded font-bold">Obrigatório</span>
                                            <?php else: ?>
                                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold">Opcional</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="text-sm text-gray-500 mt-1">
                                            Escolher entre <strong><?php echo $g['minimo']; ?></strong> e <strong><?php echo $g['maximo']; ?></strong> opções.
                                        </p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-bold text-lg hover:bg-green-700 shadow-lg transition transform hover:-translate-y-1">
                                <i class="fas fa-save mr-2"></i> Salvar Configuração
                            </button>
                        </div>

                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>