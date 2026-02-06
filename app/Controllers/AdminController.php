<?php
namespace App\Controllers;

use App\Core\Database;
// Não precisamos mais do Tenant aqui dentro, faremos a checagem manual para redirecionar
// use App\Core\Tenant; 

class AdminController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Tela de Login
    public function login() {
        // Se já está logado, verifica para onde mandar (Dashboard ou Fatura)
        if (isset($_SESSION['usuario_id'])) {
            $this->verificarRedirecionamentoInicial();
            exit;
        }
        require __DIR__ . '/../Views/admin/login.php';
    }

    // Processar o Login
    public function auth() {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $db = Database::connect();
        
        // Busca usuário
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        // Verifica Senha
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            // Busca dados da Empresa
            $stmtEmp = $db->prepare("SELECT * FROM empresas WHERE id = :id");
            $stmtEmp->execute(['id' => $usuario['empresa_id']]);
            $empresa = $stmtEmp->fetch();

            // 1. PRIMEIRO: Salva a Sessão (Necessário para acessar a tela de faturas)
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'];
            $_SESSION['empresa_slug'] = $empresa['slug'];
            $_SESSION['nivel'] = $usuario['nivel'];

            // 2. SEGUNDO: Verifica a Licença e Redireciona
            $hoje = date('Y-m-d');
            $validade = $empresa['licenca_validade'];
            $tipo = $empresa['licenca_tipo'];

            // Regra: Se NÃO for VIP e (estiver sem data OU data for menor que hoje)
            if ($tipo !== 'VIP' && (!$validade || $validade < $hoje)) {
                // Redireciona direto para o pagamento
                header('Location: ' . BASE_URL . '/admin/faturas?msg=vencido');
                exit;
            }

            // Se estiver tudo OK, vai para o Dashboard
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;

        } else {
            // Erro de login
            header('Location: ' . BASE_URL . '/admin?erro=dados_invalidos');
            exit;
        }
    }

    // O Painel Principal (Gestão de Pedidos)
    public function dashboard() {
        $this->protegerRota(); 

        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();

        // Busca Resumo para os Cards do topo
        $hoje = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as qtd, SUM(total) as faturamento FROM pedidos WHERE empresa_id = :id AND DATE(created_at) = :hoje AND status != 'cancelado'");
        $stmt->execute(['id' => $empresaId, 'hoje' => $hoje]);
        $resumoHoje = $stmt->fetch();

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/admin');
    }

    // Função auxiliar para bloquear acesso direto
    private function protegerRota() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }

    // Nova função auxiliar para quem acessa /admin já logado
    private function verificarRedirecionamentoInicial() {
        if (isset($_SESSION['empresa_id'])) {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT licenca_validade, licenca_tipo FROM empresas WHERE id = ?");
            $stmt->execute([$_SESSION['empresa_id']]);
            $empresa = $stmt->fetch();

            $hoje = date('Y-m-d');
            
            // Reaplica a lógica de bloqueio
            if ($empresa['licenca_tipo'] !== 'VIP' && (!$empresa['licenca_validade'] || $empresa['licenca_validade'] < $hoje)) {
                header('Location: ' . BASE_URL . '/admin/faturas');
            } else {
                header('Location: ' . BASE_URL . '/admin/dashboard');
            }
            exit;
        }
    }
}