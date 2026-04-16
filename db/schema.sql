-- Schema do banco de dados para a landing page IA PARA NEGOCIOS
-- Executar no phpMyAdmin do HostGator (cPanel)

CREATE TABLE IF NOT EXISTS inscricoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    empresa VARCHAR(150) DEFAULT NULL,
    cargo VARCHAR(100) DEFAULT NULL,
    cep VARCHAR(10) DEFAULT NULL,
    logradouro VARCHAR(200) DEFAULT NULL,
    numero VARCHAR(20) DEFAULT NULL,
    complemento VARCHAR(100) DEFAULT NULL,
    bairro VARCHAR(100) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado CHAR(2) DEFAULT NULL,
    valor DECIMAL(10,2) NOT NULL,
    metodo_pagamento ENUM('PIX','CREDIT_CARD') NOT NULL,
    asaas_customer_id VARCHAR(50) DEFAULT NULL,
    asaas_payment_id VARCHAR(50) DEFAULT NULL,
    status ENUM('PENDING','CONFIRMED','RECEIVED','OVERDUE','REFUNDED','CANCELLED') NOT NULL DEFAULT 'PENDING',
    data_inscricao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_pagamento DATETIME DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_asaas_payment (asaas_payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento VARCHAR(50) NOT NULL,
    payment_id VARCHAR(50) DEFAULT NULL,
    payload TEXT NOT NULL,
    processado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payment (payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
