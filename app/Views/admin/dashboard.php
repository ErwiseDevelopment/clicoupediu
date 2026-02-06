<?php 
$titulo = "Dashboard Financeiro | MK Gestor";
require __DIR__ . '/../partials/header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="md:hidden flex justify-between items-center p-4 bg-white shadow-sm z-20">
            <span class="font-bold text-lg text-gray-700">MK Gestor</span>
            <button class="text-gray-600"><i class="fas fa-bars"></i></button>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 scroll-smooth">
            
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Painel Gerencial 🚀</h2>
                    <p class="text-sm text-gray-500">
                        Análise de 
                        <strong class="text-blue-600"><?php echo date('d/m/Y', strtotime($dataInicio)); ?></strong> 
                        até 
                        <strong class="text-blue-600"><?php echo date('d/m/Y', strtotime($dataFim)); ?></strong>
                    </p>
                </div>

                <form method="GET" class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">DE</span>
                        <input type="date" name="data_inicio" value="<?php echo $dataInicio; ?>" 
                               class="pl-8 pr-2 py-2 rounded-lg border-0 bg-white text-sm font-bold text-gray-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-sm">
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">ATÉ</span>
                        <input type="date" name="data_fim" value="<?php echo $dataFim; ?>" 
                               class="pl-9 pr-2 py-2 rounded-lg border-0 bg-white text-sm font-bold text-gray-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-sm">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-blue-700 transition shadow-md shadow-blue-200">
                        <i class="fas fa-filter"></i>
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition">
                    <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Faturamento</p>
                    <h3 class="text-2xl font-black text-gray-800">R$ <?php echo number_format($vendasPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-xs text-green-600 font-bold mt-2 bg-green-50 inline-block px-2 py-0.5 rounded">
                        <i class="fas fa-receipt mr-1"></i> <?php echo $dadosOperacionais['qtd_pedidos'] ?? 0; ?> vendas
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition">
                    <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Contas Pagas</p>
                    <h3 class="text-2xl font-black text-gray-800">R$ <?php echo number_format($despesasPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-xs text-red-500 font-bold mt-2 bg-red-50 inline-block px-2 py-0.5 rounded">
                        <i class="fas fa-arrow-down mr-1"></i> Saídas
                    </div>
                </div>

                <?php $corSaldo = $saldoPeriodo >= 0 ? 'text-blue-600' : 'text-red-600'; ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition">
                    <div class="absolute right-0 top-0 h-full w-1 <?php echo $saldoPeriodo >= 0 ? 'bg-blue-500' : 'bg-red-500'; ?>"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Lucro Operacional</p>
                    <h3 class="text-2xl font-black <?php echo $corSaldo; ?>">R$ <?php echo number_format($saldoPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-xs text-gray-400 mt-2">
                        Ticket Médio: <b>R$ <?php echo number_format($dadosOperacionais['ticket_medio'] ?? 0, 2, ',', '.'); ?></b>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition">
                    <div class="absolute right-0 top-0 h-full w-1 bg-yellow-400"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Previsão (Pendente)</p>
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="block text-xs font-bold text-green-600">Receber</span>
                            <span class="text-sm font-black text-gray-800">R$ <?php echo number_format($aReceber, 2, ',', '.'); ?></span>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs font-bold text-red-500">Pagar</span>
                            <span class="text-sm font-black text-gray-800">R$ <?php echo number_format($aPagar, 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-blue-600"></i> Fluxo de Caixa
                        </h3>
                    </div>
                    <div class="h-64 w-full">
                        <canvas id="graficoFluxo"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 text-center">Receita por Canal</h3>
                    <div class="h-48 flex justify-center relative">
                        <canvas id="graficoPagto"></canvas>
                    </div>
                    <div class="mt-4 space-y-1">
                        <?php foreach($formasPagamento as $fp): ?>
                            <div class="flex justify-between text-xs border-b border-gray-50 pb-1">
                                <span class="capitalize font-bold text-gray-600"><?php echo $fp['forma_pagamento']; ?></span>
                                <span class="font-bold text-gray-800">R$ <?php echo number_format($fp['total'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-trophy text-yellow-500"></i> Campeões de Venda (Faturamento)
                    </h3>
                    <div class="h-60 w-full">
                        <canvas id="graficoTop"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-clock text-indigo-500"></i> Horários de Maior Venda
                    </h3>
                    <div class="h-60 w-full">
                        <canvas id="graficoHoras"></canvas>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<script>
    // --- DADOS PHP PARA JS ---
    const fluxoData = <?php echo json_encode($fluxoCaixa ?? []); ?>;
    const pagtoData = <?php echo json_encode($formasPagamento ?? []); ?>;
    const topData   = <?php echo json_encode($topProdutos ?? []); ?>;
    const horasData = <?php echo json_encode($vendasPorHora ?? []); ?>;

    // 1. FLUXO DE CAIXA
    if(document.getElementById('graficoFluxo')) {
        new Chart(document.getElementById('graficoFluxo').getContext('2d'), {
            type: 'bar',
            data: {
                labels: fluxoData.map(d => d.data),
                datasets: [
                    { label: 'Entradas', data: fluxoData.map(d => d.entrada), backgroundColor: '#10B981', borderRadius: 4 },
                    { label: 'Saídas', data: fluxoData.map(d => d.saida), backgroundColor: '#EF4444', borderRadius: 4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } } } }
        });
    }

    // 2. PAGAMENTOS
    if(document.getElementById('graficoPagto')) {
        new Chart(document.getElementById('graficoPagto').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pagtoData.map(d => d.forma_pagamento),
                datasets: [{ data: pagtoData.map(d => d.total), backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#6366F1'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
        });
    }

    // 3. TOP PRODUTOS (Horizontal Bar)
    if(document.getElementById('graficoTop')) {
        new Chart(document.getElementById('graficoTop').getContext('2d'), {
            type: 'bar',
            indexAxis: 'y', // Barra horizontal
            data: {
                labels: topData.map(d => d.nome.substring(0, 15) + '...'),
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: topData.map(d => d.total_faturado),
                    backgroundColor: '#F59E0B',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    // 4. VENDAS POR HORA (Line)
    if(document.getElementById('graficoHoras')) {
        new Chart(document.getElementById('graficoHoras').getContext('2d'), {
            type: 'line',
            data: {
                labels: horasData.map(d => d.hora + 'h'),
                datasets: [{
                    label: 'Vendas (R$)',
                    data: horasData.map(d => d.total),
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
        });
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>