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

O layout autenticado passou a usar menu lateral moderno com dois comportamentos:

1. em desktop, o menu pode ser recolhido e expandido, preservando a preferência no navegador;
2. em telas menores, o menu abre como painel lateral com backdrop, tecla `Esc` e estados `aria`.

As páginas de decretos, novo cadastro, edição e detalhe receberam travas de largura, `min-width: 0`, quebra de texto e ajustes de grid para evitar rolagem horizontal nos principais breakpoints.

Foi adicionado botão global "Voltar para o topo" com ícone de seta, exibido apenas após rolagem da página, para melhorar a navegação em telas longas.

A página de gestão das COMPDECs passou a usar listagem em cards responsivos, indicadores de resumo e filtros por busca ampla, região, situação da COMPDEC e UBM atuante em seletor alimentado pelo banco. A tabela foi substituída para reduzir risco de rolagem horizontal e melhorar a leitura em tablet e celular.

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

Alinhamento posterior: `status_prazo_pge_calculado` tambem passou a usar `data_conclusao_pge` como marco final quando existir, mantendo coerencia com `duracao_pge_dias`, listagem, edicao e detalhe. Migration relacionada: `database/migrations/2026_07_08_align_pge_status_view_rule.sql`.

### Complemento: homologação concluindo PGE

Quando `homologacao_status_id` passa para `HOMOLOGADO`, o sistema:

1. preserva o status e a data de conclusão PGE anteriores em campos técnicos;
2. altera `status_envio_pge_id` para `Concluído`;
3. grava `data_conclusao_pge` quando ainda não houver;
4. faz `status_prazo_pge_calculado` exibir `CONCLUÍDO`.

Quando a homologação deixa de ser `HOMOLOGADO`, o sistema restaura o status e a data de conclusão PGE preservados antes da homologação e limpa os campos técnicos de backup.

Migration relacionada: `database/migrations/2026_07_08_homologacao_pge_restore_rule.sql`.

---

## 2026-07-14 - Relatorio de impressao do decreto

Foi incluida na listagem de decretos a acao `Imprimir`, que abre um modal com relatorio administrativo do processo de decreto registrado.

Decisoes aplicadas:

1. O relatorio e carregado por rota autenticada `GET /decretos/{id}/relatorio-impressao`, protegida pela permissao `decretos.detalhe`.
2. Os dados sao obtidos por `DecretoService::buscarDetalhe()`, garantindo informacoes atualizadas, anexos e historico de edicao.
3. A geracao do PDF utiliza a impressao nativa do navegador (`window.print()`), evitando dependencia de biblioteca PDF no servidor e mantendo compatibilidade com WampServer e hospedagem compartilhada.
4. O CSS de impressao oculta a aplicacao e imprime somente uma versao paginada do relatorio, montada em A4 real (`210mm x 297mm`) com rodape por pagina.
5. A numeracao `Pagina X de Y` e calculada pelo JavaScript antes da chamada de impressao, sem depender de `counter(page)` ou `counter(pages)`, que podem retornar `0 de 0` em alguns navegadores.
6. A paginacao reserva a area do rodape e fragmenta secoes extensas, como historico de edicao, para evitar conteudo escondido na parte inferior da pagina.
7. A medicao da paginacao usa o mesmo desenho aplicado ao PDF (`@media print`), incluindo colunas dos grids e linhas das tabelas, para evitar divergencia entre o calculo em tela e a impressao.
8. A primeira pagina possui reserva inferior maior porque concentra cabecalho, capa, imagem e blocos de resumo, reduzindo risco de sobreposicao com o rodape.
9. O hero do relatorio exibe a simbologia COBRADE ao lado da descricao oficial do desastre registrado.
10. Nao houve alteracao de banco de dados.

Arquivos principais: `config/routes.php`, `app/Controllers/DecretoController.php`, `app/Views/decretos/partials/print_report.php`, `app/Views/decretos/index.php`, `app/Views/layouts/app.php`, `public/assets/js/app.js`, `public/assets/css/app.css`.

---

## 2026-07-14 - Relatorio de impressao do painel

Foi incluida na pagina Painel a acao `Gerar relatorio`, usando o mesmo modal e mecanismo de impressao/PDF do relatorio de decreto.

Decisoes aplicadas:

1. O relatorio e carregado por rota autenticada `GET /painel/relatorio-impressao`, protegida pela permissao `painel.visualizar`.
2. O botao monta a URL do relatorio a partir dos campos atuais do formulario de filtros, garantindo que o PDF respeite o recorte selecionado pelo usuario.
3. O `PainelService::relatorio()` consolida resumo, indicadores, camadas do mapa, registros recentes e decretos do recorte.
4. O layout usa a mesma estrutura visual do relatorio de decreto, com cabecalho, hero, secoes, tabelas, rodape e paginacao.
5. Nao houve alteracao de banco de dados.

Arquivos principais: `config/routes.php`, `app/Controllers/PainelController.php`, `app/Services/PainelService.php`, `app/Views/painel/index.php`, `app/Views/painel/partials/print_report.php`, `public/assets/js/app.js`, `public/assets/css/app.css`.

---

## 2026-07-14 - Deploy Hostinger DGD

Foi preparada a estrutura local `deploy/` para publicacao do DGD no subdominio `dgd.defesacivilpa.com.br`.

Decisoes aplicadas:

1. A pasta publica foi separada em `deploy/public_html`, prevista para envio ao diretorio `/home/u696029111/domains/defesacivilpa.com.br/public_html/dgd`.
2. A aplicacao foi separada em `deploy/dgd_app`, prevista para envio ao diretorio `/home/u696029111/domains/defesacivilpa.com.br/dgd_app`.
3. O `index.php` publico procura o front controller em `dgd_app/public/index.php`, priorizando a aplicacao fora de `public_html`.
4. O `.env` de producao foi gerado com `APP_URL=https://dgd.defesacivilpa.com.br`, banco `u696029111_dgd` e usuario `u696029111_dgd`; a senha real do banco deve ser preenchida manualmente no servidor.
5. Arquivos locais sensiveis de `storage`, logs, cache, credenciais temporarias e backups locais de banco nao foram incluidos no deploy.
6. A pasta `deploy/` foi adicionada ao `.gitignore` para evitar commit acidental de configuracoes de publicacao.
7. Foi criado o arquivo unico `deploy/dgd_app/database/u696029111_dgd_banco_limpo.sql` para importacao no banco `u696029111_dgd`, com estrutura, cadastros de referencia e administrador inicial, sem decretos, anexos, sessoes, logs, recuperacoes de senha ou historico de usuario.
8. O administrador inicial usa a conta `admin@defesacivilpa.com.br` com senha temporaria, exigindo cadastro de 2FA e troca de senha no primeiro acesso.
A instalacao do DGD em dispositivos moveis passou a utilizar um Web App Manifest publico, com icones PNG opacos em 192x192 e 512x512, icone adaptativo `maskable`, `apple-touch-icon` em 180x180 e favicon ICO. O manifesto usa caminhos relativos para funcionar tanto no WampServer quanto no subdominio de producao, sem armazenar paginas autenticadas em cache.

---

## 2026-07-14 - Indicadores PGE em tempo real nos formularios

Os formularios de novo registro e edicao passaram a atualizar os indicadores da PGE antes do salvamento.

Decisoes aplicadas:

1. `Status de envio`, `Dias PGE` e `Status PGE` acompanham imediatamente as alteracoes na homologacao, na data de envio e na data de homologacao ou nao homologacao.
2. O limite de sete dias permanece igual ao servico de backend: de zero a sete dias e `No prazo`; acima de sete dias e `Pendente`.
3. `Homologado` apresenta `Aprovado`, `Nao homologado` apresenta `Reprovado` e a data de homologacao encerra a contagem visual.
4. A data de envio e o protocolo permanecem preservados ao transitar entre `Enviado a PGE`, `Homologado` e `Nao homologado`, embora os campos continuem visiveis apenas no estado correspondente.
5. O calculo no navegador e apenas uma pre-visualizacao; validacao, persistencia e regra definitiva continuam sob responsabilidade do backend.
6. Nao houve alteracao no banco de dados.

Arquivos principais: `app/Views/decretos/partials/form.php`, `public/assets/js/app.js`, `app/Views/layouts/app.php`.

---

## 2026-07-14 - Preservacao da duracao PGE apos homologacao

O indicador `Dias PGE` passou a preservar o total transcorrido entre o envio a PGE e a homologacao ou nao homologacao.

Decisoes aplicadas:

1. A data oficial de homologacao ou nao homologacao e o marco final prioritario da contagem.
2. Enquanto o processo estiver como `Enviado a PGE`, a contagem continua usando a data atual.
3. Depois de `Homologado` ou `Nao homologado`, o total permanece fixo e e reutilizado na listagem, nos formularios, no detalhe, na impressao/PDF e no relatorio do painel.
4. `PgePrazoService` centraliza o enriquecimento dos registros para impedir divergencias entre telas quando uma view desatualizada retornar o indicador vazio.
5. A conclusao da homologacao exige uma data de envio previamente registrada e bloqueia data de homologacao anterior ao envio.
6. A migration `2026_07_14_preserve_pge_duration_after_homologation.sql` consolida a data final dos registros existentes e recria as views operacionais.

Arquivos principais: `app/Services/PgePrazoService.php`, `app/Services/DecretoService.php`, `app/Services/PainelService.php`, `database/views.sql`, `database/install.sql` e `database/migrations/2026_07_14_preserve_pge_duration_after_homologation.sql`.
