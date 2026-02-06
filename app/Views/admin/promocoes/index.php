<?php $titulo = "Promoções e Combos"; require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen bg-gray-50 font-sans">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                    <i class="fas fa-tags text-orange-500"></i> Promoções Ativas
                </h1>
                <p class="text-gray-500 text-sm font-medium">Gerencie seus combos e ofertas especiais.</p>
            </div>
            
            <a href="<?= BASE_URL ?>/admin/promocoes/criar" class="bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-gray-200 transition transform hover:-translate-y-1 flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo Combo
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'salvo'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center gap-3 animate-fade-in">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-bold">Sucesso!</span> Promoção salva corretamente.
            </div>
        <?php endif; ?>

        <?php if(empty($combos)): ?>
            <div class="flex flex-col items-center justify-center h-64 bg-white rounded-3xl border-2 border-dashed border-gray-200 text-gray-400">
                <i class="fas fa-hamburger text-5xl mb-4 opacity-30"></i>
                <p class="font-bold text-lg">Nenhum combo cadastrado</p>
                <p class="text-sm">Clique no botão acima para criar o primeiro.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach($combos as $c): ?>
                    <?php 
                        $isInterno = isset($c['visivel_online']) && $c['visivel_online'] == 0;
                    ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl transition duration-300 flex flex-col h-full relative">
                        
                         <?php if($isInterno): ?>
                            <div class="absolute top-0 left-0 z-10 bg-slate-800 text-white text-[10px] font-bold px-3 py-1 rounded-br-xl shadow-md">
                                <i class="fas fa-eye-slash mr-1"></i> INTERNO
                            </div>
                        <?php endif; ?>

                        <div class="h-48 bg-gray-100 relative overflow-hidden">
                            <?php 
                                $imgUrl = '';
                                if (!empty($c['imagem_url'])) {
                                    $imgUrl = (strpos($c['imagem_url'], 'http') === 0) 
                                            ? $c['imagem_url'] 
                                            : BASE_URL . '/' . $c['imagem_url'];
                                }
                            ?>
                            <?php if($imgUrl): ?>
                                <img src="<?= $imgUrl ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-300">
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute top-3 right-3 bg-white/90 backdrop-blur text-gray-800 text-xs font-black px-3 py-1 rounded-full shadow-sm uppercase tracking-wide">
                                Combo
                            </div>
                        </div>

                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-lg text-gray-800 leading-tight mb-2 line-clamp-1" title="<?= $c['nome'] ?>">
                                <?= $c['nome'] ?>
                            </h3>
                            
                            <p class="text-xs text-gray-500 line-clamp-2 mb-4 flex-1">
                                <?= $c['descricao'] ?: 'Sem descrição definida.' ?>
                            </p>

                            <div class="flex items-end justify-between border-t border-gray-50 pt-4 mt-auto">
                                <div>
                                    <span class="block text-[10px] text-gray-400 uppercase font-bold">Preço Final</span>
                                    <span class="text-2xl font-black text-green-600 tracking-tight">
                                        R$ <?= number_format((float)$c['preco_base'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                
                                <div class="flex gap-2">
                                    <a href="<?= BASE_URL ?>/admin/promocoes/editar?id=<?= $c['id'] ?>" class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button onclick="excluirCombo(<?= $c['id'] ?>)" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition shadow-sm" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<script>
function excluirCombo(id) {
    if(confirm("Tem certeza que deseja excluir esta promoção? Essa ação não pode ser desfeita.")) {
        const fd = new FormData();
        fd.append('id', id);
        
        fetch('<?= BASE_URL ?>/admin/promocoes/excluir', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(d => {
            if(d.ok) {
                location.reload();
            } else {
                alert("Erro ao excluir!");
            }
        });
    }
}
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>