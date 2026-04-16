<?php
require_once __DIR__ . '/db.php';

function enviarEmailConfirmacao(array $insc): bool {
    $cfg = config()['email'];
    $evento = config()['evento'];

    $assunto = '✓ Inscrição confirmada — Palestra IA PARA NEGÓCIOS';
    $valorFmt = 'R$ ' . number_format((float)$insc['valor'], 2, ',', '.');

    $html = '
    <div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#222;">
        <div style="background:#222;padding:24px;text-align:center;">
            <h1 style="color:#fbc02d;margin:0;font-size:22px;">TexanEduc</h1>
        </div>
        <div style="padding:32px 24px;background:#fff;">
            <h2 style="color:#222;">Olá, ' . htmlspecialchars($insc['nome']) . '!</h2>
            <p>Sua inscrição na palestra <strong>' . htmlspecialchars($evento['nome']) . '</strong> foi <strong>confirmada</strong>.</p>

            <table style="width:100%;border-collapse:collapse;margin:24px 0;">
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Data</strong></td><td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($evento['data']) . '</td></tr>
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Horário</strong></td><td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($evento['horario']) . '</td></tr>
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Local</strong></td><td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($evento['local']) . '</td></tr>
                <tr><td style="padding:8px;"><strong>Valor pago</strong></td><td style="padding:8px;">' . $valorFmt . '</td></tr>
            </table>

            <div style="background:#fff8e1;border-left:4px solid #fbc02d;padding:16px;margin:24px 0;">
                <strong>Inclui:</strong> participação na palestra, coffee break, certificado e material apresentado.
            </div>

            <p>Apresente este e-mail (ou um documento com foto) na recepção do evento.</p>
            <p style="margin-top:32px;">Dúvidas: <a href="mailto:contato@texaneduc.com.br">contato@texaneduc.com.br</a></p>
        </div>
        <div style="background:#f5f5f5;padding:16px;text-align:center;color:#888;font-size:12px;">
            © ' . date('Y') . ' TexanEduc — Soluções em Tecnologia e Inteligência Artificial
        </div>
    </div>';

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . $cfg['remetente_nome'] . ' <' . $cfg['remetente_email'] . '>',
        'Reply-To: ' . $cfg['remetente_email'],
        'X-Mailer: PHP/' . phpversion(),
    ];

    return @mail($insc['email'], $assunto, $html, implode("\r\n", $headers));
}
