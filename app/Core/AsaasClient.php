<?php

namespace App\Core;

class AsaasClient {
    private $apiKey;
    private $apiUrl;

    public function __construct($apiKey, $sandbox = false) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $sandbox ? 'https://sandbox.asaas.com/api/v3' : 'https://www.asaas.com/api/v3';
    }
    /**
     * Função Central de Requisição (CURL)
     */
    public function request($endpoint, $method, $data = []) {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $this->apiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $this->apiKey,
                'User-Agent: EdfinanceSystem/1.0'
            ],
        ];

        if (!empty($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($err) {
            return ['code' => 0, 'error' => "cURL Error: $err"];
        }

        return [
            'code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function updateSubscription($subscriptionId, $data) {
        return $this->request('/subscriptions/' . $subscriptionId, 'POST', $data);
    }

    // ==========================================================
    // MÉTODOS DE CLIENTES
    // ==========================================================

public function createCustomer($name, $cpfCnpj, $email) {
        // Limpa o CPF/CNPJ para enviar apenas números
        $cpfCnpjLimpo = preg_replace('/[^0-9]/', '', $cpfCnpj ?? '');

        // === DEBUG (Vai aparecer no log de erro ou na tela se der erro) ===
        // error_log("ASAAS DEBUG: Tentando criar/atualizar cliente: $name | Doc: $cpfCnpjLimpo | Email: $email");

        // 1. Tenta buscar se já existe pelo email
        $existing = $this->request('/customers?email=' . urlencode($email), 'GET');
        
        if (isset($existing['body']['data'][0])) {
            $clienteExistente = $existing['body']['data'][0];
            $idAsaas = $clienteExistente['id'];

            // CORREÇÃO CRUCIAL:
            // Se achou o cliente, NÓS FORÇAMOS A ATUALIZAÇÃO DO CPF/CNPJ
            // Isso resolve o erro de clientes antigos sem documento.
            $update = $this->updateCustomer($idAsaas, [
                'cpfCnpj' => $cpfCnpjLimpo,
                'name' => $name // Aproveita e atualiza o nome se mudou
            ]);

            // Se der erro na atualização (ex: CPF inválido), vamos ver
            if ($update['code'] !== 200) {
                 // Descomente abaixo para ver o erro na tela se precisar
                 // die("Erro ao atualizar cliente existente no Asaas: " . json_encode($update));
            }

            return ['code' => 200, 'body' => $clienteExistente];
        }

        // 2. Se não existe, cria um novo do zero
        return $this->request('/customers', 'POST', [
            'name' => $name,
            'cpfCnpj' => $cpfCnpjLimpo,
            'email' => $email
        ]);
    }

    /**
     * Atualiza os dados de um cliente existente no Asaas
     * Útil para corrigir CPFs ausentes ou atualizar e-mails
     */
    public function updateCustomer($customerId, $data) {
        return $this->request('/customers/' . $customerId, 'POST', $data);
    }

    // ==========================================================
    // MÉTODOS DE ASSINATURAS E PAGAMENTOS
    // ==========================================================

    /**
     * ATUALIZADO: Suporte a Cartão de Crédito
     * Se $billingType for 'CREDIT_CARD', o array $cardData é obrigatório.
     */
    public function createSubscription($customerId, $value, $billingType = 'BOLETO', $cycle = 'MONTHLY', $cardData = null) {
        
        $payload = [
            'customer' => $customerId,
            'billingType' => $billingType, // BOLETO, PIX, CREDIT_CARD
            'value' => $value,
            'cycle' => $cycle, // MONTHLY, YEARLY
            'description' => 'Assinatura Edfinance Pro'
        ];

        // Lógica para Cartão de Crédito
        if ($billingType === 'CREDIT_CARD' && !empty($cardData)) {
            
            // Cartão cobra hoje (imediato)
            $payload['nextDueDate'] = date('Y-m-d');

            // Dados Sensíveis do Cartão
            $payload['creditCard'] = [
                'holderName' => $cardData['holderName'],
                'number' => $cardData['number'],
                'expiryMonth' => $cardData['expiryMonth'],
                'expiryYear' => $cardData['expiryYear'],
                'ccv' => $cardData['ccv']
            ];

            // Dados do Titular do Cartão (Antifraude Obrigatório)
            $payload['creditCardHolderInfo'] = [
                'name' => $cardData['holderName'],
                'email' => $cardData['email'],
                'cpfCnpj' => $cardData['cpfCnpj'],
                'postalCode' => $cardData['postalCode'],
                'addressNumber' => $cardData['addressNumber'],
                'phone' => $cardData['phone']
            ];
            
        } else {
            // Boleto e Pix vencem em 3 dias para dar tempo de pagar
            $payload['nextDueDate'] = date('Y-m-d', strtotime('+3 days'));
        }

        return $this->request('/subscriptions', 'POST', $payload);
    }

    /**
     * Pega a lista de pagamentos gerados por uma assinatura
     * Útil para pegar o ID da cobrança e gerar o QR Code
     */
    public function getSubscriptionPayments($subscriptionId) {
        return $this->request('/subscriptions/' . $subscriptionId . '/payments', 'GET');
    }

    /**
     * Pega detalhes de UMA assinatura específica
     * Útil para verificar se está ACTIVE, OVERDUE ou EXPIRED
     */
    public function getSubscription($subscriptionId) {
        return $this->request('/subscriptions/' . $subscriptionId, 'GET');
    }

    // ==========================================================
    // MÉTODOS VISUAIS (PIX E BOLETO)
    // ==========================================================

    /**
     * Retorna o Payload (Copia e Cola) e a Imagem Base64 do QR Code
     */
    public function getPixQrCode($paymentId) {
        return $this->request('/payments/' . $paymentId . '/pixQrCode', 'GET');
    }

    /**
     * Retorna a Linha Digitável e o Código de Barras do Boleto
     */
    public function getBoletoCode($paymentId) {
        return $this->request('/payments/' . $paymentId . '/identificationField', 'GET');
    }

    // Adicione estes métodos à sua classe AsaasClient
    public function getCustomerPayments($customerId, $status = 'RECEIVED') {
        // Se status for vazio ou null, remove o filtro da URL para trazer todos
        $query = "?customer=$customerId";
        if (!empty($status)) {
            $query .= "&status=$status";
        }
        return $this->request("/payments$query", 'GET');
    }

        public function cancelSubscription($subscriptionId) {
        return $this->request('/subscriptions/' . $subscriptionId, 'DELETE');
    }
}
?>