<?php 
if (!isset($_GET['data_inicio']) || !isset($_GET['data_fim'])) {
    $inicioMes = date('Y-m-01');
    $fimMes    = date('Y-m-t'); 
    header("Location: ?data_inicio={$inicioMes}&data_fim={$fimMes}");
    exit;
}

$titulo = "Dashboard Estratégico | clicoupediu";
require __DIR__ . '/../partials/header.php'; 

if (!isset($dataInicio)) $dataInicio = $_GET['data_inicio'];
if (!isset($dataFim))    $dataFim    = $_GET['data_fim'];

function zapLink($telefone, $nome, $tipo = 'vip') {
    $num = preg_replace('/\D/', '', $telefone);
    if($tipo == 'vip') {
        $msg = "Olá {$nome}, você é um dos nossos melhores clientes! Preparámos um cupão especial para o seu próximo pedido...";
    } else {
        $msg = "Olá {$nome}, que saudades! Já faz mais de uma semana desde o seu último pedido. Que tal aproveitar hoje com um desconto exclusivo?";
    }
    return "https://wa.me/55{$num}?text=" . rawurlencode($msg);
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
    
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="md:hidden flex justify-between items-center p-4 bg-white shadow-sm z-20">
            <span class="font-bold text-lg text-slate-800">clicoupediu.app</span>
            <button class="text-slate-600"><i class="fas fa-bars"></i></button>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6 scroll-smooth custom-scroll">
            
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Inteligência de Negócios 🚀</h2>
                    <p class="text-sm text-slate-500">
                        Análise de <strong class="text-blue-600"><?php echo date('d/m/Y', strtotime($dataInicio)); ?></strong> 
                        até <strong class="text-blue-600"><?php echo date('d/m/Y', strtotime($dataFim)); ?></strong>
                    </p>
                </div>

                <form method="GET" class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-200 w-full md:w-auto shadow-inner">
                    <div class="relative flex-1 md:w-32">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] font-bold">DE</span>
                        <input type="date" name="data_inicio" value="<?php echo $dataInicio; ?>" 
                               class="w-full pl-8 pr-2 py-2 rounded-xl border-0 bg-white text-xs font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-sm">
                    </div>
                    <div class="relative flex-1 md:w-32">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] font-bold">ATÉ</span>
                        <input type="date" name="data_fim" value="<?php echo $dataFim; ?>" 
                               class="w-full pl-9 pr-2 py-2 rounded-xl border-0 bg-white text-xs font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-sm">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-200 active:scale-95">
                        <i class="fas fa-filter"></i>
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-5 mb-6">
                <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-green-500"></div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Faturamento Bruto</p>
                    <h3 class="text-2xl font-black text-slate-800">R$ <?php echo number_format($vendasPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-[11px] text-green-600 font-bold mt-2 bg-green-50 inline-block px-2 py-1 rounded-lg">
                        <i class="fas fa-receipt mr-1"></i> <?php echo $dadosOperacionais['qtd_pedidos'] ?? 0; ?> concluídos
                    </div>
                </div>

                <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-red-500"></div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Despesas Pagas</p>
                    <h3 class="text-2xl font-black text-slate-800">R$ <?php echo number_format($despesasPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-[11px] text-red-500 font-bold mt-2 bg-red-50 inline-block px-2 py-1 rounded-lg">
                        <i class="fas fa-arrow-down mr-1"></i> Contas liquidadas
                    </div>
                </div>

                <?php $corSaldo = $saldoPeriodo >= 0 ? 'text-blue-600' : 'text-slate-800'; ?>
                <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1.5 <?php echo $saldoPeriodo >= 0 ? 'bg-blue-500' : 'bg-slate-800'; ?>"></div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lucro Operacional</p>
                    <h3 class="text-2xl font-black <?php echo $corSaldo; ?>">R$ <?php echo number_format($saldoPeriodo, 2, ',', '.'); ?></h3>
                    <div class="text-[11px] text-slate-500 mt-2 font-medium">
                        T. Médio: <b class="text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">R$ <?php echo number_format($dadosOperacionais['ticket_medio'] ?? 0, 2, ',', '.'); ?></b>
                    </div>
                </div>

                <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-red-100 relative overflow-hidden group flex flex-col justify-between">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-red-600"></div>
                    <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-2">Pedidos Cancelados</p>
                    <div>
                        <h3 class="text-xl font-black text-red-600 mb-1">R$ <?php echo number_format($dadosCancelados['valor'] ?? 0, 2, ',', '.'); ?></h3>
                        <div class="text-[11px] text-red-500 font-bold bg-red-50 inline-block px-2 py-1 rounded-lg">
                            <i class="fas fa-ban mr-1"></i> <?php echo $dadosCancelados['qtd'] ?? 0; ?> pedidos perdidos
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 mb-8">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-chart-line"></i></div>
                    Evolução do Faturamento Diário
                </h3>
                <div class="h-64 w-full">
                    <canvas id="graficoFaturamento"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 lg:col-span-2">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fas fa-clock"></i></div>
                        Vendas por Hora (Pico de Movimento)
                    </h3>
                    <div class="h-56 w-full"><canvas id="graficoHoras"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fas fa-wallet"></i></div>
                        Formas de Pagamento
                    </h3>
                    <div class="h-40 relative flex justify-center"><canvas id="graficoPagto"></canvas></div>
                    
                    <div class="mt-auto pt-4 space-y-1">
                        <?php foreach($vendasPorTipo as $tipo): ?>
                            <div class="flex justify-between text-[11px] border-b border-slate-50 pb-1">
                                <span class="uppercase font-bold text-slate-500"><?php echo $tipo['tipo_entrega']; ?></span>
                                <span class="font-bold text-slate-800">R$ <?php echo number_format($tipo['total'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm p-6 flex flex-col">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center"><i class="fas fa-map-location-dot"></i></div>
                        Heatmap: Bairros em Alta
                    </h3>
                    
                    <div class="space-y-3 flex-1">
                        <?php if(empty($rankingBairros)): ?>
                            <p class="text-xs text-slate-400 italic text-center mt-10">Sem dados de entrega.</p>
                        <?php else: ?>
                            <?php 
                                $maxBairro = $rankingBairros[0]['total']; 
                                foreach($rankingBairros as $index => $b): 
                                    $perc = ($b['total'] / $maxBairro) * 100;
                            ?>
                                <div>
                                    <div class="flex justify-between text-[11px] font-bold mb-1">
                                        <span class="text-slate-700 truncate pr-2">
                                            <?php echo $index + 1; ?>. <?php echo mb_strtoupper($b['bairro']); ?>
                                            <span class="text-[10px] text-slate-400 font-normal ml-1">(<?php echo $b['qtd']; ?> un)</span>
                                        </span>
                                        <span class="text-teal-600 shrink-0">R$ <?php echo number_format($b['total'], 2, ',', '.'); ?></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-teal-500 h-1.5 rounded-full" style="width: <?php echo $perc; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm p-6 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center"><i class="fas fa-star"></i></div>
                            Clientes VIP
                        </h3>
                        <div class="text-right">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Fidelidade</p>
                            <span class="text-sm font-black text-purple-600"><?php echo number_format($taxaRecorrencia, 1, ',', ''); ?>%</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 flex-1">
                        <?php if(empty($clientesVip)): ?>
                            <p class="text-xs text-slate-400 italic text-center mt-10">Sem vendas identificadas.</p>
                        <?php else: ?>
                            <?php foreach($clientesVip as $vip): ?>
                                <div class="flex items-center justify-between p-2 bg-slate-50 border border-slate-100 rounded-xl">
                                    <div class="overflow-hidden w-2/3">
                                        <p class="text-xs font-bold text-slate-800 truncate"><?php echo $vip['cliente_nome']; ?></p>
                                        <p class="text-[9px] font-bold text-slate-400"><?php echo $vip['qtd']; ?> pedidos feitos</p>
                                    </div>
                                    <a href="<?php echo zapLink($vip['cliente_telefone'], $vip['cliente_nome'], 'vip'); ?>" target="_blank" class="w-7 h-7 rounded-full bg-green-100 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition">
                                        <i class="fab fa-whatsapp text-xs"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-[1.5rem] border border-red-100 shadow-sm p-6 flex flex-col relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-red-50 rounded-full blur-2xl"></div>
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4 relative z-10">
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center"><i class="fas fa-heart-crack"></i></div>
                        Recuperação (> 10 Dias)
                    </h3>
                    
                    <div class="space-y-2 flex-1 relative z-10 custom-scroll overflow-y-auto">
                        <?php if(empty($clientesRisco)): ?>
                            <div class="text-center opacity-50 mt-6">
                                <i class="fas fa-shield-check text-3xl text-green-500 mb-1"></i>
                                <p class="text-[10px] font-bold text-slate-500">Todos ativos e a comprar!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($clientesRisco as $risco): 
                                $diasSumiu = (new \DateTime($risco['ultimo_pedido']))->diff(new \DateTime($dataFim))->days;
                            ?>
                                <div class="flex items-center justify-between p-2 bg-red-50/50 border border-red-100 rounded-xl">
                                    <div class="overflow-hidden w-2/3">
                                        <p class="text-xs font-bold text-slate-800 truncate"><?php echo $risco['cliente_nome']; ?></p>
                                        <p class="text-[9px] font-black text-red-500">Sumiu há <?php echo $diasSumiu; ?> dias</p>
                                    </div>
                                    <a href="<?php echo zapLink($risco['cliente_telefone'], $risco['cliente_nome'], 'risco'); ?>" target="_blank" class="w-7 h-7 shrink-0 rounded-full bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-500 hover:text-white transition shadow-sm">
                                        <i class="fab fa-whatsapp text-xs"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                
                <div class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i class="fas fa-motorcycle"></i></div>
                        Fecho de Motoboys
                    </h3>
                    <div class="bg-orange-50 rounded-xl p-4 border border-orange-100 flex justify-between items-center mb-4">
                        <div>
                            <p class="text-[10px] font-black text-orange-600 uppercase">Repasse Total</p>
                            <h3 class="text-2xl font-black text-orange-700">R$ <?php echo number_format($custoEntregas ?? 0, 2, ',', '.'); ?></h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-orange-400 uppercase">Corridas Totais</p>
                            <span class="text-xl font-black text-orange-500"><?php echo $qtdEntregasFeitas ?? 0; ?></span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 flex-1">
                        <?php foreach($rankingMotoboys as $index => $moto): ?>
                            <div class="flex justify-between items-center text-xs p-2 rounded-lg bg-slate-50 border border-slate-100">
                                <div>
                                    <span class="font-bold text-slate-800 text-sm"><?php echo $moto['nome']; ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold ml-1 px-2 py-0.5 bg-white rounded border border-slate-200"><?php echo $moto['qtd']; ?> entregas</span>
                                </div>
                                <span class="font-black text-orange-600 text-sm">R$ <?php echo number_format($moto['total_repasse'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-hamburger"></i></div>
                        Curva ABC (Top Produtos)
                    </h3>
                    <div class="h-64 w-full"><canvas id="graficoTop"></canvas></div>
                </div>

            </div>

        </main>
    </div>
</div>

<script>
    const fluxoData = <?php echo json_encode($fluxoCaixa ?? []); ?>;
    const topData   = <?php echo json_encode($topProdutos ?? []); ?>;
    const pagtoData = <?php echo json_encode($formasPagamento ?? []); ?>;
    const horasData = <?php echo json_encode($vendasPorHora ?? []); ?>;

    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // 1. FATURAMENTO DIÁRIO (GRÁFICO DE LINHA)
    if(document.getElementById('graficoFaturamento') && fluxoData.length > 0) {
        new Chart(document.getElementById('graficoFaturamento').getContext('2d'), {
            type: 'line',
            data: {
                labels: fluxoData.map(d => d.data),
                datasets: [{ 
                    label: 'Faturamento Bruto (R$)', 
                    data: fluxoData.map(d => d.faturamento), 
                    borderColor: '#2563eb', 
                    backgroundColor: 'rgba(37, 99, 235, 0.1)', 
                    borderWidth: 3,
                    tension: 0.4, 
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } }, 
                scales: { x: { grid: { display: false } }, y: { border: { display: false } } } 
            }
        });
    }

    // 2. HORÁRIOS DE VENDA
    if(document.getElementById('graficoHoras') && horasData.length > 0) {
        new Chart(document.getElementById('graficoHoras').getContext('2d'), {
            type: 'bar', // Barra fica melhor para ver o pico por hora exata
            data: {
                labels: horasData.map(d => d.hora + 'h'),
                datasets: [{ 
                    label: 'Vendas (R$)', 
                    data: horasData.map(d => d.total), 
                    backgroundColor: '#6366f1', 
                    borderRadius: 4 
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
        });
    }

    // 3. FORMAS DE PAGAMENTO
    if(document.getElementById('graficoPagto') && pagtoData.length > 0) {
        new Chart(document.getElementById('graficoPagto').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pagtoData.map(d => d.forma_pagamento.toUpperCase()),
                datasets: [{ data: pagtoData.map(d => d.total), backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
        });
    }

    // 4. TOP PRODUTOS
    if(document.getElementById('graficoTop') && topData.length > 0) {
        new Chart(document.getElementById('graficoTop').getContext('2d'), {
            type: 'bar', indexAxis: 'y',
            data: {
                labels: topData.map(d => d.nome.substring(0, 15) + '...'),
                datasets: [{ label: 'Faturamento', data: topData.map(d => d.total_faturado), backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { grid: { display: false } } } }
        });
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>