<?php
require_once __DIR__ . '/_auth.php';
header('Location: ' . (adminEstaLogado() ? 'dashboard.php' : 'login.php'));
exit;
