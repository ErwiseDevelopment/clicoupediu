<?php
namespace App\Controllers;

use App\Core\Database;

class WebhookController {

    public function receber() {
        // 1. Recebe o JSON do Asaas
        $json = file_get_contents('php://input');
        $evento = json_decode($json, true);

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

                // A. Busca a Fatura Pendente
                $stmt = $db->prepare("SELECT id, empresa_id, status FROM faturas WHERE asaas_payment_id = ?");
                $stmt->execute([$paymentId]);
                $faturaLocal = $stmt->fetch();

                if ($faturaLocal) {
                    // Se já estiver marcada como PAGO, ignora
                    if ($faturaLocal['status'] == 'PAGO') {
                        $db->rollBack();
                        echo json_encode(['status' => 'Já processado anteriormente']);
                        exit;
                    }

                    // ATUALIZAÇÃO: Aqui forçamos o status para 'PAGO'
                    $db->prepare("UPDATE faturas SET status = 'PAGO', data_pagamento = NOW() WHERE id = ?")
                       ->execute([$faturaLocal['id']]);
                    
                    $empresaId = $faturaLocal['empresa_id'];

                } else {
                    // Fallback: Se não achar a fatura, busca a empresa pelo Customer ID
                    $customerId = $pagamento['customer'];
                    $stmtEmp = $db->prepare("SELECT id FROM empresas WHERE asaas_customer_id = ?");
                    $stmtEmp->execute([$customerId]);
                    $empresa = $stmtEmp->fetch();

                    if (!$empresa) {
                        $db->rollBack();
                        echo json_encode(['status' => 'Empresa não encontrada']);
                        exit;
                    }

                    $empresaId = $empresa['id'];

                    // INSERÇÃO: Aqui também forçamos o status para 'PAGO'
                    $sqlInsert = "INSERT INTO faturas (empresa_id, asaas_payment_id, valor, status, forma_pagamento, url_pagamento, data_pagamento) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $db->prepare($sqlInsert)->execute([
                        $empresaId, 
                        $paymentId, 
                        $pagamento['value'], 
                        'PAGO', // <--- Forçando 'PAGO'
                        $pagamento['billingType'], 
                        $pagamento['invoiceUrl'] ?? ''
                    ]);
                }

                // B. Renova Licença
                $this->renovarLicenca($db, $empresaId);

                $db->commit();
                http_response_code(200);
                echo json_encode(['status' => 'Sucesso: Licença Renovada e Fatura marcada como PAGO']);

            } catch (\Exception $e) {
                $db->rollBack();
                http_response_code(500); 
                echo json_encode(['erro' => $e->getMessage()]);
            }

        } else {
            // Ignora outros eventos (cobrança criada, vencida, etc)
            http_response_code(200);
            echo json_encode(['status' => 'Evento ignorado']);
        }
    }

    private function renovarLicenca($db, $empresaId) {
        $stmt = $db->prepare("SELECT licenca_validade FROM empresas WHERE id = ?");
        $stmt->execute([$empresaId]);
        $empresa = $stmt->fetch();

        $hoje = date('Y-m-d');
        $atual = $empresa['licenca_validade'];

        // Lógica de Renovação:
        // Se a data atual for maior que hoje (pagou adiantado), soma +30 dias nela.
        // Se a data já passou (estava vencido), soma +30 dias a partir de HOJE.
        if ($atual && $atual > $hoje) {
            $novaValidade = date('Y-m-d', strtotime($atual . ' +30 days'));
        } else {
            $novaValidade = date('Y-m-d', strtotime($hoje . ' +30 days'));
        }

        $db->prepare("UPDATE empresas SET licenca_validade = ?, licenca_tipo = 'ATIVO' WHERE id = ?")
           ->execute([$novaValidade, $empresaId]);
    }
}