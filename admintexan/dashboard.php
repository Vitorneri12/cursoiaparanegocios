<?php
require_once __DIR__ . '/_auth.php';
adminProtege();

$filtroStatus = $_GET['status'] ?? '';
$filtroMetodo = $_GET['metodo'] ?? '';
$busca        = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($filtroStatus !== '') {
    $where[] = 'status = ?';
    $params[] = $filtroStatus;
}
if ($filtroMetodo !== '') {
    $where[] = 'metodo_pagamento = ?';
    $params[] = $filtroMetodo;
}
if ($busca !== '') {
    $where[] = '(nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ? OR telefone LIKE ? OR empresa LIKE ?)';
    $like = '%' . $busca . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

$sql = 'SELECT * FROM inscricoes';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY id DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$inscricoes = $stmt->fetchAll();

// Stats globais
$stats = db()->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status IN ('CONFIRMED','RECEIVED') THEN 1 ELSE 0 END) AS pagas,
        SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) AS pendentes,
        SUM(CASE WHEN status IN ('CONFIRMED','RECEIVED') THEN valor ELSE 0 END) AS faturado
    FROM inscricoes
")->fetch() ?: ['total' => 0, 'pagas' => 0, 'pendentes' => 0, 'faturado' => 0];

$evento = config()['evento'];
$vagasRestantes = max(0, $evento['vagas_total'] - (int)$stats['pagas']);

$fmt = fn(?float $v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
$dt  = fn(?string $d) => $d ? date('d/m/Y H:i', strtotime($d)) : '—';

$badgeStatus = [
    'CONFIRMED' => ['Confirmado', 'ok'],
    'RECEIVED'  => ['Recebido',   'ok'],
    'PENDING'   => ['Pendente',   'wait'],
    'OVERDUE'   => ['Vencido',    'fail'],
    'REFUNDED'  => ['Reembolsado', 'fail'],
    'CANCELLED' => ['Cancelado',  'fail'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Inscrições · Admin TexanEduc</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin">

<header class="adm-top">
    <div class="adm-top-inner">
        <div class="adm-brand">
            <img src="../assets/images/logo.png" alt="TexanEduc">
            <span>Painel administrativo</span>
        </div>
        <div class="adm-actions">
            <a href="exportar.php<?= $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" class="btn btn-secondary btn-sm">⬇ Exportar CSV</a>
            <span class="adm-user">👤 <?= htmlspecialchars($_SESSION['admin_user']) ?></span>
            <a href="logout.php" class="btn btn-secondary btn-sm">Sair</a>
        </div>
    </div>
</header>

<main class="adm-main">
    <div class="adm-stats">
        <div class="stat">
            <div class="stat-label">Inscrições totais</div>
            <div class="stat-value"><?= (int)$stats['total'] ?></div>
        </div>
        <div class="stat stat-ok">
            <div class="stat-label">Pagas</div>
            <div class="stat-value"><?= (int)$stats['pagas'] ?></div>
        </div>
        <div class="stat stat-wait">
            <div class="stat-label">Pendentes</div>
            <div class="stat-value"><?= (int)$stats['pendentes'] ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Vagas restantes</div>
            <div class="stat-value"><?= $vagasRestantes ?>/<?= $evento['vagas_total'] ?></div>
        </div>
        <div class="stat stat-money">
            <div class="stat-label">Faturado (pagas)</div>
            <div class="stat-value"><?= $fmt($stats['faturado']) ?></div>
        </div>
    </div>

    <form class="adm-filtros" method="get">
        <input type="text" name="q" placeholder="Buscar por nome, e-mail, CPF, telefone, empresa..." value="<?= htmlspecialchars($busca) ?>">
        <select name="status">
            <option value="">Todos os status</option>
            <?php foreach ($badgeStatus as $k => $v): ?>
                <option value="<?= $k ?>" <?= $filtroStatus === $k ? 'selected' : '' ?>><?= $v[0] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="metodo">
            <option value="">Todos os métodos</option>
            <option value="PIX" <?= $filtroMetodo === 'PIX' ? 'selected' : '' ?>>PIX</option>
            <option value="CREDIT_CARD" <?= $filtroMetodo === 'CREDIT_CARD' ? 'selected' : '' ?>>Cartão</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <?php if ($filtroStatus || $filtroMetodo || $busca): ?>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">Limpar</a>
        <?php endif; ?>
    </form>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Inscrição</th>
                    <th>Participante</th>
                    <th>Contato</th>
                    <th>Empresa / cargo</th>
                    <th>Endereço</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$inscricoes): ?>
                    <tr><td colspan="8" class="empty">Nenhuma inscrição encontrada.</td></tr>
                <?php endif; ?>
                <?php foreach ($inscricoes as $i):
                    [$lbl, $cls] = $badgeStatus[$i['status']] ?? [$i['status'], 'wait'];
                ?>
                <tr>
                    <td><strong>#<?= (int)$i['id'] ?></strong></td>
                    <td>
                        <div><?= $dt($i['data_inscricao']) ?></div>
                        <?php if ($i['data_pagamento']): ?>
                            <small class="muted">Pago: <?= $dt($i['data_pagamento']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($i['nome']) ?></strong><br>
                        <small><?= htmlspecialchars($i['cpf_cnpj']) ?></small>
                    </td>
                    <td>
                        <a href="mailto:<?= htmlspecialchars($i['email']) ?>"><?= htmlspecialchars($i['email']) ?></a><br>
                        <a href="https://wa.me/55<?= preg_replace('/\D/', '', $i['telefone']) ?>" target="_blank"><?= htmlspecialchars($i['telefone']) ?></a>
                    </td>
                    <td>
                        <?= htmlspecialchars($i['empresa'] ?: '—') ?><br>
                        <small class="muted"><?= htmlspecialchars($i['cargo'] ?: '') ?></small>
                    </td>
                    <td>
                        <?php if ($i['logradouro']): ?>
                            <small>
                                <?= htmlspecialchars($i['logradouro']) ?>, <?= htmlspecialchars($i['numero']) ?>
                                <?= $i['complemento'] ? ' — ' . htmlspecialchars($i['complemento']) : '' ?><br>
                                <?= htmlspecialchars($i['bairro']) ?> · <?= htmlspecialchars($i['cidade']) ?>/<?= htmlspecialchars($i['estado']) ?><br>
                                CEP <?= htmlspecialchars($i['cep']) ?>
                            </small>
                        <?php else: ?>
                            <small class="muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= $fmt($i['valor']) ?></strong><br>
                        <small class="muted"><?= $i['metodo_pagamento'] === 'PIX' ? 'PIX' : 'Cartão' ?></small><br>
                        <?php if ($i['asaas_payment_id']): ?>
                            <small class="muted" style="font-family:monospace"><?= htmlspecialchars($i['asaas_payment_id']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?= $cls ?>"><?= $lbl ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
