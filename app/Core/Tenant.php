<?php
namespace App\Core;

use App\Core\Database;

class Tenant {
    
    public function carregarPorSlug($slug) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM empresas WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $empresa = $stmt->fetch();

        if (!$empresa) {
            return false;
        }

        $this->verificarLicenca($empresa);
        return $empresa;
    }

    public function verificarLicenca($empresa) {
        // Garante acesso à sessão para saber se é Admin ou Cliente
        if (session_status() === PHP_SESSION_NONE) session_start();

        $hoje = date('Y-m-d');
        
        // 1. Bloqueio Manual (Geral - Violação de termos, etc)
        if ($empresa['licenca_tipo'] == 'BLOQUEADO') {
            $this->bloquearAcesso("Esta conta foi suspensa administrativamente.");
        }

        // 2. VIP (Liberado)
        if ($empresa['licenca_tipo'] == 'VIP') {
            return true; 
        }

        // 3. Validação de Vencimento
        $validade = $empresa['licenca_validade'] ?? null;
        
        // Verifica se está vencido ou não tem data configurada
        if (!$validade || $validade < $hoje) {
            
            // =============================================================
            // CENÁRIO A: É O DONO DA LOJA (ADMIN)
            // =============================================================
            if (isset($_SESSION['usuario_id']) && isset($_SESSION['empresa_id'])) {
                
                // ANTI-LOOP: Se ele JÁ ESTÁ na área financeira, DEIXA PASSAR.
                // Isso permite que o Controller carregue a sidebar e o modal de pagamento.
                $urlAtual = $_SERVER['REQUEST_URI'] ?? '';
                if (strpos($urlAtual, 'faturas') !== false || strpos($urlAtual, 'financeiro') !== false) {
                    return true; 
                }

                // Se ele tentar ir para Dashboard, Pedidos, etc -> CHUTA PARA FATURAS
                header('Location: ' . BASE_URL . '/admin/faturas?msg=expirado');
                exit;
            }

            // =============================================================
            // CENÁRIO B: É O CLIENTE (PÚBLICO)
            // =============================================================
            // Aqui mostramos a tela de bloqueio total, pois cliente não paga fatura.
            
            $msgData = $validade ? date('d/m/Y', strtotime($validade)) : 'não ativada';
            $this->bloquearAcesso("A licença desta loja expirou em {$msgData}. O cardápio está temporariamente indisponível.");
        }
        
        return true;
    }

    private function bloquearAcesso($mensagem) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['erro' => true, 'msg' => $mensagem]);
            exit;
        }
        
        // Tela Bonita para o Cliente Final
        die("
        <div style='display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;background:#f8fafc;color:#1e293b'>
            <div style='text-align:center;padding:50px;background:white;border-radius:32px;box-shadow:0 20px 50px rgba(0,0,0,0.05);max-width:450px;width:90%;border:1px solid #f1f5f9'>
                <div style='background:#f1f5f9;width:70px;height:70px;border-radius:20px;display:flex;justify-content:center;align-items:center;margin:0 auto 24px auto;font-size:24px'>
                    🔒
                </div>
                <h1 style='margin:0 0 12px 0;font-size:20px;font-weight:800;letter-spacing:-0.5px;color:#0f172a uppercase'>Loja Temporariamente Offline</h1>
                <p style='font-size:14px;line-height:1.6;color:#64748b;margin-bottom:30px;font-weight:500'>{$mensagem}</p>
                
                <div style='margin-top:40px;padding-top:25px;border-top:1px solid #f1f5f9'>
                    <p style='font-size:10px;font-weight:800;color:#cbd5e1;text-transform:uppercase;letter-spacing:3px'>ClicouPediu.app.br</p>
                </div>
            </div>
        </div>");
    }
}