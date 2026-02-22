<?php
// 1. CONFIGURAÇÃO INICIAL
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se a sessão caiu, mas o cookie de "Lembrar-me" existe, restaura antes de qualquer Controller rodar!


// 2. AUTOLOAD
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// 3. ROTEAMENTO
$url = isset($_GET['url']) ? explode('/', $_GET['url']) : ['home'];

// ============================================================
// ROTA A: ÁREA ADMINISTRATIVA
// ============================================================
if ($url[0] == 'admin') {
    
    $acao = $url[1] ?? 'login';

    switch ($acao) {
        case 'login':
        case 'index':
            (new \App\Controllers\AdminController())->login();
            break;
        case 'auth':
            (new \App\Controllers\AdminController())->auth();
            break;
        case 'logout':
            (new \App\Controllers\AdminController())->logout();
            break;
        case 'dashboard':
            (new \App\Controllers\DashboardController())->index();
            break;
        
        case 'pedidos':
            if (class_exists('\App\Controllers\PedidoController')) {
                $ctrl = new \App\Controllers\PedidoController();
                $subacao = $url[2] ?? 'index';

                switch ($subacao) {
                    case 'index': $ctrl->index(); break;
                    case 'historico': $ctrl->historico(); break;
                    case 'imprimir': $ctrl->imprimir(); break;
                    case 'despachar': $ctrl->despachar(); break;
                    // --- NOVA ROTA HABILITADA ---
                    case 'imprimirCozinha': $ctrl->imprimirCozinha(); break;
                    // ----------------------------
                    case 'apiVerificarFila':      $ctrl->apiVerificarFila(); break;
                    case 'apiConfirmarImpressao': $ctrl->apiConfirmarImpressao(); break;
                    case 'resetarImpressao':      $ctrl->resetarImpressao(); break;
                    case 'apiLoginSpooler':       $ctrl->apiLoginSpooler(); break;
                    case 'salvar': $ctrl->salvar(); break;
                    case 'excluir': $ctrl->excluir(); break;
                    case 'mudarStatus': $ctrl->mudarStatus(); break;
                    case 'buscaradicionais': $ctrl->buscaradicionais(); break;
                    case 'getDadosPedido': $ctrl->getDadosPedido(); break;
                    case 'getItensPedido': $ctrl->getItensPedido(); break;
                    case 'calcularFreteAjax': $ctrl->calcularFreteAjax(); break;
                    case 'buscarClienteAjax': $ctrl->buscarClienteAjax(); break;
                    case 'vincularMotoboyAjax': $ctrl->vincularMotoboyAjax(); break;
                    case 'verificarFilaImpressao': $ctrl->verificarFilaImpressao(); break;
                    case 'marcarComoImpresso': $ctrl->marcarComoImpresso(); break;
                    case 'kds':  $ctrl->monitorUnificado(); break;
                    default: $ctrl->index(); break;
                }
            }
            break;

        case 'promocoes':
            if (class_exists('\App\Controllers\PromocaoController')) {
                $ctrl = new \App\Controllers\PromocaoController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'criar') $ctrl->criar();
                elseif ($subacao == 'editar') $ctrl->editar();
                elseif ($subacao == 'salvar') $ctrl->salvar();
                elseif ($subacao == 'excluir') $ctrl->excluir();
                else $ctrl->index();
            }
            break;

        case 'financeiro':
            $controller = new \App\Controllers\FinanceiroController();
            $metodo = $url[2] ?? 'index';
            if ($metodo == 'salvar') $controller->salvar();
            elseif ($metodo == 'baixarPagamento') $controller->baixarPagamento();
            // --- NOVAS ROTAS ADICIONADAS ---
            elseif ($metodo == 'cancelar') $controller->cancelar();
            elseif ($metodo == 'excluir') $controller->excluir();
            elseif ($metodo == 'buscarClientes') $controller->buscarClientes();
            // -------------------------------
            else $controller->index();
            break;

        case 'contas-pagar':
            $controller = new \App\Controllers\ContasPagarController();
            $metodo = $url[2] ?? 'index';
            if ($metodo == 'salvar') $controller->salvar();
            elseif ($metodo == 'buscarFornecedores') $controller->buscarFornecedores();
            elseif ($metodo == 'pagar') $controller->pagar();
            // --- NOVAS ROTAS ADICIONADAS ---
            elseif ($metodo == 'cancelar') $controller->cancelar();
            elseif ($metodo == 'excluir') $controller->excluir();
            // -------------------------------
            else $controller->index();
            break;
        case 'categorias-financeiro':
            $ctrl = new \App\Controllers\CategoriaFinanceiraController();
            $sub = $url[2] ?? 'index';
            if ($sub == 'salvar') $ctrl->salvar();
            elseif ($sub == 'excluir') $ctrl->excluir();
            else $ctrl->index();
            break;

        case 'motoboys':
            $ctrl = new \App\Controllers\MotoboyController();
            $subacao = $url[2] ?? 'index';
            if ($subacao == 'salvar') $ctrl->salvar();
            elseif ($subacao == 'excluir') $ctrl->excluir();
            else $ctrl->index();
            break;

        case 'categorias':
            if (file_exists(__DIR__ . '/../app/Controllers/CategoriaController.php')) {
                $catController = new \App\Controllers\CategoriaController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvar') $catController->salvar();
                elseif ($subacao == 'excluir') $catController->excluir();
                elseif ($subacao == 'alternar') $catController->alternarStatus();
                else $catController->index();
            }
            break;

        case 'produtos':
            if (class_exists('\App\Controllers\ProdutoController')) {
                $prodController = new \App\Controllers\ProdutoController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvar') $prodController->salvar();
                elseif ($subacao == 'excluir') $prodController->excluir();
                elseif ($subacao == 'alternar') $prodController->alternarStatus();
                elseif ($subacao == 'adicionais') $prodController->adicionais();
                else $prodController->index();
            }
            break;

        // ROTA SALÃO (Mapa e Comandas)
            case 'salao':
            if (class_exists('\App\Controllers\SalaoController')) {
                $ctrl = new \App\Controllers\SalaoController();
                $subacao = $url[2] ?? 'index';
                
                if ($subacao == 'detalhes') {
                    $ctrl->detalhes();
                }
                elseif ($subacao == 'encerrarMesa') {
                    $ctrl->encerrarMesa();
                }
                elseif ($subacao == 'mudarStatusItem') {
                    $ctrl->mudarStatusItem();
                }
                elseif ($subacao == 'adicionarPedidoMesa') {
                    $ctrl->adicionarPedidoMesa();
                }
                elseif ($subacao == 'pagarParticipante') {
                    $ctrl->pagarParticipante();
                }
                elseif ($subacao == 'abrirMesaManual') {
                    $ctrl->abrirMesaManual();
                }
                // --- ROTAS DE IMPRESSÃO ---
                elseif ($subacao == 'imprimirConferencia') {
                    $ctrl->imprimirConferencia(); // Para Cozinha (KDS)
                }
                elseif ($subacao == 'imprimirConta') {
                    $ctrl->imprimirConta();       // Para Cliente (NOVO)
                }
                // --------------------------
                elseif ($subacao == 'getConsumoAjax') {
                    $ctrl->getConsumoAjax();      // Para Celular do Cliente
                }elseif ($subacao == 'pagarParticipante') {
                        $ctrl->pagarParticipante();
                    }elseif ($subacao == 'reabrirParticipante') {
                    $ctrl->reabrirParticipante();
                }elseif ($subacao == 'aprovarSessao') {
                    $ctrl->aprovarSessao();
                }elseif ($subacao=='trocarMesa'){
                    $ctrl->trocarMesa();
                }elseif($subacao=='apiChecarPendentes'){
                    $ctrl->apiChecarPendentes();
                }           
                else {
                    $ctrl->index();
                }
            }
            break;

        // ROTA MESAS (Cadastro)
        case 'mesas':
            if (class_exists('\App\Controllers\MesaController')) {
                $ctrl = new \App\Controllers\MesaController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvar') $ctrl->salvar();
                elseif ($subacao == 'excluir') $ctrl->excluir();
                elseif ($subacao == 'gerarEmLote') $ctrl->gerarEmLote();
                else $ctrl->index();
            }
            break;

        case 'estoque':
            if (class_exists('\App\Controllers\EstoqueController')) {
                $ctrl = new \App\Controllers\EstoqueController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvarAjuste') $ctrl->salvarAjuste();
                else $ctrl->index();
            }
            break;
            
        case 'usuarios':
            if (class_exists('\App\Controllers\UsuarioController')) {
                $ctrl = new \App\Controllers\UsuarioController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvar') $ctrl->salvar();
                elseif ($subacao == 'excluir') $ctrl->excluir();
                else $ctrl->index();
            }
            break;

        case 'adicionais':
            if (class_exists('\App\Controllers\AdicionalController')) {
                $ctrl = new \App\Controllers\AdicionalController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvarGrupo') $ctrl->salvarGrupo();
                elseif ($subacao == 'salvarItem') $ctrl->salvarItem();
                elseif ($subacao == 'excluirGrupo') $ctrl->excluirGrupo();
                elseif ($subacao == 'excluirItem') $ctrl->excluirItem();
                elseif ($subacao == 'detalhes') $ctrl->detalhes();
                else $ctrl->index();
            }
            break;

        case 'configuracoes':
            if (class_exists('\App\Controllers\ConfiguracaoController')) {
                $configCtrl = new \App\Controllers\ConfiguracaoController();
                $subacao = $url[2] ?? 'empresa';
                if ($subacao == 'salvarEmpresa') $configCtrl->salvarEmpresa();
                else $configCtrl->empresa();
            }
            break;

        case 'faturas':
            if (class_exists('\App\Controllers\FaturasController')) {
                $ctrl = new \App\Controllers\FaturasController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'gerar') $ctrl->gerar();
                elseif ($subacao == 'validarCupom') $ctrl->validarCupom();
                else $ctrl->index();
            }
            break;

        case 'taxas-km':
            if (class_exists('\App\Controllers\TaxaKmController')) {
                $ctrl = new \App\Controllers\TaxaKmController();
                $subacao = $url[2] ?? 'index';
                if ($subacao == 'salvarFaixa') $ctrl->salvarFaixa();
                elseif ($subacao == 'salvarLocalizacao') $ctrl->salvarLocalizacao();
                elseif ($subacao == 'excluir') $ctrl->excluir();
                else $ctrl->index();
            }
            break;
    }

} 
// ============================================================
// ROTA B: CADASTRO
// ============================================================
elseif ($url[0] == 'cadastro') {
    if (class_exists('\App\Controllers\CadastroController')) {
        $cadastro = new \App\Controllers\CadastroController();
        $acao = $url[1] ?? 'index';
        if ($acao == 'salvar') $cadastro->salvar();
        else $cadastro->index();
    }
}
// ============================================================
// ROTA C: APP MOTOBOY
// ============================================================
elseif ($url[0] == 'app-motoboy') {
    if (class_exists('\App\Controllers\AppMotoboyController')) {
        $ctrl = new \App\Controllers\AppMotoboyController();
        $acao = $url[1] ?? 'index';
        switch ($acao) {
            case 'autenticar': $ctrl->autenticar(); break;
            case 'painel': $ctrl->painel(); break;
            case 'finalizar': $ctrl->finalizar(); break;
            case 'sair': $ctrl->sair(); break;
            default: $ctrl->index(); break;
        }
    }
}
// ============================================================
// ROTA D: CRON JOBS
// ============================================================
elseif ($url[0] == 'cron') {
    if (class_exists('\App\Controllers\CronController')) {
        $ctrl = new \App\Controllers\CronController();
        $acao = $url[1] ?? 'index';
        if ($acao == 'rodar') $ctrl->rodar();
    }
}
// ============================================================
// ROTA E: WEBHOOK
// ============================================================
elseif ($url[0] == 'webhook') {
    if (class_exists('\App\Controllers\WebhookController')) {
        $ctrl = new \App\Controllers\WebhookController();
        $ctrl->receber();
        exit; 
    }
}
// ============================================================
// ROTA F: API (MOVIDA PARA CÁ - PRIORIDADE ALTA)
// ============================================================
elseif ($url[0] == 'api') {
    $ctrl = new \App\Controllers\CardapioController();

    if ($url[1] == 'pedido' && $url[2] == 'salvar') {
        $ctrl->salvarPedido();
    }
    elseif ($url[1] == 'pedido' && $url[2] == 'frete') {
        $ctrl->calcularFrete();
    }
    elseif ($url[1] == 'cliente' && $url[2] == 'buscar') {
        $ctrl->buscarCliente();
    }
    elseif ($url[1] == 'pedido' && $url[2] == 'historico') {
        $ctrl->meusPedidos();
    }
    // Rota de Check-in (Mesa)
    elseif ($url[1] == 'mesa' && $url[2] == 'checkin') {
        $ctrl->checkinMesa();
    }elseif ($url[1] == 'mesa' && $url[2] == 'status') { $ctrl->verificarStatusMesa(); 
    } 
    elseif ($url[1] == 'mesa' && $url[2] == 'checkin') {
        $ctrl->checkinMesa();
    }
    elseif ($url[1] == 'mesa' && $url[2] == 'status') { 
        $ctrl->verificarStatusMesa(); 
    }
    // >>> ADICIONE ESTA LINHA AQUI <<<
    elseif ($url[1] == 'mesa' && $url[2] == 'validar') { 
        $ctrl->validarSessao(); 
    }elseif ($url[1] == 'cliente' && $url[2] == 'completo') {
    $ctrl->getDadosClienteCompleto();
    }
    
    exit; // Importante para não carregar HTML
}
// ============================================================
// ROTA G: CARDÁPIO / HOME (PADRÃO)
// ============================================================
else {
    $slug = $url[0];

    // Landing Page
   // Landing Page
    if(empty($slug) || $slug == 'home') {
        require __DIR__ . '/../app/Views/landing/index.php';
        exit;
    }

    // Rota de Acesso à Mesa via QR Code (ex: /slug/mesa/hash)
    if (isset($url[1]) && $url[1] == 'mesa' && isset($url[2])) {
        $ctrl = new \App\Controllers\CardapioController();
        $ctrl->mesa($slug, $url[2]); // Valida e redireciona
        exit;
    }
    if (isset($url[1]) && $url[1] == 'apiChecarStatus') {
        $ctrl = new \App\Controllers\CardapioController();
        $ctrl->apiChecarStatus();
        exit;
    }
    // --------------------------------------------

    // Rota de Acesso à Mesa via QR Code (ex: /slug/mesa/hash)
    if (isset($url[1]) && $url[1] == 'mesa' && isset($url[2])) {
        $ctrl = new \App\Controllers\CardapioController();
        $ctrl->mesa($slug, $url[2]); // Valida e redireciona
        exit;
    }

    // Carrega a Loja
   try {
        $tenant = new \App\Core\Tenant();
        $empresa = $tenant->carregarPorSlug($slug); 

        if ($empresa) {
            
            // --- INSIRA ISTO AQUI (DENTRO DO IF $empresa) ---
            if (isset($url[1]) && $url[1] == 'perfil') {
                $controller = new \App\Controllers\CardapioController();
                $controller->perfil($empresa); 
                exit; // Importante parar aqui
            }
            // -----------------------------------------------

            $controller = new \App\Controllers\CardapioController();
            $controller->index($empresa);
        } else {
            http_response_code(404);
            echo "<h1>Loja não encontrada</h1>";
        }
        
    } catch (\Exception $e) {
        echo "Erro crítico: " . $e->getMessage();
    }
}