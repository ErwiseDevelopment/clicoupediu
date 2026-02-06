<?php $titulo = "Histórico de Vendas"; require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-100 font-sans">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <div class="bg-white px-6 py-4 border-b border-gray-200 shadow-sm z-10">
            <form class="flex flex-wrap items-end gap-4" method="GET" action="">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Data Início</label>
                    <input type="date" name="inicio" value="<?php echo $dataInicio; ?>" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Data Fim</label>
                    <input type="date" name="fim" value="<?php echo $dataFim; ?>" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition text-sm h-[38px]">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
                
                <a href="<?php echo BASE_URL; ?>/admin/pedidos" class="ml-auto text-gray-500 hover:text-gray-700 font-bold text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar ao Monitor
                </a>
            </form>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs text-gray-500 font-bold uppercase">Faturamento Total</div>
                    <div class="text-2xl font-black text-gray-800">R$ <?php echo number_format($totalFaturado, 2, ',', '.'); ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs text-gray-500 font-bold uppercase">Total Pedidos</div>
                    <div class="text-2xl font-black text-blue-600"><?php echo $totalPedidos; ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs text-gray-500 font-bold uppercase">Ticket Médio</div>
                    <div class="text-2xl font-black text-green-600">R$ <?php echo number_format($ticketMedio, 2, ',', '.'); ?></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs text-gray-500 font-bold uppercase">Taxas Entrega</div>
                    <div class="text-2xl font-black text-orange-500">R$ <?php echo number_format($totalDelivery, 2, ',', '.'); ?></div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">#ID</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Data/Hora</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Pagamento</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Total</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(empty($pedidos)): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic text-sm">Nenhum pedido encontrado neste período.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($pedidos as $p): 
                                // Cores dos status
                                $corStatus = 'bg-gray-100 text-gray-600';
                                if($p['status'] == 'finalizado') $corStatus = 'bg-green-100 text-green-700';
                                if($p['status'] == 'cancelado') $corStatus = 'bg-red-100 text-red-700';
                                if($p['status'] == 'analise') $corStatus = 'bg-yellow-100 text-yellow-700';
                                if($p['status'] == 'preparo') $corStatus = 'bg-orange-100 text-orange-700';
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm font-bold text-gray-700">#<?php echo str_pad($p['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo date('d/m/Y', strtotime($p['created_at'])); ?> <br>
                                    <span class="text-xs text-gray-400"><?php echo date('H:i', strtotime($p['created_at'])); ?></span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-bold text-gray-700"><?php echo $p['cliente_nome']; ?></div>
                                    <div class="text-xs text-gray-400"><?php echo $p['cliente_telefone']; ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $corStatus; ?>">
                                        <?php echo $p['status']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs uppercase font-bold text-gray-500">
                                    <?php echo $p['tipo_entrega']; ?>
                                </td>
                                <td class="px-4 py-3 text-xs uppercase text-gray-600">
                                    <?php echo $p['forma_pagamento']; ?>
                                </td>
                                <td class="px-4 py-3 text-sm font-black text-gray-800 text-right">
                                    R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="imprimirCupom(<?php echo $p['id']; ?>)" class="text-gray-400 hover:text-blue-600 transition" title="Reimprimir Cupom">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    function imprimirCupom(id) {
        const idFrame = 'frame_impressao_oculto';
        let oldFrame = document.getElementById(idFrame);
        if (oldFrame) oldFrame.remove();

        const iframe = document.createElement('iframe');
        iframe.id = idFrame;
        iframe.style.position = 'absolute';
        iframe.style.top = '-10000px';
        iframe.src = '<?php echo BASE_URL; ?>/admin/pedidos/imprimir?id=' + id;
        document.body.appendChild(iframe);
    }
</script>