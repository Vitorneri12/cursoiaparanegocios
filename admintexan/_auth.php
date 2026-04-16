<?php
require_once __DIR__ . '/../includes/db.php';

// Sessao com nome dedicado
session_name('TEXANADM');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function adminEstaLogado(): bool {
    return !empty($_SESSION['admin_user']);
}

function adminProtege(): void {
    if (!adminEstaLogado()) {
        header('Location: login.php');
        exit;
    }
    // Sessao expira em 2h de inatividade
    if (!empty($_SESSION['admin_last']) && (time() - $_SESSION['admin_last']) > 7200) {
        session_destroy();
        header('Location: login.php?expirou=1');
        exit;
    }
    $_SESSION['admin_last'] = time();
}

function adminCsrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function adminCsrfValido(string $token): bool {
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
