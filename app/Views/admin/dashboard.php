<?php 
// Lógica de Filtro Automático
if (!isset($_GET['data_inicio']) || !isset($_GET['data_fim'])) {
    $inicioMes = date('Y-m-01');
    $fimMes    = date('Y-m-t'); 
    header("Location: ?data_inicio={$inicioMes}&data_fim={$fimMes}");
    exit;
}

$titulo = "Dashboard Estratégico | MK Gestor";
require __DIR__ . '/../partials/header.php'; 

if (!isset($dataInicio)) $dataInicio = $_GET['data_inicio'];
if (!isset($dataFim))    $dataFim    = $_GET['data_fim'];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="md:hidden flex justify-between items-center p-4 bg-white shadow-sm z-20">
            <span class="font-bold text-lg text-gray-700">MK Gestor</span>
            <button class="text-gray-600"><i class="fas fa-bars"></i></button>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6 scroll-smooth custom-scroll">
            
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Painel Estratégico 🚀</h2>
                    <p class="text-sm text-gray-500">
                        Análise de 
                        <strong class="text-indigo-600"><?php echo date('d/m/Y', strtotime($dataInicio)); ?></strong> 
                        até 
                        <strong class="text-indigo-600"><?php echo date('d/m/Y', strtotime($dataFim)); ?></strong>
                    </p>
                </div>

                <form method="GET" class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200 w-full md:w-auto">
                    <div class="relative flex-1 md:w-32">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-bold">DE</span>
                        <input type="date" name="data_inicio" value="<?php echo $dataInicio; ?>" 
                               class="w-full pl-7 pr-1 py-2 rounded-lg border-0 bg-white text-xs font-bold text-gray-600 focus:ring-2 focus:ring-indigo-100 outline-none shadow-sm">
                    </div>
                    <div class="relative flex-1 md:w-32">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-bold">ATÉ</span>
                        <input type="date" name="data_fim" value="<?php echo $dataFim; ?>" 
                               class="w-full pl-8 pr-1 py-2 rounded-lg border-0 bg-white text-xs font-bold text-gray-600 focus:ring-2 focus:ring-indigo-100 outline-none shadow-sm">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-indigo-700 transition shadow-md shadow-indigo-200">
                        <i class="fas fa-filter"></i>
                    </button>
                </form>
            </div>

            <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-3">Saúde Financeira</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-5 mb-8">
                
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Faturamento Bruto</p>
                    <h3 class="text-2xl font-black text-gray-800">R$ <?php echo number_format($vendasPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-[11px] text-green-600 font-bold mt-2 bg-green-50 inline-block px-2 py-0.5 rounded">
                        <i class="fas fa-receipt mr-1"></i> <?php echo $dadosOperacionais['qtd_pedidos'] ?? 0; ?> pedidos concluídos
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Despesas Pagas</p>
                    <h3 class="text-2xl font-black text-gray-800">R$ <?php echo number_format($despesasPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-[11px] text-red-500 font-bold mt-2 bg-red-50 inline-block px-2 py-0.5 rounded">
                        <i class="fas fa-arrow-down mr-1"></i> Contas liquidadas
                    </div>
                </div>

                <?php $corSaldo = $saldoPeriodo >= 0 ? 'text-indigo-600' : 'text-red-600'; ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1 <?php echo $saldoPeriodo >= 0 ? 'bg-indigo-500' : 'bg-red-500'; ?>"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Lucro Operacional</p>
                    <h3 class="text-2xl font-black <?php echo $corSaldo; ?>">R$ <?php echo number_format($saldoPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-xs text-gray-400 mt-2">
                        Ticket Médio: <b class="text-gray-700">R$ <?php echo number_format($dadosOperacionais['ticket_medio'] ?? 0, 2, ',', '.'); ?></b>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1 bg-yellow-400"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Previsão Pendente</p>
                    <div class="flex justify-between items-end border-b border-gray-50 pb-1 mb-1">
                        <span class="text-[11px] font-bold text-green-600">A Receber</span>
                        <span class="text-sm font-black text-gray-800">R$ <?php echo number_format($aReceber, 2, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-[11px] font-bold text-red-500">A Pagar</span>
                        <span class="text-sm font-black text-gray-800">R$ <?php echo number_format($aPagar, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col">
                    <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-motorcycle text-orange-500 bg-orange-100 p-2 rounded-lg"></i> Logística & Entregas
                        </h3>
                    </div>
                    <div class="p-5 flex-1 flex flex-col gap-4">
                        
                        <div class="bg-orange-50 rounded-xl p-4 flex justify-between items-center border border-orange-100">
                            <div>
                                <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-0.5">Taxas Geradas (Repasse)</p>
                                <h3 class="text-2xl font-black text-orange-700">R$ <?php echo number_format($custoEntregas, 2, ',', '.'); ?></h3>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-orange-400 uppercase mb-0.5">Entregas Feitas</p>
                                <span class="text-xl font-black text-orange-600"><?php echo $qtdEntregasFeitas; ?></span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 mb-2">Desempenho da Equipe</p>
                            <?php if(empty($rankingMotoboys)): ?>
                                <p class="text-xs text-gray-400 italic">Nenhuma entrega registrada no período.</p>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach($rankingMotoboys as $index => $moto): ?>
                                        <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg border border-gray-100 text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-black text-gray-400 w-4 text-center"><?php echo $index + 1; ?>º</span>
                                                <span class="font-bold text-gray-700 truncate max-w-[120px]"><?php echo $moto['nome']; ?></span>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs">
                                                <span class="bg-white border border-gray-200 px-2 py-0.5 rounded text-gray-500 font-bold"><?php echo $moto['qtd']; ?> un</span>
                                                <span class="font-black text-orange-600 w-16 text-right">R$ <?php echo number_format($moto['total_repasse'], 2, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col">
                    <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-bullseye text-purple-600 bg-purple-100 p-2 rounded-lg"></i> Análise de Marketing
                        </h3>
                    </div>
                    <div class="p-5 flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="flex flex-col justify-center items-center bg-purple-50 rounded-xl p-4 border border-purple-100">
                            <p class="text-[10px] font-black text-purple-500 uppercase tracking-widest text-center mb-2">Taxa de Fidelidade</p>
                            
                            <div class="relative w-24 h-24 rounded-full flex items-center justify-center bg-purple-200 mb-2" 
                                 style="background: conic-gradient(#9333ea <?php echo $taxaRecorrencia; ?>%, #e9d5ff 0);">
                                <div class="absolute w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center">
                                    <span class="text-xl font-black text-purple-700"><?php echo number_format($taxaRecorrencia, 1, ',', ''); ?>%</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-purple-600 font-bold text-center">Dos pedidos foram feitos<br>por clientes recorrentes.</p>
                        </div>

                        <div class="flex flex-col gap-3 justify-center">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Clientes Únicos Ativos</p>
                                <p class="text-2xl font-black text-gray-800"><?php echo $clientesUnicos; ?> <i class="fas fa-users text-sm text-gray-300"></i></p>
                            </div>
                            
                            <hr class="border-gray-100">

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Origem das Vendas</p>
                                <div class="space-y-1">
                                    <?php foreach($vendasPorTipo as $tipo): ?>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="capitalize font-bold text-gray-600"><?php echo $tipo['tipo_entrega']; ?></span>
                                            <span class="font-black text-gray-800">R$ <?php echo number_format($tipo['total'], 2, ',', '.'); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-6">
                        <i class="fas fa-chart-line text-blue-600"></i> Fluxo de Caixa (Entradas e Saídas)
                    </h3>
                    <div class="h-64 w-full">
                        <canvas id="graficoFluxo"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 text-center">Receita por Canal (Pagamento)</h3>
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
                    { label: 'Faturamento', data: fluxoData.map(d => d.entrada), backgroundColor: '#4F46E5', borderRadius: 4 },
                    { label: 'Contas Pagas', data: fluxoData.map(d => d.saida), backgroundColor: '#EF4444', borderRadius: 4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } } } }
        });
    }

    // 2. PAGAMENTOS (Canais)
    if(document.getElementById('graficoPagto')) {
        new Chart(document.getElementById('graficoPagto').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pagtoData.map(d => d.forma_pagamento),
                datasets: [{ data: pagtoData.map(d => d.total), backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
        });
    }

    // 3. TOP PRODUTOS (Horizontal Bar)
    if(document.getElementById('graficoTop')) {
        new Chart(document.getElementById('graficoTop').getContext('2d'), {
            type: 'bar',
            indexAxis: 'y',
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
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
        });
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>