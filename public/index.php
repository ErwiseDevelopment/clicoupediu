<?php
// 1. CONFIGURAÇÃO INICIAL
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// >>> CORREÇÃO AQUI: Carrega as configurações (BASE_URL) antes de tudo <<<
require_once __DIR__ . '/../app/config.php';

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
        case 'index': // Se acessar /admin direto, cai aqui
            // IMPORTANTE: Use o caminho completo da classe
            (new \App\Controllers\DashboardController())->index();
            break;;
        
      case 'pedidos':
            if (class_exists('\App\Controllers\PedidoController')) {
                $ctrl = new \App\Controllers\PedidoController();
                $subacao = $url[2] ?? 'index';

                switch ($subacao) {
                    // --- VIEWS (Telas) ---
                    case 'index':
                        $ctrl->index();
                        break;
                    case 'historico':
                        $ctrl->historico();
                        break;
                    case 'imprimir':
                        $ctrl->imprimir();
                        break;

                    // --- AÇÕES (Salvar/Excluir/Editar) ---
                    case 'salvar':
                        $ctrl->salvar();
                        break;
                    case 'excluir':
                        $ctrl->excluir();
                        break;
                    case 'mudarStatus':
                        $ctrl->mudarStatus();
                        break;

                    // --- AJAX / APIs (Retornam JSON) ---
                    case 'buscaradicionais':     // <--- A CORREÇÃO DO ERRO
                        $ctrl->buscaradicionais();
                        break;
                    case 'getDadosPedido':
                        $ctrl->getDadosPedido();
                        break;
                    case 'getItensPedido':
                        $ctrl->getItensPedido();
                        break;
                    case 'calcularFreteAjax':
                        $ctrl->calcularFreteAjax();
                        break;
                    case 'buscarClienteAjax':
                        $ctrl->buscarClienteAjax();
                        break;
                    case 'vincularMotoboyAjax':
                        $ctrl->vincularMotoboyAjax();
                        break;

                    // --- PADRÃO ---
                    default:
                        $ctrl->index();
                        break;
                }
            } else {
                die("Erro: PedidoController não encontrado.");
            }
            break;

           case 'promocoes':
        if (class_exists('\App\Controllers\PromocaoController')) {
            $ctrl = new \App\Controllers\PromocaoController();
            $subacao = $url[2] ?? 'index';

            if ($subacao == 'criar') {
                $ctrl->criar();
            } 
            elseif ($subacao == 'editar') {
                $ctrl->editar();
            } 
            elseif ($subacao == 'salvar') {
                $ctrl->salvar();
            } 
            elseif ($subacao == 'excluir') {
                $ctrl->excluir();
            } 
            
            else {
                $ctrl->index();
            }
        } else {
            die("Erro: PromocaoController não encontrado.");
        }
        break;

            case 'financeiro':
                $controller = new \App\Controllers\FinanceiroController();
                $metodo = $url[2] ?? 'index';

                if ($metodo == 'salvar') {
                    $controller->salvar(); // O exit dentro do salvar impede o erro do JSON
                } elseif ($metodo == 'baixarPagamento') {
                    $controller->baixarPagamento();
                } else {
                    $controller->index();
                }
                break;

                

                case 'contas-pagar':
    $controller = new \App\Controllers\ContasPagarController();
    $metodo = $url[2] ?? 'index';

    if ($metodo == 'salvar') $controller->salvar();
    elseif ($metodo == 'buscarFornecedores') $controller->buscarFornecedores();
    elseif ($metodo == 'pagar') $controller->pagar();
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
            // Verifica se o arquivo existe antes de chamar
            if (file_exists(__DIR__ . '/../app/Controllers/CategoriaController.php')) {
                
                $catController = new \App\Controllers\CategoriaController();
                $subacao = $url[2] ?? 'index'; // Pega a subação (salvar, excluir) ou assume index
                
                if ($subacao == 'salvar') {
            $catController->salvar();
        } elseif ($subacao == 'excluir') {
            $catController->excluir();
        
        // >>> ADICIONE ESTE BLOCO <<<
        } elseif ($subacao == 'alternar') {
            $catController->alternarStatus();
        
        } else {
            $catController->index();
        }
        

            } else {
                echo "<h1>Erro</h1><p>Arquivo CategoriaController.php não encontrado na pasta app/Controllers.</p>";
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
            } else {
                die("Crie o ProdutoController primeiro.");
            }
            break;

            case 'estoque':
            // Verifica se o arquivo e a classe existem para evitar Tela Branca
            if (class_exists('\App\Controllers\EstoqueController')) {
                $ctrl = new \App\Controllers\EstoqueController();
                
                // Pega a ação da URL (ex: /admin/estoque/salvarAjuste)
                $subacao = $url[2] ?? 'index';

                if ($subacao == 'salvarAjuste') {
                    $ctrl->salvarAjuste();
                } else {
                    $ctrl->index();
                }
            } else {
                // Se cair aqui, o arquivo não existe ou o namespace está errado
                die("ERRO CRÍTICO: O arquivo 'app/Controllers/EstoqueController.php' não foi encontrado ou a classe não foi definida corretamente (namespace App\Controllers).");
            }
            break;
            
            case 'usuarios':
                if (class_exists('\App\Controllers\UsuarioController')) {
                    $ctrl = new \App\Controllers\UsuarioController();
                    $subacao = $url[2] ?? 'index';

                    if ($subacao == 'salvar') $ctrl->salvar();
                    elseif ($subacao == 'excluir') $ctrl->excluir();
                    else $ctrl->index();
                } else {
                    die("Controller de usuários não encontrado.");
                }
                break;

            case 'adicionais':
            // Verifica se o Controller existe antes de chamar
            if (class_exists('\App\Controllers\AdicionalController')) {
                $ctrl = new \App\Controllers\AdicionalController();
                $subacao = $url[2] ?? 'index';

                if ($subacao == 'salvarGrupo') $ctrl->salvarGrupo();
                elseif ($subacao == 'salvarItem') $ctrl->salvarItem();
                elseif ($subacao == 'excluirGrupo') $ctrl->excluirGrupo();
                elseif ($subacao == 'excluirItem') $ctrl->excluirItem();
                elseif ($subacao == 'detalhes') $ctrl->detalhes();
                else $ctrl->index();
            } else {
                die("ERRO: O arquivo AdicionalController.php não foi encontrado na pasta app/Controllers.");
            }
            break;

            case 'configuracoes':
            if (class_exists('\App\Controllers\ConfiguracaoController')) {
                $configCtrl = new \App\Controllers\ConfiguracaoController();
                $subacao = $url[2] ?? 'empresa'; // Padrão agora é 'empresa'

                if ($subacao == 'salvarEmpresa') $configCtrl->salvarEmpresa();
                elseif ($subacao == 'empresa') $configCtrl->empresa();
                else $configCtrl->empresa(); // Fallback
            } else {
                die("Crie o ConfiguracaoController.");
            }
            break;
            // Substitua ou adicione junto com 'taxas'

            case 'faturas':
            // Verifica se o Controller existe para evitar erro fatal
            if (class_exists('\App\Controllers\FaturasController')) {
                $ctrl = new \App\Controllers\FaturasController();
                $subacao = $url[2] ?? 'index';

                if ($subacao == 'gerar') {
                    $ctrl->gerar();
                } 
                elseif ($subacao == 'validarCupom') {
                    $ctrl->validarCupom();
                } 
                else {
                    $ctrl->index();
                }
            } else {
                die("ERRO: O arquivo FaturasController.php não foi encontrado na pasta app/Controllers.");
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
            } else { die("Erro: TaxaKmController não encontrado."); }
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
    } else {
        die("Erro: CadastroController não encontrado.");
    }
}

elseif ($url[0] == 'app-motoboy') {
    // Verifica se o Controller existe para evitar erros
    if (class_exists('\App\Controllers\AppMotoboyController')) {
        $ctrl = new \App\Controllers\AppMotoboyController();
        $acao = $url[1] ?? 'index';

        switch ($acao) {
            case 'autenticar':
                $ctrl->autenticar();
                break;
            case 'painel':
                $ctrl->painel();
                break;
            case 'finalizar':
                $ctrl->finalizar();
                break;
            case 'sair':
                $ctrl->sair();
                break;
            default:
                $ctrl->index(); // Tela de Login
                break;
        }
    } else {
        // Ajuda no debug caso esqueça de criar o arquivo
        die("Erro: O arquivo AppMotoboyController.php não foi encontrado na pasta App/Controllers.");
    }
}
// ============================================================
// ROTA C: CARDÁPIO / HOME
// ============================================================
else {
    $slug = $url[0];

    // Landing Page
    if(empty($slug) || $slug == 'home') {
        // Exemplo simples de Landing Page
        echo '<!DOCTYPE html>
        <html lang="pt-br">
        <head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-gray-100 h-screen flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-blue-600 mb-4">Delivery SaaS</h1>
                <div class="space-x-4">
                    <a href="'.BASE_URL.'/cadastro" class="bg-green-500 text-white px-6 py-3 rounded font-bold">Criar Loja</a>
                    <a href="'.BASE_URL.'/admin" class="bg-blue-500 text-white px-6 py-3 rounded font-bold">Login</a>
                </div>
            </div>
        </body></html>';
        exit;
    }
// ============================================================
// ROTA D: CRON JOBS (Automação) - INSIRA AQUI ANTES DO ELSE
// ============================================================
elseif ($url[0] == 'cron') {
    if (class_exists('\App\Controllers\CronController')) {
        $ctrl = new \App\Controllers\CronController();
        $acao = $url[1] ?? 'index';
        if ($acao == 'rodar') $ctrl->rodar();
        else die("Comando inválido.");
    } else {
        die("CronController não encontrado.");
    }
}

// ============================================================
// ROTA E: WEBHOOK (Retorno Automático) - INSIRA AQUI ANTES DO ELSE
// ============================================================
elseif ($url[0] == 'webhook') {
    if (class_exists('\App\Controllers\WebhookController')) {
        $ctrl = new \App\Controllers\WebhookController();
        $ctrl->receber();
        exit; 
    } else {
        http_response_code(404);
        die("WebhookController não encontrado em app/Controllers.");
    }
}

    try {
        $tenant = new \App\Core\Tenant();
        $empresa = $tenant->carregarPorSlug($slug); 

       // ============================================================
// 1. ROTAS DE API (Prioridade Alta)
// ============================================================
if ($url[0] == 'api') {
    $ctrl = new \App\Controllers\CardapioController();

    // Salvar Pedido
    if ($url[1] == 'pedido' && $url[2] == 'salvar') {
        $ctrl->salvarPedido();
    }
    // Calcular Frete
    elseif ($url[1] == 'pedido' && $url[2] == 'frete') {
        $ctrl->calcularFrete();
    }
    // Buscar Cliente (Login Automático)
    elseif ($url[1] == 'cliente' && $url[2] == 'buscar') {
        $ctrl->buscarCliente();
    }
    // >>> NOVA ROTA: HISTÓRICO DE PEDIDOS <<<
    elseif ($url[1] == 'pedido' && $url[2] == 'historico') {
        $ctrl->meusPedidos();
    }
    
    exit; // Encerra aqui para não carregar o HTML do site
}

// ============================================================
// 2. ROTA DA LOJA (Carrega o Cardápio Visual)
// ============================================================
if ($empresa) {
    $controller = new \App\Controllers\CardapioController();
    $controller->index($empresa);
} 
// ============================================================
// 3. ROTA DE ERRO (Loja não existe)
// ============================================================
else {
    http_response_code(404);
    echo "<h1>Loja não encontrada</h1><a href='./'>Voltar</a>";
}
        
    } catch (\Exception $e) {
        echo "Erro crítico: " . $e->getMessage();
    }

    
}