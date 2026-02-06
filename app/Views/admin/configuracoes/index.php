<?php 
$titulo = "Minha Loja";
require __DIR__ . '/../../partials/header.php'; 
?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Configurações da Loja</h1>

        <form action="<?php echo BASE_URL; ?>/admin/configuracoes/salvar" method="POST" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 border-b pb-2">Identidade & Operação</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logotipo</label>
                        <div class="flex items-center gap-4">
                            <?php if($config['logo_url']): ?>
                                <img src="<?php echo $config['logo_url']; ?>" class="w-16 h-16 rounded-full border shadow-sm object-cover">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border"><i class="fas fa-store"></i></div>
                            <?php endif; ?>
                            <input type="file" name="logo" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Banner de Capa (Topo do Cardápio)</label>
                        <?php if($config['banner_capa_url']): ?>
                            <img src="<?php echo $config['banner_capa_url']; ?>" class="w-full h-24 object-cover rounded-lg mb-2 border">
                        <?php endif; ?>
                        <input type="file" name="capa" class="w-full text-sm text-gray-500 border rounded p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cor Principal (Botões)</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="cor_primaria" value="<?php echo $config['cor_primaria'] ?? '#FF4500'; ?>" class="h-10 w-20 rounded cursor-pointer border p-1">
                            <span class="text-xs text-gray-500">Escolha a cor da sua marca</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tempo de Entrega</label>
                            <input type="text" name="tempo_entrega" value="<?php echo $config['tempo_medio_entrega'] ?? '40-50 min'; ?>" 
                                   class="w-full border rounded-lg p-2" placeholder="Ex: 30-40 min">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pedido Mínimo (R$)</label>
                            <input type="text" name="pedido_minimo" value="<?php echo number_format($config['pedido_minimo'], 2, ',', '.'); ?>" 
                                   class="w-full border rounded-lg p-2" onkeyup="mascaraMoeda(this)">
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tempo Entrega</label>
                                <input type="text" name="tempo_entrega" value="<?php echo $config['tempo_medio_entrega'] ?? '40-50 min'; ?>" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Pedido Mínimo (R$)</label>
                                <input type="text" name="pedido_minimo" value="<?php echo number_format($config['pedido_minimo'] ?? 0, 2, ',', '.'); ?>" class="w-full border border-gray-300 rounded-lg p-2 text-sm" onkeyup="mascaraMoeda(this)">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Chave PIX (Para QR Code)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fab fa-pix text-green-600"></i>
                                </div>
                                <input type="text" name="chave_pix" value="<?php echo $config['chave_pix'] ?? ''; ?>" 
                                    class="w-full border border-gray-300 rounded-lg p-2 pl-10 text-sm font-medium focus:ring-2 focus:ring-green-500 outline-none" 
                                    placeholder="Ex: 11999999999 (Celular, CPF, Email ou Aleatória)">
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Essa chave será usada para gerar o QR Code no PDV e para o Motoboy.</p>
                        </div>
                        <div class="mb-6 flex items-start bg-blue-50 p-3 rounded-lg border border-blue-100">
                    </div>

                    <div class="mt-4 flex items-center bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <input type="checkbox" name="aberto_auto" value="1" <?php echo ($config['aberto_automatico']) ? 'checked' : ''; ?> class="h-5 w-5 text-blue-600 rounded">
                        <div class="ml-3">
                            <label class="text-sm font-bold text-blue-900">Abrir e Fechar Automaticamente</label>
                            <p class="text-xs text-blue-700">O sistema seguirá rigorosamente os horários ao lado.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 border-b pb-2">Horários de Funcionamento</h3>
                    
                    <div class="space-y-3">
                        <?php 
                        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                        foreach($horarios as $h): 
                            $diaIndex = $h['dia_semana'];
                        ?>
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-200 transition">
                            <div class="w-24 font-medium text-gray-700"><?php echo $dias[$diaIndex]; ?></div>
                            
                            <div class="flex items-center gap-2">
                                <input type="time" name="horarios[<?php echo $diaIndex; ?>][abre]" value="<?php echo substr($h['abertura'], 0, 5); ?>" class="border rounded p-1 text-sm">
                                <span class="text-gray-400 text-xs">até</span>
                                <input type="time" name="horarios[<?php echo $diaIndex; ?>][fecha]" value="<?php echo substr($h['fechamento'], 0, 5); ?>" class="border rounded p-1 text-sm">
                            </div>

                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="horarios[<?php echo $diaIndex; ?>][fechado]" value="1" <?php echo $h['fechado_hoje'] ? 'checked' : ''; ?> class="mr-2">
                                <span class="text-xs text-red-500 font-bold">Fechado</span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <div class="fixed bottom-6 right-8">
                <button type="submit" class="bg-green-600 text-white px-8 py-4 rounded-full font-bold shadow-2xl hover:bg-green-700 transition transform hover:-translate-y-1 flex items-center gap-2">
                    <i class="fas fa-save text-xl"></i> SALVAR TUDO
                </button>
            </div>

        </form>
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