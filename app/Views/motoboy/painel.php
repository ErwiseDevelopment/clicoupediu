<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Minhas Entregas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> .safe-pb { padding-bottom: 100px; } </style>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="bg-gray-900 text-white p-4 fixed top-0 w-full z-50 shadow-md flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                <i class="fas fa-motorcycle text-gray-300"></i>
            </div>
            <div>
                <h1 class="font-bold text-sm leading-tight"><?php echo explode(' ', $_SESSION['motoboy_nome'])[0]; ?></h1>
                <p class="text-[10px] text-green-400 font-bold">● Rota Ativa</p>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/app-motoboy/sair" class="text-xs font-bold text-gray-400 border border-gray-600 px-3 py-1.5 rounded-lg">SAIR</a>
    </div>

    <div class="p-4 mt-20 safe-pb space-y-4">
        
        <?php if(empty($entregas)): ?>
            <div class="flex flex-col items-center justify-center mt-32 opacity-40">
                <i class="fas fa-check-circle text-7xl text-gray-400 mb-4"></i>
                <p class="font-bold text-gray-600 text-lg">Sem entregas pendentes</p>
                <button onclick="location.reload()" class="mt-6 text-blue-600 font-bold px-6 py-2 border border-blue-200 rounded-full bg-white shadow-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Atualizar
                </button>
            </div>
        <?php else: ?>
            
            <?php if(isset($linkRotaCompleta) && !empty($linkRotaCompleta)): ?>
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-4 shadow-xl text-white mb-6 transform transition hover:scale-[1.02]">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-bold text-lg flex items-center"><i class="fas fa-route mr-2"></i> Rota Otimizada</h2>
                    <span class="bg-blue-900/50 text-xs font-bold px-2 py-1 rounded-lg border border-blue-500/30">
                        <?php echo count($entregas); ?> Paradas
                    </span>
                </div>
                <p class="text-xs text-blue-100 mb-4 opacity-90">Ordem calculada para economizar tempo.</p>
                
                <a href="<?php echo $linkRotaCompleta; ?>" target="_blank" class="block w-full bg-white text-blue-700 font-black text-center py-3.5 rounded-xl shadow-sm hover:bg-gray-50 transition flex items-center justify-center gap-2">
                    <i class="fas fa-location-arrow"></i> INICIAR GPS COMPLETO
                </a>
            </div>
            <?php endif; ?>

            <?php 
            $contador = 1; 
            foreach($entregas as $e): 
                // Limpeza do Telefone
                $telLimpo = preg_replace('/[^0-9]/', '', $e['cliente_telefone']);
                $linkZap = "https://wa.me/55{$telLimpo}";

                // Verifica se tem GPS válido
                $temGPS = (!empty($e['lat_entrega']) && !empty($e['lng_entrega']) && $e['lat_entrega'] != 0);
                
                // Link Individual Inteligente: Usa GPS se tiver, senão usa Endereço
                if ($temGPS) {
                    // Abre direto na coordenada
                    $linkMapsSingle = "https://www.google.com/maps/search/?api=1&query={$e['lat_entrega']},{$e['lng_entrega']}";
                } else {
                    // Abre busca por texto
                    $enderecoUrl = urlencode("{$e['endereco_entrega']}, {$e['numero']} - {$e['bairro']}");
                    $linkMapsSingle = "https://www.google.com/maps/search/?api=1&query={$enderecoUrl}";
                }
            ?>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden relative transition-all group mb-4" id="card_<?php echo $e['id']; ?>">
                
                <div class="absolute top-0 left-0 bg-gray-900 text-white text-[10px] font-black px-3 py-1.5 rounded-br-xl z-10 shadow-sm border-b border-r border-gray-800 flex items-center gap-2">
                    <?php echo $contador++; ?>ª PARADA
                    <?php if(!$temGPS): ?>
                        <span class="text-yellow-400 animate-pulse" title="Sem localização exata"><i class="fas fa-exclamation-triangle"></i></span>
                    <?php endif; ?>
                </div>

                <div class="bg-gray-50 p-3 pt-8 border-b border-gray-100 flex justify-between items-center">
                    <span class="font-black text-lg text-gray-700">#<?php echo str_pad($e['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wide">
                        <?php echo $e['forma_pagamento']; ?>
                    </span>
                </div>

                <div class="p-5">
                    <div class="mb-5">
                        <h3 class="font-bold text-xl text-gray-800 mb-1 line-clamp-1"><?php echo $e['cliente_nome']; ?></h3>
                        
                        <div class="text-gray-600 text-sm leading-snug bg-gray-50 p-3 rounded-xl border border-gray-100 relative">
                            <?php if(!$temGPS): ?>
                                <p class="text-[10px] text-red-500 mb-1 font-bold"><i class="fas fa-map-marker-slash"></i> Endereço sem GPS (Verifique manual)</p>
                            <?php endif; ?>

                            <div class="absolute left-0 top-3 bottom-3 w-1 bg-gray-300 rounded-r"></div>
                            <p class="font-bold text-gray-800 ml-2"><?php echo $e['endereco_entrega']; ?>, <?php echo $e['numero']; ?></p>
                            <p class="ml-2 text-xs text-gray-500"><?php echo $e['bairro']; ?></p>
                            <?php if($e['complemento']): ?>
                                <p class="mt-2 ml-2 text-[10px] text-orange-600 font-bold bg-orange-50 p-1 rounded inline-block border border-orange-100">
                                    <i class="fas fa-info-circle"></i> <?php echo $e['complemento']; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">Cobrar</p>
                            <p class="text-2xl font-black text-gray-900">R$ <?php echo number_format($e['valor_total'], 2, ',', '.'); ?></p>
                        </div>
                        <?php if($e['troco_para'] > 0): ?>
                            <div class="text-right">
                                <p class="text-[10px] text-red-400 font-bold uppercase mb-0.5">Troco p/</p>
                                <p class="text-lg font-bold text-red-500">R$ <?php echo number_format($e['troco_para'], 2, ',', '.'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <a href="<?php echo $linkMapsSingle; ?>" target="_blank" class="bg-gray-50 text-gray-600 py-3 rounded-xl font-bold flex flex-col items-center justify-center hover:bg-gray-200 transition">
                            <i class="fas fa-map-marker-alt text-xl mb-1"></i>
                            <span class="text-[10px]">VER LOCAL</span>
                        </a>
                        <a href="<?php echo $linkZap; ?>" target="_blank" class="bg-green-50 text-green-600 py-3 rounded-xl font-bold flex flex-col items-center justify-center hover:bg-green-100 transition border border-green-100">
                            <i class="fab fa-whatsapp text-xl mb-1"></i>
                            <span class="text-[10px]">WHATSAPP</span>
                        </a>
                    </div>

                    <button onclick="finalizarEntrega(<?php echo $e['id']; ?>)" class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold shadow-lg flex items-center justify-center gap-2 hover:bg-black transition active:scale-95">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span>FINALIZAR ENTREGA</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';

        function finalizarEntrega(id) {
            if(!confirm("Tem certeza que entregou e recebeu o pagamento?")) return;

            const card = document.getElementById('card_' + id);
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';

            let formData = new FormData();
            formData.append('id', id);

            fetch(`${BASE_URL}/app-motoboy/finalizar`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if(data.ok) {
                    card.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        card.style.display = 'none';
                        const visiveis = document.querySelectorAll('[id^="card_"]:not([style*="display: none"])');
                        if(visiveis.length === 0) location.reload();
                    }, 300);
                } else {
                    alert("Erro: " + (data.erro || "Tente novamente"));
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            })
            .catch(err => {
                alert("Erro de conexão");
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            });
        }
    </script>
</body>
</html>