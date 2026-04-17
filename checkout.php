<?php
require_once __DIR__ . '/includes/db.php';

$evento = config()['evento'];
try {
    $preco = precoAtual();
} catch (Throwable $e) {
    $preco = [
        'pagas' => 0,
        'numero_lote' => 1,
        'nome_lote' => $evento['lotes'][0]['nome'],
        'preco' => $evento['lotes'][0]['preco'],
        'lote' => $evento['lotes'][0],
        'lotes' => $evento['lotes'],
        'preco_maximo' => max(array_column($evento['lotes'], 'preco')),
        'vagas_total' => $evento['vagas_total'],
        'vagas_restantes' => $evento['vagas_total'],
        'vagas_lote' => $evento['lotes'][0]['vagas'],
        'esgotado' => false,
        'pre_venda' => false,
    ];
}

if ($preco['esgotado']) {
    header('Location: index.php?esgotado=1');
    exit;
}

$fmt = fn(float $v) => 'R$ ' . number_format($v, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — IA PARA NEGÓCIOS</title>
<link rel="icon" href="assets/images/logo-compact.png">
<link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="page-checkout">

<header class="topbar">
    <div class="container topbar-inner">
        <a href="index.php" class="logo"><img src="assets/images/logo.png" alt="TexanEduc"></a>
        <div class="topbar-actions">
            <a href="https://wa.me/5519978033293?text=Ol%C3%A1!%20Tenho%20uma%20d%C3%BAvida%20sobre%20a%20palestra%20IA%20PARA%20NEG%C3%93CIOS" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <a href="index.php" class="link-back">← Voltar</a>
        </div>
    </div>
</header>

<main class="checkout">
    <div class="container checkout-grid">
        <section class="checkout-form">
            <h1>Inscrição</h1>
            <p class="muted">Preencha os dados abaixo. Após confirmar, você é direcionado ao pagamento.</p>

            <form id="form-inscricao" novalidate>
                <fieldset>
                    <legend>Dados do participante</legend>

                    <label>Nome completo *
                        <input type="text" name="nome" required maxlength="150">
                    </label>

                    <label>E-mail *
                        <input type="email" name="email" required maxlength="150">
                    </label>

                    <div class="row-2">
                        <label>CPF ou CNPJ *
                            <input type="text" name="cpf_cnpj" required maxlength="20" placeholder="000.000.000-00">
                        </label>
                        <label>Telefone (WhatsApp) *
                            <input type="text" name="telefone" required maxlength="20" placeholder="(00) 00000-0000">
                        </label>
                    </div>

                    <div class="row-2">
                        <label>Empresa
                            <input type="text" name="empresa" maxlength="150">
                        </label>
                        <label>Cargo
                            <input type="text" name="cargo" maxlength="100">
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Endereço</legend>

                    <div class="row-2">
                        <label>CEP *
                            <input type="text" name="cep" required maxlength="9" placeholder="00000-000">
                        </label>
                        <label>Estado *
                            <input type="text" name="estado" required maxlength="2" placeholder="SP" style="text-transform:uppercase">
                        </label>
                    </div>

                    <label>Logradouro (rua/avenida) *
                        <input type="text" name="logradouro" required maxlength="200">
                    </label>

                    <div class="row-2">
                        <label>Número *
                            <input type="text" name="numero" required maxlength="20">
                        </label>
                        <label>Complemento
                            <input type="text" name="complemento" maxlength="100">
                        </label>
                    </div>

                    <div class="row-2">
                        <label>Bairro *
                            <input type="text" name="bairro" required maxlength="100">
                        </label>
                        <label>Cidade *
                            <input type="text" name="cidade" required maxlength="100">
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Forma de pagamento</legend>
                    <div class="payment-toggle">
                        <label class="pay-option">
                            <input type="radio" name="metodo" value="PIX" checked>
                            <span><strong>PIX</strong><small>Aprovação imediata</small></span>
                        </label>
                        <label class="pay-option">
                            <input type="radio" name="metodo" value="CREDIT_CARD">
                            <span><strong>Cartão de crédito</strong><small>Em até <?= $evento['parcelas_max'] ?>x</small></span>
                        </label>
                    </div>

                    <div id="bloco-cartao" hidden>
                        <label>Número do cartão *
                            <input type="text" name="cc_number" maxlength="19" placeholder="0000 0000 0000 0000">
                        </label>
                        <label>Nome impresso no cartão *
                            <input type="text" name="cc_holder" maxlength="100">
                        </label>
                        <div class="row-3">
                            <label>Mês *
                                <input type="text" name="cc_month" maxlength="2" placeholder="MM">
                            </label>
                            <label>Ano *
                                <input type="text" name="cc_year" maxlength="4" placeholder="AAAA">
                            </label>
                            <label>CVV *
                                <input type="text" name="cc_cvv" maxlength="4" placeholder="123">
                            </label>
                        </div>
                        <label>Parcelas
                            <select name="cc_parcelas" id="cc_parcelas">
                                <?php for ($i = 1; $i <= $evento['parcelas_max']; $i++):
                                    $info = valorComTaxaCartao((float)$preco['preco'], $i);
                                    $label = $i === 1
                                        ? "1x de " . $fmt($info['parcela']) . " · total " . $fmt($info['total'])
                                        : "{$i}x de " . $fmt($info['parcela']) . " · total " . $fmt($info['total']);
                                ?>
                                    <option value="<?= $i ?>" data-total="<?= $info['total'] ?>" data-parcela="<?= $info['parcela'] ?>" data-taxa="<?= $info['taxa'] ?>">
                                        <?= $label ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <small class="muted">Taxa do cartão repassada ao participante. PIX não tem taxa.</small>
                        </label>
                    </div>
                </fieldset>

                <div class="form-msg" id="form-msg" hidden></div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-pagar"
                        data-base="<?= $preco['preco'] ?>" data-base-fmt="<?= $fmt($preco['preco']) ?>">
                    Confirmar inscrição · <?= $fmt($preco['preco']) ?>
                </button>

                <p class="terms">Ao concluir, você concorda com os termos do evento. Pagamento processado pela Asaas.</p>
            </form>
        </section>

        <aside class="checkout-summary">
            <h3>Resumo da inscrição</h3>
            <div class="sum-row"><span>Palestra</span><strong>IA PARA NEGÓCIOS</strong></div>
            <div class="sum-row"><span>Data</span><strong><?= htmlspecialchars($evento['data']) ?></strong></div>
            <div class="sum-row"><span>Local</span><strong><?= htmlspecialchars($evento['local']) ?></strong></div>
            <div class="sum-row"><span>Horário</span><strong><?= htmlspecialchars($evento['horario']) ?></strong></div>
            <hr>
            <div class="sum-row" id="sum-taxa-row" hidden>
                <span>Taxa do cartão</span>
                <strong id="sum-taxa">R$ 0,00</strong>
            </div>
            <div class="sum-row sum-total">
                <span>Total</span>
                <strong id="sum-total"><?= $fmt($preco['preco']) ?></strong>
            </div>
            <?php if ($preco['preco'] < $preco['preco_maximo']): ?>
                <p class="muted small"><?= htmlspecialchars($preco['nome_lote']) ?> · economia de <?= $fmt($preco['preco_maximo'] - $preco['preco']) ?></p>
            <?php endif; ?>
            <ul class="sum-includes">
                <li>✓ Participação na palestra</li>
                <li>✓ Coffee break</li>
                <li>✓ Certificado</li>
                <li>✓ Material apresentado</li>
            </ul>
        </aside>
    </div>
</main>

<script src="assets/js/main.js?v=1"></script>
<script src="assets/js/checkout.js?v=1"></script>

<elevenlabs-convai agent-id="agent_4501kpbqc4xbemps4n1ys9panxqa"></elevenlabs-convai>
<script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
</body>
</html>
