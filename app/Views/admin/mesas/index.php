<?php $titulo = "Gerenciar Mesas"; require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen bg-gray-50 overflow-hidden">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <div class="bg-white border-b border-gray-200 px-8 py-5 flex justify-between items-center shrink-0">
            <div>
                <h2 class="text-2xl font-black text-gray-800">Cadastro de Mesas</h2>
                <p class="text-sm text-gray-500">Crie e imprima os QR Codes para suas mesas.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/salao" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar ao Mapa
            </a>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-plus"></i></div>
                            Nova Mesa (Individual)
                        </h3>
                        <form action="<?php echo BASE_URL; ?>/admin/mesas/salvar" method="POST">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nome/Número</label>
                            <input type="text" name="numero" class="w-full border border-gray-300 rounded-lg p-3 text-lg font-bold mb-4 focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Ex: 01, VIP, Balcão..." required>
                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg shadow-md transition">
                                CADASTRAR
                            </button>
                        </form>
                    </div>

                    <div class="bg-blue-50 p-6 rounded-xl shadow-sm border border-blue-100">
                        <h3 class="font-bold text-blue-800 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-white text-blue-600 flex items-center justify-center shadow-sm"><i class="fas fa-magic"></i></div>
                            Gerador Automático
                        </h3>
                        <form action="<?php echo BASE_URL; ?>/admin/mesas/gerarEmLote" method="POST">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Do nº</label>
                                    <input type="number" name="inicio" class="w-full border border-blue-200 rounded-lg p-2 text-center font-bold" value="1" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Ao nº</label>
                                    <input type="number" name="fim" class="w-full border border-blue-200 rounded-lg p-2 text-center font-bold" value="10" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Prefixo (Opcional)</label>
                                <input type="text" name="prefixo" class="w-full border border-blue-200 rounded-lg p-2 text-sm" placeholder="Ex: Mesa ">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-md transition text-xs uppercase">
                                GERAR AGORA
                            </button>
                        </form>
                        <p class="text-[10px] text-blue-500 mt-2 text-center">Isso criará mesas sequenciais automaticamente.</p>
                    </div>

                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">Mesa</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">QR Code / Link</th>
                                    <th class="px-6 py-3 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(empty($mesas)): ?>
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Nenhuma mesa cadastrada.</td></tr>
                                <?php else: ?>
                                    <?php foreach($mesas as $m): 
                                        // Busca o slug da sessão para montar o link correto
                                        $slug = $_SESSION['empresa_slug'] ?? 'loja'; 
                                        $linkMesa = BASE_URL . '/' . $slug . '/mesa/' . $m['hash_qr'];
                                    ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3 font-black text-gray-800 text-lg"><?php echo $m['numero']; ?></td>
                                        <td class="px-6 py-3">
                                            <?php if($m['status_atual'] == 'ocupada'): ?>
                                                <span class="px-2 py-1 rounded bg-red-100 text-red-600 text-xs font-bold uppercase">Ocupada</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 rounded bg-green-100 text-green-600 text-xs font-bold uppercase">Livre</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=<?php echo urlencode($linkMesa); ?>" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded text-xs font-bold transition flex items-center gap-1">
                                                    <i class="fas fa-qrcode"></i> Baixar QR
                                                </a>
                                                <button onclick="navigator.clipboard.writeText('<?php echo $linkMesa; ?>'); alert('Link copiado!');" class="text-gray-400 hover:text-blue-600" title="Copiar Link">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <a href="<?php echo BASE_URL; ?>/admin/mesas/excluir?id=<?php echo $m['id']; ?>" onclick="return confirm('Excluir esta mesa?')" class="text-red-400 hover:text-red-600">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>