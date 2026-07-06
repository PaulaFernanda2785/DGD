# Plano de Implementacao do DGD

**Sistema:** DGD - Sistema de Gerenciamento de Desastres  
**Orgao gestor:** CEDEC-PA  
**Fase:** 1 - Auditoria inicial dos documentos e plano de implementacao  
**Data:** 2026-07-06  

---

## 1. Analise tecnica inicial

O repositorio contem, neste momento, os documentos tecnicos oficiais do DGD, ativos visuais, base COBRADE em planilha, arquivos de simbologia COBRADE e dados territoriais dos municipios do Para.

Ainda nao existe estrutura PHP MVC implementada. Nao foram encontrados os diretorios `app`, `config`, `database`, `public`, `storage` ou `bootstrap`.

Documentos usados como fonte de verdade:

1. `docs/01_DOCUMENTO_TECNICO_DEFINICAO_CONCEITUAL_DGD.md`
2. `docs/02_MAPA_COMPLETO_MODULOS_PAGINAS_HIERARQUIA_NAVEGACAO_DGD.md`
3. `docs/03_DOCUMENTO_TECNICO_PERFIS_USUARIO_MATRIZ_PERMISSOES_DGD.md`
4. `docs/04_DOCUMENTO_TECNICO_ARQUITETURA_MVC_COMPLETA_DGD.md`
5. `docs/05_DOCUMENTO_TECNICO_ESTRUTURA_COMPLETA_BANCO_DADOS_DGD.md`
6. `docs/06_DOCUMENTO_TECNICO_DICIONARIO_DADOS_COMPLETO_DGD.md`
7. `docs/PROMPT_OFICIAL_COMANDO_CODEX_CORTEX_DGD.md`

Ordem de autoridade adotada em caso de divergencia:

1. Documento 06 - Dicionario de Dados.
2. Documento 05 - Banco de Dados.
3. Documento 04 - Arquitetura MVC.
4. Documento 03 - Perfis e Permissoes.
5. Documento 02 - Navegacao.
6. Documento 01 - Conceito.

---

## 2. Estrutura final de diretorios

Estrutura prevista para o MVP:

```text
DGD/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Helpers/
│   ├── Middlewares/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   └── Views/
│       ├── layouts/
│       ├── components/
│       ├── auth/
│       ├── painel/
│       ├── decretos/
│       ├── usuarios/
│       ├── senha/
│       └── errors/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeds/
│   ├── schema.sql
│   ├── seed.sql
│   ├── views.sql
│   └── install.sql
├── docs/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── icons/
│   ├── .htaccess
│   └── index.php
├── storage/
│   ├── cache/
│   ├── logs/
│   ├── tmp/
│   └── uploads/
│       └── decretos/
├── tests/
├── .env.example
├── .gitignore
└── README.md
```

---

## 3. Arquivos que serao criados por fase

### Fase 2 - Banco de dados

1. `database/schema.sql`
2. `database/seed.sql`
3. `database/views.sql`
4. `database/install.sql`
5. `database/migrations/001_create_base_dgd.sql`
6. `database/seeds/001_seed_perfis_permissoes.sql`
7. `docs/BANCO_DE_DADOS.md`

### Fase 3 - Nucleo MVC e configuracao

1. `public/index.php`
2. `public/.htaccess`
3. `bootstrap/app.php`
4. `config/app.php`
5. `config/database.php`
6. `config/routes.php`
7. `config/upload.php`
8. `config/permissions.php`
9. `app/Core/App.php`
10. `app/Core/Router.php`
11. `app/Core/Controller.php`
12. `app/Core/Database.php`
13. `app/Core/Request.php`
14. `app/Core/Response.php`
15. `app/Core/View.php`
16. `app/Core/Session.php`
17. `app/Core/Auth.php`
18. `app/Core/Permission.php`
19. `app/Core/Csrf.php`
20. `app/Core/Validator.php`
21. `app/Core/Logger.php`
22. `app/Middlewares/AuthMiddleware.php`
23. `app/Middlewares/GuestMiddleware.php`
24. `app/Middlewares/PermissionMiddleware.php`
25. `app/Middlewares/CsrfMiddleware.php`
26. `.env.example`

### Fase 4 - Autenticacao, usuarios e permissoes

1. `app/Controllers/AuthController.php`
2. `app/Controllers/UsuarioController.php`
3. `app/Controllers/SenhaController.php`
4. `app/Services/AuthService.php`
5. `app/Services/UsuarioService.php`
6. `app/Services/AuditoriaService.php`
7. `app/Repositories/UsuarioRepository.php`
8. `app/Repositories/PerfilRepository.php`
9. `app/Repositories/AuditoriaRepository.php`
10. Views de login, usuarios e alterar senha.

### Fase 5 - Modulo Decretos

1. `app/Controllers/DecretoController.php`
2. `app/Controllers/AnexoController.php`
3. `app/Controllers/CobradeController.php`
4. `app/Controllers/PainelController.php`
5. Services de decretos, protocolo, PGE, afetados, anexos, COBRADE e painel.
6. Repositories de decretos, anexos, COBRADE, municipios e dominios.
7. Views de listagem, cadastro, edicao e detalhe de decretos.
8. JavaScript modular para COBRADE, afetados e anexos.

### Fase 6 - Interface visual

1. Layout publico.
2. Layout autenticado.
3. Componentes de menu, breadcrumb, mensagens, paginacao, badges e modal de confirmacao.
4. CSS institucional.
5. JS de comportamento da interface.
6. Copia controlada dos ativos visuais necessarios para `public/assets`.

### Fase 7 - Revisao e documentacao final

1. `README.md`
2. `docs/INSTALACAO_WAMPSERVER.md`
3. `docs/INSTALACAO_HOSTINGER.md`
4. `docs/DECISOES_TECNICAS.md`
5. `docs/TESTES_MANUAIS.md`
6. `docs/SEGURANCA.md`

---

## 4. Ordem de implementacao

1. Criar estrutura de diretorios base e arquivos de protecao.
2. Implementar banco de dados: schema, seeds, views e install.sql.
3. Gerar ou preparar carga de municipios do Para a partir da base existente em `terit/PA_Municipios_2025`.
4. Preparar carga COBRADE a partir de `base_cobrade_sistema_com_subtipo_definicao.xlsx`, preservando hierarquia.
5. Implementar nucleo MVC puro em PHP 8.x, sem framework pesado e sem dependencia obrigatoria de Composer.
6. Implementar autenticacao, sessao segura, CSRF e RBAC.
7. Implementar modulo Usuarios restrito ao Admin.
8. Implementar Alterar senha para todos os perfis autenticados.
9. Implementar Painel.
10. Implementar Modulo Decretos com cadastro, listagem, detalhe, edicao, exclusao logica, status rapido e anexos.
11. Implementar rotas JSON auxiliares COBRADE.
12. Implementar layout institucional responsivo.
13. Revisar seguranca, auditoria, uploads, paths, documentacao e checklist de testes.

---

## 5. Divergencias e decisoes adotadas

### 5.1. Rotas de logout

O Documento 02 cita `GET /logout`, mas o prompt oficial e as boas praticas exigem `POST /logout` com CSRF.

**Decisao:** usar `POST /logout` com CSRF.

### 5.2. Rotas de decretos

O Documento 02 usa rotas como `/decretos/salvar` e `/decretos/detalhe/{id}`. O prompt oficial prioriza rotas REST-like como `POST /decretos`, `GET /decretos/{id}` e `POST /decretos/{id}/excluir`.

**Decisao:** usar as rotas REST-like do prompt oficial e Documento 04.

### 5.3. Status PGE

Os documentos conceituais usam termos proximos para status PGE. O Documento 05 e o prompt oficial separam `status_envio_pge_id` de `status_prazo_pge_calculado`.

**Decisao:** manter dois conceitos separados:

1. `status_envio_pge_id`: administrativo e editavel por Admin/Gestor.
2. `status_prazo_pge_calculado`: calculado, nunca editavel.

### 5.4. Total de afetados

O Documento 05 recomenda coluna gerada no banco, mas reconhece possivel incompatibilidade em hospedagem.

**Decisao:** criar coluna gerada em `schema.sql` quando suportada e documentar fallback por PHP/view se o MariaDB da hospedagem nao aceitar.

### 5.5. Admin inicial

O Documento 05 mostra insert com placeholder de hash.

**Decisao:** nao gravar senha fixa no repositorio. O `seed.sql` deve usar placeholder e a documentacao deve orientar a geracao do hash com `password_hash`.

---

## 6. Riscos tecnicos

| Risco | Impacto | Mitigacao |
|---|---|---|
| Encoding dos documentos aparece corrompido em alguns textos | Pode gerar nomes ou mensagens com acentos quebrados | Criar novos arquivos em UTF-8 e revisar textos exibidos na interface |
| Base COBRADE em XLSX exige conversao | Pode atrasar seed completo | Criar importador/documentar carga e gerar seed validado |
| Hospedagem compartilhada pode limitar `CREATE VIEW` | Views podem falhar no phpMyAdmin | Documentar fallback por queries em repositories |
| Coluna gerada pode variar entre MySQL/MariaDB | `total_afetados` pode falhar na importacao | Manter fallback calculado no backend |
| Upload fora da pasta publica pode ser limitado na Hostinger | Risco de exposicao de anexos | Documentar estrutura recomendada e usar controller autenticado |
| Projeto ainda nao esta versionado em Git | Risco de perda de historico tecnico | Inicializar repositorio Git antes das fases de codigo, se autorizado |

---

## 7. Checklist de conclusao do MVP

1. Estrutura MVC criada.
2. Banco importavel via phpMyAdmin.
3. `.env.example` criado sem credenciais reais.
4. Login funcional com `password_hash` e `password_verify`.
5. CSRF aplicado em todos os POST.
6. Rotas internas protegidas por autenticacao.
7. Permissoes por perfil aplicadas no backend.
8. Admin acessa Usuarios.
9. Gestor e Operador nao acessam Usuarios.
10. Operador cadastra desastre, mas nao edita depois.
11. Protocolo DGD gerado automaticamente em transacao.
12. Total de afetados calculado automaticamente.
13. Status de prazo PGE calculado automaticamente.
14. Listagem de Decretos limitada a 20 registros por pagina.
15. Anexos validados por extensao, MIME e tamanho.
16. Anexos baixados apenas via controller autenticado.
17. Exclusao de desastre, usuario e anexo feita de forma logica.
18. Acoes criticas registradas em auditoria.
19. Views escapam saidas dinamicas.
20. README, instalacao, seguranca e testes manuais atualizados.

---

## 8. Proxima fase recomendada

A proxima etapa tecnica e a **Fase 2 - Banco de dados**.

Ela deve criar `database/schema.sql`, `database/seed.sql`, `database/views.sql`, `database/install.sql` e `docs/BANCO_DE_DADOS.md`, seguindo prioritariamente os Documentos 05 e 06.

Antes dessa fase, recomenda-se decidir se o repositorio sera versionado com Git neste diretorio. No momento da auditoria, `D:\wamp64\www\DGD` nao esta inicializado como repositorio Git.
