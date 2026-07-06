# DGD - Sistema de Gerenciamento de Desastres

Sistema administrativo em PHP MVC para registro, acompanhamento e gestao de desastres, decretos municipais, homologacao estadual, reconhecimento federal, PGE, recursos, afetados, anexos, usuarios e auditoria.

**Orgao gestor:** CEDEC-PA  
**Ambiente local previsto:** Wampserver  
**Producao prevista:** Hostinger com PHP/MySQL/phpMyAdmin  
**Backend:** PHP 8.x puro  
**Banco:** MySQL/MariaDB  
**Frontend:** HTML, CSS e JavaScript puro  

---

## 1. Estado atual

O MVP possui:

1. nucleo MVC proprio;
2. configuracao por `.env`;
3. banco SQL em `database/`;
4. login, logout e sessoes;
5. recuperacao de senha;
6. verificacao em duas etapas;
7. usuarios e perfis;
8. alteracao de senha;
9. painel;
10. modulo Decretos/Desastres;
11. protocolo DGD automatico;
12. anexos protegidos;
13. COBRADE em JSON;
14. auditoria basica;
15. documentacao de instalacao e testes.

---

## 2. Estrutura principal

```text
app/          Controllers, Services, Repositories, Core, Views e Middlewares
bootstrap/    Inicializacao e autoload proprio
config/       Configuracoes da aplicacao
database/     Schema, seeds, views e install.sql
docs/         Documentacao tecnica
public/       Entrada publica da aplicacao
storage/      Logs, cache, temporarios e uploads protegidos
```

---

## 3. Configuracao inicial

Crie um arquivo `.env` local com base em `.env.example`.

Nunca envie `.env` para o GitHub.

Exemplo minimo:

```env
APP_NAME="DGD"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/DGD/public
APP_TIMEZONE=America/Belem

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dgd_db
DB_USERNAME=dgd_app
DB_PASSWORD=sua_senha_local
DB_CHARSET=utf8mb4
```

---

## 4. Banco de dados

Importe no phpMyAdmin:

1. `database/schema.sql`
2. `database/seed.sql`
3. `database/views.sql`

Ou use:

```text
database/install.sql
```

O `install.sql` foi gerado como SQL concatenado para facilitar importacao.

---

## 5. Admin inicial

Por seguranca, o reposititorio nao contem senha real.

Gere o hash:

```bash
php -r "echo password_hash('SENHA_TEMPORARIA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

Depois execute no banco:

```sql
INSERT INTO usuarios (perfil_id, nome, email, senha_hash, ativo, trocar_senha_proximo_acesso)
VALUES (1, 'Administrador DGD', 'admin@dgd.local', 'HASH_GERADO_AQUI', 1, 1);
```

---

## 6. Execucao local rapida

Com PHP no PATH:

```bash
php -S 127.0.0.1:8080 -t public
```

Acesse:

```text
http://127.0.0.1:8080
```

No Wampserver, acesse conforme o virtual host ou caminho local configurado.

Com o virtual host local configurado:

```text
http://dgd.local/login
```

O link de recuperacao de senha em ambiente local e registrado em:

```text
storage/logs/password_recovery_links.log
```

Para envio real por e-mail profissional, configure o SMTP no `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.seudominio.com
MAIL_PORT=587
MAIL_USERNAME=sistema@seudominio.com
MAIL_PASSWORD=senha_do_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sistema@seudominio.com
MAIL_FROM_NAME="DGD - CEDEC-PA"
MAIL_CA_FILE="D:\wamp64\bin\php\certs\cacert.pem"
MAIL_VERIFY_PEER=true
```

No Wampserver, mantenha `MAIL_VERIFY_PEER=true` e informe um `cacert.pem` valido em `MAIL_CA_FILE` quando o PHP nao tiver `openssl.cafile` configurado.

---

## 7. Documentacao

1. `docs/INSTALACAO_WAMPSERVER.md`
2. `docs/INSTALACAO_HOSTINGER.md`
3. `docs/SEGURANCA.md`
4. `docs/TESTES_MANUAIS.md`
5. `docs/BANCO_DE_DADOS.md`
6. `docs/DECISOES_TECNICAS.md`

---

## 8. Pendencias para validacao humana

1. Validar base completa COBRADE a partir da planilha oficial.
2. Validar lista final de UBMs.
3. Validar nomes, codigos IBGE e geolocalizacao dos municipios.
4. Confirmar regra operacional final do prazo PGE.
5. Definir politica institucional de senha.
6. Revisar identidade visual final com a CEDEC-PA.
