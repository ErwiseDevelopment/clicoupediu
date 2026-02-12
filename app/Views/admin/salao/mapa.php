<?php $titulo = "Mapa de Mesas"; require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen bg-gray-50 overflow-hidden">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shrink-0">
            <div>
                <h2 class="text-xl font-black text-gray-800">Salão & Mesas</h2>
                <p class="text-xs text-gray-500">Gerencie o atendimento presencial</p>
            </div>
            <div class="flex gap-4">
                <div class="flex items-center gap-2 text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-full">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div> Livre
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-full">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div> Ocupada
                </div>
                
                <a href="<?php echo BASE_URL; ?>/admin/mesas" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-gray-900 transition">
                    <i class="fas fa-cog mr-1"></i> Configurar Mesas
                </a>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                
                <?php foreach($mesas as $m): 
                    $ocupada = ($m['status_atual'] == 'ocupada');
                    $corCard = $ocupada ? 'bg-white border-l-4 border-l-red-500' : 'bg-white border-l-4 border-l-green-500 hover:bg-green-50';
                    $link = $ocupada ? BASE_URL . '/admin/salao/detalhes?id=' . $m['id'] : '#'; // Futuro: Link para abrir mesa manualmente
                    $total = $m['total_consumido'] ?? 0;
                    $tempo = $ocupada ? (int)((time() - strtotime($m['data_abertura'])) / 60) . ' min' : '';
                ?>
                
                <a href="<?php echo $link; ?>" class="block <?php echo $corCard; ?> shadow-sm rounded-lg p-4 border border-gray-200 transition transform hover:-translate-y-1 relative group">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-2xl font-black text-gray-700"><?php echo $m['numero']; ?></span>
                        <i class="fas fa-chair <?php echo $ocupada ? 'text-red-500' : 'text-green-500'; ?> text-xl"></i>
                    </div>

                    <?php if($ocupada): ?>
                        <div class="space-y-1">
                            <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase">
                                <i class="fas fa-users"></i> <?php echo $m['qtd_pessoas']; ?> Pessoas
                            </div>
                            <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase">
                                <i class="far fa-clock"></i> <?php echo $tempo; ?>
                            </div>
                            <div class="pt-2 mt-1 border-t border-gray-100">
                                <span class="block text-[10px] text-gray-400 uppercase font-bold">Consumo</span>
                                <span class="text-lg font-black text-gray-800">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                            </div>
                        </div>
                        <?php else: ?>
                            <div onclick="abrirMesaManual(<?php echo $m['id']; ?>, '<?php echo $m['numero']; ?>')" class="mt-4 text-center cursor-pointer">
                                <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full hover:bg-green-200 transition">ABRIR MESA</span>
                            </div>
                            <div class="mt-2 text-center text-[10px] text-gray-400">
                                Toque para iniciar
                            </div>
                        <?php endif; ?>
                </a>

                <?php endforeach; ?>

            </div>
        </div>
    </main>
</div>
<script>
function abrirMesaManual(id, numero) {
    // Pergunta o nome do cliente (opcional)
    let nome = prompt(`Abrir a ${numero} para qual cliente? (Deixe vazio para "Cliente Balcão")`);
    if (nome === null) return; // Cancelou

    const f = new FormData();
    f.append('mesa_id', id);
    f.append('nome_cliente', nome);

    fetch('<?php echo BASE_URL; ?>/admin/salao/abrirMesaManual', {
        method: 'POST',
        body: f
    })
    .then(r => r.json())
    .then(d => {
        if(d.ok) {
            // Recarrega para mostrar ocupada
            location.reload();
        } else {
            alert('Erro: ' + d.erro);
        }
    });
}
</script>