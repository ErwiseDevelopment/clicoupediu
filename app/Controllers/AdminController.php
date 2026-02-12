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
        // ATENÇÃO: Verifique se no seu banco a coluna é 'senha' ou 'senha_hash'. 
        // Mantive 'senha_hash' conforme seu arquivo original enviada.
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        // Verifica Senha
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            // Busca dados da Empresa
            $stmtEmp = $db->prepare("SELECT * FROM empresas WHERE id = :id LIMIT 1");
            $stmtEmp->execute(['id' => $usuario['empresa_id']]);
            $empresa = $stmtEmp->fetch();

            // Seta Sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'];
            $_SESSION['empresa_slug'] = $empresa['slug'];

            // --- NOVO: MANTER CONECTADO ---
            // Se o checkbox foi marcado, cria o token persistente
            if (isset($_POST['lembrar_me'])) {
                \App\Core\AuthSession::lembrarUsuario($usuario['id']);
            }
            // -----------------------------

            // Redireciona conforme status da conta
            $this->verificarRedirecionamentoInicial();
            exit;

        } else {
            // Erro de login
            header('Location: ' . BASE_URL . '/admin?erro=1');
            exit;
        }
    }

    public function index() {
        $this->protegerRota();
        
        // Dados para o Dashboard
        $empresaId = $_SESSION['empresa_id'];
        $hoje = date('Y-m-d');

        $db = Database::connect();
        
        // Exemplo: Total de Pedidos Hoje
        $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(total) as faturamento FROM pedidos WHERE empresa_id = :id AND DATE(created_at) = :hoje AND status != 'cancelado'");
        $stmt->execute(['id' => $empresaId, 'hoje' => $hoje]);
        $resumoHoje = $stmt->fetch();

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function logout() {
        // --- NOVO: LIMPA O COOKIE DE LEMBRANÇA ---
        \App\Core\AuthSession::limparLembranca();
        // -----------------------------------------

        session_destroy();
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    // Função auxiliar para bloquear acesso direto
    private function protegerRota() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }

    // Função auxiliar para quem acessa /admin já logado
    private function verificarRedirecionamentoInicial() {
        if (isset($_SESSION['empresa_id'])) {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT licenca_validade, licenca_tipo FROM empresas WHERE id = ?");
            $stmt->execute([$_SESSION['empresa_id']]);
            $empresa = $stmt->fetch();

            $hoje = date('Y-m-d');
            
            // Reaplica a lógica de bloqueio
            if ($empresa['licenca_tipo'] !== 'VIP' && (!$empresa['licenca_validade'] || $empresa['licenca_validade'] < $hoje)) {
                // Se estiver vencido, manda para a tela de pagamento/bloqueio
                header('Location: ' . BASE_URL . '/admin/fatura'); 
                exit;
            }

            // Se tudo ok, vai para o dashboard
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }
    }
}