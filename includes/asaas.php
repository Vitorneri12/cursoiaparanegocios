<?php
require_once __DIR__ . '/db.php';

function asaasRequest(string $method, string $path, ?array $body = null): array {
    $cfg = config()['asaas'];
    $url = rtrim($cfg['base_url'], '/') . $path;

    $ch = curl_init($url);
    $headers = [
        'access_token: ' . $cfg['api_key'],
        'Content-Type: application/json',
        'User-Agent: TexanEduc-LP/1.0',
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException("Falha na requisicao Asaas: $err");
    }

    $data = json_decode($response, true);
    if ($status >= 400) {
        $msg = $data['errors'][0]['description'] ?? "Erro Asaas HTTP $status";
        throw new RuntimeException($msg);
    }

    return $data ?? [];
}

function asaasCriarCliente(array $dados): array {
    return asaasRequest('POST', '/customers', [
        'name'       => $dados['nome'],
        'email'      => $dados['email'],
        'cpfCnpj'    => preg_replace('/\D/', '', $dados['cpf_cnpj']),
        'mobilePhone'=> preg_replace('/\D/', '', $dados['telefone']),
        'company'    => $dados['empresa'] ?? null,
    ]);
}

function asaasCriarCobrancaPix(string $customerId, float $valor, string $descricao): array {
    $cobranca = asaasRequest('POST', '/payments', [
        'customer'    => $customerId,
        'billingType' => 'PIX',
        'value'       => $valor,
        'dueDate'     => date('Y-m-d', strtotime('+1 day')),
        'description' => $descricao,
    ]);

    $qr = asaasRequest('GET', "/payments/{$cobranca['id']}/pixQrCode");
    $cobranca['pix'] = [
        'qrCodeBase64' => $qr['encodedImage'] ?? null,
        'copiaCola'    => $qr['payload'] ?? null,
        'expiraEm'     => $qr['expirationDate'] ?? null,
    ];

    return $cobranca;
}

function asaasCriarCobrancaCartao(string $customerId, float $valor, string $descricao, array $cartao, array $titular, int $parcelas = 1): array {
    $payload = [
        'customer'    => $customerId,
        'billingType' => 'CREDIT_CARD',
        'value'       => $valor,
        'dueDate'     => date('Y-m-d'),
        'description' => $descricao,
        'creditCard' => [
            'holderName'  => $cartao['holderName'],
            'number'      => preg_replace('/\D/', '', $cartao['number']),
            'expiryMonth' => $cartao['expiryMonth'],
            'expiryYear'  => $cartao['expiryYear'],
            'ccv'         => $cartao['ccv'],
        ],
        'creditCardHolderInfo' => [
            'name'              => $titular['nome'],
            'email'             => $titular['email'],
            'cpfCnpj'           => preg_replace('/\D/', '', $titular['cpf_cnpj']),
            'postalCode'        => preg_replace('/\D/', '', $titular['cep']),
            'addressNumber'     => $titular['numero'],
            'addressComplement' => $titular['complemento'] ?? null,
            'phone'             => preg_replace('/\D/', '', $titular['telefone']),
            'mobilePhone'       => preg_replace('/\D/', '', $titular['telefone']),
        ],
        'remoteIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    ];

    if ($parcelas > 1) {
        $payload['installmentCount'] = $parcelas;
        $payload['installmentValue'] = round($valor / $parcelas, 2);
        unset($payload['value']);
    }

    return asaasRequest('POST', '/payments', $payload);
}

function asaasConsultarPagamento(string $paymentId): array {
    return asaasRequest('GET', "/payments/{$paymentId}");
}
