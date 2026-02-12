<?php
namespace App\Core;

use App\Core\Database;

class AuthSession {

    // Cria o cookie e salva a sessão no banco
    public static function lembrarUsuario($userId) {
        $db = Database::connect();
        
        // Gera um token criptograficamente seguro
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // Expira em 30 dias
        $expire = time() + (30 * 24 * 60 * 60); 
        $expireDate = date('Y-m-d H:i:s', $expire);
        
        // Salva no Banco (hash) e no Navegador (token puro)
        $stmt = $db->prepare("INSERT INTO user_sessions (user_id, token_hash, user_agent, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $tokenHash, $_SERVER['HTTP_USER_AGENT'] ?? '', $expireDate]);
        
        setcookie('remember_token', $token, $expire, '/', '', isset($_SERVER['HTTPS']), true);
    }

    // Verifica se existe cookie e loga o usuário automaticamente
    public static function verificarLoginAutomatico() {
        // Se já está logado ou não tem cookie, ignora
        if (isset($_SESSION['usuario_id']) || !isset($_COOKIE['remember_token'])) {
            return;
        }

        $token = $_COOKIE['remember_token'];
        $tokenHash = hash('sha256', $token);
        
        $db = Database::connect();
        
        // Busca sessão válida
        $stmt = $db->prepare("SELECT u.id, u.nome, u.email, u.empresa_id 
                              FROM user_sessions s 
                              JOIN usuarios u ON s.user_id = u.id 
                              WHERE s.token_hash = ? AND s.expires_at > NOW() LIMIT 1");
        $stmt->execute([$tokenHash]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($usuario) {
            // Recria a sessão PHP
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'];
        }
    }

    // Limpa tudo ao fazer Logout
    public static function limparLembranca() {
        if (isset($_COOKIE['remember_token'])) {
            $tokenHash = hash('sha256', $_COOKIE['remember_token']);
            $db = Database::connect();
            $db->prepare("DELETE FROM user_sessions WHERE token_hash = ?")->execute([$tokenHash]);
            setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }
    }
}