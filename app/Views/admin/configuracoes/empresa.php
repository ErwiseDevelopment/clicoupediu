<?php 
$titulo = "Configurações da Empresa";
// Recupere a chave do seu arquivo de config ou mantenha aqui
$googleApiKey = "AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U"; 
?>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googleApiKey; ?>&libraries=places&loading=async"></script>

<style>
    #mapa_google { height: 350px; width: 100%; border-radius: 12px; border: 1px solid #e5e7eb; margin-top: 10px; }
</style>

<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2">
                <i class="fas fa-check-circle text-xl"></i>
                <div>
                    <p class="font-bold">Dados salvos com sucesso!</p>
                    <p class="text-sm">A localização da loja foi atualizada.</p>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/admin/configuracoes/salvarEmpresa" method="POST" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Visual & Marca</h3>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="relative group cursor-pointer">
                                <?php if(!empty($config['logo_url'])): ?>
                                    <img src="<?php echo $config['logo_url']; ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-100 shadow-sm">
                                <?php else: ?>
                                    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center text-blue-300 border-2 border-dashed border-blue-200">
                                        <i class="fas fa-camera text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Logotipo</label>
                                <p class="text-xs text-gray-400">Clique na imagem para alterar</p>
                            </div>
                        </div>
                        <div class="mb-4">
                             <label class="block text-sm font-bold text-gray-700 mb-1">Banner de Capa</label>
                             <input type="file" name="capa" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Cor da Marca</label>
                            <input type="color" name="cor_primaria" value="<?php echo $config['cor_primaria'] ?? '#FF4500'; ?>" class="h-8 w-14 rounded border cursor-pointer">
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Localização (Google Maps)</h3>
                        
                        <div class="mb-4 relative">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Endereço Completo</label>
                            <input type="text" id="input_endereco" name="endereco_completo" 
                                   value="<?php echo $config['endereco_completo'] ?? ''; ?>" 
                                   class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none text-sm shadow-sm"
                                   placeholder="Digite para buscar..." autocomplete="off">
                                   
                            <input type="hidden" id="lat" name="lat" value="<?php echo $config['lat'] ?? ''; ?>">
                            <input type="hidden" id="lng" name="lng" value="<?php echo $config['lng'] ?? ''; ?>">
                        </div>

                        <div id="mapa_google"></div>
                        <div class="mt-2 text-center">
                            <p class="text-xs text-gray-500 font-bold mb-1">Arraste o pino vermelho para a posição exata da loja.</p>
                            <p class="text-[10px] text-gray-400">Lat: <span id="debug_lat"><?php echo $config['lat'] ?? '...'; ?></span> | Lng: <span id="debug_lng"><?php echo $config['lng'] ?? '...'; ?></span></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Operacional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tempo Entrega</label>
                            <input type="text" name="tempo_entrega" value="<?php echo $config['tempo_medio_entrega'] ?? '40-50 min'; ?>" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pedido Mínimo (R$)</label>
                            <input type="text" name="pedido_minimo" value="<?php echo number_format($config['pedido_minimo'] ?? 0, 2, ',', '.'); ?>" class="w-full border border-gray-300 rounded-lg p-2 text-sm" onkeyup="mascaraMoeda(this)">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Chave PIX (Para recebimento)</label>
                            <input type="text" name="chave_pix" value="<?php echo $config['chave_pix'] ?? ''; ?>" 
                                placeholder="CPF, CNPJ, Email, Telefone ou Chave Aleatória"
                                class="w-full border border-gray-300 rounded-lg p-2 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Essa chave será exibida para o cliente no momento do pagamento.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">WhatsApp Suporte (Fale com a Loja)</label>
                            <input type="text" name="telefone_suporte" value="<?php echo $config['telefone_suporte'] ?? ''; ?>" 
                                placeholder="(11) 99999-9999"
                                class="w-full border border-gray-300 rounded-lg p-2 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-green-500 transition">
                            <p class="text-xs text-gray-400 mt-1">Aparecerá um botão flutuante no cardápio.</p>
                        </div>
                    </div>

                    <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase">Horários</h4>
                    <div class="space-y-1">
                        <?php 
                        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                        // Se não tiver horários, cria array vazio para não dar erro
                        $horarios = $horarios ?? [];
                        
                        // Garante que mostre os 7 dias mesmo se o banco estiver vazio
                        for($i=0; $i<=6; $i++):
                            // Tenta achar o horário do dia $i no array do banco
                            $h = null;
                            foreach($horarios as $item) {
                                if($item['dia_semana'] == $i) { $h = $item; break; }
                            }
                            
                            $abre = $h['abertura'] ?? '18:00';
                            $fecha = $h['fechamento'] ?? '23:00';
                            $fechado = $h['fechado_hoje'] ?? 0;
                        ?>
                        <div class="flex items-center justify-between p-2 rounded hover:bg-gray-50 border border-transparent hover:border-gray-200 text-sm transition">
                            <div class="w-20 font-medium text-gray-600"><?php echo $dias[$i]; ?></div>
                            <div class="flex items-center gap-2">
                                <input type="time" name="horarios[<?php echo $i; ?>][abre]" value="<?php echo substr($abre, 0, 5); ?>" class="border border-gray-300 rounded p-1 text-xs">
                                <span class="text-gray-400 text-xs">-</span>
                                <input type="time" name="horarios[<?php echo $i; ?>][fecha]" value="<?php echo substr($fecha, 0, 5); ?>" class="border border-gray-300 rounded p-1 text-xs">
                            </div>
                            <label class="flex items-center cursor-pointer ml-2">
                                <input type="checkbox" name="horarios[<?php echo $i; ?>][fechado]" value="1" <?php echo $fechado ? 'checked' : ''; ?> class="mr-1.5 h-4 w-4 text-red-500 rounded">
                                <span class="text-xs text-red-500 font-bold">Fechado</span>
                            </label>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-6 right-8 z-50">
                <button type="submit" class="bg-green-600 text-white px-8 py-4 rounded-full font-bold shadow-2xl hover:bg-green-700 transition transform hover:-translate-y-1 hover:scale-105 flex items-center gap-3">
                    <i class="fas fa-save text-xl"></i> <span>SALVAR DADOS</span>
                </button>
            </div>
        </form>
    </main>
</div>

<script>
    let map, marker;

    function initMap() {
        // Coordenadas iniciais
        let latEl = document.getElementById('lat');
        let lngEl = document.getElementById('lng');
        
        // Pega valor ou usa padrão (SP) se estiver vazio
        let latInicial = parseFloat(latEl.value);
        let lngInicial = parseFloat(lngEl.value);

        if (isNaN(latInicial) || latInicial === 0) latInicial = -23.550520;
        if (isNaN(lngInicial) || lngInicial === 0) lngInicial = -46.633308;

        const posicao = { lat: latInicial, lng: lngInicial };

        map = new google.maps.Map(document.getElementById("mapa_google"), {
            center: posicao,
            zoom: 16,
            mapTypeControl: false,
            streetViewControl: false
        });

        marker = new google.maps.Marker({
            position: posicao,
            map: map,
            draggable: true,
            title: "Localização da Loja",
            animation: google.maps.Animation.DROP
        });

        marker.addListener("dragend", () => {
            const pos = marker.getPosition();
            atualizarInputs(pos.lat(), pos.lng());
        });

        const input = document.getElementById("input_endereco");
        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: "br" },
            fields: ["geometry", "formatted_address"],
        });

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();

            if (!place.geometry || !place.geometry.location) {
                alert("Endereço não encontrado detalhadamente.");
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            marker.setPosition(place.geometry.location);
            atualizarInputs(place.geometry.location.lat(), place.geometry.location.lng());
        });
    }

    function atualizarInputs(lat, lng) {
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        document.getElementById('debug_lat').innerText = lat.toFixed(6);
        document.getElementById('debug_lng').innerText = lng.toFixed(6);
    }

    window.onload = initMap;

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