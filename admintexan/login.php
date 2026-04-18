<?php
require_once __DIR__ . '/_auth.php';

if (adminEstaLogado()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$adminCfg = config()['admin'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user  = trim((string)($_POST['user'] ?? ''));
    $pass  = (string)($_POST['pass'] ?? '');
    $token = (string)($_POST['_csrf'] ?? '');

    if (!isset($_SESSION['tries'])) $_SESSION['tries'] = 0;
    if ($_SESSION['tries'] >= 5 && (time() - ($_SESSION['tries_at'] ?? 0)) < 300) {
        $erro = 'Muitas tentativas. Aguarde 5 minutos.';
    } elseif (!adminCsrfValido($token)) {
        $erro = 'Sessão expirada. Tente novamente.';
    } elseif (!$adminCfg) {
        $erro = 'Painel não configurado.';
    } elseif ($user !== ($adminCfg['user'] ?? '') || !password_verify($pass, $adminCfg['pass_hash'] ?? '')) {
        $_SESSION['tries']++;
        $_SESSION['tries_at'] = time();
        $erro = 'Usuário ou senha inválidos.';
    } else {
        $_SESSION['tries'] = 0;
        $_SESSION['admin_user'] = $user;
        $_SESSION['admin_last'] = time();
        session_regenerate_id(true);
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Acesso restrito · TexanEduc</title>
<link rel="stylesheet" href="admin.css?v=2">
</head>
<body class="admin-login">
    <div class="login-bg"></div>

    <div class="login-card">
        <div class="login-brand">
            <img src="../assets/images/logo.png" alt="TexanEduc" class="login-logo">
        </div>

        <div class="login-head">
            <span class="login-tag">Acesso restrito</span>
            <h1>Painel administrativo</h1>
            <p>Informe suas credenciais para continuar</p>
        </div>

        <?php if (!empty($_GET['expirou'])): ?>
            <div class="alert">Sua sessão expirou. Faça login novamente.</div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off" class="login-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(adminCsrfToken()) ?>">

            <div class="field">
                <label for="user">Usuário</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" name="user" id="user" required autofocus placeholder="seu usuário">
                </div>
            </div>

            <div class="field">
                <label for="pass">Senha</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="pass" id="pass" required placeholder="••••••••">
                    <button type="button" class="toggle-pass" aria-label="Mostrar senha" id="togglePass">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-btn">
                Entrar
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>

        <div class="login-foot">
            <a href="../index.php">← Voltar ao site</a>
        </div>
    </div>

    <script>
    const tog = document.getElementById('togglePass');
    const inp = document.getElementById('pass');
    tog.addEventListener('click', () => {
        const isPwd = inp.type === 'password';
        inp.type = isPwd ? 'text' : 'password';
        tog.classList.toggle('on', isPwd);
    });
    </script>
</body>
</html>
