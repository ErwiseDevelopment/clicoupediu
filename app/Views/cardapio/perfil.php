<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Minha Conta | <?php echo $empresa['nome_fantasia']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }</style>
</head>
<body>

    <header class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-30 flex items-center gap-3">
        <a href="<?= BASE_URL ?>/<?= $empresa['slug'] ?>" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="font-bold text-lg text-gray-800">Minha Conta</h1>
    </header>

    <div id="tela-login" class="p-6 max-w-md mx-auto mt-10 text-center hidden">
        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 text-3xl">
            <i class="fas fa-user-shield"></i>
        </div>
        <h2 class="text-xl font-black text-gray-800 mb-2">Identifique-se</h2>
        <p class="text-sm text-gray-500 mb-6">Informe seu WhatsApp para acessar seus pedidos e cadastro.</p>
        
        <input type="tel" id="login-tel" placeholder="(00) 00000-0000" class="w-full border border-gray-300 rounded-xl p-4 text-center text-lg font-bold outline-none focus:border-blue-500 mb-4 shadow-sm">
        
        <button onclick="fazerLogin()" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-blue-700 transition">
            ACESSAR
        </button>
    </div>

    <div id="tela-perfil" class="hidden pb-20 max-w-md mx-auto">
        
        <div class="flex bg-white border-b border-gray-200 sticky top-[57px] z-20">
            <button onclick="mudarAba('pedidos')" id="tab-pedidos" class="flex-1 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600">
                <i class="fas fa-motorcycle mr-1"></i> Pedidos
            </button>
            <button onclick="mudarAba('dados')" id="tab-dados" class="flex-1 py-3 text-sm font-bold text-gray-400 border-b-2 border-transparent">
                <i class="fas fa-user-edit mr-1"></i> Cadastro
            </button>
        </div>

        <div id="conteudo-pedidos" class="p-4 space-y-3">
            <div id="loading-pedidos" class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i></div>
            <div id="lista-pedidos"></div>
        </div>

        <div id="conteudo-dados" class="p-4 hidden">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Seu Nome</label>
                    <input type="text" id="cad-nome" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700" readonly>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Endereço Salvo</label>
                    <input type="text" id="cad-end" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-700" readonly>
                </div>
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Número</label>
                        <input type="text" id="cad-num" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-center text-gray-700" readonly>
                    </div>
                    <div class="flex-[2]">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Bairro</label>
                        <input type="text" id="cad-bairro" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-700" readonly>
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 text-center mb-2">Para alterar, faça um novo pedido.</p>
                    <button onclick="sair()" class="w-full border border-red-200 text-red-500 font-bold py-3 rounded-lg hover:bg-red-50 text-sm">
                        <i class="fas fa-sign-out-alt mr-1"></i> Sair da Conta
                    </button>
                </div>
            </div>
        </div>

    </div>

<script>
    const empresaId = "<?= $empresa['id'] ?>";
    const baseUrlApi = "<?= BASE_URL ?>/api";

    // 1. INICIALIZAÇÃO
    window.onload = function() {
        const tel = localStorage.getItem('delivery_telefone');
        if (tel && tel.length >= 8) {
            carregarPerfil(tel);
        } else {
            document.getElementById('tela-login').classList.remove('hidden');
        }
    }

    function fazerLogin() {
        const tel = document.getElementById('login-tel').value.replace(/\D/g, '');
        if (tel.length < 8) { alert('Digite um telefone válido.'); return; }
        
        localStorage.setItem('delivery_telefone', tel);
        location.reload();
    }

    function sair() {
        localStorage.removeItem('delivery_telefone');
        location.reload();
    }

    // 2. CARREGAR DADOS
    function carregarPerfil(telefone) {
        document.getElementById('tela-login').classList.add('hidden');
        document.getElementById('tela-perfil').classList.remove('hidden');

        const f = new FormData();
        f.append('telefone', telefone);
        f.append('empresa_id', empresaId);

        fetch(baseUrlApi + '/cliente/completo', { method: 'POST', body: f })
            .then(r => r.json())
            .then(d => {
                document.getElementById('loading-pedidos').classList.add('hidden');
                
                if (d.ok) {
                    // Preenche Cadastro
                    if (d.cliente) {
                        document.getElementById('cad-nome').value = d.cliente.nome || '-';
                        document.getElementById('cad-end').value = d.cliente.endereco_ultimo || '-';
                        document.getElementById('cad-num').value = d.cliente.numero_ultimo || '-';
                        document.getElementById('cad-bairro').value = d.cliente.bairro_ultimo || '-';
                    }

                    // Preenche Pedidos
                    renderizarPedidos(d.pedidos);
                }
            })
            .catch(() => alert('Erro de conexão'));
    }

    function renderizarPedidos(pedidos) {
        const div = document.getElementById('lista-pedidos');
        if (pedidos.length === 0) {
            div.innerHTML = '<div class="text-center py-10 text-gray-400">Você ainda não fez pedidos.</div>';
            return;
        }

        let html = '';
        pedidos.forEach(p => {
            const data = new Date(p.created_at).toLocaleDateString('pt-BR', {day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'});
            let statusCor = 'bg-gray-100 text-gray-500';
            let statusIcon = 'fa-clock';
            let statusTexto = 'Em Análise';

            if(p.status === 'preparo') { statusCor = 'bg-orange-100 text-orange-600'; statusIcon = 'fa-fire'; statusTexto = 'Preparando'; }
            if(p.status === 'entrega') { statusCor = 'bg-blue-100 text-blue-600'; statusIcon = 'fa-motorcycle'; statusTexto = 'Saiu p/ Entrega'; }
            if(p.status === 'finalizado') { statusCor = 'bg-green-100 text-green-600'; statusIcon = 'fa-check-double'; statusTexto = 'Entregue'; }
            if(p.status === 'cancelado') { statusCor = 'bg-red-100 text-red-600'; statusIcon = 'fa-ban'; statusTexto = 'Cancelado'; }

            html += `
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col gap-3">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold text-gray-400">#${p.id} • ${data}</span>
                        <h4 class="font-black text-gray-800 text-lg">R$ ${parseFloat(p.valor_total).toFixed(2).replace('.',',')}</h4>
                        <p class="text-xs text-gray-500">${p.qtd_itens} itens • ${p.tipo_entrega === 'entrega' ? 'Delivery' : 'Retirada'}</p>
                    </div>
                    <span class="${statusCor} px-3 py-1 rounded-full text-xs font-black uppercase flex items-center gap-1">
                        <i class="fas ${statusIcon}"></i> ${statusTexto}
                    </span>
                </div>
                
                ${p.status === 'analise' || p.status === 'preparo' || p.status === 'entrega' ? 
                  `<div class="w-full bg-gray-100 rounded-full h-1.5 mt-1 overflow-hidden">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all duration-1000" style="width: ${getProgress(p.status)}%"></div>
                   </div>` : ''
                }
            </div>`;
        });
        div.innerHTML = html;
    }

    function getProgress(s) {
        if(s==='analise') return 10;
        if(s==='preparo') return 50;
        if(s==='entrega') return 90;
        return 100;
    }

    function mudarAba(aba) {
        if(aba === 'pedidos') {
            document.getElementById('conteudo-pedidos').classList.remove('hidden');
            document.getElementById('conteudo-dados').classList.add('hidden');
            document.getElementById('tab-pedidos').className = 'flex-1 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600';
            document.getElementById('tab-dados').className = 'flex-1 py-3 text-sm font-bold text-gray-400 border-b-2 border-transparent';
        } else {
            document.getElementById('conteudo-pedidos').classList.add('hidden');
            document.getElementById('conteudo-dados').classList.remove('hidden');
            document.getElementById('tab-dados').className = 'flex-1 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600';
            document.getElementById('tab-pedidos').className = 'flex-1 py-3 text-sm font-bold text-gray-400 border-b-2 border-transparent';
        }
    }
</script>
</body>
</html>