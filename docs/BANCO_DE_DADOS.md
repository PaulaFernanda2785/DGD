# Banco de Dados do DGD

**Sistema:** DGD - Sistema de Gerenciamento de Desastres  
**Fase:** 2 - Banco de dados  
**SGBD previsto:** MySQL/MariaDB  
**Charset:** `utf8mb4`  
**Collation:** `utf8mb4_unicode_ci`  

---

## 1. Arquivos entregues

1. `database/schema.sql` - cria a estrutura principal do banco.
2. `database/seed.sql` - insere perfis, permissoes, dominios, configuracoes, municipios, COMPDECs e catalogo COBRADE completo.
3. `database/views.sql` - cria `vw_decretos_listagem` e `vw_painel_resumo`.
4. `database/install.sql` - orientador de instalacao completa.
5. `database/migrations/001_create_base_dgd.sql` - marcador da migration inicial.
6. `database/seeds/001_seed_perfis_permissoes_dominios.sql` - marcador do seed inicial.
7. `database/seeds/002_seed_municipios_pa.sql` - carga de municipios do Para gerada do CSV local.
8. `database/seeds/003_seed_compdecs.sql` - carga oficial de COMPDECs e sincronizacao das UBMs atuantes.
9. `database/seeds/004_seed_cobrade_catalogo_completo.sql` - carga COBRADE completa com grupo, subgrupo, tipo, subtipo e simbologia.

---

## 2. Ordem de importacao

No phpMyAdmin, importe nesta ordem:

1. `database/schema.sql`
2. `database/seed.sql`
3. `database/views.sql`

O arquivo `database/install.sql` foi gerado como SQL concatenado, sem depender de `SOURCE`, para ser importado diretamente pelo phpMyAdmin.

---

## 3. Tabelas criadas

### Seguranca e acesso

1. `perfis`
2. `permissoes`
3. `perfil_permissoes`
4. `usuarios`
5. `recuperacoes_senha`
6. `usuarios_sessoes`
7. `login_logs`

### Territorio e orgao atuante

1. `municipios`
2. `ubms`

### COBRADE

1. `cobrade_grupos`
2. `cobrade_subgrupos`
3. `cobrade_tipos`
4. `cobrade_subtipos`

### Dominios

1. `tipos_decreto`
2. `status_homologacao`
3. `status_reconhecimento`
4. `status_recurso`
5. `status_envio_pge`
6. `tipos_anexo`

### Operacao principal

1. `sequencias_protocolos`
2. `desastres`
3. `desastre_anexos`
4. `desastre_historico_status`
5. `auditoria_logs`
6. `configuracoes_sistema`

### Views

1. `vw_decretos_listagem`
2. `vw_painel_resumo`

---

## 4. Regras preservadas

1. `desastres.protocolo_dgd` e unico.
2. `desastres.protocolo_ano + protocolo_sequencial` e unico.
3. `usuarios.email` e unico.
4. `usuarios.cpf` e unico quando preenchido.
5. Status usam tabelas de dominio, nao texto livre.
6. `total_afetados` e coluna gerada automaticamente.
7. Anexos sao metadados no banco, nao BLOB.
8. Exclusao de usuarios, desastres e anexos e logica.
9. Auditoria possui tabela propria.
10. A view de listagem calcula duracao e status de prazo PGE.
11. `recuperacoes_senha.token_hash` armazena apenas hash do token de recuperacao.
12. `usuarios.two_factor_*` controla credenciamento e validacao TOTP.
13. `compdecs` e a fonte oficial para regiao de integracao, prefeito, coordenador, telefone, e-mail e `ubm_nome`.
14. `desastres.compdec_*` guarda snapshot dos dados da COMPDEC usados no cadastro.

---

## 5. Admin inicial

Por seguranca, o seed nao grava senha real.

Gere o hash localmente:

```bash
php -r "echo password_hash('SENHA_TEMPORARIA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

Depois execute manualmente no banco:

```sql
INSERT INTO usuarios (perfil_id, nome, email, senha_hash, ativo, trocar_senha_proximo_acesso)
VALUES (1, 'Administrador DGD', 'admin@dgd.local', 'HASH_GERADO_AQUI', 1, 1);
```

Troque o e-mail e a senha temporaria conforme a politica da CEDEC-PA. Nunca versionar senha real.

---

## 6. Carga de municipios

Existe base local em:

```text
terit/PA_Municipios_2025/para_municipios_com_geolocalizacao.csv
```

A estrutura `municipios` ja possui campos para `codigo_ibge`, `nome`, `uf`, `latitude` e `longitude`.

A carga foi gerada em:

```text
database/seeds/002_seed_municipios_pa.sql
```

E tambem foi incorporada a:

```text
database/seed.sql
database/install.sql
```

Antes da producao, a CEDEC-PA deve validar os nomes oficiais, codigos IBGE e geolocalizacao.

---

## 7. Carga de COMPDECs e UBMs

A tabela `compdecs` foi importada do arquivo `compdecs.sql`, fornecido a partir da base COMPDEC do Sistema Multirriscos/Defesa Civil.

A carga foi gerada em:

```text
database/seeds/003_seed_compdecs.sql
```

E tambem foi incorporada a:

```text
database/seed.sql
database/install.sql
```

A importacao usa `municipios.codigo_ibge` para relacionar cada municipio a `compdecs.municipio_codigo`. A tabela `ubms` passa a ser sincronizada a partir de `compdecs.ubm_nome`; os registros sincronizados ficam identificados com:

```text
Fonte: COMPDEC DGD
```

No formulario de novo cadastro de desastre, ao selecionar o municipio, o sistema busca a COMPDEC correspondente e preenche automaticamente:

1. UBM atuante.
2. Regiao de integracao.
3. Prefeito.
4. Coordenador.
5. Telefone.
6. E-mail.

Esses campos sao gravados em `desastres` como snapshot para preservar o historico do cadastro.

---
## 8. Carga COBRADE

Existe base local em:

```text
base_cobrade_sistema_com_subtipo_definicao.xlsx
```

Tambem existem imagens de simbologia em:

```text
cobrade_simbologia_png/cobrade_simbologia/
```

O `seed.sql` inclui a base COBRADE completa, convertida a partir da planilha validada e preservando:

1. grupo;
2. subgrupo;
3. tipo;
4. subtipo;
5. codigo COBRADE;
6. descricao;
7. simbologia.

---

## 9. Compatibilidade e fallback

### Coluna gerada

`desastres.total_afetados` foi criado como coluna gerada:

```sql
GENERATED ALWAYS AS (...) STORED
```

Se o MariaDB/MySQL da hospedagem nao aceitar esse recurso, substituir por:

```sql
total_afetados INT UNSIGNED NOT NULL DEFAULT 0
```

Nesse caso, o backend deve calcular o total antes de gravar e as views devem continuar tratando o campo como fonte de leitura.

### Views

Se a hospedagem nao permitir `CREATE VIEW`, os repositories devem executar consulta equivalente no PHP usando PDO e prepared statements.

---

## 9. Segurança para GitHub

Foi criada `.gitignore` para manter fora do repositorio:

1. `.env` e arquivos de ambiente reais;
2. uploads;
3. logs;
4. cache;
5. temporarios;
6. backups;
7. dumps locais;
8. `vendor/`, caso Composer seja usado futuramente.

Arquivos `.gitkeep` foram adicionados para preservar a estrutura vazia de `storage`.

---

## 10. Alteracoes de autenticacao

A migration:

```text
database/migrations/2026_07_06_auth_recovery_two_factor.sql
```

adiciona:

1. `usuarios.two_factor_secret`;
2. `usuarios.two_factor_enabled`;
3. `usuarios.two_factor_confirmed_at`;
4. `usuarios.two_factor_last_verified_at`;
5. tabela `recuperacoes_senha`.

Essas alteracoes tambem foram incorporadas a `database/schema.sql` e `database/install.sql`.
