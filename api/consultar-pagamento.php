<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/asaas.php';

$paymentId = $_GET['id'] ?? '';
if (!$paymentId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'id obrigatorio']);
    exit;
}

try {
    $insc = db()->prepare("SELECT id, status FROM inscricoes WHERE asaas_payment_id = ?");
    $insc->execute([$paymentId]);
    $local = $insc->fetch();

    $remoto = asaasConsultarPagamento($paymentId);
    $statusRemoto = $remoto['status'] ?? 'PENDING';

    if ($local && $local['status'] !== $statusRemoto && in_array($statusRemoto, ['CONFIRMED','RECEIVED'], true)) {
        db()->prepare("UPDATE inscricoes SET status = ?, data_pagamento = COALESCE(data_pagamento, NOW()) WHERE asaas_payment_id = ?")
            ->execute([$statusRemoto, $paymentId]);
    }

    echo json_encode([
        'ok'     => true,
        'status' => $statusRemoto,
        'pago'   => in_array($statusRemoto, ['CONFIRMED','RECEIVED'], true),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
