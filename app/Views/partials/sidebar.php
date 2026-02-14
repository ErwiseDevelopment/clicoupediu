<?php
use App\Core\Database;

if (session_status() === PHP_SESSION_NONE) session_start();

$url_atual = $_GET['url'] ?? 'dashboard';
$usuarioId = $_SESSION['usuario_id'] ?? 0;
$empresaId = $_SESSION['empresa_id'] ?? 0;

$db = Database::connect();

$stmtConf = $db->prepare("
    SELECT servico_salao 
    FROM configuracoes_filial 
    WHERE filial_id = (SELECT id FROM filiais WHERE empresa_id = ? LIMIT 1)
");
$stmtConf->execute([$empresaId]);
$confFilial = $stmtConf->fetch();
$temSalao = ($confFilial && $confFilial['servico_salao'] == 1);


// Busca nome do usuário e o slug da empresa para os links dinâmicos
$stmtUser = $db->prepare("SELECT u.nome, e.slug FROM usuarios u JOIN empresas e ON u.empresa_id = e.id WHERE u.id = ?");
$stmtUser->execute([$usuarioId]);
$userDados = $stmtUser->fetch();
$nomeExibicao = $userDados['nome'] ?? 'Usuário';
$slugEmpresa = $userDados['slug'] ?? '';

// Verificação de Licença e Bloqueio
$stmtEmp = $db->prepare("SELECT licenca_validade, licenca_tipo FROM empresas WHERE id = ?");
$stmtEmp->execute([$empresaId]);
$empresaSide = $stmtEmp->fetch();

$hoje = date('Y-m-d');
$validade = $empresaSide['licenca_validade'] ?? null;
$tipoLicenca = $empresaSide['licenca_tipo'] ?? 'TESTE';
$isBloqueado = ($tipoLicenca !== 'VIP' && (!$validade || $validade < $hoje));
$classeBloqueio = $isBloqueado ? 'opacity-40 pointer-events-none grayscale' : '';

function menuAtivo($url_atual, $rota) {
    return (strpos($url_atual, $rota) !== false) 
        ? 'bg-indigo-50 text-indigo-700 border-r-4 border-indigo-600 font-bold shadow-sm' 
        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-r-4 border-transparent';
}
?>

<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/60 z-[70] hidden backdrop-blur-sm transition-opacity opacity-0"></div>

<aside id="sidebar" class="bg-white border-r border-slate-200 flex flex-col h-screen fixed md:static top-0 left-0 z-[80] transition-all duration-300 w-64 transform -translate-x-full md:translate-x-0">
    
    <div class="h-24 hidden md:flex items-center justify-center px-6 border-b border-slate-100 mb-2 shrink-0">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center justify-center w-full">
            <img src="<?= BASE_URL ?>/assets/img/logosidebar.png" alt="Logo">
        </a>
    </div>

    <div class="px-4 mb-4 mt-4 md:mt-2 shrink-0">
        <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-2">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1">Links de Acesso</p>
            <a href="<?= BASE_URL ?>/<?= $slugEmpresa ?>" target="_blank" class="flex items-center gap-2 text-[11px] font-bold text-slate-600 hover:text-indigo-600 transition group">
                <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-sm border border-slate-100">
                    <i class="fas fa-utensils text-[10px]"></i>
                </div>
                Meu Cardápio
            </a>
            <a href="<?= BASE_URL ?>/app-motoboy/painel" target="_blank" class="flex items-center gap-2 text-[11px] font-bold text-slate-600 hover:text-indigo-600 transition group">
                <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-sm border border-slate-100">
                    <i class="fas fa-motorcycle text-[10px]"></i>
                </div>
                Painel Motoboy
            </a>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 space-y-1 custom-scrollbar pb-10">
    
        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[2px] mb-2 mt-4">Operação</p>
        <div class="<?= $classeBloqueio ?>">
            
            <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'dashboard') ?>">
                <i class="fas fa-chart-line w-5 text-center mr-3"></i> <span>Painel Geral</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/pedidos/kds" target="_blank" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= (strpos($url_atual, 'kds') !== false) ? 'bg-red-50 text-red-700 border-r-4 border-red-600 font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-r-4 border-transparent' ?>">
                <i class="fas fa-fire w-5 text-center mr-3 text-red-500"></i> <span>Monitor Cozinha</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/pedidos" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= (strpos($url_atual, 'pedidos') !== false && strpos($url_atual, 'historico') === false && strpos($url_atual, 'kds') === false) ? 'bg-orange-50 text-orange-700 border-r-4 border-orange-500 font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-r-4 border-transparent' ?>">
                <i class="fas fa-desktop w-5 text-center mr-3"></i> <span>Pedidos</span>
            </a>

            <?php if (isset($temSalao) && $temSalao): ?>
            <a href="<?= BASE_URL ?>/admin/salao" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'salao') ?>">
                <i class="fas fa-chair w-5 text-center mr-3 text-purple-600"></i> <span>Mesas / Salão</span>
            </a>
            <?php endif; ?>

            <a href="<?php echo BASE_URL; ?>/admin/pedidos/historico" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= (strpos($url_atual, 'historico') !== false) ? 'bg-indigo-50 text-indigo-700 border-r-4 border-indigo-600 font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-r-4 border-transparent' ?>">
                <i class="fas fa-history w-5 text-center mr-3"></i> <span>Histórico Vendas</span>
            </a>
        </div>

        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[2px] mb-2 mt-6">Catálogo</p>
        <div class="<?= $classeBloqueio ?>">
            <a href="<?= BASE_URL ?>/admin/produtos" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'produtos') ?>">
                <i class="fas fa-hamburger w-5 text-center mr-3"></i> <span>Produtos</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/promocoes" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'promocoes') ?>">
                <i class="fas fa-fire w-5 text-center mr-3 text-red-500"></i> <span>Combos & Ofertas</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/categorias" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'categorias') ?>">
                <i class="fas fa-layer-group w-5 text-center mr-3"></i> <span>Categorias</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/adicionais" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'adicionais') ?>">
                <i class="fas fa-puzzle-piece w-5 text-center mr-3"></i> <span>Adicionais</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/estoque" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'estoque') ?>">
                <i class="fas fa-boxes-stacked w-5 text-center mr-3"></i> <span>Controle de Estoque</span>
            </a>
        </div>

        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[2px] mb-2 mt-6">Financeiro</p>
        <div class="<?= $classeBloqueio ?>">
            <a href="<?= BASE_URL ?>/admin/financeiro" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'financeiro') ?>">
                <i class="fas fa-money-bill-trend-up w-5 text-center mr-3"></i> <span>Contas a Receber</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/contas-pagar" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'contas-pagar') ?>">
                <i class="fas fa-file-invoice w-5 text-center mr-3"></i> <span>Contas a Pagar</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/faturas" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= (strpos($url_atual, 'faturas') !== false) ? 'bg-indigo-50 text-indigo-700 font-bold border-r-4 border-indigo-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50' ?>">
                <i class="fas fa-credit-card w-5 text-center mr-3"></i> <span>Minha Assinatura</span>
                <?php if($isBloqueado): ?> <span class="ml-auto text-[8px] bg-red-600 text-white px-1.5 py-0.5 rounded font-black uppercase">Vencida</span> <?php endif; ?>
            </a>
        </div>

        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[2px] mb-2 mt-6">Configurações</p>
        <div class="<?= $classeBloqueio ?>">
            <a href="<?= BASE_URL ?>/admin/configuracoes/empresa" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'configuracoes') ?>">
                <i class="fas fa-shop w-5 text-center mr-3"></i> <span>Minha Loja</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/taxas-km" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'taxas-km') ?>">
                <i class="fas fa-route w-5 text-center mr-3"></i> <span>Raio de Entrega</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/motoboys" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'motoboys') ?>">
                <i class="fas fa-motorcycle w-5 text-center mr-3"></i> <span>Motoboys</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/usuarios" class="flex items-center px-4 py-3 text-sm rounded-xl transition-all <?= menuAtivo($url_atual, 'usuarios') ?>">
                <i class="fas fa-users-gear w-5 text-center mr-3"></i> <span>Equipe & Acessos</span>
            </a>
        </div>
    </nav>
    

    <div class="p-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
        <div class="flex items-center p-2 rounded-2xl bg-white shadow-sm border border-slate-200">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shrink-0">
                <?= strtoupper(substr($nomeExibicao, 0, 1)) ?>
            </div>
            <div class="ml-3 truncate flex-1">
                <p class="text-xs font-black text-slate-800 leading-tight truncate"><?= $nomeExibicao ?></p>
                <a href="<?= BASE_URL ?>/admin/logout" class="text-[10px] text-red-500 font-bold hover:underline">Sair do Sistema</a>
            </div>
        </div>
    </div>
</aside>

<div class="md:hidden fixed bottom-0 left-0 w-full bg-white h-16 border-t border-slate-200 z-[90] flex items-center justify-around px-2 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
    <a href="<?= BASE_URL ?>/admin/dashboard" class="flex flex-col items-center gap-1 <?= menuAtivo($url_atual, 'dashboard') ? 'text-indigo-600' : 'text-slate-400' ?>">
        <i class="fas fa-chart-line text-lg"></i>
        <span class="text-[9px] font-black uppercase">Início</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/pedidos" class="flex flex-col items-center gap-1 <?= (strpos($url_atual, 'pedidos') !== false && strpos($url_atual, 'novo') === false) ? 'text-orange-500' : 'text-slate-400' ?>">
        <i class="fas fa-desktop text-lg"></i>
        <span class="text-[9px] font-black uppercase">Pedidos</span>
    </a>
    
    <button type="button" onclick="abrirModalVenda()" class="bg-indigo-600 text-white w-12 h-12 rounded-full flex items-center justify-center -mt-8 shadow-lg shadow-indigo-200 border-4 border-white active:scale-95 transition-transform">
        <i class="fas fa-plus text-lg"></i>
    </button>

    <a href="<?= BASE_URL ?>/admin/produtos" class="flex flex-col items-center gap-1 <?= menuAtivo($url_atual, 'produtos') ? 'text-indigo-600' : 'text-slate-400' ?>">
        <i class="fas fa-hamburger text-lg"></i>
        <span class="text-[9px] font-black uppercase">Itens</span>
    </a>
    <button id="btnOpenMenuFooter" class="flex flex-col items-center gap-1 text-slate-400">
        <i class="fas fa-bars text-lg"></i>
        <span class="text-[9px] font-black uppercase">Menu</span>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const btnFooterMenu = document.getElementById('btnOpenMenuFooter');

        function toggleMenu() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                overlay.classList.remove('opacity-100');
                document.body.classList.remove('overflow-hidden');
            } else {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
                document.body.classList.add('overflow-hidden');
            }
        }

        if(btnFooterMenu) btnFooterMenu.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);

        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) toggleMenu();
            });
        });
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    @media (max-width: 768px) {
        main { padding-bottom: 80px !important; }
        aside#sidebar { top: 0; height: 100vh; }
    }
</style>