<?php
namespace App\Controllers;

use App\Core\Database;

class WebhookController {

    public function receber() {
        // 1. Recebe o JSON do Asaas
        $json = file_get_contents('php://input');
        $evento = json_decode($json, true);

        // Debug Opcional: Salva o log na raiz para você ver o que chegou
        // file_put_contents(__DIR__ . '/../../webhook_log.txt', print_r($evento, true), FILE_APPEND);

        // 2. Validações Básicas
        if (!isset($evento['event']) || !isset($evento['payment'])) {
            http_response_code(400); 
            echo json_encode(['status' => 'Ignorado: JSON inválido']);
            exit;
        }

        $tipoEvento = $evento['event'];
        $pagamento  = $evento['payment'];
        $paymentId  = $pagamento['id'];

        $db = Database::connect();

        // 3. Processa Apenas Pagamentos Confirmados
        if ($tipoEvento == 'PAYMENT_RECEIVED' || $tipoEvento == 'PAYMENT_CONFIRMED') {
            
            try {
                $db->beginTransaction();

                // A. Busca a Fatura Pendente no seu banco pelo ID do Asaas
                $stmt = $db->prepare("SELECT id, empresa_id, status FROM faturas WHERE asaas_payment_id = ?");
                $stmt->execute([$paymentId]);
                $faturaLocal = $stmt->fetch();

                if ($faturaLocal) {
                    // Cenario 1: A fatura já existe (Gerada pelo seu Cron ou Botão) -> FAZ UPDATE
                    // Se já estiver paga, ignora para não somar data duas vezes
                    if ($faturaLocal['status'] == 'RECEIVED' || $faturaLocal['status'] == 'CONFIRMED') {
                        $db->rollBack();
                        echo json_encode(['status' => 'Já processado anteriormente']);
                        exit;
                    }

                    $db->prepare("UPDATE faturas SET status = 'CONFIRMED', data_pagamento = NOW() WHERE id = ?")
                       ->execute([$faturaLocal['id']]);
                    
                    $empresaId = $faturaLocal['empresa_id'];

                } else {
                    // Cenario 2: A fatura não existe no banco local (talvez gerada no painel do Asaas manualmente?)
                    // Vamos tentar achar a empresa pelo Customer ID para não perder o pagamento
                    $customerId = $pagamento['customer'];
                    $stmtEmp = $db->prepare("SELECT id FROM empresas WHERE asaas_customer_id = ?");
                    $stmtEmp->execute([$customerId]);
                    $empresa = $stmtEmp->fetch();

                    if (!$empresa) {
                        // Não achou nem a fatura nem a empresa, desiste.
                        $db->rollBack();
                        echo json_encode(['status' => 'Empresa não encontrada']);
                        exit;
                    }

                    $empresaId = $empresa['id'];

                    // Cria o registro da fatura para ficar no histórico
                    $sqlInsert = "INSERT INTO faturas (empresa_id, asaas_payment_id, valor, status, forma_pagamento, url_pagamento, data_pagamento) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $db->prepare($sqlInsert)->execute([
                        $empresaId, 
                        $paymentId, 
                        $pagamento['value'], 
                        'CONFIRMED', 
                        $pagamento['billingType'], 
                        $pagamento['invoiceUrl'] ?? ''
                    ]);
                }

                // B. RENOVAÇÃO DA LICENÇA (Ajustado para suas colunas: licenca_validade)
                $this->renovarLicenca($db, $empresaId);

                $db->commit();
                http_response_code(200);
                echo json_encode(['status' => 'Sucesso: Licença Renovada']);

            } catch (\Exception $e) {
                $db->rollBack();
                http_response_code(500); // Erro interno
                echo json_encode(['erro' => $e->getMessage()]);
            }

        } 
        // 4. Tratamento de Inadimplência (Opcional: Bloquear se vencer)
        elseif ($tipoEvento == 'PAYMENT_OVERDUE') {
            // Você pode implementar bloqueio automático aqui se quiser
            // $customerId = $pagamento['customer'];
            // $db->prepare("UPDATE empresas SET licenca_tipo = 'BLOQUEADO' WHERE asaas_customer_id = ?")->execute([$customerId]);
            
            http_response_code(200);
            echo json_encode(['status' => 'Fatura vencida processada']);
        } 
        else {
            // Outros eventos (Cobrança criada, etc) apenas ignoramos com 200 OK
            http_response_code(200);
            echo json_encode(['status' => 'Evento ignorado']);
        }
    }

    // Lógica Inteligente de Renovação
    private function renovarLicenca($db, $empresaId) {
        // Busca validade atual
        $stmt = $db->prepare("SELECT licenca_validade FROM empresas WHERE id = ?");
        $stmt->execute([$empresaId]);
        $empresa = $stmt->fetch();

        $hoje = date('Y-m-d');
        $atual = $empresa['licenca_validade'];

        // Se a data atual for FUTURA (ele pagou adiantado), soma +30 dias nela.
        // Se a data já passou (estava vencido), soma +30 dias a partir de HOJE.
        if ($atual && $atual > $hoje) {
            $novaValidade = date('Y-m-d', strtotime($atual . ' +30 days'));
        } else {
            $novaValidade = date('Y-m-d', strtotime($hoje . ' +30 days'));
        }

        // Atualiza usando as colunas corretas do seu banco
        $db->prepare("UPDATE empresas SET licenca_validade = ?, licenca_tipo = 'ATIVO' WHERE id = ?")
           ->execute([$novaValidade, $empresaId]);
    }
}