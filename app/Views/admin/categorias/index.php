<?php 
// 1. Definição do Título
$titulo = "Gerenciar Categorias";

// 2. Carrega Header e Sidebar
// Note o caminho "../../" para voltar da pasta 'categorias' e 'admin' até chegar em 'partials'
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Categorias</h1>
                <p class="text-sm text-gray-500">Organize os produtos do seu cardápio</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-lg text-sm">
                            <i class="fas fa-pen"></i>
                        </span>
                        <span id="titulo_form">Nova Categoria</span>
                    </h3>
                    
                    <form action="<?php echo BASE_URL; ?>/admin/categorias/salvar" method="POST">
                        <input type="hidden" name="id" id="cat_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input type="text" name="nome" id="cat_nome" required 
                                       placeholder="Ex: Pizzas Tradicionais"
                                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3 border">
                            </div>

                            <div class="flex items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <input type="checkbox" name="ativa" id="cat_ativa" value="1" checked 
                                       class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                                <label for="cat_ativa" class="ml-3 block text-sm font-medium text-gray-700 cursor-pointer">
                                    Visível no Cardápio
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" 
                                    class="flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-blue-700 transition shadow-md hover:shadow-lg">
                                Salvar
                            </button>
                            
                            <button type="button" id="btn_cancelar" onclick="limparForm()" 
                                    class="hidden px-4 py-2.5 rounded-lg font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    
                    <?php if(empty($categorias)): ?>
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <i class="fas fa-tags text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Nenhuma categoria</h3>
                            <p class="text-gray-500 mt-1">Cadastre a primeira categoria usando o formulário ao lado.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($categorias as $cat): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $cat['nome']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="<?php echo BASE_URL; ?>/admin/categorias/alternar?id=<?php echo $cat['id']; ?>" 
                                                class="group flex items-center cursor-pointer"
                                                title="Clique para ativar/desativar">
                                                    
                                                    <?php if($cat['ativa']): ?>
                                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200 group-hover:bg-green-200 transition">
                                                            <i class="fas fa-check-circle mr-1 mt-0.5"></i> Ativa
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600 border border-gray-200 group-hover:bg-gray-200 transition">
                                                            <i class="fas fa-ban mr-1 mt-0.5"></i> Oculta
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <span class="ml-2 text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <i class="fas fa-sync-alt"></i> Alternar
                                                    </span>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button onclick='editar(<?php echo json_encode($cat); ?>)' 
                                                        class="text-blue-600 hover:text-blue-900 font-bold mr-4 bg-blue-50 px-3 py-1 rounded hover:bg-blue-100 transition">
                                                    Editar
                                                </button>
                                                
                                                <a href="<?php echo BASE_URL; ?>/admin/categorias/excluir?id=<?php echo $cat['id']; ?>" 
                                                   onclick="return confirm('Tem certeza que deseja excluir? Isso pode afetar os produtos vinculados.')"
                                                   class="text-red-500 hover:text-red-700 font-bold bg-red-50 px-3 py-1 rounded hover:bg-red-100 transition">
                                                    Excluir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function editar(categoria) {
        // Preenche os campos do formulário
        document.getElementById('cat_id').value = categoria.id;
        document.getElementById('cat_nome').value = categoria.nome;
        
        // Marca/Desmarca o checkbox (banco salva 1 ou 0)
        document.getElementById('cat_ativa').checked = (categoria.ativa == 1);
        
        // Muda o título e mostra botão cancelar
        document.getElementById('titulo_form').innerText = 'Editar Categoria';
        document.getElementById('btn_cancelar').classList.remove('hidden');
        
        // Foca no campo nome para facilitar
        document.getElementById('cat_nome').focus();
        
        // Efeito visual no formulário
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limparForm() {
        // Reseta tudo
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_nome').value = '';
        document.getElementById('cat_ativa').checked = true;
        
        // Volta ao estado original
        document.getElementById('titulo_form').innerText = 'Nova Categoria';
        document.getElementById('btn_cancelar').classList.add('hidden');
    }
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>