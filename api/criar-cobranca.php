<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/asaas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Metodo nao permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

function req(array $arr, string $key): string {
    $v = trim((string)($arr[$key] ?? ''));
    if ($v === '') {
        throw new RuntimeException("Campo obrigatorio: $key");
    }
    return $v;
}

try {
    $nome     = req($input, 'nome');
    $email    = req($input, 'email');
    $cpfCnpj  = req($input, 'cpf_cnpj');
    $telefone = req($input, 'telefone');
    $empresa  = trim((string)($input['empresa'] ?? ''));
    $cargo    = trim((string)($input['cargo'] ?? ''));
    $cep         = req($input, 'cep');
    $logradouro  = req($input, 'logradouro');
    $numero      = req($input, 'numero');
    $complemento = trim((string)($input['complemento'] ?? ''));
    $bairro      = req($input, 'bairro');
    $cidade      = req($input, 'cidade');
    $estado      = strtoupper(req($input, 'estado'));
    $metodo   = req($input, 'metodo');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('E-mail invalido');
    }
    if (!in_array($metodo, ['PIX', 'CREDIT_CARD'], true)) {
        throw new RuntimeException('Metodo de pagamento invalido');
    }

    $preco = precoAtual();
    if ($preco['esgotado']) {
        throw new RuntimeException('Evento esgotado');
    }
    $valorBase = (float) $preco['preco'];
    $valor = $valorBase;
    $parcelas = max(1, (int)($input['cc_parcelas'] ?? 1));

    if ($metodo === 'CREDIT_CARD') {
        $info = valorComTaxaCartao($valorBase, $parcelas);
        $valor = $info['total'];
    }

    $cliente = asaasCriarCliente([
        'nome'     => $nome,
        'email'    => $email,
        'cpf_cnpj' => $cpfCnpj,
        'telefone' => $telefone,
        'empresa'  => $empresa ?: null,
    ]);
    $customerId = $cliente['id'];

    $descricao = 'Inscricao Palestra IA PARA NEGOCIOS - 09/05';

    if ($metodo === 'PIX') {
        $cobranca = asaasCriarCobrancaPix($customerId, $valor, $descricao);
    } else {
        $cartao = [
            'holderName'  => req($input, 'cc_holder'),
            'number'      => req($input, 'cc_number'),
            'expiryMonth' => req($input, 'cc_month'),
            'expiryYear'  => req($input, 'cc_year'),
            'ccv'         => req($input, 'cc_cvv'),
        ];
        $titular = [
            'nome'        => $nome,
            'email'       => $email,
            'cpf_cnpj'    => $cpfCnpj,
            'cep'         => $cep,
            'numero'      => $numero,
            'complemento' => $complemento,
            'telefone'    => $telefone,
        ];
        $cobranca = asaasCriarCobrancaCartao($customerId, $valor, $descricao, $cartao, $titular, $parcelas);
    }

    $stmt = db()->prepare("
        INSERT INTO inscricoes
        (nome, email, cpf_cnpj, telefone, empresa, cargo,
         cep, logradouro, numero, complemento, bairro, cidade, estado,
         valor, metodo_pagamento, asaas_customer_id, asaas_payment_id, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $nome, $email, $cpfCnpj, $telefone,
        $empresa ?: null, $cargo ?: null,
        $cep, $logradouro, $numero, $complemento ?: null, $bairro, $cidade, $estado,
        $valor, $metodo, $customerId, $cobranca['id'],
        $cobranca['status'] ?? 'PENDING',
    ]);
    $inscricaoId = (int) db()->lastInsertId();

    $resp = [
        'ok'           => true,
        'inscricao_id' => $inscricaoId,
        'payment_id'   => $cobranca['id'],
        'status'       => $cobranca['status'] ?? 'PENDING',
        'metodo'       => $metodo,
    ];

    if ($metodo === 'PIX' && !empty($cobranca['pix'])) {
        $resp['pix'] = $cobranca['pix'];
    }

    echo json_encode($resp);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
