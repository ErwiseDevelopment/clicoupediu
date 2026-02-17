<?php
namespace App\Controllers;

use App\Core\Database;

class DashboardController {

    public function index() {
        $this->verificarLogin();
        $empresaId = $_SESSION['empresa_id'];
        $db = Database::connect();

        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim    = $_GET['data_fim'] ?? date('Y-m-t');
        $dataFimDateTime = $dataFim . ' 23:59:59'; 

        // =========================================================================
        // 1. INDICADORES FINANCEIROS BÁSICOS E PERDAS (CANCELAMENTOS)
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

        $stmtCanc = $db->prepare("SELECT COUNT(id) as qtd, SUM(valor_total) as valor FROM pedidos WHERE empresa_id = ? AND status = 'cancelado' AND DATE(created_at) BETWEEN ? AND ?");
        $stmtCanc->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosCancelados = $stmtCanc->fetch(\PDO::FETCH_ASSOC);

        // =========================================================================
        // 2. GRÁFICOS DIÁRIOS E VENDAS
        // =========================================================================
        
        $fluxoCaixa = [];
        $currentDate = new \DateTime($dataInicio);
        $endDate     = new \DateTime($dataFim);
        
        while ($currentDate <= $endDate) {
            $dia = $currentDate->format('Y-m-d');
            
            $s1 = $db->prepare("SELECT SUM(valor_total) FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) = ?");
            $s1->execute([$empresaId, $dia]);
            $ent = $s1->fetchColumn() ?: 0;

            if($ent > 0 || $dataInicio == $dataFim) {
                $fluxoCaixa[] = ['data' => $currentDate->format('d/m'), 'faturamento' => $ent];
            }
            $currentDate->modify('+1 day');
        }

        $sqlTop = "SELECT p.nome, SUM(pi.total) as total_faturado, SUM(pi.quantidade) as qtd 
                   FROM pedido_itens pi JOIN pedidos ped ON pi.pedido_id = ped.id JOIN produtos p ON pi.produto_id = p.id
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
        // 3. INTELIGÊNCIA GEOGRÁFICA E LOGÍSTICA
        // =========================================================================

        $sqlBairros = "SELECT bairro, COUNT(id) as qtd, SUM(valor_total) as total 
                       FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND tipo_entrega = 'entrega' AND bairro IS NOT NULL AND TRIM(bairro) != '' 
                       AND DATE(created_at) BETWEEN ? AND ? GROUP BY bairro ORDER BY total DESC LIMIT 6";
        $stmtBairros = $db->prepare($sqlBairros);
        $stmtBairros->execute([$empresaId, $dataInicio, $dataFim]);
        $rankingBairros = $stmtBairros->fetchAll(\PDO::FETCH_ASSOC);

        $sqlTaxas = "SELECT SUM(taxa_entrega) as total_taxas, COUNT(id) as qtd_entregas FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND tipo_entrega = 'entrega' AND DATE(created_at) BETWEEN ? AND ?";
        $stmtTaxas = $db->prepare($sqlTaxas);
        $stmtTaxas->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosEntregas = $stmtTaxas->fetch(\PDO::FETCH_ASSOC);
        
        $sqlMoto = "SELECT m.nome, COUNT(p.id) as qtd, SUM(p.taxa_entrega) as total_repasse FROM pedidos p JOIN motoboys m ON p.motoboy_id = m.id WHERE p.empresa_id = ? AND p.status != 'cancelado' AND p.tipo_entrega = 'entrega' AND DATE(p.created_at) BETWEEN ? AND ? GROUP BY m.id ORDER BY total_repasse DESC LIMIT 5";
        $stmtMoto = $db->prepare($sqlMoto);
        $stmtMoto->execute([$empresaId, $dataInicio, $dataFim]);
        $rankingMotoboys = $stmtMoto->fetchAll(\PDO::FETCH_ASSOC);

        $sqlTipoEnt = "SELECT tipo_entrega, COUNT(*) as qtd, SUM(valor_total) as total FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ? GROUP BY tipo_entrega ORDER BY total DESC";
        $stmtTipoEnt = $db->prepare($sqlTipoEnt);
        $stmtTipoEnt->execute([$empresaId, $dataInicio, $dataFim]);
        $vendasPorTipo = $stmtTipoEnt->fetchAll(\PDO::FETCH_ASSOC);

        // =========================================================================
        // 4. CRM E MARKETING AVANÇADO (COM FILTRO RÍGIDO DE CLIENTES REAIS)
        // =========================================================================

        $filtroClientesReais = " AND cliente_telefone IS NOT NULL 
                                 AND TRIM(cliente_telefone) != '' 
                                 AND LENGTH(cliente_telefone) >= 10 
                                 AND cliente_nome IS NOT NULL 
                                 AND TRIM(cliente_nome) != '' 
                                 AND LOWER(TRIM(cliente_nome)) NOT IN ('cliente', 'consumidor', 'balcao', 'balcão', 'mesa', 'diversos', 'avulso') 
                                 AND cliente_telefone NOT LIKE '%00000000%' 
                                 AND cliente_telefone NOT LIKE '%11111111%' 
                                 AND cliente_telefone NOT LIKE '%99999999%' ";

        $sqlMkt = "SELECT COUNT(DISTINCT cliente_telefone) as clientes_unicos, COUNT(id) as total_pedidos 
                   FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND DATE(created_at) BETWEEN ? AND ? " . $filtroClientesReais;
        $stmtMkt = $db->prepare($sqlMkt);
        $stmtMkt->execute([$empresaId, $dataInicio, $dataFim]);
        $dadosMkt = $stmtMkt->fetch(\PDO::FETCH_ASSOC);
        
        $clientesUnicos = $dadosMkt['clientes_unicos'] ?: 0;
        $pedidosComTel = $dadosMkt['total_pedidos'] ?: 0;
        $taxaRecorrencia = $pedidosComTel > 0 ? (($pedidosComTel - $clientesUnicos) / $pedidosComTel) * 100 : 0;

        // REGRA DE VIP ATUALIZADA: Mais de 10 pedidos! (HAVING qtd > 10)
        // Nota: O filtro de data foi removido daqui para buscar os verdadeiros VIPs de todo o histórico da loja.
        $sqlVips = "SELECT cliente_nome, cliente_telefone, COUNT(id) as qtd, SUM(valor_total) as total 
                    FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' " . $filtroClientesReais . "
                    GROUP BY cliente_telefone, cliente_nome 
                    HAVING qtd > 10
                    ORDER BY total DESC LIMIT 5";
        $stmtVips = $db->prepare($sqlVips);
        $stmtVips->execute([$empresaId]);
        $clientesVip = $stmtVips->fetchAll(\PDO::FETCH_ASSOC);

        $sqlRisco = "SELECT cliente_nome, cliente_telefone, MAX(created_at) as ultimo_pedido, COUNT(id) as qtd, SUM(valor_total) as total_gasto 
                     FROM pedidos WHERE empresa_id = ? AND status != 'cancelado' AND created_at <= ? " . $filtroClientesReais . "
                     GROUP BY cliente_telefone, cliente_nome 
                     HAVING ultimo_pedido < DATE_SUB(?, INTERVAL 10 DAY) 
                     ORDER BY total_gasto DESC LIMIT 5";
        $stmtRisco = $db->prepare($sqlRisco);
        $stmtRisco->execute([$empresaId, $dataFimDateTime, $dataFimDateTime]);
        $clientesRisco = $stmtRisco->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: ' . BASE_URL . '/admin'); exit; }
    }
}