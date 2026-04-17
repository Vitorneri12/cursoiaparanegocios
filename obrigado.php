<?php
require_once __DIR__ . '/includes/db.php';

$paymentId = $_GET['payment'] ?? '';
$metodo    = $_GET['metodo'] ?? '';
$registro  = null;

if ($paymentId) {
    $stmt = db()->prepare("SELECT * FROM inscricoes WHERE asaas_payment_id = ?");
    $stmt->execute([$paymentId]);
    $registro = $stmt->fetch();
}

$pagoNoCartao = $registro && in_array($registro['status'], ['CONFIRMED','RECEIVED'], true);
$ehPix = ($metodo === 'PIX') || ($registro && $registro['metodo_pagamento'] === 'PIX');
$pixData = json_decode($_GET['pix'] ?? '[]', true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscrição confirmada — IA PARA NEGÓCIOS</title>
<link rel="icon" href="assets/images/logo-compact.png">
<link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="page-obrigado">

<header class="topbar">
    <div class="container topbar-inner">
        <a href="index.php" class="logo"><img src="assets/images/logo.png" alt="TexanEduc"></a>
        <div class="topbar-actions">
            <a href="https://wa.me/5519978033293" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
        </div>
    </div>
</header>

<main class="thanks">
    <div class="container thanks-card">
        <?php if ($ehPix && !$pagoNoCartao): ?>
            <h1>📱 Pague com PIX para garantir sua vaga</h1>
            <p class="muted">Aponte a câmera para o QR Code abaixo ou copie e cole o código no app do seu banco.</p>

            <?php if (!empty($pixData['qrCodeBase64'])): ?>
                <div class="qr-wrap">
                    <img src="data:image/png;base64,<?= htmlspecialchars($pixData['qrCodeBase64']) ?>" alt="QR Code PIX">
                </div>
            <?php endif; ?>

            <?php if (!empty($pixData['copiaCola'])): ?>
                <label class="copy-label">PIX Copia e Cola</label>
                <div class="copy-row">
                    <input type="text" id="pix-copia" readonly value="<?= htmlspecialchars($pixData['copiaCola']) ?>">
                    <button class="btn btn-primary" id="btn-copiar">Copiar</button>
                </div>
            <?php endif; ?>

            <div id="status-pix" class="status-pix">⏳ Aguardando confirmação do pagamento...</div>

        <?php elseif ($pagoNoCartao): ?>
            <div class="check-icon">✓</div>
            <h1>Pagamento confirmado!</h1>
            <p>Sua inscrição na palestra <strong>IA PARA NEGÓCIOS</strong> está garantida.</p>
            <p class="muted">Enviamos a confirmação para <strong><?= htmlspecialchars($registro['email']) ?></strong>.</p>

            <div class="info-evento">
                <div><strong>Local</strong><br>TRYP by Wyndham Ribeirão Preto</div>
                <div><strong>Data</strong><br>09 de maio</div>
                <div><strong>Horário</strong><br>09h30 às 16h00</div>
            </div>
        <?php else: ?>
            <h1>Inscrição registrada</h1>
            <p>Aguardando processamento do pagamento. Você receberá a confirmação por e-mail.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary mt">Voltar à página inicial</a>
    </div>
</main>

<script>
(function () {
    const btn = document.getElementById('btn-copiar');
    const inp = document.getElementById('pix-copia');
    if (btn && inp) {
        btn.addEventListener('click', () => {
            inp.select();
            navigator.clipboard.writeText(inp.value).then(() => {
                btn.textContent = 'Copiado!';
                setTimeout(() => btn.textContent = 'Copiar', 2000);
            });
        });
    }

    const paymentId = <?= json_encode($paymentId) ?>;
    const statusEl = document.getElementById('status-pix');
    if (paymentId && statusEl) {
        const check = async () => {
            try {
                const r = await fetch('api/consultar-pagamento.php?id=' + encodeURIComponent(paymentId));
                const j = await r.json();
                if (j.ok && j.pago) {
                    statusEl.innerHTML = '✓ Pagamento confirmado! Enviamos sua confirmação por e-mail.';
                    statusEl.classList.add('ok');
                    return true;
                }
            } catch (e) {}
            return false;
        };
        const interval = setInterval(async () => {
            if (await check()) clearInterval(interval);
        }, 5000);
    }
})();
</script>

<elevenlabs-convai agent-id="agent_4501kpbqc4xbemps4n1ys9panxqa"></elevenlabs-convai>
<script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
</body>
</html>
