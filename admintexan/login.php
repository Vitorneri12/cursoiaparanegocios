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

    // Throttle simples para forca bruta
    if (!isset($_SESSION['tries'])) $_SESSION['tries'] = 0;
    if ($_SESSION['tries'] >= 5 && (time() - ($_SESSION['tries_at'] ?? 0)) < 300) {
        $erro = 'Muitas tentativas. Aguarde 5 minutos.';
    } elseif (!adminCsrfValido($token)) {
        $erro = 'Sessao expirada. Tente novamente.';
    } elseif (!$adminCfg) {
        $erro = 'Painel nao configurado.';
    } elseif ($user !== ($adminCfg['user'] ?? '') || !password_verify($pass, $adminCfg['pass_hash'] ?? '')) {
        $_SESSION['tries']++;
        $_SESSION['tries_at'] = time();
        $erro = 'Usuario ou senha invalidos.';
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
<title>Admin · TexanEduc</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-login">
    <div class="login-card">
        <img src="../assets/images/logo.png" alt="TexanEduc" class="login-logo">
        <h1>Painel administrativo</h1>

        <?php if (!empty($_GET['expirou'])): ?>
            <div class="alert">Sua sessão expirou. Faça login novamente.</div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(adminCsrfToken()) ?>">
            <label>Usuário
                <input type="text" name="user" required autofocus>
            </label>
            <label>Senha
                <input type="password" name="pass" required>
            </label>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
    </div>
</body>
</html>
