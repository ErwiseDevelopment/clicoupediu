<?php 
$titulo = "Mapa de Mesas"; 
require __DIR__ . '/../../partials/header.php'; 

// Calcula quantas mesas pendentes existem ao carregar a página (Para o Auto-Refresh)
$qtdPendentesAtual = 0;
foreach($mesas as $m) {
    if (!empty($m['sessao_id']) && isset($m['sessao_aprovada']) && $m['sessao_aprovada'] == 0) {
        $qtdPendentesAtual++;
    }
}
?>

<style>
    /* Animação suave para mesa pendente */
    @keyframes pulse-border {
        0% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.4); border-color: #facc15; }
        70% { box-shadow: 0 0 0 6px rgba(234, 179, 8, 0); border-color: #eab308; }
        100% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0); border-color: #facc15; }
    }
    .card-pendente {
        animation: pulse-border 2s infinite;
        background-color: #fefce8; /* Amarelo bem clarinho */
    }
    .card-padrao {
        transition: all 0.2s ease-in-out;
    }
    .card-padrao:active {
        transform: scale(0.96);
    }
</style>

<div class="flex h-screen bg-slate-50 overflow-hidden">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <div class="bg-white border-b border-slate-200 px-8 py-5 flex justify-between items-center shrink-0 z-10 shadow-sm">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Salão & Mesas</h2>
                <p class="text-sm text-slate-500 font-medium">Gerencie o atendimento em tempo real</p>
            </div>
            
            <div class="flex gap-3">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                    <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></div> Livre
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                    <div class="w-2.5 h-2.5 bg-rose-500 rounded-full"></div> Ocupada
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-yellow-700 bg-yellow-50 px-3 py-1.5 rounded-lg border border-yellow-200 shadow-sm">
                    <div class="w-2.5 h-2.5 bg-yellow-500 rounded-full animate-pulse"></div> Solicitando
                </div>
                
                <a href="<?php echo BASE_URL; ?>/admin/mesas" class="ml-4 bg-slate-800 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-slate-900 transition flex items-center gap-2 shadow-lg shadow-slate-300">
                    <i class="fas fa-cog"></i> Configurar
                </a>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8 bg-slate-50">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                
                <?php 
                // ARRAY PARA GUARDAR MESAS LIVRES PARA O MODAL DE TROCA
                $mesasLivres = [];

                foreach($mesas as $m): 
                    $temSessaoAtiva = !empty($m['sessao_id']);
                    $statusAprovacao = isset($m['sessao_aprovada']) ? $m['sessao_aprovada'] : 1;

                    $isPendente = ($temSessaoAtiva && $statusAprovacao == 0);
                    $isOcupada  = ($temSessaoAtiva && $statusAprovacao == 1);
                    $isLivre    = (!$temSessaoAtiva);

                    if ($isLivre) {
                        $mesasLivres[] = $m; // Guarda na lista de mesas livres para o modal
                    }

                    // Dados da mesa
                    $total = $m['total_consumido'] ?? 0;
                    $tempo = ($temSessaoAtiva && isset($m['data_abertura'])) ? (int)((time() - strtotime($m['data_abertura'])) / 60) . ' min' : '';
                    $qtdPessoas = $m['qtd_pessoas'] ?? 1;
                    $tipoConta = ($m['tipo_divisao'] ?? 'unica') == 'individual' ? 'Individual' : 'Única';
                    
                    if ($isPendente) {
                        $cardClass = 'card-pendente border-2 border-yellow-400';
                        $numColor = 'text-yellow-700';
                        $iconClass = 'fa-hand-paper text-yellow-500 animate-bounce';
                        $action = "aprovarMesa({$m['id']}, '{$m['numero']}')";
                    } elseif ($isOcupada) {
                        $cardClass = 'bg-white border border-slate-200 border-l-4 border-l-rose-500 shadow-sm hover:shadow-md';
                        $numColor = 'text-slate-700';
                        $iconClass = 'fa-users text-rose-500';
                        $action = "location.href='" . BASE_URL . "/admin/salao/detalhes?id=" . $m['id'] . "'";
                    } else {
                        $cardClass = 'bg-white border border-slate-200 border-l-4 border-l-emerald-500 shadow-sm hover:shadow-md opacity-90 hover:opacity-100';
                        $numColor = 'text-slate-600';
                        $iconClass = 'fa-check-circle text-emerald-500';
                        $action = "abrirMesaManual({$m['id']}, '{$m['numero']}')";
                    }
                ?>
                
                <div onclick="<?php echo $action; ?>" 
                     class="card-padrao relative rounded-xl p-5 cursor-pointer min-h-[11rem] flex flex-col justify-between select-none <?php echo $cardClass; ?>">
                    
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mesa</span>
                            <div class="text-3xl font-black <?php echo $numColor; ?> leading-none mt-1">
                                <?php echo str_pad($m['numero'], 2, '0', STR_PAD_LEFT); ?>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50">
                                <i class="fas <?php echo $iconClass; ?> text-lg"></i>
                            </div>
                            <?php if($isOcupada): ?>
                                <button onclick="abrirModalTroca(<?= $m['id'] ?>, '<?= $m['numero'] ?>'); event.stopPropagation();" 
                                        class="text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-1 rounded transition shadow-sm z-10 font-bold border border-slate-200" title="Trocar Cliente de Mesa">
                                    <i class="fas fa-exchange-alt text-blue-500"></i> Trocar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <?php if($isPendente): ?>
                            <div class="flex items-center justify-center gap-2 bg-yellow-100/50 p-2 rounded-lg mt-3 border border-yellow-200">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                                </span>
                                <span class="text-[10px] font-black text-yellow-700 uppercase">Aprovar Agora</span>
                            </div>

                        <?php elseif($isOcupada): ?>
                            <div class="flex flex-col gap-1.5 mt-2">
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-500 uppercase">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-user-friends"></i> <?= $qtdPessoas ?> Pessoas</span>
                                    <span class="flex items-center gap-1.5"><i class="far fa-clock"></i> <?= $tempo ?></span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase mb-1">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-receipt"></i> Conta <?= $tipoConta ?></span>
                                </div>
                                <div class="flex justify-between items-end border-t border-slate-100/80 pt-2 mt-1">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Consumo</span>
                                    <span class="text-[15px] font-black text-slate-800">R$ <?= number_format($total, 2, ',', '.') ?></span>
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="text-center group mt-4">
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-4 py-2 rounded-full group-hover:bg-emerald-100 transition border border-emerald-100">
                                    <i class="fas fa-plus mr-1"></i> ABRIR MESA
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </main>
</div>

<div id="modalTrocarMesa" class="fixed inset-0 bg-black/50 z-[999] hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform transition-all scale-100">
        <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="fas fa-exchange-alt text-blue-600"></i> Trocar de Mesa
        </h3>
        <p class="text-sm text-gray-500 mb-6">Transferindo da Mesa <b id="nomeMesaOrigem" class="text-blue-600"></b> para:</p>
        
        <form id="formTrocarMesa" onsubmit="confirmarTrocaMesa(event)">
            <input type="hidden" id="trocarMesaIdOrigem" name="mesa_origem">
            
            <div class="space-y-3">
                <?php if(empty($mesasLivres)): ?>
                    <div class="p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100">
                        Não há mesas livres no momento para realizar a troca.
                    </div>
                <?php else: ?>
                    <div class="relative">
                        <select name="mesa_destino" id="selectMesaDestino" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none font-bold text-gray-700" required>
                            <option value="" disabled selected>Selecione uma mesa livre...</option>
                            <?php foreach($mesasLivres as $ml): ?>
                                <option value="<?= $ml['id'] ?>">Mesa <?= $ml['numero'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="button" onclick="fecharModalTroca()" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition" <?= empty($mesasLivres) ? 'disabled' : '' ?>>
                    Transferir
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// --- FUNÇÕES DE ABERTURA E APROVAÇÃO ---
function aprovarMesa(id, numero) {
    if(!confirm(`⚠️ CLIENTE AGUARDANDO:\n\nDeseja APROVAR a abertura da mesa ${numero} e liberar o cardápio?`)) return;
    const f = new FormData(); f.append('mesa_id', id);
    fetch('<?php echo BASE_URL; ?>/admin/salao/aprovarSessao', { method: 'POST', body: f })
    .then(r => r.json()).then(d => { if(d.ok) location.reload(); else alert('Erro: ' + (d.erro || 'Falha ao aprovar')); });
}

function abrirMesaManual(id, numero) {
    let nome = prompt(`Abrir a ${numero} para qual cliente? (Deixe vazio para "Cliente Balcão")`);
    if (nome === null) return; 
    const f = new FormData(); f.append('mesa_id', id); f.append('nome_cliente', nome);
    fetch('<?php echo BASE_URL; ?>/admin/salao/abrirMesaManual', { method: 'POST', body: f })
    .then(r => r.json()).then(d => { if(d.ok) location.reload(); else alert('Erro: ' + d.erro); });
}

// --- FUNÇÕES DE TROCA DE MESA ---
function abrirModalTroca(idOrigem, numeroOrigem) {
    document.getElementById('trocarMesaIdOrigem').value = idOrigem;
    document.getElementById('nomeMesaOrigem').innerText = numeroOrigem;
    document.getElementById('modalTrocarMesa').classList.remove('hidden');
}

function fecharModalTroca() {
    document.getElementById('modalTrocarMesa').classList.add('hidden');
}

function confirmarTrocaMesa(e) {
    e.preventDefault();
    const f = new FormData(document.getElementById('formTrocarMesa'));

    fetch('<?php echo BASE_URL; ?>/admin/salao/trocarMesa', {
        method: 'POST',
        body: f
    })
    .then(r => r.json())
    .then(d => {
        if(d.ok) location.reload();
        else alert('Erro: ' + (d.erro || 'Falha ao transferir a mesa'));
    });
}

// --- SISTEMA DE AUTO-REFRESH (ATUALIZAÇÃO AUTOMÁTICA) ---
let pendentesConhecidos = <?= $qtdPendentesAtual ?>;

setInterval(() => {
    fetch('<?= BASE_URL ?>/admin/salao/apiChecarPendentes')
    .then(r => r.json())
    .then(d => {
        // Se o número de mesas pedindo aprovação for DIFERENTE do que temos na tela, recarrega a página.
        if (d.pendentes !== pendentesConhecidos) {
            location.reload();
        }
    })
    .catch(err => console.log("Aguardando conexão com o servidor..."));
}, 5000); // Executa a cada 5 segundos
</script>