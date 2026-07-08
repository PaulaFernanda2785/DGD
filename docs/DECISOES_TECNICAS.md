# Decisoes Tecnicas do DGD

**Sistema:** DGD - Sistema de Gerenciamento de Desastres  
**Orgao gestor:** CEDEC-PA  

---

## 2026-07-06 - Fase 3: Nucleo MVC e configuracao

### 1. MVC puro sem dependencia obrigatoria de Composer

Foi criado autoloader proprio em `bootstrap/app.php`, usando o namespace `App\` e carregamento direto a partir da pasta `app/`.

Justificativa:

1. manter compatibilidade com Wampserver e Hostinger;
2. evitar dependencia obrigatoria de terminal em producao;
3. preservar simplicidade de implantacao em hospedagem compartilhada.

### 2. Configuracao por `.env`

Foi criado `.env.example` sem credenciais reais.

O arquivo `.env` real permanece ignorado pelo Git por regra de `.gitignore`.

Justificativa:

1. nao expor senha de banco no GitHub;
2. permitir configuracao diferente entre local e producao;
3. manter boas praticas de seguranca.

### 3. Rotas REST-like

O arquivo `config/routes.php` usa as rotas REST-like previstas no prompt oficial e no Documento 04.

Exemplos:

1. `POST /logout`
2. `POST /decretos`
3. `GET /decretos/{id}`
4. `POST /decretos/{id}/excluir`

Justificativa:

1. evitar exclusao por GET;
2. aplicar CSRF em acoes de escrita;
3. manter previsibilidade do roteamento.

### 4. CSRF em todos os POST protegidos

Foi criado `App\Core\Csrf` e `App\Middlewares\CsrfMiddleware`.

Toda rota POST configurada para alteracao de dados recebeu middleware CSRF.

### 5. Protecao de pastas internas

Foram adicionados `.htaccess` de bloqueio em:

1. `app/`
2. `bootstrap/`
3. `config/`
4. `database/`
5. `storage/`

Justificativa:

Caso a hospedagem nao permita apontar o dominio diretamente para `public/`, esses bloqueios reduzem o risco de acesso direto a arquivos internos.

### 6. Tratamento de erro

Foram criadas respostas para:

1. 403 - acesso negado;
2. 404 - pagina nao encontrada;
3. 419 - token CSRF invalido;
4. 500 - erro interno.

Em ambiente sem debug, detalhes tecnicos nao devem ser exibidos ao usuario.

---

## 2026-07-06 - Fase 4: Autenticacao, usuarios e permissoes

### 1. Autenticacao propria com hash seguro

Foi implementado login com `password_verify()` e armazenamento esperado por `password_hash()`.

O sistema nao possui senha fixa versionada. O primeiro Admin deve ser criado com hash gerado localmente, conforme `docs/BANCO_DE_DADOS.md`.

### 2. Bloqueio temporario por tentativas invalidas

Falhas de senha incrementam `usuarios.tentativas_login_falhas`.

A partir de 5 falhas, `usuarios.bloqueado_ate` e preenchido por 15 minutos.

### 3. Auditoria e logs

Tentativas de login sao gravadas em `login_logs`.

Acoes sensiveis de usuarios e senha sao registradas em `auditoria_logs`, incluindo:

1. login bem-sucedido;
2. logout;
3. criacao de usuario;
4. edicao de usuario;
5. exclusao logica de usuario;
6. alteracao da propria senha.

### 4. Protecao do ultimo Admin ativo

O service de usuarios bloqueia alteracoes que inativem, removam ou troquem o perfil do ultimo Admin ativo.

### 5. Usuarios restritos ao Admin

As rotas de usuarios continuam protegidas pelas permissoes canonicas:

1. `usuarios.visualizar`
2. `usuarios.criar`
3. `usuarios.editar`
4. `usuarios.excluir`

Na matriz atual, apenas `ADMIN` possui essas permissoes.

---

## 2026-07-06 - Fase 5: Modulo Decretos e Cadastro de Desastre

### 1. Tabela operacional principal

O modulo visual continua chamado de `Decretos`, mas a entidade principal implementada e `desastres`, conforme os Documentos 05 e 06.

### 2. Protocolo DGD automatico

Foi criado `ProtocoloDgdService` para gerar protocolo automaticamente em transacao.

Formato adotado:

```text
DGD-AAAA-000001-AAAAMMDD-MUNICIPIO_NORMALIZADO
```

O sequencial e controlado por `sequencias_protocolos` com bloqueio `FOR UPDATE`.

### 3. Campos automaticos protegidos

O formulario nao envia `protocolo_dgd`, `protocolo_ano`, `protocolo_sequencial` nem `total_afetados` como campos gravaveis.

O total de afetados e calculado pelo banco pela coluna gerada e apenas pre-visualizado em JavaScript.

### 4. Listagem limitada a 20 registros

`DecretoRepository::paginate()` aplica limite maximo de 20 registros no backend, independentemente da interface.

### 5. Status PGE separado

Foi mantida a separacao entre:

1. `status_envio_pge_id`: editavel por Admin/Gestor;
2. `status_prazo_pge_calculado`: calculado pela view `vw_decretos_listagem` e pelo `PgePrazoService`.

### 6. Anexos protegidos

Anexos sao salvos em `storage/uploads/decretos`, fora da pasta publica.

O banco salva apenas metadados. Download passa por `AnexoController`, com rota autenticada.

### 7. COBRADE em cascata

Foram criadas rotas JSON autenticadas para carregar:

1. grupos;
2. subgrupos;
3. tipos;
4. subtipos;
5. detalhe do subtipo.

A base completa COBRADE ainda depende da conversao validada da planilha existente.

---

## 2026-07-06 - Fase 6: Interface visual e experiencia do usuario

### 1. Identidade visual institucional

Foram reaproveitados ativos existentes do projeto em `imagens/`, copiando para `public/assets` apenas os arquivos necessarios para exibicao publica:

1. `logo-cedec.png`
2. `app-icon-192.png`
3. `icon-password-eye.svg`

### 2. URL base inferida quando nao houver `.env`

`config/app.php` deixou de assumir URL fixa.

Quando `APP_URL` nao estiver definido, o helper `url()` infere esquema, host e caminho base a partir da requisicao. Isso permite testar pelo servidor PHP embutido e tambem manter compatibilidade com Wampserver.

### 3. Confirmacao de acoes criticas

Foi criado modal de confirmacao reutilizavel para acoes destrutivas, substituindo `confirm()` inline.

### 4. Responsividade

O menu lateral passou a ter comportamento recolhivel em telas menores, com botao acessivel no topo.

### 5. Acessibilidade basica

Foram adicionados:

1. foco visivel em links, botoes e campos;
2. `aria-label` no alternador de senha;
3. modal com `role="dialog"` e `aria-modal`;
4. estados visuais de botoes desabilitados durante processamento.

### 6. Badges de status

Foi criado `status_badge()` para padronizar status de homologacao, reconhecimento, PGE e prazo calculado.

---

## 2026-07-06 - Fase 7: Revisao, testes e documentacao de implantacao

### 1. Documentacao final criada

Foram criados os documentos:

1. `README.md`
2. `docs/INSTALACAO_WAMPSERVER.md`
3. `docs/INSTALACAO_HOSTINGER.md`
4. `docs/TESTES_MANUAIS.md`
5. `docs/SEGURANCA.md`

### 2. Checklist tecnico executado

Foi executado `php -l` em todos os arquivos PHP de `app`, `bootstrap`, `config` e `public`.

Resultado: sem erros de sintaxe.

### 3. Validacao de seguranca estatica

Foi verificada a presenca de:

1. CSRF nas rotas POST;
2. prepared statements;
3. `password_hash()` e `password_verify()`;
4. exclusao logica por `excluido_em`;
5. validacao de upload por MIME/extensao/tamanho;
6. ausencia de `.env` real e arquivos sensiveis em `storage`.

### 4. Limitacao da validacao

Nao foi executada importacao real do banco nesta fase, porque o cliente MySQL/MariaDB nao esta disponivel no PATH do ambiente de terminal.

A validacao real de login, cadastro e modulo Decretos depende de:

1. importar o banco no phpMyAdmin;
2. criar o Admin inicial com hash;
3. configurar `.env`;
4. executar o checklist de `docs/TESTES_MANUAIS.md`.

---

## 2026-07-08 - Regra de envio à PGE

Foi criada a coluna `desastres.data_conclusao_pge` para encerrar a contagem operacional da PGE sem depender de `atualizado_em`.

Regras aplicadas:

1. ao informar `data_envio_pge` no cadastro ou edição, o status muda automaticamente para `Enviado à PGE` quando ainda estiver em estado anterior ao envio;
2. ao alterar o status para `Enviado à PGE` pela listagem, o modal de histórico exige a data de envio;
3. ao alterar o status para `Concluído`, o sistema grava `data_conclusao_pge` quando ainda não houver;
4. a view `vw_decretos_listagem` calcula `duracao_pge_dias` de `data_envio_pge` até `data_conclusao_pge` ou até a data atual;
5. quando o status é `Concluído`, o prazo calculado passa a exibir `CONCLUÍDO` e a contagem fica congelada.

Migration relacionada: `database/migrations/2026_07_08_pge_status_date_rules.sql`.

### Complemento: homologação concluindo PGE

Quando `homologacao_status_id` passa para `HOMOLOGADO`, o sistema:

1. preserva o status e a data de conclusão PGE anteriores em campos técnicos;
2. altera `status_envio_pge_id` para `Concluído`;
3. grava `data_conclusao_pge` quando ainda não houver;
4. faz `status_prazo_pge_calculado` exibir `CONCLUÍDO`.

Quando a homologação deixa de ser `HOMOLOGADO`, o sistema restaura o status e a data de conclusão PGE preservados antes da homologação e limpa os campos técnicos de backup.

Migration relacionada: `database/migrations/2026_07_08_homologacao_pge_restore_rule.sql`.
