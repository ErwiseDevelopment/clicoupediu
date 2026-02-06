<?php 
$titulo = "Financeiro | ClicouPediu";
$basePath = dirname(__DIR__, 2);
require $basePath . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php'; 
?>

<div class="flex h-screen bg-slate-50 font-sans text-slate-900">
    
    <?php require $basePath . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-6xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Assinatura & Planos</h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">Gerencie sua licença e renove seu acesso.</p>
                </div>
                <button onclick="abrirModalPagamento()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center gap-2 transform active:scale-95">
                    <i class="fas fa-sync-alt"></i> RENOVAR ACESSO
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between relative overflow-hidden group">
                    <?php 
                        $licencaTipo = $empresa['licenca_tipo'] ?? 'TESTE';
                        $dataValidade = $empresa['licenca_validade'] ?? null;
                        
                        $hoje = new DateTime(date('Y-m-d'));
                        $vencimento = $dataValidade ? new DateTime($dataValidade) : null;
                        
                        $statusTitulo = "SEM LICENÇA";
                        $statusDesc = "Nenhuma data definida";
                        $statusCor = "text-gray-500";
                        $bgIcone = "bg-gray-50 text-gray-400";
                        $icone = "fa-question-circle";
                        $piscar = false;

                        if ($licencaTipo === 'VIP') {
                            $statusTitulo = "VIP VITALÍCIO";
                            $statusDesc = "Acesso ilimitado garantido";
                            $statusCor = "text-indigo-600";
                            $bgIcone = "bg-indigo-50 text-indigo-500";
                            $icone = "fa-crown";
                        } elseif ($vencimento) {
                            $diferenca = $hoje->diff($vencimento);
                            $dias = $diferenca->days;
                            $isInvertido = $diferenca->invert; 

                            if ($isInvertido) {
                                $statusTitulo = "VENCIDO";
                                $statusDesc = $dias == 1 ? "Expirou ontem" : "Expirou há {$dias} dias";
                                $statusCor = "text-red-600";
                                $bgIcone = "bg-red-50 text-red-500";
                                $icone = "fa-times-circle";
                                $piscar = true; 
                            } elseif ($dias <= 7) {
                                $statusTitulo = "EXPIRA EM BREVE";
                                $statusDesc = $dias == 0 ? "Vence HOJE!" : "Restam apenas {$dias} dias";
                                $statusCor = "text-orange-500";
                                $bgIcone = "bg-orange-50 text-orange-500";
                                $icone = "fa-exclamation-triangle";
                            } else {
                                $statusTitulo = "ATIVO";
                                $statusDesc = "Válido até " . $vencimento->format('d/m/Y');
                                $statusCor = "text-green-600";
                                $bgIcone = "bg-green-50 text-green-500";
                                $icone = "fa-check-circle";
                            }
                        }
                    ?>

                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status da Assinatura</p>
                        <h3 class="text-xl font-black <?php echo $statusCor; ?> flex items-center gap-2">
                            <?php echo $statusTitulo; ?>
                            <?php if($piscar): ?>
                                <span class="flex h-3 w-3 relative">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                            <?php endif; ?>
                        </h3>
                        <p class="text-xs font-bold text-slate-500 mt-1"><?php echo $statusDesc; ?></p>
                    </div>

                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl transition-all duration-300 group-hover:scale-110 shadow-sm <?php echo $bgIcone; ?>">
                        <i class="fas <?php echo $icone; ?>"></i>
                    </div>
                    <div class="absolute left-0 top-0 bottom-0 w-1 <?php echo str_replace('text-', 'bg-', $statusCor); ?>"></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Plano Atual</p>
                        <h3 class="text-xl font-black text-slate-800">Profissional</h3>
                        <p class="text-xs font-bold text-slate-500 mt-1">R$ <?php echo number_format($empresa['valor_mensalidade'] ?? 230, 2, ',', '.'); ?> / mês</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-layer-group"></i></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ciclo</p>
                        <h3 class="text-xl font-black text-slate-800">Mensal</h3>
                        <p class="text-xs font-bold text-slate-500 mt-1">Renovação Automática</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-white">
                    <h3 class="font-bold text-lg text-slate-800">Histórico Financeiro</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-wider font-bold">
                                <th class="p-5">Data</th>
                                <th class="p-5">Valor</th>
                                <th class="p-5">Método</th>
                                <th class="p-5">Status</th>
                                <th class="p-5 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if(empty($faturas)): ?>
                                <tr><td colspan="5" class="p-8 text-center text-slate-400 text-sm font-medium">Nenhuma fatura encontrada.</td></tr>
                            <?php else: ?>
                                <?php foreach($faturas as $fat): ?>
                                    <?php 
                                        // Tradução visual dos status
                                        $stClass = 'bg-slate-100 text-slate-600';
                                        $stLabel = $fat['status'];
                                        if($fat['status'] == 'CONFIRMED' || $fat['status'] == 'RECEIVED' || $fat['status'] == 'PAGO') {
                                            $stClass = 'bg-green-100 text-green-700';
                                            $stLabel = 'PAGO';
                                        } elseif($fat['status'] == 'PENDENTE') {
                                            $stClass = 'bg-yellow-100 text-yellow-700';
                                        } elseif($fat['status'] == 'OVERDUE') {
                                            $stClass = 'bg-red-100 text-red-700';
                                            $stLabel = 'VENCIDO';
                                        }
                                    ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-5 text-sm font-semibold text-slate-600"><?= date('d/m/Y', strtotime($fat['data_criacao'])) ?></td>
                                    <td class="p-5 text-sm font-black text-slate-800">R$ <?= number_format($fat['valor'], 2, ',', '.') ?></td>
                                    <td class="p-5 text-xs font-bold text-slate-500 uppercase"><?= $fat['forma_pagamento'] ?></td>
                                    <td class="p-5"><span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase <?= $stClass ?>"><?= $stLabel ?></span></td>
                                    <td class="p-5 text-right">
                                        <a href="<?= $fat['url_pagamento'] ?>" target="_blank" class="text-blue-600 hover:underline text-xs font-bold">Ver Fatura</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="modalPagamento" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all">
            
            <button onclick="fecharModalPagamento()" class="absolute top-4 right-4 text-slate-400 hover:text-red-500 p-2">
                <i class="fas fa-times text-lg"></i>
            </button>

            <div class="p-8">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-black text-slate-900">Renovar Assinatura</h3>
                    <p class="text-sm text-slate-500 mt-1">Escolha como deseja pagar.</p>
                </div>

                <form id="formPagamento" onsubmit="gerarPagamentoAjax(event)">
                    
                    <div class="space-y-4 mb-6">
                        <label class="flex items-center p-4 border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                            <input type="radio" name="metodo" value="PIX" checked class="hidden">
                            <i class="fab fa-pix text-2xl text-teal-500 mr-4"></i>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">PIX Instantâneo</h4>
                                <p class="text-xs text-slate-400">Liberação imediata.</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                            <input type="radio" name="metodo" value="BOLETO" class="hidden">
                            <i class="fas fa-barcode text-2xl text-slate-500 mr-4"></i>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Boleto Bancário</h4>
                                <p class="text-xs text-slate-400">Compensação em até 2 dias.</p>
                            </div>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Possui Cupom?</label>
                        <div class="flex gap-2 mt-1">
                            <input type="text" id="inputCupom" name="cupom" placeholder="Ex: PROMO150" class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none text-sm font-bold uppercase text-slate-700">
                            <button type="button" onclick="aplicarCupom()" class="bg-slate-200 text-slate-600 hover:bg-slate-300 font-bold px-4 rounded-xl text-xs uppercase">Aplicar</button>
                        </div>
                        <p id="msgCupom" class="text-[10px] font-bold mt-2 hidden"></p>
                        <div id="resumoValor" class="mt-2 text-right hidden">
                            <span class="text-xs text-gray-400 line-through mr-2" id="valorOriginalDisplay"></span>
                            <span class="text-lg font-black text-green-600" id="valorFinalDisplay"></span>
                        </div>
                    </div>

                    <button type="submit" id="btnGerar" class="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-4 rounded-xl shadow-xl transition-all uppercase tracking-widest text-sm flex items-center justify-center gap-2">
                        GERAR PAGAMENTO <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modalResultado" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md transition-opacity"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden transform transition-all text-center">
            
            <button onclick="location.reload()" class="absolute top-4 right-4 text-slate-400 hover:text-red-500 p-2">
                <i class="fas fa-times text-lg"></i>
            </button>

            <div class="p-8">
                <div id="areaPix" class="hidden">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-teal-50 mb-4 text-teal-500">
                        <i class="fab fa-pix text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Pague com PIX</h3>
                    <p class="text-sm text-slate-500 mb-6">Escaneie o QR Code ou copie o código abaixo.</p>
                    
                    <div class="bg-slate-50 p-4 rounded-xl mb-4 border border-slate-100 inline-block">
                        <img id="imgQrCode" src="" alt="QR Code Pix" class="w-48 h-48 mx-auto mix-blend-multiply">
                    </div>
                    
                    <div class="relative mb-4">
                        <textarea id="textoCopiaCola" readonly class="w-full bg-slate-100 border border-slate-200 text-slate-600 text-xs p-3 rounded-xl h-20 resize-none outline-none focus:border-teal-500"></textarea>
                        <button onclick="copiarPix()" class="absolute bottom-2 right-2 bg-teal-500 text-white text-[10px] font-bold px-3 py-1 rounded-lg hover:bg-teal-600 shadow-sm">
                            COPIAR
                        </button>
                    </div>
                </div>

                <div id="areaBoleto" class="hidden">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-50 mb-4 text-orange-500">
                        <i class="fas fa-barcode text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Boleto Gerado</h3>
                    <p class="text-sm text-slate-500 mb-6">Copie o código de barras abaixo para pagar.</p>
                    
                    <div class="bg-slate-100 p-4 rounded-xl border border-slate-200 mb-4 relative">
                        <p id="linhaDigitavel" class="font-mono text-sm font-bold text-slate-700 break-all"></p>
                        <button onclick="copiarBoleto()" class="mt-3 w-full bg-slate-800 text-white text-xs font-bold py-2 rounded-lg hover:bg-slate-900">
                            COPIAR CÓDIGO
                        </button>
                    </div>
                    <a id="linkBoletoPdf" href="#" target="_blank" class="text-blue-600 font-bold text-sm hover:underline">
                        <i class="fas fa-file-pdf"></i> Baixar PDF do Boleto
                    </a>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-xs text-slate-400 mb-4">Assim que pagar, seu acesso é liberado automaticamente.</p>
                    <button onclick="location.reload()" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors">
                        JÁ FIZ O PAGAMENTO
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalPagamento() { document.getElementById('modalPagamento').classList.remove('hidden'); }
    function fecharModalPagamento() { document.getElementById('modalPagamento').classList.add('hidden'); }

    // --- LÓGICA AJAX PARA GERAR PAGAMENTO ---
    function gerarPagamentoAjax(e) {
        e.preventDefault(); 
        
        const btn = document.getElementById('btnGerar');
        const form = document.getElementById('formPagamento');
        const formData = new FormData(form);

        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> GERANDO...';
        btn.disabled = true;

        fetch('<?= BASE_URL ?>/admin/faturas/gerar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = 'GERAR PAGAMENTO <i class="fas fa-arrow-right"></i>';
            btn.disabled = false;

            if (data.erro) {
                alert("Erro: " + data.msg);
            } else if (data.sucesso) {
                fecharModalPagamento();
                
                const modalRes = document.getElementById('modalResultado');
                const areaPix = document.getElementById('areaPix');
                const areaBoleto = document.getElementById('areaBoleto');
                
                areaPix.classList.add('hidden');
                areaBoleto.classList.add('hidden');

                if (data.tipo === 'PIX') {
                    areaPix.classList.remove('hidden');
                    document.getElementById('imgQrCode').src = 'data:image/png;base64,' + data.pix_imagem;
                    document.getElementById('textoCopiaCola').value = data.pix_copia_cola;
                } else {
                    areaBoleto.classList.remove('hidden');
                    document.getElementById('linhaDigitavel').innerText = data.boleto_linha;
                    document.getElementById('linkBoletoPdf').href = data.url_fatura;
                }

                modalRes.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = 'TENTAR NOVAMENTE';
            btn.disabled = false;
            alert("Erro de comunicação com o servidor.");
        });
    }

    function copiarPix() {
        const texto = document.getElementById('textoCopiaCola');
        texto.select();
        document.execCommand('copy');
        alert('Copia e Cola copiado!');
    }

    function copiarBoleto() {
        const texto = document.getElementById('linhaDigitavel').innerText;
        navigator.clipboard.writeText(texto).then(() => {
            alert('Código de barras copiado!');
        });
    }

    // --- LÓGICA DE CUPOM ---
    function aplicarCupom() {
        const codigo = document.getElementById('inputCupom').value;
        const btn = document.querySelector('button[onclick="aplicarCupom()"]');
        const msg = document.getElementById('msgCupom');
        const resumo = document.getElementById('resumoValor');
        
        if(!codigo) return;
        
        btn.innerHTML = '...';
        btn.disabled = true;
        
        const formData = new FormData();
        formData.append('cupom', codigo);
        
        fetch('<?= BASE_URL ?>/admin/faturas/validarCupom', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = 'APLICAR';
            btn.disabled = false;
            
            msg.classList.remove('hidden', 'text-green-600', 'text-red-500');
            
            if(data.sucesso) {
                msg.innerText = data.mensagem;
                msg.classList.add('text-green-600');
                
                // Mostra valores
                resumo.classList.remove('hidden');
                document.getElementById('valorOriginalDisplay').innerText = 'R$ ' + data.valor_original;
                document.getElementById('valorFinalDisplay').innerText = 'R$ ' + data.novo_valor;
                
            } else {
                msg.innerText = data.mensagem;
                msg.classList.add('text-red-500');
                resumo.classList.add('hidden');
            }
        })
        .catch(err => {
            btn.innerHTML = 'APLICAR';
            btn.disabled = false;
            alert("Erro ao validar cupom.");
        });
    }
</script>

<?php require $basePath . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>