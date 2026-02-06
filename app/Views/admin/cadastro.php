<?php 
$titulo = "Criar Conta | ClicouPediu";
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php'; 
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-slate-50 relative overflow-hidden font-sans">
    <div class="absolute top-0 left-0 w-full h-80 bg-blue-600 rounded-b-[4rem] z-0 shadow-lg"></div>

    <div class="max-w-6xl w-full grid md:grid-cols-2 bg-white rounded-[2.5rem] shadow-2xl overflow-hidden relative z-10 border border-gray-100">
        
        <div class="hidden md:flex flex-col justify-between p-12 bg-slate-900 text-white">
            <div>
                <div class="flex items-center gap-2 mb-12">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shopping-basket text-lg text-white"></i>
                    </div>
                    <span class="font-800 text-2xl tracking-tighter uppercase">ClicouPediu<span class="text-blue-500">.app</span></span>
                </div>
                <h2 class="text-4xl font-800 mb-8 leading-tight">Tudo pronto para o seu delivery.</h2>
                <div class="space-y-8">
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-500/20 p-3 rounded-2xl text-blue-400"><i class="fab fa-whatsapp text-2xl"></i></div>
                        <div>
                            <h4 class="font-bold text-lg">Cardápio via WhatsApp</h4>
                            <p class="text-slate-400 text-sm">Pedidos organizados direto no seu celular.</p>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">© 2026 ClicouPediu.app.br</p>
        </div>

        <div class="p-8 md:p-14 bg-white">
            <h2 class="text-3xl font-800 text-slate-800 mb-6">Cadastre sua Loja</h2>

            <form action="<?= BASE_URL ?>/cadastro/salvar" method="POST" class="space-y-4">
                
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Nome da Loja</label>
                    <input type="text" name="nome_fantasia" id="nome_fantasia" required onkeyup="gerarSlug()" placeholder="Ex: MK Assados"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">CPF ou CNPJ</label>
                    <input type="text" name="documento" required placeholder="000.000.000-00" oninput="mascaraDocumento(this)" maxlength="18"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Endereço Completo</label>
                    <input type="text" name="endereco_completo" required placeholder="Rua, Número, Bairro, Cidade"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700 text-sm">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Seu link exclusivo</label>
                    <div class="flex items-center bg-blue-50 rounded-2xl px-5 py-3 border-2 border-dashed border-blue-200">
                        <span class="text-blue-400 font-bold text-sm">clicoupediu.app.br/</span>
                        <input type="text" name="slug" id="slug" readonly class="bg-transparent w-full outline-none font-bold text-blue-700">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Seu Nome Completo</label>
                    <input type="text" name="nome_usuario" required placeholder="Ex: Raphael Silva"
                           class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-slate-700">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <input type="email" name="email" required placeholder="E-mail" class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-sm">
                    <input type="password" name="senha" required placeholder="Crie uma Senha" class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-600 outline-none font-bold text-sm">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-blue-200 transition-all flex items-center justify-center gap-3 text-lg uppercase tracking-tight">
                    ATIVAR CARDÁPIO <i class="fas fa-rocket"></i>
                </button>
                
                <p class="text-center text-slate-400 font-bold text-xs mt-4">
                    Já é parceiro? <a href="<?= BASE_URL ?>/admin" class="text-blue-600 hover:underline">Fazer login</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
function gerarSlug() {
    const nome = document.getElementById('nome_fantasia').value;
    const slug = nome.toLowerCase().trim()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
    document.getElementById('slug').value = slug;
}

function mascaraDocumento(input) {
    let v = input.value.replace(/\D/g, ""); 
    if (v.length <= 11) { // CPF
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    } else { // CNPJ
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
    }
    input.value = v;
}
</script>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>