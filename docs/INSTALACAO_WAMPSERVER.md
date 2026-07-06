# Instalacao no Wampserver

## 1. Requisitos

1. Wampserver com Apache, PHP 8.x e MySQL/MariaDB.
2. Extensoes PHP: `pdo_mysql`, `fileinfo`, `mbstring`.
3. Acesso ao phpMyAdmin.

---

## 2. Pasta do projeto

Local recomendado:

```text
D:\wamp64\www\DGD
```

O ponto de entrada publico e:

```text
D:\wamp64\www\DGD\public
```

Se nao criar virtual host, acesse:

```text
http://localhost/DGD/public
```

---

## 3. Configurar `.env`

Crie `.env` com base em `.env.example`.

Exemplo:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/DGD/public
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dgd_db
DB_USERNAME=root
DB_PASSWORD=
```

Em ambiente local, `root` sem senha pode existir no Wampserver. Em producao, nao use root.

---

## 4. Criar banco

No phpMyAdmin:

1. crie o banco `dgd_db`;
2. selecione charset `utf8mb4`;
3. importe `database/install.sql`.

Alternativa:

1. importe `database/schema.sql`;
2. importe `database/seed.sql`;
3. importe `database/views.sql`.

---

## 5. Criar Admin inicial

Gere o hash:

```bash
php -r "echo password_hash('SENHA_TEMPORARIA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

Execute o insert:

```sql
INSERT INTO usuarios (perfil_id, nome, email, senha_hash, ativo, trocar_senha_proximo_acesso)
VALUES (1, 'Administrador DGD', 'admin@dgd.local', 'HASH_GERADO_AQUI', 1, 1);
```

---

## 6. Permissoes de pasta

Garanta escrita em:

```text
storage/logs
storage/uploads
storage/cache
storage/tmp
```

---

## 7. Teste inicial

1. Acesse `/login`.
2. Entre com o Admin inicial.
3. Acesse Painel.
4. Acesse Usuarios.
5. Cadastre usuario Gestor.
6. Cadastre usuario Operador.
7. Cadastre um desastre em Decretos.
