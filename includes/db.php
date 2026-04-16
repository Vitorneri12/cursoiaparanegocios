<?php
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];

    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

function config(): array {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

/**
 * Calcula valor total a cobrar para repassar a taxa do Asaas ao cliente.
 * Formula: total = (valor_base + taxa_fixa) / (1 - percentual)
 * Garante que o lojista receba liquido = valor_base.
 */
function valorComTaxaCartao(float $valorBase, int $parcelas): array {
    $taxas = config()['taxas_cartao'] ?? null;
    if (!$taxas) {
        return ['total' => $valorBase, 'parcela' => $valorBase / max(1, $parcelas), 'taxa' => 0.0];
    }
    $perc = $taxas['percentuais'][$parcelas] ?? end($taxas['percentuais']);
    $fixa = (float)($taxas['fixa'] ?? 0);
    $total = ($valorBase + $fixa) / (1 - $perc);
    $total = round($total, 2);
    return [
        'total'   => $total,
        'parcela' => round($total / max(1, $parcelas), 2),
        'taxa'    => round($total - $valorBase, 2),
    ];
}

function precoAtual(): array {
    $cfg = config()['evento'];
    $sql = "SELECT COUNT(*) FROM inscricoes WHERE status IN ('CONFIRMED','RECEIVED')";
    $pagas = (int) db()->query($sql)->fetchColumn();

    $promo = $pagas < $cfg['vagas_promo'];
    $vagas_restantes_total = max(0, $cfg['vagas_total'] - $pagas);
    $vagas_restantes_promo = max(0, $cfg['vagas_promo'] - $pagas);

    return [
        'pagas'                 => $pagas,
        'promo_ativa'           => $promo,
        'preco'                 => $promo ? $cfg['preco_promo'] : $cfg['preco_normal'],
        'preco_promo'           => $cfg['preco_promo'],
        'preco_normal'          => $cfg['preco_normal'],
        'vagas_total'           => $cfg['vagas_total'],
        'vagas_restantes'       => $vagas_restantes_total,
        'vagas_restantes_promo' => $vagas_restantes_promo,
        'esgotado'              => $vagas_restantes_total === 0,
    ];
}
