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
        // 2. INDICADORES FINANCEIROS BÁSICOS
        // =========================================================================
        
        $sqlVendas = "SELECT SUM(valor_total) FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ?";
        $stmtV = $db->prepare($sqlVendas);
        $stmtV->execute([$empresaId, $dataInicio, $dataFim]);
        $vendasPeriodo = $stmtV->fetchColumn() ?: 0;

        $sqlDespesas = "SELECT SUM(valor) FROM contas_pagar WHERE empresa_id = ? AND status = 'pago' AND DATE(data_vencimento) BETWEEN ? AND ?";
        $stmtD = $db->prepare($sqlDespesas);
        $stmtD->execute([$empresaId, $dataInicio, $dataFim]);
        $despesasPeriodo = $stmtD->fetchColumn() ?: 0;

        $saldoPeriodo = $vendasPeriodo - $despesasPeriodo;

        $sqlReceber = "SELECT SUM(valor) FROM contas_receber WHERE empresa_id = ? AND status = 'pendente' AND DATE(data_vencimento) BETWEEN ? AND ?";
        $stmtR = $db->prepare($sqlReceber);
        $stmtR->execute([$empresaId, $dataInicio, $dataFim]);
        $aReceber = $stmtR->fetchColumn() ?: 0;

        $sqlPagar = "SELECT SUM(valor) FROM contas_pagar WHERE empresa_id = ? AND status = 'pendente' AND DATE(data_vencimento) BETWEEN ? AND ?";
        $stmtP = $db->prepare($sqlPagar);
        $stmtP->execute([$empresaId, $dataInicio, $dataFim]);
        $aPagar = $stmtP->fetchColumn() ?: 0;

        // =========================================================================
        // 3. GRÁFICOS FINANCEIROS
        // =========================================================================
        
        $fluxoCaixa = [];
        $currentDate = new \DateTime($dataInicio);
        $endDate     = new \DateTime($dataFim);
        
        while ($currentDate <= $endDate) {
            $dia = $currentDate->format('Y-m-d');
            
            $s1 = $db->prepare("SELECT SUM(valor_total) FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) = ?");
            $s1->execute([$empresaId, $dia]);
            $ent = $s1->fetchColumn() ?: 0;

            $s2 = $db->prepare("SELECT SUM(valor) FROM contas_pagar WHERE empresa_id = ? AND status = 'pago' AND DATE(data_vencimento) = ?");
            $s2->execute([$empresaId, $dia]);
            $sai = $s2->fetchColumn() ?: 0;

            if($ent > 0 || $sai > 0 || $dataInicio == $dataFim) {
                $fluxoCaixa[] = ['data' => $currentDate->format('d/m'), 'entrada' => $ent, 'saida' => $sai];
            }
            $currentDate->modify('+1 day');
        }

        $sqlTop = "SELECT p.nome, SUM(pi.total) as total_faturado, SUM(pi.quantidade) as qtd 
                   FROM pedido_itens pi
                   JOIN pedidos ped ON pi.pedido_id = ped.id
                   JOIN produtos p ON pi.produto_id = p.id
                   WHERE ped.empresa_id = ? AND ped.status != 'cancelado' AND DATE(ped.created_at) BETWEEN ? AND ?
                   GROUP BY p.id ORDER BY total_faturado DESC LIMIT 5";
        $stmtTop = $db->prepare($sqlTop);
        $stmtTop->execute([$empresaId, $dataInicio, $dataFim]);
        $topProdutos = $stmtTop->fetchAll(\PDO::FETCH_ASSOC);

        $sqlHoras = "SELECT HOUR(created_at) as hora, COUNT(*) as qtd, SUM(valor_total) as total 
                     FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ? 
                     GROUP BY HOUR(created_at) ORDER BY hora ASC";
        $stmtHoras = $db->prepare($sqlHoras);
        $stmtHoras->execute([$empresaId, $dataInicio, $dataFim]);
        $vendasPorHora = $stmtHoras->fetchAll(\PDO::FETCH_ASSOC);

        $sqlPagto = "SELECT forma_pagamento, SUM(valor_total) as total 
                     FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ? 
                     GROUP BY forma_pagamento";
        $stmtPagto = $db->prepare($sqlPagto);
        $stmtPagto->execute([$empresaId, $dataInicio, $dataFim]);
        $formasPagamento = $stmtPagto->fetchAll(\PDO::FETCH_ASSOC);

        $resumo = $db->prepare("SELECT COUNT(id) as qtd_pedidos, AVG(valor_total) as ticket_medio FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ?");
        $resumo->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosOperacionais = $resumo->fetch(\PDO::FETCH_ASSOC);

        // =========================================================================
        // 4. MÓDULO LOGÍSTICA (MOTOBOYS) E MKT (MARKETING) - NOVO!
        // =========================================================================

        // 4.1 Custo Total de Taxas de Entrega Geradas (Valor repassado aos motoboys)
        $sqlTaxas = "SELECT SUM(taxa_entrega) as total_taxas, COUNT(id) as qtd_entregas 
                     FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND tipo_entrega = 'entrega' 
                     AND DATE(created_at) BETWEEN ? AND ?";
        $stmtTaxas = $db->prepare($sqlTaxas);
        $stmtTaxas->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosEntregas = $stmtTaxas->fetch(\PDO::FETCH_ASSOC);
        $custoEntregas = $dadosEntregas['total_taxas'] ?: 0;
        $qtdEntregasFeitas = $dadosEntregas['qtd_entregas'] ?: 0;

        // 4.2 Ranking de Produtividade dos Motoboys
        $sqlMoto = "SELECT m.nome, COUNT(p.id) as qtd, SUM(p.taxa_entrega) as total_repasse 
                    FROM pedidos p
                    JOIN motoboys m ON p.motoboy_id = m.id
                    WHERE p.empresa_id = ? AND p.status != 'cancelado' AND p.tipo_entrega = 'entrega' AND DATE(p.created_at) BETWEEN ? AND ?
                    GROUP BY m.id ORDER BY total_repasse DESC";
        $stmtMoto = $db->prepare($sqlMoto);
        $stmtMoto->execute([$empresaId, $dataInicio, $dataFim]);
        $rankingMotoboys = $stmtMoto->fetchAll(\PDO::FETCH_ASSOC);

        // 4.3 Modalidades de Pedido (Delivery vs Balcão vs Mesa)
        $sqlTipoEnt = "SELECT tipo_entrega, COUNT(*) as qtd, SUM(valor_total) as total 
                       FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ? 
                       GROUP BY tipo_entrega ORDER BY total DESC";
        $stmtTipoEnt = $db->prepare($sqlTipoEnt);
        $stmtTipoEnt->execute([$empresaId, $dataInicio, $dataFim]);
        $vendasPorTipo = $stmtTipoEnt->fetchAll(\PDO::FETCH_ASSOC);

        // 4.4 Análise de Retenção de Clientes (Marketing)
        $sqlMkt = "SELECT COUNT(DISTINCT cliente_telefone) as clientes_unicos, COUNT(id) as total_pedidos 
                   FROM pedidos 
                   WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ? 
                   AND cliente_telefone IS NOT NULL AND cliente_telefone != ''";
        $stmtMkt = $db->prepare($sqlMkt);
        $stmtMkt->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosMkt = $stmtMkt->fetch(\PDO::FETCH_ASSOC);
        
        $clientesUnicos = $dadosMkt['clientes_unicos'] ?: 0;
        $pedidosComTel = $dadosMkt['total_pedidos'] ?: 0;
        
        // Se há mais pedidos do que clientes únicos, significa que houve recorrência!
        $taxaRecorrencia = $pedidosComTel > 0 ? (($pedidosComTel - $clientesUnicos) / $pedidosComTel) * 100 : 0;

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}