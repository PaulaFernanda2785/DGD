# Seguranca do DGD

## 1. Controles implementados

1. PDO com prepared statements.
2. Senhas com `password_hash()` e `password_verify()`.
3. CSRF em rotas POST.
4. Escape de saida HTML com `e()`.
5. Sessoes com cookie `HttpOnly`.
6. Regeneracao de sessao apos login.
7. Bloqueio temporario apos tentativas invalidas.
8. RBAC por permissoes canonicas.
9. Exclusao logica.
10. Auditoria de acoes criticas.
11. Upload validado por extensao, MIME e tamanho.
12. Download de anexos via controller autenticado.
13. `.htaccess` bloqueando pastas internas.
14. `.env` ignorado no Git.
15. Recuperacao de senha por token temporario com hash no banco.
16. Verificacao em duas etapas por TOTP para acesso ao painel.

---

## 2. Arquivos sensiveis fora do GitHub

O `.gitignore` bloqueia:

1. `.env`;
2. uploads;
3. logs;
4. cache;
5. temporarios;
6. backups;
7. dumps;
8. `vendor/`.

---

## 3. Regras de senha

Regra atual:

1. minimo de 8 caracteres;
2. confirmacao obrigatoria;
3. senha nunca exibida;
4. senha nunca gravada em texto puro;
5. troca de senha auditada.

A CEDEC-PA pode endurecer a politica futuramente.

Na recuperacao de senha, a nova senha exige minimo de 10 caracteres, letras maiusculas, letras minusculas e pelo menos um numero.

---

## 4. Recuperacao de senha e 2FA

Rotas publicas:

1. `/esqueci-senha`
2. `/recuperar-senha/{token}`
3. `/2fa/configurar`
4. `/2fa/verificar`

Regras implementadas:

1. a recuperacao retorna mensagem generica para evitar enumeracao de usuarios;
2. o token real nunca e salvo no banco, apenas `token_hash`;
3. tokens expiram em 60 minutos e sao invalidados apos uso;
4. em ambiente local, o link de recuperacao e registrado em `storage/logs/password_recovery_links.log`;
5. com SMTP configurado, o link e enviado para o e-mail profissional cadastrado;
6. em producao, nao deve haver fallback para arquivo de log;
7. o segundo fator usa codigo TOTP de 6 digitos em aplicativo autenticador;
8. a etapa 2FA expira em 10 minutos;
9. apos 5 codigos invalidos, o desafio 2FA e cancelado.

Configuracoes SMTP esperadas no `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=sistema@seudominio.com
MAIL_PASSWORD=senha_do_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=sistema@seudominio.com
MAIL_FROM_NAME="DGD - CEDEC-PA"
```

Na hospedagem Hostinger, `MAIL_USERNAME` e `MAIL_FROM_ADDRESS` devem usar o mesmo e-mail profissional completo. Credenciais e tokens nunca devem ser registrados nos logs da aplicacao.

A validacao TLS usa `MAIL_CA_FILE` quando informado. Se a variavel estiver vazia, o sistema utiliza a cadeia confiavel distribuida em `config/certs/cacert.pem`; `MAIL_VERIFY_PEER` deve permanecer habilitado em producao.

Falhas de entrega e etapas da recuperacao sao registradas, sem e-mail ou token em texto aberto, nos arquivos `storage/logs/mail.log` e `storage/logs/password_recovery.log`.

A criacao e a validacao da expiracao usam exclusivamente o relogio do MySQL. O prazo e calculado a partir de `recuperacoes_senha.criado_em`, evitando invalidacao imediata quando PHP e banco operam em fusos horarios diferentes.

---

## 5. Uploads

Extensoes permitidas:

1. `pdf`
2. `doc`
3. `docx`
4. `jpg`
5. `jpeg`
6. `png`

Tamanho padrao:

```text
20 MB
```

Arquivos sao salvos em:

```text
storage/uploads/decretos
```

Esse diretorio nao deve ser publico.

---

## 6. Banco de dados

Recomendacoes:

1. nao usar root em producao;
2. usar usuario especifico da aplicacao;
3. fazer backup antes de migrations;
4. nunca editar dados criticos diretamente em producao sem registro;
5. preservar auditoria.

---

## 7. Pontos ainda dependentes de ambiente

1. HTTPS deve ser forcado no dominio final.
2. Permissoes de escrita devem ser revisadas na Hostinger.
3. Limites de upload dependem do plano PHP.
4. `CREATE VIEW` e coluna gerada devem ser validados no MySQL/MariaDB final.
