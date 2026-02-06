<?php
namespace App\Controllers;

use App\Core\Database;

class DashboardController {

    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();

        // 1. CAPTURA O FILTRO DE DATA
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-d');
        $dataFim    = $_GET['data_fim'] ?? date('Y-m-d');

        // =========================================================================
        // 2. INDICADORES FINANCEIROS (CARDS)
        // =========================================================================
        
        // 2.1 Entradas (Vendas)
        $sqlVendas = "SELECT SUM(valor_total) FROM pedidos 
                      WHERE empresa_id = ? AND status != 'cancelado' 
                      AND DATE(created_at) BETWEEN ? AND ?";
        $stmtV = $db->prepare($sqlVendas);
        $stmtV->execute([$empresaId, $dataInicio, $dataFim]);
        $vendasPeriodo = $stmtV->fetchColumn() ?: 0;

        // 2.2 Saídas (Contas Pagas)
        // Se ainda não criou a coluna data_pagamento, mantenha data_vencimento
        $sqlDespesas = "SELECT SUM(valor) FROM contas_pagar 
                        WHERE empresa_id = ? AND status = 'pago' 
                        AND DATE(data_vencimento) BETWEEN ? AND ?";
        $stmtD = $db->prepare($sqlDespesas);
        $stmtD->execute([$empresaId, $dataInicio, $dataFim]);
        $despesasPeriodo = $stmtD->fetchColumn() ?: 0;

        // 2.3 Saldo
        $saldoPeriodo = $vendasPeriodo - $despesasPeriodo;

        // 2.4 Pendente (A Receber vs A Pagar)
        $sqlReceber = "SELECT SUM(valor) FROM contas_receber WHERE empresa_id = ? AND status = 'pendente' AND DATE(data_vencimento) BETWEEN ? AND ?";
        $stmtR = $db->prepare($sqlReceber);
        $stmtR->execute([$empresaId, $dataInicio, $dataFim]);
        $aReceber = $stmtR->fetchColumn() ?: 0;

        $sqlPagar = "SELECT SUM(valor) FROM contas_pagar WHERE empresa_id = ? AND status = 'pendente' AND DATE(data_vencimento) BETWEEN ? AND ?";
        $stmtP = $db->prepare($sqlPagar);
        $stmtP->execute([$empresaId, $dataInicio, $dataFim]);
        $aPagar = $stmtP->fetchColumn() ?: 0;


        // =========================================================================
        // 3. GRÁFICOS E ANÁLISES ESTRATÉGICAS
        // =========================================================================
        
        // 3.1 Fluxo de Caixa Diário (Loop de Dias)
        $fluxoCaixa = [];
        $currentDate = new \DateTime($dataInicio);
        $endDate     = new \DateTime($dataFim);
        
        // Limita o loop para não travar se o intervalo for gigante (opcional)
        while ($currentDate <= $endDate) {
            $dia = $currentDate->format('Y-m-d');
            
            $s1 = $db->prepare("SELECT SUM(valor_total) FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) = ?");
            $s1->execute([$empresaId, $dia]);
            $ent = $s1->fetchColumn() ?: 0;

            $s2 = $db->prepare("SELECT SUM(valor) FROM contas_pagar WHERE empresa_id = ? AND status = 'pago' AND DATE(data_vencimento) = ?");
            $s2->execute([$empresaId, $dia]);
            $sai = $s2->fetchColumn() ?: 0;

            // Só adiciona se houver movimento para não poluir o gráfico
            if($ent > 0 || $sai > 0 || $dataInicio == $dataFim) {
                $fluxoCaixa[] = ['data' => $currentDate->format('d/m'), 'entrada' => $ent, 'saida' => $sai];
            }
            $currentDate->modify('+1 day');
        }

        // 3.2 Top 5 Produtos (Faturamento)
        // Descobre o que dá mais dinheiro, não só o que sai mais
        $sqlTop = "SELECT p.nome, SUM(pi.total) as total_faturado, SUM(pi.quantidade) as qtd 
                   FROM pedido_itens pi
                   JOIN pedidos ped ON pi.pedido_id = ped.id
                   JOIN produtos p ON pi.produto_id = p.id
                   WHERE ped.empresa_id = ? AND ped.status != 'cancelado' 
                   AND DATE(ped.created_at) BETWEEN ? AND ?
                   GROUP BY p.id 
                   ORDER BY total_faturado DESC LIMIT 5";
        $stmtTop = $db->prepare($sqlTop);
        $stmtTop->execute([$empresaId, $dataInicio, $dataFim]);
        $topProdutos = $stmtTop->fetchAll(\PDO::FETCH_ASSOC);

        // 3.3 Horários de Pico (Vendas por Hora)
        $sqlHoras = "SELECT HOUR(created_at) as hora, COUNT(*) as qtd, SUM(valor_total) as total 
                     FROM pedidos 
                     WHERE empresa_id = ? AND status != 'cancelado'
                     AND DATE(created_at) BETWEEN ? AND ? 
                     GROUP BY HOUR(created_at) ORDER BY hora ASC";
        $stmtHoras = $db->prepare($sqlHoras);
        $stmtHoras->execute([$empresaId, $dataInicio, $dataFim]);
        $vendasPorHora = $stmtHoras->fetchAll(\PDO::FETCH_ASSOC);

        // 3.4 Meios de Pagamento
        $sqlPagto = "SELECT forma_pagamento, SUM(valor_total) as total 
                     FROM pedidos WHERE empresa_id = ? AND status != 'cancelado'
                     AND DATE(created_at) BETWEEN ? AND ? 
                     GROUP BY forma_pagamento";
        $stmtPagto = $db->prepare($sqlPagto);
        $stmtPagto->execute([$empresaId, $dataInicio, $dataFim]);
        $formasPagamento = $stmtPagto->fetchAll(\PDO::FETCH_ASSOC);

        // 3.5 Resumo Operacional
        $resumo = $db->prepare("SELECT COUNT(id) as qtd_pedidos, AVG(valor_total) as ticket_medio FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ?");
        $resumo->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosOperacionais = $resumo->fetch(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}