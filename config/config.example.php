<?php
// Copie este arquivo para config.php e preencha com os dados reais.
// NAO commitar config.php no git.

return [
    'asaas' => [
        // Producao: https://api.asaas.com/v3
        // Sandbox:  https://api-sandbox.asaas.com/v3
        'base_url' => 'https://api.asaas.com/v3',
        'api_key'  => 'COLOQUE_SUA_API_KEY_AQUI',
        // Token livre que voce cria e configura no painel Asaas (Webhook > Token de autenticacao)
        'webhook_token' => 'TROQUE_POR_UM_TOKEN_FORTE',
    ],

    'db' => [
        'host'    => 'localhost',
        'name'    => 'NOME_DO_BANCO',
        'user'    => 'USUARIO',
        'pass'    => 'SENHA',
        'charset' => 'utf8mb4',
    ],

    'evento' => [
        'nome'           => 'IA PARA NEGÓCIOS',
        'data'           => '09 de maio',
        'local'          => 'TRYP by Wyndham Ribeirão Preto',
        'horario'        => '09h30 às 16h00',
        'vagas_total'    => 100,
        'vagas_promo'    => 30,
        'preco_promo'    => 999.90,
        'preco_normal'   => 1200.00,
        'parcelas_max'   => 5,
    ],

    // Taxas Asaas para cartao de credito (repassadas ao cliente).
    // Confirme os valores no seu painel Asaas (Configuracoes > Tarifas).
    // Valores padrao a partir de tabela publica Asaas:
    //   1x:           1,99%
    //   2x a 6x:      2,99%
    //   7x a 12x:     3,49%
    // Taxa fixa por transacao: R$ 0,49 (varia por plano)
    'taxas_cartao' => [
        'fixa'         => 0.49,
        'percentuais'  => [
            1 => 0.0199,
            2 => 0.0299,
            3 => 0.0299,
            4 => 0.0299,
            5 => 0.0299,
        ],
    ],

    'email' => [
        'remetente_nome'  => 'TexanEduc',
        'remetente_email' => 'contato@texaneduc.com.br',
        'smtp_host'       => 'mail.texaneduc.com.br',
        'smtp_port'       => 465,
        'smtp_user'       => 'contato@texaneduc.com.br',
        'smtp_pass'       => 'SENHA_DO_EMAIL',
        'smtp_secure'     => 'ssl',
    ],

    'site' => [
        'url' => 'https://iaparanegocio.texaneduc.com.br',
    ],

    // Credenciais do painel /admintexan
    // Troque o usuario e gere um novo hash de senha com:
    //   php -r 'echo password_hash("SUA_SENHA_FORTE", PASSWORD_DEFAULT);'
    'admin' => [
        'user'      => 'admin',
        'pass_hash' => '$2y$12$N57zQpwMFs/ebhOnnqS7A.g9ZHBqy6jL7vs7bJb8Pmy34.gDSCxiq', // senha padrao: admin@texan2026 (TROCAR!)
    ],
];
