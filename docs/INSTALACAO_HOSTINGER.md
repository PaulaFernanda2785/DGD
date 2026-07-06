# Instalacao na Hostinger

## 1. Premissas

1. Plano com PHP 8.x.
2. Banco MySQL/MariaDB criado no painel.
3. Acesso ao phpMyAdmin.
4. HTTPS ativo.

---

## 2. Organizacao recomendada

Preferencialmente, mantenha arquivos internos fora de `public_html`:

```text
/home/usuario/dgd-app/
├── app/
├── bootstrap/
├── config/
├── database/
├── docs/
├── storage/
└── public_html/
    ├── index.php
    ├── .htaccess
    └── assets/
```

Se a hospedagem obrigar tudo dentro de `public_html`, mantenha os `.htaccess` ja criados em:

1. `app/`
2. `bootstrap/`
3. `config/`
4. `database/`
5. `storage/`

---

## 3. Configuracao `.env`

Crie `.env` no diretorio raiz da aplicacao, fora de acesso publico quando possivel.

Exemplo:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.gov.br
APP_TIMEZONE=America/Belem

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_do_banco
DB_PASSWORD=senha_forte
DB_CHARSET=utf8mb4

SESSION_SECURE=true
```

Nunca envie `.env` ao GitHub.

---

## 4. Banco de dados

No phpMyAdmin:

1. selecione o banco criado;
2. importe `database/install.sql`;
3. se houver erro em coluna gerada, aplicar fallback documentado em `docs/BANCO_DE_DADOS.md`;
4. se houver erro em `CREATE VIEW`, executar as queries equivalentes via repositories futuramente.

---

## 5. Admin inicial

Gere o hash em ambiente seguro:

```bash
php -r "echo password_hash('SENHA_TEMPORARIA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

Execute o insert do Admin no phpMyAdmin.

Depois do primeiro acesso, altere a senha.

---

## 6. Permissoes

Garanta permissao de escrita para:

```text
storage/logs
storage/uploads
storage/cache
storage/tmp
```

Nao permita listagem publica de diretorios.

---

## 7. Pontos de atencao

1. Confirmar limite de upload do PHP no painel.
2. Usar HTTPS.
3. Nao usar usuario root do banco.
4. Fazer backup do banco e de `storage/uploads`.
5. Validar se `public/` pode ser a raiz publica do dominio.
