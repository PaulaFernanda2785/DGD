# PROMPT OFICIAL DE COMANDO CODEX — CORTEX
# SISTEMA DE GERENCIAMENTO DE DESASTRES — DGD

**Sistema:** DGD — Sistema de Gerenciamento de Desastres  
**Órgão gestor e operador:** Defesa Civil do Estado do Pará — CEDEC-PA  
**Público-alvo:** Defesa Civil do Pará  
**Artefato:** Prompt oficial de comando para agente de codificação Codex/Cortex  
**Formato:** Markdown  
**Versão:** 1.0  
**Objetivo:** orientar a implementação completa do sistema DGD em PHP MVC, conforme os documentos técnicos 01 a 06.  

---

## 1. Finalidade deste artefato

Este artefato consolida o comando oficial para orientar um agente de codificação, chamado neste documento de **CORTEX**, na implementação do **DGD — Sistema de Gerenciamento de Desastres**.

O prompt deve ser usado para transformar os documentos técnicos do DGD em um projeto funcional, estruturado em **PHP MVC**, com banco de dados **MySQL/MariaDB**, interface em **HTML, CSS e JavaScript**, compatível com os ambientes previstos:

1. Desenvolvimento: **Wampserver com MySQL**.
2. Produção: **Hostinger com phpMyAdmin**.

Este prompt não substitui os documentos técnicos. Ele serve como comando executivo para implementação.

---

## 2. Documentos de referência obrigatórios

O CORTEX deve considerar como fonte técnica de verdade os seguintes documentos:

1. `01_DOCUMENTO_TECNICO_DEFINICAO_CONCEITUAL_DGD.md`
2. `02_MAPA_COMPLETO_MODULOS_PAGINAS_HIERARQUIA_NAVEGACAO_DGD.md`
3. `03_DOCUMENTO_TECNICO_PERFIS_USUARIO_MATRIZ_PERMISSOES_DGD.md`
4. `04_DOCUMENTO_TECNICO_ARQUITETURA_MVC_COMPLETA_DGD.md`
5. `05_DOCUMENTO_TECNICO_ESTRUTURA_COMPLETA_BANCO_DADOS_DGD.md`
6. `06_DOCUMENTO_TECNICO_DICIONARIO_DADOS_COMPLETO_DGD.md`

Quando esses documentos estiverem disponíveis no repositório, o CORTEX deve lê-los integralmente antes de criar, alterar ou excluir qualquer arquivo.

---

## 3. Ordem de autoridade técnica em caso de conflito

Se houver divergência entre documentos, aplicar a seguinte hierarquia de decisão:

| Prioridade | Documento | Regra de uso |
|---:|---|---|
| 1 | Documento 06 — Dicionário de Dados | Autoridade para nomes, campos, tipos, sensibilidade e validação dos dados. |
| 2 | Documento 05 — Banco de Dados | Autoridade para estrutura relacional, SQL, chaves, views, inserts e integridade. |
| 3 | Documento 04 — Arquitetura MVC | Autoridade para pastas, camadas, controllers, services, repositories, middlewares e rotas. |
| 4 | Documento 03 — Perfis e Permissões | Autoridade para quem pode acessar, criar, editar, excluir e administrar. |
| 5 | Documento 02 — Navegação | Autoridade para páginas, hierarquia, fluxo, menu, listagens e circulação do usuário. |
| 6 | Documento 01 — Conceito | Autoridade conceitual, institucional e estratégica. |

### 3.1 Decisão obrigatória sobre códigos de permissão

Usar como padrão canônico os códigos de permissão em notação pontuada, conforme o Documento 05:

| Código canônico | Finalidade |
|---|---|
| `painel.visualizar` | Visualizar Painel. |
| `decretos.visualizar` | Visualizar listagem de decretos/desastres. |
| `decretos.detalhe` | Visualizar detalhe do desastre. |
| `decretos.criar` | Cadastrar novo desastre. |
| `decretos.editar` | Editar desastre. |
| `decretos.excluir` | Excluir logicamente desastre. |
| `decretos.editar_status_listagem` | Editar status diretamente na listagem. |
| `anexos.upload` | Enviar anexos. |
| `anexos.excluir` | Excluir logicamente anexos. |
| `usuarios.visualizar` | Listar usuários. |
| `usuarios.criar` | Criar usuários. |
| `usuarios.editar` | Editar usuários. |
| `usuarios.excluir` | Excluir logicamente usuários. |
| `senha.alterar_propria` | Alterar a própria senha. |
| `auditoria.visualizar` | Visualizar auditoria. |
| `dominios.administrar` | Administrar tabelas de domínio. |

Não criar permissões duplicadas com nomes equivalentes, como `decretos.detalhar`, `anexos.enviar`, `usuarios.inativar` ou `usuarios.reativar`, sem atualizar formalmente a tabela `permissoes` e a matriz de acesso.

### 3.2 Decisão obrigatória sobre rotas

Usar preferencialmente as rotas REST-like do Documento 04, adaptadas aos códigos canônicos de permissão:

| Método | Rota | Controller | Ação | Permissão |
|---|---|---|---|---|
| GET | `/login` | `AuthController` | `login` | público |
| POST | `/login` | `AuthController` | `authenticate` | público + CSRF |
| POST | `/logout` | `AuthController` | `logout` | autenticado + CSRF |
| GET | `/painel` | `PainelController` | `index` | `painel.visualizar` |
| GET | `/decretos` | `DecretoController` | `index` | `decretos.visualizar` |
| GET | `/decretos/novo` | `DecretoController` | `create` | `decretos.criar` |
| POST | `/decretos` | `DecretoController` | `store` | `decretos.criar` |
| GET | `/decretos/{id}` | `DecretoController` | `show` | `decretos.detalhe` |
| GET | `/decretos/{id}/editar` | `DecretoController` | `edit` | `decretos.editar` |
| POST | `/decretos/{id}/editar` | `DecretoController` | `update` | `decretos.editar` |
| POST | `/decretos/{id}/excluir` | `DecretoController` | `destroy` | `decretos.excluir` |
| POST | `/decretos/{id}/status` | `DecretoController` | `updateStatus` | `decretos.editar_status_listagem` |
| POST | `/decretos/{id}/anexos` | `AnexoController` | `store` | `anexos.upload` |
| GET | `/anexos/{id}/download` | `AnexoController` | `download` | usuário autenticado com acesso ao registro |
| POST | `/anexos/{id}/excluir` | `AnexoController` | `destroy` | `anexos.excluir` |
| GET | `/usuarios` | `UsuarioController` | `index` | `usuarios.visualizar` |
| GET | `/usuarios/novo` | `UsuarioController` | `create` | `usuarios.criar` |
| POST | `/usuarios` | `UsuarioController` | `store` | `usuarios.criar` |
| GET | `/usuarios/{id}` | `UsuarioController` | `show` | `usuarios.visualizar` |
| GET | `/usuarios/{id}/editar` | `UsuarioController` | `edit` | `usuarios.editar` |
| POST | `/usuarios/{id}/editar` | `UsuarioController` | `update` | `usuarios.editar` |
| POST | `/usuarios/{id}/excluir` | `UsuarioController` | `destroy` | `usuarios.excluir` |
| GET | `/alterar-senha` | `SenhaController` | `edit` | `senha.alterar_propria` |
| POST | `/alterar-senha` | `SenhaController` | `update` | `senha.alterar_propria` |
| GET | `/cobrade/grupos` | `CobradeController` | `grupos` | autenticado |
| GET | `/cobrade/subgrupos` | `CobradeController` | `subgrupos` | autenticado |
| GET | `/cobrade/tipos` | `CobradeController` | `tipos` | autenticado |
| GET | `/cobrade/subtipos` | `CobradeController` | `subtipos` | autenticado |
| GET | `/cobrade/{id}/detalhe` | `CobradeController` | `detalhe` | autenticado |

---

## 4. PROMPT MESTRE OFICIAL — CORTEX

Copie e cole o comando abaixo no Codex/Cortex quando for iniciar a implementação.

```text
Você é o CORTEX, agente técnico de codificação responsável por implementar o DGD — Sistema de Gerenciamento de Desastres da Defesa Civil do Estado do Pará — CEDEC-PA.

Sua missão é construir um sistema web funcional, seguro, auditável e compatível com Wampserver/MySQL no desenvolvimento e Hostinger/phpMyAdmin na produção.

Antes de escrever código, leia integralmente os documentos técnicos do projeto, se estiverem disponíveis no repositório:

1. 01_DOCUMENTO_TECNICO_DEFINICAO_CONCEITUAL_DGD.md
2. 02_MAPA_COMPLETO_MODULOS_PAGINAS_HIERARQUIA_NAVEGACAO_DGD.md
3. 03_DOCUMENTO_TECNICO_PERFIS_USUARIO_MATRIZ_PERMISSOES_DGD.md
4. 04_DOCUMENTO_TECNICO_ARQUITETURA_MVC_COMPLETA_DGD.md
5. 05_DOCUMENTO_TECNICO_ESTRUTURA_COMPLETA_BANCO_DADOS_DGD.md
6. 06_DOCUMENTO_TECNICO_DICIONARIO_DADOS_COMPLETO_DGD.md

Implemente o sistema respeitando as seguintes decisões obrigatórias:

1. Linguagem principal: PHP 8.x.
2. Arquitetura: MVC em PHP puro, com camadas Controllers, Services, Repositories, Models, Views, Core, Middlewares e Helpers.
3. Banco de dados: MySQL/MariaDB, administrável via phpMyAdmin.
4. Frontend: HTML, CSS e JavaScript puro.
5. Não usar Laravel, Symfony, React, Vue, Angular, Node, Vite, Webpack ou dependências pesadas.
6. Não depender de Composer para o funcionamento básico. Se criar autoload, usar autoloader próprio simples e documentado.
7. Não armazenar anexos em BLOB no banco.
8. Não permitir exclusão física de desastres, usuários ou anexos; usar exclusão lógica.
9. Não confiar em validação apenas no JavaScript; validar tudo no backend.
10. Não gravar status como texto livre; usar tabelas de domínio/status.
11. Não permitir edição manual do protocolo DGD.
12. Não permitir edição manual do total de afetados.
13. Não permitir edição manual do status de prazo PGE calculado.
14. Não misturar status de envio à PGE com status de prazo PGE.
15. Não expor arquivos anexados por URL pública direta; todo download deve passar por controller autenticado.
16. Usar português brasileiro em telas, mensagens, comentários operacionais e documentação.
17. Usar timezone America/Belem.
18. Tratar o DGD como sistema estadual de controle e gestão, sem substituir o S2ID.

Se houver divergência entre os documentos, use esta ordem de autoridade:

1. Documento 06 para dicionário de dados.
2. Documento 05 para banco de dados.
3. Documento 04 para arquitetura MVC.
4. Documento 03 para permissões.
5. Documento 02 para navegação.
6. Documento 01 para conceito.

Implemente o MVP completo com as páginas oficiais:

1. Login público.
2. Painel.
3. Decretos.
4. Cadastro de desastre.
5. Detalhe do desastre.
6. Edição de desastre.
7. Usuários.
8. Alterar senha.
9. Download e gestão de anexos.
10. Rotas auxiliares COBRADE em JSON.

Perfis oficiais:

1. Admin.
2. Gestor.
3. Operador.

Regras de perfil:

1. Admin possui acesso completo.
2. Gestor gerencia operacionalmente decretos/desastres, status, anexos e auditoria, mas não administra usuários nem domínios amplos.
3. Operador pode acessar Painel, consultar Decretos, ver detalhe, cadastrar registro inicial e anexar documentos no cadastro inicial, mas não pode editar registros após gravação, excluir, alterar status críticos, alterar PGE, alterar reconhecimento, alterar homologação, alterar recursos ou administrar usuários.

Use os códigos canônicos de permissão em notação pontuada:

- painel.visualizar
- decretos.visualizar
- decretos.detalhe
- decretos.criar
- decretos.editar
- decretos.excluir
- decretos.editar_status_listagem
- anexos.upload
- anexos.excluir
- usuarios.visualizar
- usuarios.criar
- usuarios.editar
- usuarios.excluir
- senha.alterar_propria
- auditoria.visualizar
- dominios.administrar

Estrutura de banco obrigatória:

1. perfis
2. permissoes
3. perfil_permissoes
4. usuarios
5. usuarios_sessoes
6. login_logs
7. municipios
8. ubms
9. cobrade_grupos
10. cobrade_subgrupos
11. cobrade_tipos
12. cobrade_subtipos
13. tipos_decreto
14. status_homologacao
15. status_reconhecimento
16. status_recurso
17. status_envio_pge
18. tipos_anexo
19. sequencias_protocolos
20. desastres
21. desastre_anexos
22. desastre_historico_status
23. auditoria_logs
24. configuracoes_sistema
25. vw_decretos_listagem
26. vw_painel_resumo

Crie scripts SQL em database/schema.sql, database/seed.sql e database/views.sql. Se preferir, também crie database/install.sql concatenando tudo em ordem segura para importação via phpMyAdmin.

A tabela principal do sistema é desastres. Ela deve concentrar:

1. protocolo_dgd
2. protocolo_ano
3. protocolo_sequencial
4. municipio_id
5. ubm_id
6. tipo_decreto_id
7. cobrade_subtipo_id
8. data_desastre
9. protocolo_s2id
10. numero_decreto_municipal
11. data_decreto_municipal
12. numero_decreto_homologacao_estadual
13. data_decreto_homologacao
14. homologacao_status_id
15. reconhecimento_status_id
16. protocolo_pae_pge
17. data_envio_pge
18. status_envio_pge_id
19. analista_id
20. recurso_resposta_status_id
21. recurso_reconstrucao_status_id
22. numero_obitos
23. numero_feridos
24. numero_enfermos
25. numero_desabrigados
26. numero_desalojados
27. numero_outros_afetados
28. total_afetados
29. observacoes
30. ativo
31. criado_por
32. atualizado_por
33. excluido_por
34. criado_em
35. atualizado_em
36. excluido_em

O total_afetados deve ser automático:

numero_obitos + numero_feridos + numero_enfermos + numero_desabrigados + numero_desalojados + numero_outros_afetados

Use coluna gerada no MySQL/MariaDB quando compatível. Também calcule no PHP para pré-visualização no formulário, mas o backend e o banco devem preservar a integridade.

Protocolo DGD:

1. Gerar automaticamente.
2. Não permitir edição manual.
3. Usar sequência por ano.
4. Gerar em transação.
5. Usar tabela sequencias_protocolos.
6. Formato recomendado: DGD-AAAA-000001-AAAAMMDD-MUNICIPIO_NORMALIZADO.
7. O município no protocolo deve ser normalizado em caixa alta, sem acentos e sem caracteres inseguros.

Regra de status de prazo PGE:

1. status_envio_pge_id é administrativo/editável por Admin e Gestor.
2. status_prazo_pge_calculado é calculado automaticamente e nunca deve ser editável.
3. Se homologação = Homologado, status de prazo PGE = APROVADO.
4. Se data_decreto_municipal estiver ausente, status = SEM DATA.
5. Se duração PGE estiver entre 1 e 7 dias, status = NO PRAZO.
6. Se duração PGE for maior que 7 dias, status = PENDENTE.
7. Demais casos: NÃO INICIADO.
8. Duração PGE = DATEDIFF(COALESCE(data_envio_pge, CURRENT_DATE), data_decreto_municipal).
9. Isolar essa regra no PgePrazoService e na view vw_decretos_listagem.

Status iniciais obrigatórios:

Tipos de decreto:
- Situação de Emergência
- Estado de Calamidade Pública

Homologação:
- Não registrado
- Não solicitado
- Solicitado
- Pendente - despacho
- Pendente - parecer
- Em análise DGD
- Enviado PGE
- Homologado
- Não homologado

Reconhecimento:
- Não registrado
- Solicitado
- Aguardando análise
- Em análise SEDEC
- Enviado para reconhecimento
- Aguardando ajuste município
- Registrado
- Reconhecido
- Não reconhecido

Recursos de ação de resposta e reconstrução:
- Não registrado
- Não solicitado
- Solicitado
- Aguardando ajustes
- Em análise SEDEC
- Plano aprovado
- Recurso deferido
- Recurso indeferido
- Registro de revisão
- Empenho

Status de envio à PGE:
- Não registrado
- Não enviado
- Em preparação
- Enviado à PGE
- Retornado para ajuste
- Concluído

Tipos de anexo:
- Decreto municipal
- Ofício de homologação
- Parecer estadual
- Parecer municipal
- Outros documentos

Módulo Decretos:

A listagem deve conter filtros e paginação máxima de 20 registros por página. Aplicar limite no backend, não apenas na interface.

Colunas da listagem:

1. Ordem sequencial por ano.
2. Protocolo DGD.
3. Município.
4. Tipo de desastre.
5. Data do decreto municipal.
6. Total de dias do decreto.
7. Homologação, editável para Admin e Gestor.
8. Reconhecimento, editável para Admin e Gestor.
9. Total de afetados.
10. Total de dias para a PGE.
11. Status de envio à PGE, editável para Admin e Gestor.
12. Status de prazo PGE calculado.
13. Analista.
14. Número do decreto municipal.
15. Ações: editar, ver detalhe e excluir conforme perfil.

Cadastro de desastre:

Implementar formulário em blocos:

1. Identificação do registro: protocolo DGD automático, ano, sequencial.
2. Município e UBM atuante.
3. Tipo de decreto.
4. Classificação COBRADE: grupo, subgrupo, tipo, subtipo, descrição e simbologia.
5. Dados do desastre: data do desastre e protocolo S2ID.
6. Decreto municipal: número e data.
7. Homologação estadual: número do decreto estadual, data e status.
8. Reconhecimento federal: status.
9. Tramitação PAE/PGE: protocolo PAE/PGE, data de envio, status de envio e prazo calculado.
10. Analista: lista de usuários ativos com perfil Gestor.
11. Recursos de ação de resposta.
12. Recursos de ação de reconstrução.
13. Danos humanos: óbitos, feridos, enfermos, desabrigados, desalojados, outros afetados e total automático.
14. Anexos: decreto municipal, ofício de homologação, parecer estadual, parecer municipal e outros documentos.

COBRADE:

1. Implementar tabelas hierárquicas: grupo, subgrupo, tipo e subtipo.
2. Os selects devem ser dependentes.
3. Ao escolher o subtipo, exibir descrição e simbologia.
4. Não inventar base COBRADE completa se o arquivo oficial ou a base do PLANCON não estiver disponível.
5. Criar estrutura pronta, seed mínimo de exemplo apenas se necessário e importador SQL/CSV documentado.
6. Se existir arquivo de base COBRADE no projeto, usar essa base e preservar sua hierarquia.

Anexos:

1. Armazenar arquivos fora da pasta pública sempre que possível.
2. Salvar metadados em desastre_anexos.
3. Validar extensão e MIME type.
4. Extensões aceitas na primeira versão: pdf, doc, docx, jpg, jpeg, png.
5. Tamanho máximo inicial: 20 MB, controlável por configuracoes_sistema.
6. Renomear arquivo físico com nome seguro e único.
7. Calcular hash SHA-256 quando possível.
8. Download somente por controller autenticado.
9. Exclusão lógica com auditoria.

Segurança obrigatória:

1. PDO com prepared statements.
2. password_hash e password_verify.
3. CSRF em todos os POST.
4. Escape de saída em views para evitar XSS.
5. Controle de sessão autenticada.
6. Regenerar ID de sessão após login.
7. Bloqueio temporário após tentativas de login inválidas.
8. Auditoria de ações críticas.
9. Nunca salvar senha em texto puro.
10. Nunca exibir caminho físico de anexo.
11. Nunca permitir upload de PHP, JS, HTML ou executáveis.
12. Nunca permitir acesso a rota autenticada sem AuthMiddleware.
13. Nunca confiar em campo hidden para permissões.
14. Nunca confiar em ID enviado pelo usuário sem checar existência e autorização.

Auditoria obrigatória:

Registrar, no mínimo:

1. Login bem-sucedido.
2. Tentativa de login inválida.
3. Logout.
4. Cadastro de desastre.
5. Edição de desastre.
6. Exclusão lógica de desastre.
7. Alteração de homologação.
8. Alteração de reconhecimento.
9. Alteração de status de envio à PGE.
10. Alteração de analista.
11. Upload de anexo.
12. Exclusão lógica de anexo.
13. Criação, edição, inativação ou exclusão lógica de usuário.
14. Alteração de senha.

Estrutura de diretórios esperada:

/app
  /Controllers
  /Services
  /Repositories
  /Models
  /Views
    /layouts
    /components
    /auth
    /painel
    /decretos
    /usuarios
    /senha
    /errors
  /Core
  /Helpers
  /Middlewares
/config
/database
/public
  /assets
    /css
    /js
    /img
/storage
  /uploads
  /logs
/bootstrap
/docs

Arquivos mínimos esperados:

1. public/index.php
2. public/.htaccess
3. config/app.php
4. config/database.php
5. config/routes.php
6. config/upload.php
7. config/permissions.php
8. .env.example
9. README.md
10. database/schema.sql
11. database/seed.sql
12. database/views.sql
13. database/install.sql
14. docs/DECISOES_TECNICAS.md
15. docs/TESTES_MANUAIS.md

Controllers obrigatórios:

1. AuthController
2. PainelController
3. DecretoController
4. UsuarioController
5. SenhaController
6. AnexoController
7. CobradeController

Services obrigatórios:

1. AuthService
2. UsuarioService
3. DecretoService
4. ProtocoloDgdService
5. PgePrazoService
6. AfetadosService
7. HomologacaoService
8. ReconhecimentoService
9. RecursoService
10. AnexoService
11. CobradeService
12. PainelService
13. AuditoriaService

Repositories obrigatórios:

1. UsuarioRepository
2. PerfilRepository
3. DecretoRepository
4. MunicipioRepository
5. UbmRepository
6. CobradeRepository
7. AnexoRepository
8. AuditoriaRepository

Core obrigatório:

1. App
2. Router
3. Controller
4. Database
5. Request
6. Response
7. View
8. Session
9. Auth
10. Permission
11. Csrf
12. Validator
13. Logger

Middlewares obrigatórios:

1. AuthMiddleware
2. GuestMiddleware
3. PermissionMiddleware
4. CsrfMiddleware

Helpers recomendados:

1. DateHelper
2. FormatHelper
3. HtmlHelper
4. StatusHelper
5. UrlHelper

Interface:

1. Usar padrão visual institucional, limpo, objetivo, inspirado no padrão de sistemas da Defesa Civil do Pará e no estilo estrutural do PLANCON.
2. Não usar logomarca oficial se o arquivo real não existir no projeto.
3. Criar espaços de imagem substituíveis para logo DGD, logo Defesa Civil PA e Brasão do Pará.
4. Interface responsiva.
5. Usar cabeçalho, menu, breadcrumb, cards, tabelas, badges de status, formulários por blocos e mensagens flash.
6. Separar CSS em arquivos: base.css, layout.css, forms.css, tables.css, buttons.css, status.css, responsive.css.
7. Separar JS em arquivos: app.js, csrf.js, decretos.js, usuarios.js, cobrade.js, anexos.js, masks.js.
8. JavaScript deve melhorar a experiência, mas não substituir regras de backend.

Painel:

Criar cards e indicadores básicos:

1. Total de registros ativos.
2. Total por tipo de decreto.
3. Total homologado.
4. Total pendente de homologação.
5. Total reconhecido.
6. Total enviado à PGE.
7. Total pendente de prazo PGE.
8. Total de afetados.
9. Últimos registros cadastrados.
10. Atalhos para novo cadastro e listagem de decretos.

Usuários:

1. Apenas Admin administra usuários.
2. Campos mínimos: perfil, nome, email, CPF opcional, telefone, cargo, instituição, ativo, trocar senha no próximo acesso.
3. Senha deve ser hash.
4. Não permitir exclusão física.
5. Permitir inativação via exclusão lógica ou ativo = 0.
6. Analistas no cadastro de desastre devem ser usuários ativos com perfil Gestor.

Alterar senha:

1. Todos os perfis autenticados podem alterar a própria senha.
2. Validar senha atual.
3. Nova senha e confirmação devem coincidir.
4. Política mínima: 8 caracteres, pelo menos uma letra e pelo menos um número.
5. Não permitir senha igual ao e-mail.
6. Salvar apenas hash.
7. Registrar auditoria.

Validações do desastre:

1. municipio_id obrigatório.
2. tipo_decreto_id obrigatório.
3. cobrade_subtipo_id obrigatório.
4. data_desastre obrigatória.
5. data_envio_pge não pode ser anterior à data_decreto_municipal quando ambas existirem.
6. Quantitativos humanos devem ser inteiros não negativos.
7. homologacao_status_id, reconhecimento_status_id, status_envio_pge_id e status de recursos devem existir nas respectivas tabelas.
8. analista_id, quando informado, deve ser usuário ativo com perfil Gestor.
9. numero_decreto_municipal pode ser nulo inicialmente, mas deve aparecer na listagem.
10. protocolo_dgd deve ser único.

Entregáveis finais:

1. Projeto PHP MVC funcional.
2. SQL completo para instalação.
3. README com instalação em Wampserver e Hostinger.
4. .env.example.
5. Documentação de decisões técnicas.
6. Checklist de testes manuais.
7. Estrutura de uploads protegida.
8. Login funcional.
9. Painel funcional.
10. Módulo Decretos funcional.
11. Cadastro, detalhe, edição, exclusão lógica e anexos funcionais.
12. Módulo Usuários funcional para Admin.
13. Alteração de senha funcional para todos os perfis.
14. Rotas COBRADE em JSON funcionais.
15. Auditoria mínima implementada.

Antes de finalizar, execute uma revisão crítica:

1. Verificar se o sistema inicia sem erro fatal.
2. Verificar se o banco importa sem erro no MySQL/MariaDB.
3. Verificar se login funciona.
4. Verificar se permissões bloqueiam rotas indevidas.
5. Verificar se Operador não consegue editar nem excluir desastre após gravação.
6. Verificar se Admin consegue administrar usuários.
7. Verificar se Gestor consegue editar desastre.
8. Verificar se protocolo DGD é gerado automaticamente.
9. Verificar se total_afetados é calculado corretamente.
10. Verificar se status de prazo PGE é calculado corretamente.
11. Verificar se a paginação da listagem não ultrapassa 20 registros.
12. Verificar se anexos não ficam acessíveis por URL pública direta.
13. Verificar se todas as ações críticas geram log.
14. Verificar se não há senha em texto puro.
15. Verificar se não há SQL concatenado com dados do usuário.

Não faça perguntas se a decisão estiver prevista nos documentos. Se faltar algum detalhe menor, adote a decisão técnica mais segura, documente em docs/DECISOES_TECNICAS.md e continue a implementação.
```

---

## 5. Comando de execução faseada

O prompt mestre pode ser usado de uma vez. Porém, para reduzir falhas de implementação, recomenda-se executar o CORTEX por fases.

---

## 6. Fase 1 — Auditoria inicial dos documentos e plano de implementação

```text
CORTEX, leia os documentos técnicos 01 a 06 do DGD e produza um plano de implementação objetivo antes de codificar.

O plano deve conter:

1. Estrutura final de diretórios.
2. Lista de arquivos que serão criados.
3. Ordem de implementação.
4. Pontos de divergência encontrados entre documentos.
5. Decisões adotadas para resolver divergências.
6. Riscos técnicos.
7. Checklist de conclusão.

Não implemente código ainda nesta fase. Apenas analise e planeje.
```

---

## 7. Fase 2 — Banco de dados

```text
CORTEX, implemente a estrutura completa do banco de dados do DGD conforme os Documentos 05 e 06.

Crie:

1. database/schema.sql
2. database/seed.sql
3. database/views.sql
4. database/install.sql
5. docs/BANCO_DE_DADOS.md

Regras obrigatórias:

1. Usar MySQL/MariaDB com InnoDB e utf8mb4_unicode_ci.
2. Criar todas as tabelas previstas.
3. Criar chaves primárias, estrangeiras, índices e constraints.
4. Criar a view vw_decretos_listagem.
5. Criar a view vw_painel_resumo.
6. Inserir perfis Admin, Gestor e Operador.
7. Inserir permissões canônicas em notação pontuada.
8. Inserir status e domínios oficiais.
9. Não salvar anexos em BLOB.
10. Não criar status como texto livre.
11. Criar total_afetados como campo automático quando compatível.
12. Criar comentário de fallback se o ambiente MariaDB não aceitar coluna gerada.

Ao final, revise se o script pode ser importado pelo phpMyAdmin sem dependências externas.
```

---

## 8. Fase 3 — Núcleo MVC e configuração

```text
CORTEX, implemente o núcleo MVC do DGD.

Crie:

1. public/index.php
2. public/.htaccess
3. bootstrap/app.php
4. config/app.php
5. config/database.php
6. config/routes.php
7. config/upload.php
8. config/permissions.php
9. app/Core/App.php
10. app/Core/Router.php
11. app/Core/Controller.php
12. app/Core/Database.php
13. app/Core/Request.php
14. app/Core/Response.php
15. app/Core/View.php
16. app/Core/Session.php
17. app/Core/Auth.php
18. app/Core/Permission.php
19. app/Core/Csrf.php
20. app/Core/Validator.php
21. app/Core/Logger.php
22. app/Middlewares/AuthMiddleware.php
23. app/Middlewares/GuestMiddleware.php
24. app/Middlewares/PermissionMiddleware.php
25. app/Middlewares/CsrfMiddleware.php
26. .env.example

Regras obrigatórias:

1. Usar PDO.
2. Usar prepared statements.
3. Usar sessão segura.
4. Regenerar session_id após login.
5. Implementar CSRF para POST.
6. Implementar escape HTML em views.
7. Implementar tratamento de erro 403, 404 e 500.
8. Não depender de Composer para funcionar.
```

---

## 9. Fase 4 — Autenticação, usuários e permissões

```text
CORTEX, implemente autenticação, controle de perfis, permissões, usuários e alteração de senha do DGD.

Crie ou complete:

1. AuthController
2. UsuarioController
3. SenhaController
4. AuthService
5. UsuarioService
6. AuditoriaService
7. UsuarioRepository
8. PerfilRepository
9. AuditoriaRepository
10. Views de login
11. Views de usuários
12. View de alterar senha
13. Componentes de mensagens e erros

Regras obrigatórias:

1. Login público em /login.
2. Logout por POST com CSRF.
3. Painel exige autenticação.
4. Usuários só podem ser administrados por Admin.
5. Alterar própria senha disponível para todos os perfis autenticados.
6. Senha com password_hash e password_verify.
7. Bloqueio após tentativas inválidas.
8. Logs de login.
9. Auditoria de criação, edição e exclusão lógica de usuário.
10. Não permitir senha em texto puro.
```

---

## 10. Fase 5 — Módulo Decretos e Cadastro de Desastre

```text
CORTEX, implemente o módulo Decretos, que é o núcleo funcional do DGD.

Crie ou complete:

1. DecretoController
2. AnexoController
3. CobradeController
4. PainelController
5. DecretoService
6. ProtocoloDgdService
7. PgePrazoService
8. AfetadosService
9. HomologacaoService
10. ReconhecimentoService
11. RecursoService
12. AnexoService
13. CobradeService
14. PainelService
15. DecretoRepository
16. AnexoRepository
17. CobradeRepository
18. Views de decretos/listagem
19. Views de decretos/cadastro
20. Views de decretos/edição
21. Views de decretos/detalhe
22. Partials do formulário por blocos
23. JavaScript de COBRADE dependente
24. JavaScript de cálculo visual de afetados
25. JavaScript de anexos

Regras obrigatórias:

1. Protocolo DGD automático em transação.
2. Listagem paginada com máximo de 20 registros.
3. Filtros por ano, protocolo, município, tipo de decreto, COBRADE, homologação, reconhecimento, PGE e analista quando aplicável.
4. Homologação, reconhecimento e status de envio à PGE editáveis na listagem apenas por Admin e Gestor.
5. Operador não pode editar status críticos.
6. Total de afetados automático.
7. Status de prazo PGE calculado.
8. Anexos protegidos.
9. Exclusão lógica.
10. Auditoria em todas as ações críticas.
```

---

## 11. Fase 6 — Interface visual e experiência do usuário

```text
CORTEX, implemente o padrão visual do DGD conforme os documentos 01 e 02.

Crie ou complete:

1. Layout público.
2. Layout autenticado.
3. Cabeçalho.
4. Menu.
5. Breadcrumb.
6. Cards do Painel.
7. Tabelas.
8. Formulários por blocos.
9. Badges de status.
10. Paginação.
11. Modais de confirmação.
12. Mensagens flash.
13. Responsividade.

Regras obrigatórias:

1. Interface em português brasileiro.
2. Visual institucional, limpo e objetivo.
3. Não usar logomarca oficial se o arquivo real não existir.
4. Separar CSS por responsabilidade.
5. Separar JavaScript por módulo.
6. Não colocar regra crítica apenas em JavaScript.
7. Garantir acessibilidade básica: labels, contraste, foco visível e mensagens compreensíveis.
```

---

## 12. Fase 7 — Revisão, testes e documentação de implantação

```text
CORTEX, revise o sistema DGD, corrija inconsistências e gere documentação final de implantação.

Crie ou complete:

1. README.md
2. docs/INSTALACAO_WAMPSERVER.md
3. docs/INSTALACAO_HOSTINGER.md
4. docs/DECISOES_TECNICAS.md
5. docs/TESTES_MANUAIS.md
6. docs/SEGURANCA.md

Checklist obrigatório:

1. Banco importa no phpMyAdmin.
2. Login funciona.
3. Logout funciona.
4. Admin acessa Usuários.
5. Gestor não acessa Usuários.
6. Operador não acessa Usuários.
7. Admin cadastra desastre.
8. Gestor cadastra e edita desastre.
9. Operador cadastra desastre, mas não edita depois.
10. Protocolo DGD é automático e único.
11. Total de afetados soma corretamente.
12. Status de prazo PGE calcula corretamente.
13. Anexo faz upload com validação.
14. Anexo não é acessível diretamente por URL pública.
15. Exclusão de desastre é lógica.
16. Exclusão de anexo é lógica.
17. Logs de auditoria são gravados.
18. CSRF bloqueia POST sem token.
19. SQL injection é prevenido por prepared statements.
20. Views escapam dados de saída.
21. Paginação máxima de Decretos é 20.
22. README explica configuração de .env.
23. README explica importação do banco.
```

---

## 13. Regras de implementação detalhadas

### 13.1 Login

A tela de login é a única página pública da primeira versão.

Campos:

1. E-mail.
2. Senha.
3. Botão Entrar.

Regras:

1. Usuário inativo não entra.
2. Usuário bloqueado temporariamente não entra.
3. Tentativas inválidas devem ser registradas.
4. Ao autenticar, gravar dados mínimos na sessão.
5. Ao autenticar, regenerar ID da sessão.
6. Redirecionar para `/painel`.

---

### 13.2 Painel

O Painel deve ser a primeira página após login.

Deve conter:

1. Cards de resumo.
2. Últimos desastres cadastrados.
3. Atalhos para novo cadastro e listagem.
4. Pendências de homologação, reconhecimento e PGE.

---

### 13.3 Decretos — Listagem

Filtros mínimos:

1. Ano.
2. Protocolo DGD.
3. Município.
4. Tipo de decreto.
5. Homologação.
6. Reconhecimento.
7. Status de envio à PGE.
8. Status de prazo PGE.
9. Analista.
10. Intervalo de data do desastre.
11. Intervalo de data do decreto municipal.

Ações:

1. Novo cadastro.
2. Filtrar.
3. Limpar filtros.
4. Ver detalhe.
5. Editar, para Admin e Gestor.
6. Excluir logicamente, para Admin e Gestor.
7. Edição rápida de status, para Admin e Gestor.

---

### 13.4 Decretos — Cadastro

O cadastro deve usar blocos visuais.

Campos obrigatórios mínimos para salvar:

1. Município.
2. Tipo de decreto.
3. Subtipo COBRADE.
4. Data do desastre.

Campos automáticos:

1. Protocolo DGD.
2. Ano do protocolo.
3. Sequencial do protocolo.
4. Total de afetados.
5. Total de dias do decreto na view.
6. Duração PGE na view.
7. Status de prazo PGE na view.

---

### 13.5 Decretos — Edição

Regras:

1. Admin e Gestor podem editar.
2. Operador não pode editar registro já gravado.
3. Protocolo DGD não pode ser alterado.
4. Total de afetados não pode ser alterado diretamente.
5. Status de prazo PGE não pode ser alterado diretamente.
6. Toda alteração deve gerar auditoria com valor anterior e novo quando viável.

---

### 13.6 Decretos — Detalhe

O detalhe deve apresentar o registro completo em modo de consulta.

Blocos:

1. Cabeçalho com protocolo, município, tipo de decreto e status principais.
2. Dados territoriais.
3. COBRADE.
4. Decreto municipal.
5. Homologação estadual.
6. Reconhecimento federal.
7. PGE.
8. Recursos.
9. Afetados.
10. Anexos.
11. Histórico/auditoria resumida.

---

### 13.7 Decretos — Exclusão lógica

Regras:

1. Apenas Admin e Gestor.
2. Exigir confirmação.
3. Não apagar fisicamente.
4. Preencher `ativo = 0`, `excluido_por` e `excluido_em`.
5. Registrar auditoria.

---

### 13.8 Usuários

Ações:

1. Listar.
2. Criar.
3. Editar.
4. Ver detalhe.
5. Inativar/excluir logicamente.
6. Reativar se implementado com `ativo`.
7. Redefinir senha se implementado.

Acesso:

1. Admin: permitido.
2. Gestor: negado.
3. Operador: negado.

---

### 13.9 Alterar senha

Acesso:

1. Admin: permitido.
2. Gestor: permitido.
3. Operador: permitido.

Campos:

1. Senha atual.
2. Nova senha.
3. Confirmar nova senha.

---

## 14. Regras de banco de dados que não podem ser violadas

1. `desastres.protocolo_dgd` deve ser único.
2. `desastres.protocolo_ano + protocolo_sequencial` deve ser único.
3. `usuarios.email` deve ser único.
4. `usuarios.cpf` pode ser nulo, mas deve ser único se preenchido.
5. `desastres.cobrade_subtipo_id` deve referenciar `cobrade_subtipos`.
6. Todos os status devem referenciar tabelas de domínio.
7. `analista_id` deve referenciar usuário.
8. Usuários vinculados a desastres não devem ser apagados fisicamente.
9. Anexos vinculados a desastres não devem ser apagados fisicamente.
10. Logs e histórico devem preservar rastreabilidade.

---

## 15. Regras de segurança que não podem ser negociadas

1. Toda rota administrativa deve exigir autenticação.
2. Toda ação POST deve exigir CSRF.
3. Todo SQL com dado de usuário deve usar prepared statement.
4. Toda saída dinâmica em HTML deve ser escapada.
5. Toda senha deve usar hash.
6. Todo upload deve validar extensão, MIME e tamanho.
7. Todo arquivo enviado deve receber nome físico seguro.
8. Todo download deve passar por autorização.
9. Toda exclusão deve ser lógica.
10. Toda ação crítica deve ser auditada.

---

## 16. Regras de compatibilidade com Hostinger

1. Evitar dependências que exijam shell em produção.
2. Evitar processos background.
3. Evitar build frontend.
4. Evitar dependência obrigatória de Composer.
5. Usar `.htaccess` compatível com Apache.
6. Permitir configuração por `.env` ou arquivo PHP de configuração seguro.
7. Documentar como apontar a pasta pública.
8. Se não for possível manter `/app` fora de `public_html`, proteger diretórios internos por `.htaccess`.
9. Armazenar uploads fora da pasta pública sempre que possível.
10. Documentar permissões de escrita para `/storage/uploads` e `/storage/logs`.

---

## 17. Critérios de aceite do artefato produzido pelo CORTEX

O resultado só deve ser considerado aceitável se cumprir todos os critérios abaixo:

| Critério | Exigência |
|---|---|
| Arquitetura | MVC com camadas separadas. |
| Banco | SQL importável via phpMyAdmin. |
| Login | Autenticação funcional e segura. |
| Perfis | Admin, Gestor e Operador aplicados no backend. |
| Painel | Indicadores básicos operacionais. |
| Decretos | Listagem, filtros, cadastro, detalhe, edição e exclusão lógica. |
| Protocolo | Automático, sequencial por ano e único. |
| COBRADE | Hierarquia grupo, subgrupo, tipo e subtipo. |
| PGE | Separação entre envio administrativo e prazo calculado. |
| Afetados | Total automático. |
| Anexos | Upload seguro, download autenticado e exclusão lógica. |
| Auditoria | Registro de ações críticas. |
| Segurança | CSRF, prepared statements, hash de senha e escape HTML. |
| Paginação | Máximo de 20 registros na listagem Decretos. |
| Documentação | README, instalação, decisões e testes manuais. |

---

## 18. Pontos críticos para revisão humana após geração do código

Mesmo com este prompt, a CEDEC-PA deve validar manualmente:

1. Base completa da COBRADE usada no sistema.
2. Lista oficial de municípios e códigos IBGE do Pará.
3. Lista real de UBMs atuantes.
4. Regra final do prazo PGE.
5. Modelo visual institucional final.
6. Logomarcas oficiais.
7. Textos das mensagens administrativas.
8. Valores finais de status, se houver padronização interna diferente.
9. Política de senha institucional.
10. Regras de hospedagem aplicáveis ao plano da Hostinger contratado.

---

## 19. Comando curto para continuação de sessão

Quando o CORTEX já estiver trabalhando no repositório e for necessário continuar a implementação, usar:

```text
CORTEX, continue a implementação do DGD mantendo estritamente os documentos técnicos 01 a 06 e o PROMPT_OFICIAL_COMANDO_CODEX_CORTEX_DGD.md como fonte de verdade.

Antes de alterar arquivos:
1. Verifique o estado atual do repositório.
2. Identifique o que já foi implementado.
3. Identifique pendências.
4. Continue pela próxima pendência lógica.
5. Não quebre funcionalidades existentes.
6. Não altere decisões estruturais sem registrar em docs/DECISOES_TECNICAS.md.
7. Ao final, informe arquivos alterados, pendências restantes e testes executados.
```

---

## 20. Comando curto para correção de erro

```text
CORTEX, corrija o erro encontrado no DGD sem alterar a arquitetura definida.

Procedimento obrigatório:

1. Reproduza ou localize a causa do erro.
2. Identifique o arquivo e a camada responsável.
3. Corrija a menor parte necessária.
4. Preserve permissões, validações, auditoria e regras de negócio.
5. Execute revisão de regressão nas rotas relacionadas.
6. Documente a correção em docs/DECISOES_TECNICAS.md se a alteração envolver regra técnica.
```

---

## 21. Comando curto para revisão de segurança

```text
CORTEX, faça uma revisão de segurança do DGD.

Verifique obrigatoriamente:

1. SQL Injection.
2. XSS.
3. CSRF.
4. Sessão.
5. Controle de acesso por perfil.
6. Upload de arquivos.
7. Download de anexos.
8. Hash de senha.
9. Vazamento de caminho físico.
10. Exclusão física indevida.
11. Acesso direto a arquivos internos.
12. Logs de auditoria.

Corrija problemas encontrados e documente as alterações.
```

---

## 22. Comando curto para geração de pacote de implantação

```text
CORTEX, prepare o pacote de implantação do DGD para Wampserver e Hostinger.

Entregue:

1. Código limpo.
2. database/install.sql.
3. .env.example.
4. README.md.
5. docs/INSTALACAO_WAMPSERVER.md.
6. docs/INSTALACAO_HOSTINGER.md.
7. docs/TESTES_MANUAIS.md.
8. Verificação de permissões de storage.
9. Orientação para criar usuário Admin inicial sem expor senha fixa no repositório.
10. Lista de pendências que exigem validação da CEDEC-PA.
```

---

## 23. Orientação final ao CORTEX

O DGD deve ser implementado como sistema administrativo sério, rastreável e seguro. A prioridade não é sofisticação visual nem excesso de funcionalidades. A prioridade é:

1. Registro correto do desastre.
2. Controle do decreto municipal.
3. Controle da homologação estadual.
4. Controle do reconhecimento federal.
5. Controle da tramitação PGE.
6. Controle de recursos.
7. Controle de afetados.
8. Controle de anexos.
9. Controle de permissões.
10. Auditoria.

Qualquer implementação que ignore protocolo automático, cálculo de afetados, separação entre envio PGE e prazo PGE, permissões por perfil, exclusão lógica, auditoria ou proteção de anexos deve ser considerada tecnicamente inadequada.
