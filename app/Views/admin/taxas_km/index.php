<?php 
$titulo = "Mapa de Entregas";

// Carrega Leaflet (Mapas)
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* O mapa ocupa toda a altura disponível */
    #mapa_full { height: 100%; width: 100%; z-index: 0; }
    
    /* Estilo dos círculos de raio */
    .raio-label {
        background: rgba(0, 128, 0, 0.7);
        color: white;
        padding: 2px 5px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 10px;
    }
</style>

<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-white">
    
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex relative overflow-hidden">
        
        <div class="flex-1 relative h-full">
            <div id="mapa_full"></div>
            
            <div class="absolute top-4 left-4 z-[400]">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="bg-white px-4 py-2 rounded-lg shadow-md text-gray-700 font-bold hover:bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> <span>Voltar</span>
                </a>
            </div>
        </div>

        <div class="w-96 bg-white shadow-2xl z-10 flex flex-col border-l border-gray-200 h-full">
            
            <div class="p-6 border-b border-gray-100 bg-white">
                <h2 class="font-bold text-xl text-gray-800">Configurações de Entrega</h2>
                <p class="text-sm text-gray-500 mt-1">Defina as áreas e taxas da sua loja.</p>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50">
                
                <div class="bg-white p-4 rounded-lg border border-blue-100 shadow-sm">
                    <h3 class="font-bold text-blue-900 text-sm mb-2 flex items-center">
                        <i class="fas fa-store mr-2"></i> Endereço da Loja
                    </h3>
                    <p class="text-xs text-gray-600 mb-3 truncate font-medium" title="<?php echo $config['endereco_completo']; ?>">
                        <?php echo $config['endereco_completo'] ? $config['endereco_completo'] : 'Endereço não configurado'; ?>
                    </p>
                    
                    <form action="<?php echo BASE_URL; ?>/admin/taxas-km/salvarLocalizacao" method="POST">
                        <div class="flex gap-2 mb-2">
                            <input type="text" id="endereco_busca" name="endereco" value="<?php echo $config['endereco_completo']; ?>" 
                                   class="w-full text-xs border border-gray-300 rounded p-2 focus:border-blue-500 outline-none" placeholder="Buscar novo endereço...">
                            <button type="button" onclick="buscarEndereco()" class="bg-blue-600 text-white px-3 rounded hover:bg-blue-700 transition">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <input type="hidden" id="lat" name="lat" value="<?php echo $config['lat']; ?>">
                        <input type="hidden" id="lng" name="lng" value="<?php echo $config['lng']; ?>">
                        <button type="submit" class="w-full text-xs bg-blue-50 text-blue-700 font-bold py-2 rounded border border-blue-200 hover:bg-blue-100 transition">
                            Atualizar Ponto Central
                        </button>
                    </form>
                </div>

                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-700 text-sm">Raios de Entrega</h3>
                        <button onclick="limparForm()" class="text-xs text-blue-600 hover:underline font-bold">+ Novo Raio</button>
                    </div>

                    <form action="<?php echo BASE_URL; ?>/admin/taxas-km/salvarFaixa" method="POST">
                        <input type="hidden" name="id" id="faixa_id">
                        
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Km Min</label>
                                <input type="number" step="0.1" name="km_min" id="km_min" required placeholder="0" class="w-full border-gray-300 rounded p-2 text-center text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Km Max</label>
                                <input type="number" step="0.1" name="km_max" id="km_max" required placeholder="3" class="w-full border-gray-300 rounded p-2 text-center text-sm">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tempo (min)</label>
                                <input type="number" name="tempo" id="tempo" value="30" class="w-full border-gray-300 rounded p-2 text-center text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Preço (R$)</label>
                                <input type="text" name="valor" id="valor" required placeholder="0,00" class="w-full border-gray-300 rounded p-2 text-center text-sm font-bold text-green-600" onkeyup="mascaraMoeda(this)">
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-green-600 text-white py-2 rounded font-bold text-xs hover:bg-green-700 shadow-sm transition">
                                SALVAR RAIO
                            </button>
                            <button type="button" id="btn_cancelar" onclick="limparForm()" class="hidden px-3 bg-gray-100 rounded text-gray-600 hover:bg-gray-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="space-y-3 pb-6">
                    <?php if(empty($faixas)): ?>
                        <p class="text-xs text-gray-400 text-center py-4">Nenhum raio configurado.</p>
                    <?php else: ?>
                        <?php foreach($faixas as $f): ?>
                            <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm hover:border-blue-400 transition group">
                                
                                <div class="flex items-center gap-3">
                                    <div class="bg-gray-100 w-9 h-9 rounded-full flex items-center justify-center text-gray-600 text-xs font-bold border border-gray-200">
                                        <?php echo intval($f['km_max']); ?>km
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Até <?php echo $f['km_max']; ?> km</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs font-bold text-gray-800 bg-gray-100 px-1.5 rounded"><?php echo $f['tempo_estimado']; ?> min</span>
                                            <span class="text-xs font-bold text-green-600">R$ <?php echo number_format($f['valor'], 2, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick='editar(<?php echo json_encode($f); ?>)' class="p-1.5 text-blue-500 hover:bg-blue-50 rounded"><i class="fas fa-pen text-xs"></i></button>
                                    <a href="<?php echo BASE_URL; ?>/admin/taxas-km/excluir?id=<?php echo $f['id']; ?>" class="p-1.5 text-red-500 hover:bg-red-50 rounded" onclick="return confirm('Excluir?')"><i class="fas fa-trash text-xs"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
    // 1. DADOS PHP PARA JS
    var latLoja = <?php echo $config['lat'] ? $config['lat'] : '-23.5505'; ?>;
    var lngLoja = <?php echo $config['lng'] ? $config['lng'] : '-46.6333'; ?>;
    var faixas = <?php echo json_encode($faixas); ?>;

    // 2. INICIA O MAPA
    var map = L.map('mapa_full', { zoomControl: false }).setView([latLoja, lngLoja], 13);
    
    L.control.zoom({ position: 'topright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // 3. DESENHA A LOJA
    var lojaIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    
    var marker = L.marker([latLoja, lngLoja], {icon: lojaIcon, draggable: true}).addTo(map);
    marker.bindPopup("<b>Sua Loja</b><br>Ponto de partida.").openPopup();

    marker.on('dragend', function(event) {
        var pos = marker.getLatLng();
        document.getElementById('lat').value = pos.lat;
        document.getElementById('lng').value = pos.lng;
    });

    // 4. DESENHA OS CÍRCULOS
    if(faixas && faixas.length > 0) {
        faixas.sort((a,b) => b.km_max - a.km_max); // Desenha do maior para o menor

        faixas.forEach(function(f) {
            var raioMetros = parseFloat(f.km_max) * 1000;
            
            L.circle([latLoja, lngLoja], {
                color: '#16a34a',       // Verde mais bonito (Tailwind green-600)
                fillColor: '#4ade80',   // Verde claro (Tailwind green-400)
                fillOpacity: 0.15,
                weight: 1.5,
                radius: raioMetros
            }).addTo(map).bindPopup(`
                <div class="text-center">
                    <b class="text-green-700">Raio: ${f.km_max} km</b><br>
                    Taxa: R$ ${parseFloat(f.valor).toFixed(2)}<br>
                    Tempo: ${f.tempo_estimado} min
                </div>
            `);
        });
        
        // Ajusta o zoom para ver o maior círculo
        var maiorRaio = faixas[0].km_max * 1000;
        var group = new L.featureGroup([L.circle([latLoja, lngLoja], {radius: maiorRaio})]);
        map.fitBounds(group.getBounds());
    }

    // 5. FUNÇÕES
    function buscarEndereco() {
        var endereco = document.getElementById('endereco_busca').value;
        if(!endereco) return;
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${endereco}`)
            .then(r => r.json())
            .then(data => {
                if(data.length > 0) {
                    var lat = data[0].lat;
                    var lon = data[0].lon;
                    map.setView([lat, lon], 14);
                    marker.setLatLng([lat, lon]);
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lon;
                } else {
                    alert('Endereço não encontrado');
                }
            });
    }

    function mascaraMoeda(i) {
        var v = i.value.replace(/\D/g,'');
        v = (v/100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
        v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
        i.value = v;
    }

    function editar(f) {
        document.getElementById('faixa_id').value = f.id;
        document.getElementById('km_min').value = f.km_min;
        document.getElementById('km_max').value = f.km_max;
        document.getElementById('tempo').value = f.tempo_estimado;
        document.getElementById('valor').value = parseFloat(f.valor).toFixed(2).replace('.', ',');
        document.getElementById('btn_cancelar').classList.remove('hidden');
    }

    function limparForm() {
        document.getElementById('faixa_id').value = '';
        document.getElementById('km_min').value = '';
        document.getElementById('km_max').value = '';
        document.getElementById('tempo').value = '30';
        document.getElementById('valor').value = '';
        document.getElementById('btn_cancelar').classList.add('hidden');
    }
</script>