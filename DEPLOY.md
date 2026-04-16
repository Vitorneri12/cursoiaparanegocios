# Deploy — Landing IA PARA NEGÓCIOS

## 1. Banco de dados (cPanel HostGator)

1. Entre no cPanel → **MySQL Databases**.
2. Crie um banco novo (ex: `texaneduc_iapn`).
3. Crie um usuário e adicione ao banco com **ALL PRIVILEGES**.
4. Anote: `host`, `nome do banco`, `user`, `senha`.
5. Vá em **phpMyAdmin** e execute o conteúdo de `db/schema.sql`.

## 2. Configuração

1. Copie `config/config.example.php` para `config/config.php`.
2. Preencha:
   - `asaas.api_key`: sua chave de **produção** Asaas (gere uma nova, a anterior foi exposta no chat).
   - `asaas.webhook_token`: invente um token forte (ex: `openssl rand -hex 32`).
   - `db.*`: credenciais MySQL criadas no passo 1.
   - `email.smtp_pass`: senha da caixa `contato@texaneduc.com.br`.

## 3. Subdomínio

No cPanel → **Subdomínios**:
- Crie `iaparanegocio.texaneduc.com.br`
- Aponte para a pasta `public_html/iaparanegocio` (ou similar)

## 4. Upload dos arquivos

Via SSH (recomendado) ou File Manager. Estrutura final no servidor:

```
public_html/iaparanegocio/
├── index.php
├── checkout.php
├── obrigado.php
├── .htaccess
├── api/
├── assets/
├── config/
│   ├── .htaccess
│   ├── config.example.php
│   └── config.php          ← criado por você, NÃO commitado
├── includes/
└── db/
```

Comando exemplo (SCP):
```bash
scp -i ~/.ssh/SUA_KEY -r ./* USUARIO@SERVIDOR:/home/USUARIO/public_html/iaparanegocio/
```

## 5. Webhook Asaas

No painel Asaas → **Configurações → Integrações → Webhooks**:

- URL: `https://iaparanegocio.texaneduc.com.br/api/webhook-asaas.php`
- Token: o mesmo que você colocou em `config.php` → `asaas.webhook_token`
- Eventos: marcar todos os `PAYMENT_*`

## 6. Teste

1. Acesse `https://iaparanegocio.texaneduc.com.br`
2. Faça uma inscrição teste com PIX (use sandbox antes de produção, se possível).
3. Verifique se a inscrição aparece em `inscricoes` (phpMyAdmin).
4. Pague o PIX e confirme que:
   - Webhook chegou (`webhook_logs`)
   - Status mudou para `CONFIRMED`
   - E-mail foi enviado

## Lógica de preços

- Vagas 1–30 (com pagamento confirmado): **R$ 999,90**
- Vagas 31–100 (com pagamento confirmado): **R$ 1.200,00**
- Após vaga 100 (pagas): formulário bloqueado, página mostra "ESGOTADO"

A contagem é feita por inscrições com status `CONFIRMED` ou `RECEIVED`.

## Ajustes futuros

- Troque preços/datas em `config/config.php` → `evento`
- Para mudar o limite de vagas/promo: mesmo arquivo
