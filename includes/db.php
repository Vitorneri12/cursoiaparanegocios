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

/**
 * Determina o lote ativo baseado em data atual + vagas pagas acumuladas.
 * - Lote N e o primeiro cuja data_fim ainda nao passou E vagas acumuladas
 *   ate aquele lote ainda nao foram preenchidas.
 * - Antes do lote 1 comecar: usa lote 1 como preview.
 * - Apos lote 3 terminar OU vagas totais esgotadas: ESGOTADO.
 */
function precoAtual(): array {
    $cfg   = config()['evento'];
    $lotes = $cfg['lotes'];
    $hoje  = date('Y-m-d');

    $pagas = (int) db()->query(
        "SELECT COUNT(*) FROM inscricoes WHERE status IN ('CONFIRMED','RECEIVED')"
    )->fetchColumn();

    // Antes do primeiro lote comecar: usa lote 1 como preview
    if ($hoje < $lotes[0]['inicio']) {
        $l = $lotes[0];
        return [
            'pagas'           => $pagas,
            'numero_lote'     => 1,
            'nome_lote'       => $l['nome'],
            'preco'           => (float) $l['preco'],
            'lote'            => $l,
            'lotes'           => $lotes,
            'preco_maximo'    => max(array_column($lotes, 'preco')),
            'vagas_total'     => $cfg['vagas_total'],
            'vagas_restantes' => $l['vagas'],
            'vagas_lote'      => $l['vagas'],
            'esgotado'        => false,
            'pre_venda'       => true,
        ];
    }

    $vagasAcum = 0;
    foreach ($lotes as $idx => $lote) {
        $vagasAcum += $lote['vagas'];
        // Pula lotes vencidos
        if ($hoje > $lote['fim']) continue;
        // Pula lotes cujas vagas (acumuladas) ja foram preenchidas
        if ($pagas >= $vagasAcum) continue;

        $vagasRestantesLote = $vagasAcum - $pagas;
        return [
            'pagas'           => $pagas,
            'numero_lote'     => $idx + 1,
            'nome_lote'       => $lote['nome'],
            'preco'           => (float) $lote['preco'],
            'lote'            => $lote,
            'lotes'           => $lotes,
            'preco_maximo'    => max(array_column($lotes, 'preco')),
            'vagas_total'     => $cfg['vagas_total'],
            'vagas_restantes' => max(0, $cfg['vagas_total'] - $pagas),
            'vagas_lote'      => $vagasRestantesLote,
            'esgotado'        => false,
            'pre_venda'       => false,
        ];
    }

    // Esgotado: ou todas as vagas pagas, ou todas as datas passaram
    return [
        'pagas'           => $pagas,
        'numero_lote'     => null,
        'nome_lote'       => null,
        'preco'           => (float) end($lotes)['preco'],
        'lote'            => end($lotes),
        'lotes'           => $lotes,
        'preco_maximo'    => max(array_column($lotes, 'preco')),
        'vagas_total'     => $cfg['vagas_total'],
        'vagas_restantes' => 0,
        'vagas_lote'      => 0,
        'esgotado'        => true,
        'pre_venda'       => false,
    ];
}
