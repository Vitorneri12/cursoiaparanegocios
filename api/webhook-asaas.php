<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/email.php';

$cfg = config()['asaas'];

// Validacao do token configurado no painel Asaas (Webhook > Token de autenticacao)
$tokenRecebido = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? '';
if (!empty($cfg['webhook_token']) && !hash_equals($cfg['webhook_token'], $tokenRecebido)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'token invalido']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?: [];
$evento  = $payload['event'] ?? '';
$pay     = $payload['payment'] ?? [];
$paymentId = $pay['id'] ?? null;

try {
    db()->prepare("INSERT INTO webhook_logs (evento, payment_id, payload) VALUES (?,?,?)")
        ->execute([$evento, $paymentId, $raw]);

    if (!$paymentId) {
        echo json_encode(['ok' => true, 'msg' => 'sem payment id']);
        exit;
    }

    $statusMap = [
        'PAYMENT_CONFIRMED'      => 'CONFIRMED',
        'PAYMENT_RECEIVED'       => 'RECEIVED',
        'PAYMENT_OVERDUE'        => 'OVERDUE',
        'PAYMENT_REFUNDED'       => 'REFUNDED',
        'PAYMENT_DELETED'        => 'CANCELLED',
        'PAYMENT_RESTORED'       => 'PENDING',
        'PAYMENT_CHARGEBACK'     => 'REFUNDED',
    ];

    $novoStatus = $statusMap[$evento] ?? null;
    if ($novoStatus !== null) {
        $stmt = db()->prepare("
            UPDATE inscricoes
            SET status = ?, data_pagamento = CASE WHEN ? IN ('CONFIRMED','RECEIVED') AND data_pagamento IS NULL THEN NOW() ELSE data_pagamento END
            WHERE asaas_payment_id = ?
        ");
        $stmt->execute([$novoStatus, $novoStatus, $paymentId]);

        if (in_array($novoStatus, ['CONFIRMED','RECEIVED'], true)) {
            $insc = db()->prepare("SELECT * FROM inscricoes WHERE asaas_payment_id = ?");
            $insc->execute([$paymentId]);
            $registro = $insc->fetch();
            if ($registro) {
                @enviarEmailConfirmacao($registro);
            }
        }
    }

    db()->prepare("UPDATE webhook_logs SET processado = 1 WHERE payment_id = ? AND evento = ?")
        ->execute([$paymentId, $evento]);

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
