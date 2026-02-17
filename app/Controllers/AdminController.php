<?php
namespace App\Controllers;

use App\Core\Database;

class AdminController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // --- NOVA FUNÇÃO: Restaura a sessão via Cookie + Tabela user_sessions ---
    private function verificarOuRestaurarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Se a sessão normal do PHP ainda está viva, segue o jogo
        if (isset($_SESSION['usuario_id'])) {
            return true;
        }

        // 2. Se a sessão morreu, mas o navegador tem o Cookie de Lembrança
        if (isset($_COOKIE['admin_token'])) {
            $tokenPuro = $_COOKIE['admin_token'];
            $tokenHash = hash('sha256', $tokenPuro);

            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT u.id, u.nome, u.empresa_id, e.slug 
                FROM user_sessions us
                INNER JOIN usuarios u ON us.user_id = u.id
                LEFT JOIN empresas e ON u.empresa_id = e.id
                WHERE us.token_hash = ? 
                LIMIT 1
            ");
            $stmt->execute([$tokenHash]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                // Acesso Legítimo! Recria a sessão invisivelmente
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['empresa_id'] = $user['empresa_id'];
                $_SESSION['empresa_slug'] = $user['slug'] ?? '';
                return true;
            } else {
                // O cookie existe, mas o hash é falso ou expirado. Limpa o invasor.
                setcookie('admin_token', '', time() - 3600, '/');
            }
        }
        return false;
    }

    // Tela de Login
    public function login() {
        // Se já está logado ou se o cookie restaurou o login automaticamente
        if ($this->verificarOuRestaurarLogin()) {
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
        
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        // Verifica Senha
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            $stmtEmp = $db->prepare("SELECT * FROM empresas WHERE id = :id LIMIT 1");
            $stmtEmp->execute(['id' => $usuario['empresa_id']]);
            $empresa = $stmtEmp->fetch();

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'];
            $_SESSION['empresa_slug'] = $empresa['slug'];

            // === SISTEMA DE LOGIN PERSISTENTE (Salva na user_sessions) ===
            if (isset($_POST['lembrar_me'])) {
                $tokenPuro = bin2hex(random_bytes(32)); 
                $tokenHash = hash('sha256', $tokenPuro); 

                // Limpa sessões antigas deste usuário no banco
                $db->prepare("DELETE FROM user_sessions WHERE user_id = ?")->execute([$usuario['id']]);
                
                // Salva o Hash no banco
                $db->prepare("INSERT INTO user_sessions (user_id, token_hash) VALUES (?, ?)")
                   ->execute([$usuario['id'], $tokenHash]);

                // Salva o Token Puro no cookie do navegador (Válido por 30 dias)
                $tempoExpiracao = time() + (30 * 24 * 60 * 60);
                setcookie('admin_token', $tokenPuro, $tempoExpiracao, '/');
            }
            // ==============================================================

            $this->verificarRedirecionamentoInicial();
            exit;

        } else {
            header('Location: ' . BASE_URL . '/admin?erro=1');
            exit;
        }
    }

    public function index() {
        $this->protegerRota();
        
        $empresaId = $_SESSION['empresa_id'];
        $hoje = date('Y-m-d');

        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(total) as faturamento FROM pedidos WHERE empresa_id = :id AND DATE(created_at) = :hoje AND status != 'cancelado'");
        $stmt->execute(['id' => $empresaId, 'hoje' => $hoje]);
        $resumoHoje = $stmt->fetch();

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // === LIMPA O COOKIE E O BANCO ===
        if (isset($_COOKIE['admin_token'])) {
            $tokenHash = hash('sha256', $_COOKIE['admin_token']);
            $db = Database::connect();
            $db->prepare("DELETE FROM user_sessions WHERE token_hash = ?")->execute([$tokenHash]);
            
            setcookie('admin_token', '', time() - 3600, '/');
        }
        // ================================

        session_destroy();
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    private function protegerRota() {
        // Agora, se a sessão cair, ele tenta restaurar antes de bloquear
        if (!$this->verificarOuRestaurarLogin()) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }

    private function verificarRedirecionamentoInicial() {
        if (isset($_SESSION['empresa_id'])) {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT licenca_validade, licenca_tipo FROM empresas WHERE id = ?");
            $stmt->execute([$_SESSION['empresa_id']]);
            $empresa = $stmt->fetch();

            $hoje = date('Y-m-d');
            
            if ($empresa['licenca_tipo'] !== 'VIP' && (!$empresa['licenca_validade'] || $empresa['licenca_validade'] < $hoje)) {
                header('Location: ' . BASE_URL . '/admin/fatura'); 
                exit;
            }

            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }
    }
}