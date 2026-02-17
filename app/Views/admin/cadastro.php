<?php 
$titulo = "Criar Conta | clicoupediu.app.br";
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php'; 
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-slate-50 relative overflow-hidden font-sans">
    <div class="absolute top-0 left-0 w-full h-80 bg-blue-700 rounded-b-[4rem] z-0 shadow-lg"></div>

    <div class="max-w-6xl w-full grid md:grid-cols-2 bg-white rounded-[2.5rem] shadow-2xl overflow-hidden relative z-10 border border-gray-100">
        
        <div class="hidden md:flex flex-col justify-between p-12 bg-gradient-to-br from-slate-900 to-slate-800 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-blue-600/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="mb-10">
                    <img src="<?= BASE_URL ?>/assets/img/logosidebar.png" alt="Logo clicoupediu.app.br" class="h-14 md:h-16 w-auto object-contain brightness-0 invert">
                </div>

                <h2 class="text-3xl md:text-4xl font-black mb-8 leading-tight tracking-tight">
                    Tudo o que o seu delivery precisa num <span class="text-blue-400">único sistema.</span>
                </h2>
                
                <div class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-500/20 w-10 h-10 rounded-xl flex items-center justify-center text-blue-400 shrink-0 shadow-inner border border-blue-500/30">
                            <i class="fas fa-store text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-100">Cardápio Digital</h4>
                            <p class="text-slate-400 text-xs">Link exclusivo com cálculo de frete automático.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="bg-red-500/20 w-10 h-10 rounded-xl flex items-center justify-center text-red-400 shrink-0 shadow-inner border border-red-500/30">
                            <i class="fas fa-fire-burner text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-100">Monitor KDS</h4>
                            <p class="text-slate-400 text-xs">Fim do papel! Pedidos diretos na ecrã da cozinha.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="bg-indigo-500/20 w-10 h-10 rounded-xl flex items-center justify-center text-indigo-400 shrink-0 shadow-inner border border-indigo-500/30">
                            <i class="fas fa-motorcycle text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-100">App do Entregador</h4>
                            <p class="text-slate-400 text-xs">Painel próprio para motoboys com rotas por GPS.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="bg-teal-500/20 w-10 h-10 rounded-xl flex items-center justify-center text-teal-400 shrink-0 shadow-inner border border-teal-500/30">
                            <i class="fas fa-desktop text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-100">PDV e Mesas</h4>
                            <p class="text-slate-400 text-xs">Frente de caixa rápido e QR Code para as mesas.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 bg-blue-600/20 border border-blue-500/30 p-4 rounded-2xl flex items-center gap-4 relative z-10 backdrop-blur-sm">
                <i class="fas fa-percentage text-3xl text-blue-400"></i>
                <div>
                    <h4 class="font-black text-white uppercase tracking-wider text-sm">Zero Comissões</h4>
                    <p class="text-blue-200 text-xs">100% do lucro é seu. Venda à vontade!</p>
                </div>
            </div>
        </div>

        <div class="p-8 md:p-14 bg-white">
            <h2 class="text-3xl font-black text-slate-800 mb-6 tracking-tight">Crie a sua Loja</h2>

            <form action="<?= BASE_URL ?>/cadastro/salvar" method="POST" class="space-y-4">
                
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Nome da Loja</label>
                    <input type="text" name="nome_fantasia" id="nome_fantasia" required onkeyup="gerarSlug()" placeholder="Ex: MK Assados"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700 transition">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">O seu link exclusivo</label>
                    <div class="flex items-center bg-blue-50 rounded-2xl px-5 py-3 border-2 border-dashed border-blue-200">
                        <span class="text-blue-500 font-bold text-sm">clicoupediu.app.br/</span>
                        <input type="text" name="slug" id="slug" readonly tabindex="-1" class="bg-transparent w-full outline-none font-bold text-blue-800 pointer-events-none">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">CPF ou CNPJ</label>
                        <input type="text" name="documento" required placeholder="000.000.000-00" oninput="mascaraDocumento(this)" maxlength="18"
                               class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700 text-sm transition">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">WhatsApp da Loja</label>
                        <input type="tel" name="whatsapp" required placeholder="(00) 00000-0000" oninput="mascaraTelefone(this)" maxlength="15"
                               class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700 text-sm transition">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Endereço Completo</label>
                    <input type="text" name="endereco_completo" required placeholder="Rua, Número, Bairro, Cidade"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700 text-sm transition">
                </div>

                <hr class="border-slate-100 my-4">

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">O seu Nome (Responsável)</label>
                    <input type="text" name="nome_usuario" required placeholder="Ex: Raphael Silva"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700 text-sm transition">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <input type="email" name="email" required placeholder="O seu E-mail" class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-sm transition">
                    <input type="password" name="senha" required placeholder="Crie uma Senha" class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-sm transition">
                </div>

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-200 transition-all flex items-center justify-center gap-3 text-lg uppercase tracking-wide mt-6">
                    ATIVAR O MEU SISTEMA <i class="fas fa-rocket"></i>
                </button>
                
                <p class="text-center text-slate-500 font-bold text-xs mt-4">
                    Já é cliente? <a href="<?= BASE_URL ?>/admin" class="text-blue-700 hover:text-blue-800 hover:underline">Faça login aqui</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// Gera o slug da loja em tempo real
function gerarSlug() {
    let nome = document.getElementById('nome_fantasia').value;
    let slug = nome.toLowerCase().trim()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Remove acentos
        .replace(/[^a-z0-9\s-]/g, '') // Remove caracteres especiais
        .replace(/\s+/g, '-') // Troca espaços por traços
        .replace(/-+/g, '-'); // Evita traços duplicados
    
    // Remove traço no final se houver
    if(slug.endsWith('-')) slug = slug.slice(0, -1);
        
    document.getElementById('slug').value = slug;
}

// Máscara inteligente para CPF e CNPJ
function mascaraDocumento(input) {
    let v = input.value.replace(/\D/g, ""); // Remove tudo que não é dígito

    if (v.length <= 11) { // Formata como CPF
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    } else { // Formata como CNPJ
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
    }
    input.value = v;
}

// Máscara para o WhatsApp da Loja
function mascaraTelefone(input) {
    let v = input.value.replace(/\D/g, ""); 
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); 
    v = v.replace(/(\d)(\d{4})$/, "$1-$2"); 
    input.value = v;
}
</script>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>