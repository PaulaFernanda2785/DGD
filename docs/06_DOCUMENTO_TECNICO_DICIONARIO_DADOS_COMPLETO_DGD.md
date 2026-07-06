# 06 — DOCUMENTO TÉCNICO
# DICIONÁRIO DE DADOS COMPLETO DO SISTEMA DGD

**Sistema:** DGD — Sistema de Gerenciamento de Desastres  
**Órgão gestor:** Coordenadoria Estadual de Defesa Civil do Estado do Pará — CEDEC-PA  
**Público-alvo:** Defesa Civil do Pará  
**Tipo de documento:** Dicionário de dados completo  
**Versão:** 1.0  
**Formato:** Markdown  
**Banco de dados previsto:** MySQL/MariaDB administrado por phpMyAdmin  
**Ambiente de desenvolvimento:** Wampserver com MySQL  
**Ambiente de produção:** Hostinger com PHP, MySQL/MariaDB e phpMyAdmin  
**Status:** Especificação técnica inicial para implantação do DGD  

---

## 1. Finalidade do documento

Este documento define o **dicionário de dados completo** do **DGD — Sistema de Gerenciamento de Desastres**, descrevendo campo por campo as tabelas, views, domínios, regras de preenchimento, regras de validação, origem dos dados, obrigatoriedade, comportamento esperado na interface e observações de implementação.

O dicionário tem como função servir de referência para:

1. Criação e manutenção do banco de dados.
2. Implementação dos Models e Repositories na arquitetura MVC.
3. Validação dos formulários HTML/PHP.
4. Padronização dos cadastros de desastre e decretos.
5. Controle de permissões por perfil.
6. Construção das listagens e filtros.
7. Geração de relatórios e painéis.
8. Auditoria de alterações críticas.
9. Treinamento dos operadores do sistema.
10. Manutenção futura por equipe técnica da CEDEC-PA.

Este documento complementa o **Documento 05 — Estrutura Completa do Banco de Dados**, que definiu as tabelas, chaves, constraints, índices e views. Aqui, o foco é a **semântica dos dados**.

---

## 2. Relação com os documentos anteriores

| Documento | Relação com este dicionário |
|---|---|
| 01 — Definição Conceitual do Sistema | Define a finalidade institucional e operacional do DGD. |
| 02 — Mapa dos Módulos, Páginas e Hierarquia de Navegação | Define onde cada dado aparece na interface. |
| 03 — Perfis de Usuário e Matriz de Permissões | Define quem pode visualizar, criar, editar ou excluir cada dado. |
| 04 — Arquitetura MVC Completa | Define onde as regras de validação e persistência serão implementadas. |
| 05 — Estrutura Completa do Banco de Dados | Define fisicamente as tabelas, campos e relacionamentos. |
| 06 — Dicionário de Dados | Define o significado, origem, validação e uso de cada campo. |

---

## 3. Convenções utilizadas no dicionário

### 3.1. Convenções de obrigatoriedade

| Valor | Significado |
|---|---|
| Sim | Campo obrigatório para gravação. |
| Não | Campo opcional. |
| Auto | Campo preenchido automaticamente pelo banco ou pelo backend. |
| Condicional | Campo obrigatório apenas em determinado estado do registro ou fluxo operacional. |

### 3.2. Convenções de chave

| Valor | Significado |
|---|---|
| PK | Chave primária. |
| FK | Chave estrangeira. |
| UK | Valor único. |
| IDX | Campo indexado para pesquisa, filtro ou ordenação. |
| Domínio | Campo que controla uma tabela de valores padronizados. |
| Calculado | Campo derivado de outros campos. |

### 3.3. Tipos de dados principais

| Tipo | Uso no DGD |
|---|---|
| `TINYINT UNSIGNED` | Identificadores pequenos, status, perfis e flags. |
| `SMALLINT UNSIGNED` | Ano do protocolo e códigos numéricos de menor volume. |
| `INT UNSIGNED` | Identificadores médios e quantidades. |
| `BIGINT UNSIGNED` | Identificadores de tabelas operacionais com grande volume. |
| `VARCHAR` | Texto curto com tamanho limitado. |
| `TEXT` | Texto longo, observações e descrições. |
| `DATE` | Datas sem horário. |
| `DATETIME` | Data e hora completas. |
| `TINYINT(1)` | Booleano operacional: 1 para verdadeiro/ativo e 0 para falso/inativo. |
| `JSON` | Registro estruturado de dados antes/depois em auditoria, quando suportado. |
| `LONGTEXT` | Alternativa ao tipo JSON em ambientes MariaDB/phpMyAdmin incompatíveis. |

---

## 4. Campos comuns do sistema

Alguns campos aparecem em várias tabelas. A regra semântica é padronizada.

| Campo | Significado padrão | Regra operacional |
|---|---|---|
| `id` | Identificador único da linha. | Deve ser gerado automaticamente pelo banco. Não deve ser editável pela interface. |
| `ativo` | Indica se o registro está ativo. | `1` ativo, `0` inativo. Não substitui `excluido_em`. |
| `criado_em` | Data/hora de criação do registro. | Preenchido automaticamente. Não editável. |
| `atualizado_em` | Data/hora da última alteração. | Atualizado automaticamente pelo banco ou backend. Não editável manualmente. |
| `excluido_em` | Data/hora da exclusão lógica. | Usado para ocultar registros sem apagar fisicamente. |
| `criado_por` | Usuário que criou o registro. | Preenchido pelo backend com o usuário autenticado. |
| `atualizado_por` | Usuário da última alteração. | Preenchido pelo backend. |
| `excluido_por` | Usuário que executou a exclusão lógica. | Preenchido somente quando houver exclusão. |
| `codigo` | Código interno padronizado. | Deve ser estável e usado por regras de negócio, não por texto livre. |
| `nome` | Nome legível para interface. | Pode ser exibido em telas, filtros e relatórios. |
| `descricao` | Descrição complementar. | Usado para detalhamento administrativo ou técnico. |
| `ordem` | Ordem de exibição. | Usado em selects e listagens de domínio. |
| `classe_css` | Classe visual de status. | Usado para badges, etiquetas e marcações de interface. |

---

## 5. Classificação de sensibilidade dos dados

| Classe | Exemplos | Regra de tratamento |
|---|---|---|
| Público institucional | Nome do município, tipo de desastre, número do decreto. | Pode aparecer em relatórios administrativos e painéis internos. Divulgação externa depende de autorização institucional. |
| Operacional interno | Homologação, reconhecimento, PGE, analista, status de recurso. | Acesso restrito a usuários autenticados. Edição restrita a Gestor/Admin. |
| Segurança de acesso | Senha hash, sessão, IP, user agent, tentativas de login. | Nunca expor em interface comum. Acesso técnico ou auditoria controlada. |
| Documento anexado | Decreto, parecer, ofício e outros arquivos. | Download apenas via controller autenticado. Nunca expor caminho físico direto. |
| Auditoria | Dados antes/depois, logs de login e ações críticas. | Acesso restrito. Não deve ser alterado manualmente. |
| Quantitativos humanos | Óbitos, feridos, enfermos, desabrigados, desalojados, outros afetados. | Acesso operacional. Validar com rigor, pois impacta relatórios e decisões. |

---

# PARTE I — TABELAS DE SEGURANÇA E ACESSO

---

## 6. Tabela `perfis`

### 6.1. Finalidade

Armazena os perfis oficiais de acesso do DGD. Cada usuário deve estar vinculado a exatamente um perfil.

Perfis oficiais:

| Código | Nome | Nível | Finalidade |
|---|---:|---:|---|
| `ADMIN` | Admin | 3 | Administração geral do sistema. |
| `GESTOR` | Gestor | 2 | Gestão operacional, edição e acompanhamento de desastres/decretos. |
| `OPERADOR` | Operador | 1 | Cadastro inicial e consulta operacional controlada. |

### 6.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador único do perfil. |
| `codigo` | `VARCHAR(30)` | Sim | UK | Administrador/sistema | Código interno usado nas regras de autorização. Deve ser estável. |
| `nome` | `VARCHAR(60)` | Sim | - | Administrador/sistema | Nome exibido na interface. |
| `descricao` | `TEXT` | Não | - | Administrador | Descrição da finalidade do perfil. |
| `nivel_acesso` | `TINYINT UNSIGNED` | Sim | IDX lógico | Sistema | Hierarquia numérica. Quanto maior, maior o nível de acesso. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Controla se o perfil está disponível para uso. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 6.3. Regras específicas

1. O sistema deve iniciar com os perfis `ADMIN`, `GESTOR` e `OPERADOR`.
2. O perfil de um usuário não deve ser removido se houver usuário vinculado.
3. O código do perfil deve ser usado pelo backend para decisões de permissão.
4. A interface não deve depender apenas do nome do perfil, pois nomes podem ser ajustados.

---

## 7. Tabela `permissoes`

### 7.1. Finalidade

Armazena o catálogo de permissões internas utilizadas pelo middleware de autorização.

### 7.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `SMALLINT UNSIGNED` | Auto | PK | Banco | Identificador único da permissão. |
| `codigo` | `VARCHAR(80)` | Sim | UK | Sistema | Código técnico da permissão, no padrão `modulo.acao`. |
| `modulo` | `VARCHAR(60)` | Sim | IDX | Sistema | Módulo funcional associado, como `decretos`, `usuarios`, `painel`. |
| `acao` | `VARCHAR(60)` | Sim | IDX | Sistema | Ação autorizada, como `visualizar`, `criar`, `editar`, `excluir`. |
| `descricao` | `TEXT` | Não | - | Sistema | Explicação da permissão. |
| `ativo` | `TINYINT(1)` | Sim | IDX lógico | Sistema | Indica se a permissão está ativa. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última alteração. |

### 7.3. Catálogo inicial de permissões

| Código | Módulo | Ação | Uso |
|---|---|---|---|
| `painel.visualizar` | Painel | Visualizar | Acesso ao painel inicial. |
| `decretos.visualizar` | Decretos | Visualizar | Acesso à listagem. |
| `decretos.detalhe` | Decretos | Detalhe | Acesso à tela de detalhe. |
| `decretos.criar` | Decretos | Criar | Cadastro de novo desastre. |
| `decretos.editar` | Decretos | Editar | Edição completa autorizada. |
| `decretos.excluir` | Decretos | Excluir | Exclusão lógica. |
| `decretos.editar_status_listagem` | Decretos | Editar status | Edição rápida na listagem. |
| `anexos.upload` | Anexos | Upload | Envio de documentos. |
| `anexos.excluir` | Anexos | Excluir | Exclusão lógica de anexos. |
| `usuarios.visualizar` | Usuários | Visualizar | Acesso à listagem de usuários. |
| `usuarios.criar` | Usuários | Criar | Criação de usuários. |
| `usuarios.editar` | Usuários | Editar | Edição de usuários. |
| `usuarios.excluir` | Usuários | Excluir | Exclusão lógica de usuários. |
| `senha.alterar_propria` | Senha | Alterar própria | Alteração da própria senha. |
| `auditoria.visualizar` | Auditoria | Visualizar | Consulta de registros de auditoria. |
| `dominios.administrar` | Domínios | Administrar | Administração de tabelas de domínio. |

---

## 8. Tabela `perfil_permissoes`

### 8.1. Finalidade

Relaciona perfis com permissões. É uma tabela associativa de muitos-para-muitos.

### 8.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `perfil_id` | `TINYINT UNSIGNED` | Sim | PK/FK | `perfis.id` | Perfil que receberá a permissão. |
| `permissao_id` | `SMALLINT UNSIGNED` | Sim | PK/FK | `permissoes.id` | Permissão vinculada ao perfil. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora da vinculação. |

### 8.3. Regras específicas

1. A combinação `perfil_id` + `permissao_id` deve ser única.
2. O Admin deve possuir todas as permissões.
3. O Gestor deve possuir permissões de gestão operacional, mas não necessariamente administração de domínios e usuários.
4. O Operador deve possuir permissões de cadastro inicial e consulta, sem edição posterior de campos críticos.

---

## 9. Tabela `usuarios`

### 9.1. Finalidade

Armazena os usuários autenticados do DGD.

### 9.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador único do usuário. |
| `perfil_id` | `TINYINT UNSIGNED` | Sim | FK/IDX | `perfis.id` | Perfil de acesso do usuário. |
| `nome` | `VARCHAR(150)` | Sim | IDX | Cadastro | Nome completo do usuário. |
| `email` | `VARCHAR(180)` | Sim | UK/IDX | Cadastro | E-mail usado para login. Deve ser único. |
| `cpf` | `VARCHAR(14)` | Não | UK | Cadastro | CPF do usuário, quando informado. Deve ser único. |
| `telefone` | `VARCHAR(30)` | Não | - | Cadastro | Telefone institucional ou funcional. |
| `cargo` | `VARCHAR(120)` | Não | - | Cadastro | Cargo, função ou lotação funcional. |
| `instituicao` | `VARCHAR(150)` | Sim | - | Cadastro/sistema | Instituição do usuário. Padrão: `CEDEC-PA`. |
| `senha_hash` | `VARCHAR(255)` | Sim | Sensível | Backend | Hash seguro da senha. Nunca armazenar senha em texto puro. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o usuário pode acessar o sistema. |
| `trocar_senha_proximo_acesso` | `TINYINT(1)` | Sim | - | Admin/backend | Obriga alteração de senha no próximo login. |
| `ultimo_acesso_em` | `DATETIME` | Não | - | Backend | Data/hora do último login bem-sucedido. |
| `tentativas_login_falhas` | `TINYINT UNSIGNED` | Sim | - | Backend | Contador de falhas consecutivas de login. |
| `bloqueado_ate` | `DATETIME` | Não | - | Backend | Data/hora até a qual o usuário fica bloqueado após falhas. |
| `criado_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que criou o cadastro. |
| `atualizado_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que realizou a última alteração. |
| `excluido_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que excluiu logicamente o registro. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |
| `excluido_em` | `DATETIME` | Não | IDX | Backend | Data/hora da exclusão lógica. |

### 9.3. Validações obrigatórias

| Campo | Validação |
|---|---|
| `nome` | Mínimo recomendado de 3 caracteres. Remover espaços duplicados. |
| `email` | Formato de e-mail válido; converter para minúsculas antes de comparar unicidade. |
| `cpf` | Opcional. Se informado, validar formato e unicidade. |
| `senha_hash` | Gerado por `password_hash()` no PHP. Não aceitar senha já criptografada vinda da interface. |
| `perfil_id` | Deve apontar para perfil ativo. |
| `ativo` | Somente Admin deve ativar/desativar usuários. |

### 9.4. Regras de interface

1. A tela de usuários deve exibir `nome`, `email`, `perfil`, `instituicao`, `ativo` e `último acesso`.
2. `senha_hash` nunca deve ser exibido.
3. A exclusão deve marcar `excluido_em` e `excluido_por`, sem apagar fisicamente.
4. Usuários vinculados como analistas em desastres devem permanecer preservados para histórico.

---

## 10. Tabela `usuarios_sessoes`

### 10.1. Finalidade

Controla sessões autenticadas, permitindo auditoria, encerramento e controle adicional de segurança.

### 10.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador da sessão registrada. |
| `usuario_id` | `BIGINT UNSIGNED` | Sim | FK/IDX | `usuarios.id` | Usuário autenticado. |
| `session_id_hash` | `CHAR(64)` | Sim | UK | Backend | Hash SHA-256 do identificador de sessão. Não gravar o session id bruto. |
| `ip` | `VARCHAR(45)` | Não | - | Requisição | Endereço IP IPv4 ou IPv6. |
| `user_agent` | `VARCHAR(255)` | Não | - | Requisição | Navegador/dispositivo utilizado. |
| `iniciou_em` | `DATETIME` | Auto | - | Banco/backend | Data/hora de início da sessão. |
| `expira_em` | `DATETIME` | Não | - | Backend | Data/hora prevista para expiração. |
| `encerrada_em` | `DATETIME` | Não | - | Backend | Data/hora de logout ou encerramento forçado. |
| `ativa` | `TINYINT(1)` | Sim | IDX | Backend | Indica se a sessão ainda é válida. |

### 10.3. Regras específicas

1. Sessões expiradas devem ser marcadas como inativas.
2. O backend deve regenerar o ID da sessão após login.
3. A sessão ativa deve ser invalidada no logout.

---

## 11. Tabela `login_logs`

### 11.1. Finalidade

Registra tentativas de autenticação, bem-sucedidas ou não.

### 11.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador do log de login. |
| `usuario_id` | `BIGINT UNSIGNED` | Não | FK/IDX | `usuarios.id` | Usuário identificado, quando o e-mail existir. |
| `email_informado` | `VARCHAR(180)` | Não | IDX | Login | E-mail digitado na tentativa de login. |
| `sucesso` | `TINYINT(1)` | Sim | IDX | Backend | `1` para login aceito, `0` para login recusado. |
| `motivo_falha` | `VARCHAR(120)` | Não | - | Backend | Motivo técnico ou funcional da falha. |
| `ip` | `VARCHAR(45)` | Não | - | Requisição | IP da tentativa. |
| `user_agent` | `VARCHAR(255)` | Não | - | Requisição | Navegador/dispositivo da tentativa. |
| `criado_em` | `DATETIME` | Auto | IDX | Banco | Data/hora da tentativa. |

### 11.3. Motivos de falha recomendados

| Código textual sugerido | Significado |
|---|---|
| `usuario_nao_encontrado` | E-mail não cadastrado. |
| `senha_incorreta` | Senha inválida. |
| `usuario_inativo` | Usuário desativado. |
| `usuario_bloqueado` | Usuário bloqueado temporariamente. |
| `sessao_expirada` | Tentativa com sessão inválida ou expirada. |

---

# PARTE II — TABELAS TERRITORIAIS E INSTITUCIONAIS

---

## 12. Tabela `municipios`

### 12.1. Finalidade

Armazena os municípios do Pará usados no cadastro de desastres e filtros do módulo Decretos.

### 12.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador do município. |
| `codigo_ibge` | `INT UNSIGNED` | Não | UK | Carga oficial/CEDEC-PA | Código IBGE do município, quando disponível. |
| `nome` | `VARCHAR(150)` | Sim | UK/IDX | Carga oficial/CEDEC-PA | Nome do município. |
| `uf` | `CHAR(2)` | Sim | UK/IDX | Sistema | Unidade federativa. Padrão `PA`. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o município aparece em selects/filtros. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 12.3. Regras específicas

1. A combinação `nome` + `uf` deve ser única.
2. A lista inicial deve conter os municípios do Pará validados pela CEDEC-PA.
3. O campo `codigo_ibge` deve ser tratado como dado de apoio para integração e relatórios.
4. O cadastro de desastre deve aceitar apenas municípios ativos.

---

## 13. Tabela `ubms`

### 13.1. Finalidade

Armazena as UBM/unidades atuantes vinculáveis ao cadastro do desastre.

### 13.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador da UBM. |
| `municipio_id` | `INT UNSIGNED` | Não | FK/IDX | `municipios.id` | Município de referência da UBM, quando aplicável. |
| `nome` | `VARCHAR(150)` | Sim | IDX | Cadastro/CEDEC-PA | Nome completo da unidade atuante. |
| `sigla` | `VARCHAR(40)` | Não | - | Cadastro/CEDEC-PA | Sigla ou identificação curta. |
| `descricao` | `TEXT` | Não | - | Cadastro/CEDEC-PA | Observações sobre a unidade. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se a unidade aparece no cadastro do desastre. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 13.3. Regras específicas

1. Uma UBM pode ser estadual, regional ou municipal; por isso `municipio_id` é opcional.
2. O cadastro de desastre deve listar apenas UBMs ativas.
3. Caso a UBM não exista, a criação deve ser feita por Admin/Gestor conforme regra administrativa.

---

# PARTE III — TABELAS COBRADE

---

## 14. Visão geral da classificação COBRADE

A classificação COBRADE deve ser hierárquica no DGD:

```text
Grupo → Subgrupo → Tipo → Subtipo
```

O cadastro do desastre deve gravar o menor nível aplicável, preferencialmente `cobrade_subtipo_id`. Os níveis superiores devem ser recuperados por relacionamento.

### 14.1. Regra crítica

A base COBRADE não deve ser digitada livremente pelo usuário no formulário de desastre. O usuário deve selecionar valores previamente cadastrados e validados.

---

## 15. Tabela `cobrade_grupos`

### 15.1. Finalidade

Armazena o primeiro nível da classificação COBRADE.

### 15.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador do grupo COBRADE. |
| `codigo` | `VARCHAR(20)` | Sim | UK | Carga COBRADE/PLANCON | Código do grupo. |
| `nome` | `VARCHAR(150)` | Sim | IDX | Carga COBRADE/PLANCON | Nome do grupo. |
| `descricao` | `TEXT` | Não | - | Carga COBRADE/PLANCON | Descrição do grupo. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o grupo está disponível. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

---

## 16. Tabela `cobrade_subgrupos`

### 16.1. Finalidade

Armazena o segundo nível da classificação COBRADE, subordinado a um grupo.

### 16.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador do subgrupo. |
| `grupo_id` | `INT UNSIGNED` | Sim | FK/IDX | `cobrade_grupos.id` | Grupo COBRADE ao qual o subgrupo pertence. |
| `codigo` | `VARCHAR(20)` | Sim | UK composta | Carga COBRADE/PLANCON | Código do subgrupo dentro do grupo. |
| `nome` | `VARCHAR(150)` | Sim | IDX | Carga COBRADE/PLANCON | Nome do subgrupo. |
| `descricao` | `TEXT` | Não | - | Carga COBRADE/PLANCON | Descrição complementar. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o subgrupo está disponível. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 16.3. Regras específicas

1. A combinação `grupo_id` + `codigo` deve ser única.
2. O select de subgrupo deve ser filtrado pelo grupo selecionado.

---

## 17. Tabela `cobrade_tipos`

### 17.1. Finalidade

Armazena o terceiro nível da classificação COBRADE, subordinado a um subgrupo.

### 17.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador do tipo COBRADE. |
| `subgrupo_id` | `INT UNSIGNED` | Sim | FK/IDX | `cobrade_subgrupos.id` | Subgrupo ao qual o tipo pertence. |
| `codigo` | `VARCHAR(20)` | Sim | UK composta | Carga COBRADE/PLANCON | Código do tipo dentro do subgrupo. |
| `nome` | `VARCHAR(150)` | Sim | IDX | Carga COBRADE/PLANCON | Nome do tipo. |
| `descricao` | `TEXT` | Não | - | Carga COBRADE/PLANCON | Descrição complementar. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o tipo está disponível. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 17.3. Regras específicas

1. A combinação `subgrupo_id` + `codigo` deve ser única.
2. O select de tipo deve ser filtrado pelo subgrupo selecionado.

---

## 18. Tabela `cobrade_subtipos`

### 18.1. Finalidade

Armazena o quarto nível da classificação COBRADE. É o nível preferencial a ser gravado no cadastro do desastre.

### 18.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador do subtipo COBRADE. |
| `tipo_id` | `INT UNSIGNED` | Sim | FK/IDX | `cobrade_tipos.id` | Tipo COBRADE ao qual o subtipo pertence. |
| `codigo` | `VARCHAR(30)` | Sim | UK | Carga COBRADE/PLANCON | Código completo ou identificador do subtipo. |
| `nome` | `VARCHAR(180)` | Sim | IDX | Carga COBRADE/PLANCON | Nome do subtipo. |
| `descricao` | `TEXT` | Não | - | Carga COBRADE/PLANCON | Descrição do subtipo, usada em detalhe e relatório. |
| `simbologia` | `VARCHAR(255)` | Não | - | Carga COBRADE/PLANCON | Referência textual, caminho ou identificador da simbologia usada no PLANCON/COBRADE. |
| `origem` | `VARCHAR(120)` | Não | - | Sistema/carga | Origem da base importada. Padrão sugerido: `PLANCON/COBRADE`. |
| `versao` | `VARCHAR(40)` | Não | - | Sistema/carga | Versão da base de classificação utilizada. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o subtipo aparece no cadastro de desastre. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 18.3. Regras específicas

1. `codigo` deve ser único.
2. O cadastro do desastre deve armazenar `cobrade_subtipo_id`.
3. O sistema deve carregar automaticamente grupo, subgrupo, tipo, descrição e simbologia a partir do subtipo selecionado.
4. Subtipos inativos não devem aparecer em novos cadastros, mas devem permanecer válidos para registros históricos.

---

# PARTE IV — TABELAS DE DOMÍNIO E STATUS

---

## 19. Tabela `tipos_decreto`

### 19.1. Finalidade

Armazena os tipos de decreto utilizados no cadastro do desastre.

### 19.2. Valores iniciais

| Código | Nome | Prazo padrão |
|---|---|---:|
| `SITUACAO_EMERGENCIA` | Situação de Emergência | 180 dias |
| `ESTADO_CALAMIDADE_PUBLICA` | Estado de Calamidade Pública | 180 dias |

### 19.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador do tipo de decreto. |
| `codigo` | `VARCHAR(40)` | Sim | UK | Sistema/Admin | Código interno do tipo. |
| `nome` | `VARCHAR(120)` | Sim | - | Sistema/Admin | Nome exibido no formulário e listagem. |
| `descricao` | `TEXT` | Não | - | Admin | Descrição do tipo. |
| `prazo_padrao_dias` | `SMALLINT UNSIGNED` | Não | - | Admin | Prazo padrão informativo ou usado em regra futura. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se aparece em selects. |
| `ordem` | `TINYINT UNSIGNED` | Sim | IDX | Admin | Ordem de exibição. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

---

## 20. Tabela `status_homologacao`

### 20.1. Finalidade

Armazena os status da homologação estadual do decreto municipal.

### 20.2. Valores iniciais

| Código | Nome | Ordem |
|---|---|---:|
| `NAO_REGISTRADO` | Não registrado | 1 |
| `NAO_SOLICITADO` | Não solicitado | 2 |
| `SOLICITADO` | Solicitado | 3 |
| `PENDENTE_DESPACHO` | Pendente - despacho | 4 |
| `PENDENTE_PARECER` | Pendente - parecer | 5 |
| `EM_ANALISE_DGD` | Em análise DGD | 6 |
| `ENVIADO_PGE` | Enviado PGE | 7 |
| `HOMOLOGADO` | Homologado | 8 |
| `NAO_HOMOLOGADO` | Não homologado | 9 |

### 20.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador do status. |
| `codigo` | `VARCHAR(50)` | Sim | UK | Sistema/Admin | Código usado pelas regras de negócio. |
| `nome` | `VARCHAR(120)` | Sim | - | Sistema/Admin | Nome exibido na interface. |
| `descricao` | `TEXT` | Não | - | Admin | Explicação do status. |
| `classe_css` | `VARCHAR(60)` | Não | - | Frontend/Admin | Classe visual para badge ou etiqueta. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se status aparece em edição. |
| `ordem` | `TINYINT UNSIGNED` | Sim | IDX | Admin | Ordem de exibição. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 20.4. Regras específicas

1. O status `HOMOLOGADO` altera o cálculo do status de prazo PGE para `APROVADO` na view.
2. Alterações neste campo devem gerar registro em `desastre_historico_status`.
3. Operador não deve editar homologação após gravação inicial, conforme matriz de permissões.

---

## 21. Tabela `status_reconhecimento`

### 21.1. Finalidade

Armazena os status do reconhecimento federal/registro operacional associado ao desastre.

### 21.2. Valores iniciais

| Código | Nome | Ordem |
|---|---|---:|
| `NAO_REGISTRADO` | Não registrado | 1 |
| `SOLICITADO` | Solicitado | 2 |
| `AGUARDANDO_ANALISE` | Aguardando análise | 3 |
| `EM_ANALISE_SEDEC` | Em análise SEDEC | 4 |
| `ENVIADO_RECONHECIMENTO` | Enviado para reconhecimento | 5 |
| `AGUARDANDO_AJUSTE_MUNICIPIO` | Aguardando ajuste município | 6 |
| `REGISTRADO` | Registrado | 7 |
| `RECONHECIDO` | Reconhecido | 8 |
| `NAO_RECONHECIDO` | Não reconhecido | 9 |

### 21.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador do status. |
| `codigo` | `VARCHAR(60)` | Sim | UK | Sistema/Admin | Código usado internamente. |
| `nome` | `VARCHAR(140)` | Sim | - | Sistema/Admin | Nome visível ao usuário. |
| `descricao` | `TEXT` | Não | - | Admin | Descrição funcional. |
| `classe_css` | `VARCHAR(60)` | Não | - | Frontend/Admin | Classe visual para exibição. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se status está disponível para seleção. |
| `ordem` | `TINYINT UNSIGNED` | Sim | IDX | Admin | Ordem no select. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 21.4. Regras específicas

1. Alterações neste campo devem gerar histórico.
2. Status inativos devem continuar válidos para registros antigos.
3. O campo deve ser editável na listagem apenas por Gestor/Admin.

---

## 22. Tabela `status_recurso`

### 22.1. Finalidade

Armazena status reutilizados para recursos de ação de resposta e recursos de ação de reconstrução.

### 22.2. Valores iniciais

| Código | Nome | Ordem |
|---|---|---:|
| `NAO_REGISTRADO` | Não registrado | 1 |
| `NAO_SOLICITADO` | Não solicitado | 2 |
| `SOLICITADO` | Solicitado | 3 |
| `AGUARDANDO_AJUSTES` | Aguardando ajustes | 4 |
| `EM_ANALISE_SEDEC` | Em análise SEDEC | 5 |
| `PLANO_APROVADO` | Plano aprovado | 6 |
| `RECURSO_DEFERIDO` | Recurso deferido | 7 |
| `RECURSO_INDEFERIDO` | Recurso indeferido | 8 |
| `REGISTRO_REVISAO` | Registro de revisão | 9 |
| `EMPENHO` | Empenho | 10 |

### 22.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador do status. |
| `codigo` | `VARCHAR(60)` | Sim | UK | Sistema/Admin | Código interno do status. |
| `nome` | `VARCHAR(140)` | Sim | - | Sistema/Admin | Nome exibido na interface. |
| `descricao` | `TEXT` | Não | - | Admin | Descrição funcional. |
| `classe_css` | `VARCHAR(60)` | Não | - | Frontend/Admin | Classe visual para status. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se pode ser selecionado. |
| `ordem` | `TINYINT UNSIGNED` | Sim | IDX | Admin | Ordem de exibição. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 22.4. Regras específicas

1. A mesma tabela alimenta `recurso_resposta_status_id` e `recurso_reconstrucao_status_id`.
2. Alterações nos dois campos de recurso devem gerar histórico.
3. O nome do status deve ser interpretado conforme o contexto: resposta ou reconstrução.

---

## 23. Tabela `status_envio_pge`

### 23.1. Finalidade

Armazena o status administrativo/editável do envio do processo à PGE.

### 23.2. Valores iniciais

| Código | Nome | Ordem |
|---|---|---:|
| `NAO_REGISTRADO` | Não registrado | 1 |
| `NAO_ENVIADO` | Não enviado | 2 |
| `EM_PREPARACAO` | Em preparação | 3 |
| `ENVIADO_PGE` | Enviado à PGE | 4 |
| `RETORNADO_AJUSTE` | Retornado para ajuste | 5 |
| `CONCLUIDO` | Concluído | 6 |

### 23.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador do status. |
| `codigo` | `VARCHAR(60)` | Sim | UK | Sistema/Admin | Código interno do status administrativo. |
| `nome` | `VARCHAR(140)` | Sim | - | Sistema/Admin | Nome exibido na listagem e formulário. |
| `descricao` | `TEXT` | Não | - | Admin | Descrição funcional. |
| `classe_css` | `VARCHAR(60)` | Não | - | Frontend/Admin | Classe visual para badge. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o status aparece para seleção. |
| `ordem` | `TINYINT UNSIGNED` | Sim | IDX | Admin | Ordem no select. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 23.4. Regras específicas

1. Este campo é diferente de `status_prazo_pge_calculado`.
2. `status_envio_pge_id` é administrativo e pode ser editado por usuário autorizado.
3. `status_prazo_pge_calculado` é derivado por regra e não deve ser editado manualmente.

---

## 24. Tabela `tipos_anexo`

### 24.1. Finalidade

Classifica os documentos anexados ao desastre.

### 24.2. Valores iniciais

| Código | Nome | Obrigatório inicial |
|---|---|---:|
| `DECRETO_MUNICIPAL` | Decreto municipal | Sim |
| `OFICIO_HOMOLOGACAO` | Ofício de homologação | Não |
| `PARECER_ESTADUAL` | Parecer estadual | Não |
| `PARECER_MUNICIPAL` | Parecer municipal | Não |
| `OUTROS_DOCUMENTOS` | Outros documentos | Não |

### 24.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `TINYINT UNSIGNED` | Auto | PK | Banco | Identificador do tipo de anexo. |
| `codigo` | `VARCHAR(60)` | Sim | UK | Sistema/Admin | Código interno do tipo. |
| `nome` | `VARCHAR(140)` | Sim | - | Sistema/Admin | Nome visível ao usuário. |
| `descricao` | `TEXT` | Não | - | Admin | Explicação do tipo de documento. |
| `obrigatorio` | `TINYINT(1)` | Sim | - | Admin | Indica se o anexo é obrigatório em determinado fluxo. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se aparece na tela de upload. |
| `ordem` | `TINYINT UNSIGNED` | Sim | IDX | Admin | Ordem de exibição. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

---

# PARTE V — PROTOCOLO DGD

---

## 25. Tabela `sequencias_protocolos`

### 25.1. Finalidade

Controla o número sequencial anual usado para gerar o protocolo DGD.

### 25.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `ano` | `SMALLINT UNSIGNED` | Sim | PK | Backend | Ano base do protocolo, normalmente extraído de `data_desastre`. |
| `ultimo_sequencial` | `INT UNSIGNED` | Sim | - | Backend | Último número usado naquele ano. Deve ser incrementado em transação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização da sequência. |

### 25.3. Regra de geração do protocolo

Formato recomendado:

```text
DGD-{ANO}-{SEQUENCIAL}-{DATA_DESASTRE}-{MUNICIPIO_NORMALIZADO}
```

Exemplo:

```text
DGD-2026-000001-20260115-BELEM
```

| Componente | Origem | Regra |
|---|---|---|
| `DGD` | Sistema | Prefixo fixo. |
| `ANO` | `data_desastre` | Ano com quatro dígitos. |
| `SEQUENCIAL` | `sequencias_protocolos.ultimo_sequencial + 1` | Preenchido com zeros à esquerda. |
| `DATA_DESASTRE` | `data_desastre` | Formato `AAAAMMDD`. |
| `MUNICIPIO_NORMALIZADO` | `municipios.nome` | Caixa alta, sem acentos e com espaços tratados. |

### 25.4. Regras específicas

1. O protocolo deve ser gerado pelo backend, nunca digitado manualmente.
2. A geração deve ocorrer dentro de transação.
3. O backend deve prevenir duplicidade em cadastros simultâneos.
4. O ano do protocolo deve seguir a data do desastre, não necessariamente a data de cadastro.

---

# PARTE VI — TABELA PRINCIPAL DE DESASTRES/DECRETOS

---

## 26. Tabela `desastres`

### 26.1. Finalidade

É a tabela principal do DGD. Armazena o cadastro do desastre, os dados do decreto municipal, homologação estadual, reconhecimento, PGE, recursos, danos humanos, total automático de afetados e metadados de controle.

Embora a página se chame **Decretos**, o registro principal é um **desastre**. O decreto municipal, a homologação e os processos relacionados são atributos desse desastre.

### 26.2. Dicionário de campos — identificação e protocolo

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador interno do desastre. |
| `protocolo_dgd` | `VARCHAR(120)` | Auto | UK | Backend | Protocolo oficial interno do DGD. Gerado automaticamente. Não editável. |
| `protocolo_ano` | `SMALLINT UNSIGNED` | Auto | UK composta | Backend | Ano usado no sequencial do protocolo. Derivado de `data_desastre`. |
| `protocolo_sequencial` | `INT UNSIGNED` | Auto | UK composta | Backend | Sequencial anual do protocolo. Gerado via `sequencias_protocolos`. |

### 26.3. Dicionário de campos — localização e classificação

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `municipio_id` | `INT UNSIGNED` | Sim | FK/IDX | `municipios.id` | Município onde ocorreu o desastre. Deve estar ativo no cadastro. |
| `ubm_id` | `INT UNSIGNED` | Não | FK/IDX | `ubms.id` | UBM atuante no evento. Pode ser nulo quando não houver unidade definida. |
| `tipo_decreto_id` | `TINYINT UNSIGNED` | Sim | FK/IDX | `tipos_decreto.id` | Tipo de decreto: Situação de Emergência ou Estado de Calamidade Pública. |
| `cobrade_subtipo_id` | `INT UNSIGNED` | Sim | FK/IDX | `cobrade_subtipos.id` | Subtipo COBRADE selecionado. A partir dele são obtidos grupo, subgrupo, tipo, descrição e simbologia. |

### 26.4. Dicionário de campos — datas e protocolos externos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `data_desastre` | `DATE` | Sim | IDX | Formulário | Data de ocorrência do desastre. Deve ser igual ou anterior à data atual. |
| `protocolo_s2id` | `VARCHAR(80)` | Não | IDX | Formulário | Número/protocolo do S2ID, quando existente. Não deve ser inventado pelo sistema. |

### 26.5. Dicionário de campos — decreto municipal

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `numero_decreto_municipal` | `VARCHAR(80)` | Não | - | Formulário | Número do decreto municipal. Recomendável para conclusão do cadastro. |
| `data_decreto_municipal` | `DATE` | Condicional | IDX | Formulário | Data do decreto municipal. Necessária para cálculo de prazo PGE. |

### 26.6. Dicionário de campos — homologação estadual

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `numero_decreto_homologacao_estadual` | `VARCHAR(80)` | Não | Histórico | Formulário | Número do decreto estadual de homologação, quando houver. Alteração deve gerar histórico. |
| `data_decreto_homologacao` | `DATE` | Condicional | Histórico | Formulário | Data do decreto estadual de homologação. Obrigatória quando homologação for `HOMOLOGADO`, salvo regra administrativa diversa. |
| `homologacao_status_id` | `TINYINT UNSIGNED` | Sim | FK/IDX | `status_homologacao.id` | Status da homologação. Padrão: `NAO_REGISTRADO`. Campo crítico. |

### 26.7. Dicionário de campos — reconhecimento

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `reconhecimento_status_id` | `TINYINT UNSIGNED` | Sim | FK/IDX | `status_reconhecimento.id` | Status do reconhecimento. Padrão: `NAO_REGISTRADO`. Campo crítico. |

### 26.8. Dicionário de campos — PGE

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `protocolo_pae_pge` | `VARCHAR(100)` | Não | IDX | Formulário | Protocolo PAE/PGE, quando houver. |
| `data_envio_pge` | `DATE` | Condicional | Histórico | Formulário | Data de envio para PGE. Necessária para cálculo real da duração PGE. |
| `status_envio_pge_id` | `TINYINT UNSIGNED` | Sim | FK/IDX | `status_envio_pge.id` | Status administrativo do envio à PGE. Campo editável por Gestor/Admin. |

### 26.9. Dicionário de campos — analista

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `analista_id` | `BIGINT UNSIGNED` | Não | FK/IDX | `usuarios.id` | Analista responsável pelo acompanhamento. Deve ser usuário ativo com perfil Gestor, salvo decisão administrativa. |

### 26.10. Dicionário de campos — recursos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `recurso_resposta_status_id` | `TINYINT UNSIGNED` | Sim | FK | `status_recurso.id` | Status dos recursos de ação de resposta. Padrão: `NAO_REGISTRADO`. |
| `recurso_reconstrucao_status_id` | `TINYINT UNSIGNED` | Sim | FK | `status_recurso.id` | Status dos recursos de ação de reconstrução. Padrão: `NAO_REGISTRADO`. |

### 26.11. Dicionário de campos — danos humanos e afetados

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `numero_obitos` | `INT UNSIGNED` | Sim | - | Formulário | Quantidade de óbitos. Padrão 0. Não aceita número negativo. |
| `numero_feridos` | `INT UNSIGNED` | Sim | - | Formulário | Quantidade de feridos. Padrão 0. Não aceita número negativo. |
| `numero_enfermos` | `INT UNSIGNED` | Sim | - | Formulário | Quantidade de enfermos. Padrão 0. Não aceita número negativo. |
| `numero_desabrigados` | `INT UNSIGNED` | Sim | - | Formulário | Pessoas que perderam moradia e precisam de abrigo público ou institucional. Padrão 0. |
| `numero_desalojados` | `INT UNSIGNED` | Sim | - | Formulário | Pessoas que deixaram temporariamente suas residências e não estão em abrigo público. Padrão 0. |
| `numero_outros_afetados` | `INT UNSIGNED` | Sim | - | Formulário | Demais pessoas afetadas não enquadradas nos campos anteriores. Padrão 0. |
| `total_afetados` | `INT UNSIGNED` | Auto | Calculado | Banco/backend | Soma automática dos campos de afetados. Não editável. |

### 26.12. Fórmula do total de afetados

```text
total_afetados = numero_obitos
                + numero_feridos
                + numero_enfermos
                + numero_desabrigados
                + numero_desalojados
                + numero_outros_afetados
```

### 26.13. Dicionário de campos — observações e controle

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `observacoes` | `TEXT` | Não | - | Formulário | Observações gerais sobre o desastre, decreto ou processo. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o desastre está ativo operacionalmente. |
| `criado_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que criou o registro. |
| `atualizado_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que realizou a última atualização. |
| `excluido_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que realizou a exclusão lógica. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |
| `excluido_em` | `DATETIME` | Não | IDX | Backend | Data/hora da exclusão lógica. Registros excluídos não aparecem na listagem padrão. |

### 26.14. Campos automáticos e não editáveis

| Campo | Motivo |
|---|---|
| `id` | Identificador interno. |
| `protocolo_dgd` | Deve ser gerado pelo sistema para preservar padrão e unicidade. |
| `protocolo_ano` | Derivado da data do desastre. |
| `protocolo_sequencial` | Controlado por sequência anual. |
| `total_afetados` | Calculado automaticamente. |
| `criado_por` | Preenchido pela sessão autenticada. |
| `atualizado_por` | Preenchido pela sessão autenticada. |
| `excluido_por` | Preenchido somente na exclusão lógica. |
| `criado_em` | Preenchido pelo banco. |
| `atualizado_em` | Preenchido pelo banco/backend. |
| `excluido_em` | Preenchido pelo backend na exclusão lógica. |

### 26.15. Validações obrigatórias na gravação

| Regra | Descrição |
|---|---|
| Município obrigatório | `municipio_id` deve apontar para município ativo. |
| Tipo de decreto obrigatório | `tipo_decreto_id` deve apontar para tipo ativo. |
| COBRADE obrigatório | `cobrade_subtipo_id` deve apontar para subtipo ativo. |
| Data do desastre obrigatória | `data_desastre` não pode ser nula. |
| Data futura bloqueada | `data_desastre` não deve ser maior que a data corrente. |
| Quantitativos não negativos | Campos numéricos de afetados não aceitam valores negativos. |
| Total automático | `total_afetados` não deve ser enviado pelo formulário como valor editável. |
| Homologação válida | `homologacao_status_id` deve existir e estar ativo para seleção. |
| Reconhecimento válido | `reconhecimento_status_id` deve existir e estar ativo para seleção. |
| Status PGE válido | `status_envio_pge_id` deve existir e estar ativo para seleção. |
| Recurso válido | Os dois campos de recurso devem apontar para `status_recurso`. |
| Analista válido | Se informado, deve apontar para usuário ativo e preferencialmente perfil Gestor. |

### 26.16. Validações recomendadas por fluxo

| Situação | Regra recomendada |
|---|---|
| Homologação = `HOMOLOGADO` | Exigir `numero_decreto_homologacao_estadual` e `data_decreto_homologacao`, salvo exceção justificada. |
| Status PGE = `ENVIADO_PGE` | Exigir `data_envio_pge` e, se aplicável, `protocolo_pae_pge`. |
| Existe decreto municipal | Recomendar preenchimento de `numero_decreto_municipal` e `data_decreto_municipal`. |
| Cálculo de prazo PGE | Depende de `data_decreto_municipal` e `data_envio_pge` ou data corrente. |
| Anexos obrigatórios | Para conclusão processual, exigir ao menos tipo `DECRETO_MUNICIPAL`. |

### 26.17. Campos críticos que exigem histórico

Alterações nos seguintes campos devem gerar registro em `desastre_historico_status`:

1. `homologacao_status_id`.
2. `reconhecimento_status_id`.
3. `status_envio_pge_id`.
4. `recurso_resposta_status_id`.
5. `recurso_reconstrucao_status_id`.
6. `analista_id`.
7. `data_envio_pge`.
8. `numero_decreto_homologacao_estadual`.
9. `data_decreto_homologacao`.

---

# PARTE VII — ANEXOS

---

## 27. Tabela `desastre_anexos`

### 27.1. Finalidade

Armazena metadados dos arquivos anexados ao cadastro do desastre. Os arquivos físicos não devem ser salvos como BLOB no banco.

### 27.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador do anexo. |
| `desastre_id` | `BIGINT UNSIGNED` | Sim | FK/IDX | `desastres.id` | Desastre ao qual o arquivo pertence. |
| `tipo_anexo_id` | `TINYINT UNSIGNED` | Sim | FK/IDX | `tipos_anexo.id` | Tipo documental do anexo. |
| `nome_original` | `VARCHAR(255)` | Sim | - | Upload | Nome original enviado pelo usuário. Deve ser sanitizado antes de exibir. |
| `nome_arquivo` | `VARCHAR(255)` | Sim | - | Backend | Nome físico gerado pelo sistema para evitar conflito e execução indevida. |
| `caminho_arquivo` | `VARCHAR(500)` | Sim | Sensível | Backend | Caminho interno do arquivo. Não deve ser exposto diretamente ao usuário. |
| `mime_type` | `VARCHAR(120)` | Sim | - | Backend/upload | Tipo MIME identificado. Deve ser validado. |
| `extensao` | `VARCHAR(20)` | Sim | - | Backend/upload | Extensão do arquivo em minúsculas. |
| `tamanho_bytes` | `BIGINT UNSIGNED` | Sim | - | Backend/upload | Tamanho do arquivo em bytes. Deve respeitar limite configurado. |
| `hash_sha256` | `CHAR(64)` | Não | Integridade | Backend | Hash SHA-256 do arquivo para controle de integridade. |
| `descricao` | `TEXT` | Não | - | Formulário | Observação opcional sobre o documento. |
| `enviado_por` | `BIGINT UNSIGNED` | Não | FK/IDX | `usuarios.id` | Usuário que realizou o upload. |
| `enviado_em` | `DATETIME` | Auto | IDX | Banco/backend | Data/hora do upload. |
| `ativo` | `TINYINT(1)` | Sim | IDX | Sistema | Indica se o anexo está disponível. |
| `excluido_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que excluiu logicamente o anexo. |
| `excluido_em` | `DATETIME` | Não | - | Backend | Data/hora da exclusão lógica. |

### 27.3. Extensões recomendadas

```text
pdf, doc, docx, jpg, jpeg, png
```

### 27.4. Validações obrigatórias

| Regra | Descrição |
|---|---|
| Tipo obrigatório | Todo anexo deve possuir `tipo_anexo_id`. |
| Vínculo obrigatório | Todo anexo deve estar associado a um `desastre_id`. |
| Nome físico controlado | O sistema deve renomear o arquivo no servidor. |
| MIME type validado | Não confiar apenas na extensão. |
| Caminho protegido | O caminho do arquivo não deve estar diretamente acessível pela URL pública. |
| Download autenticado | O download deve passar por controller com permissão. |
| Exclusão lógica | Marcar `ativo = 0`, `excluido_em` e `excluido_por`. |
| Limite de tamanho | Usar `upload_tamanho_maximo_mb` da tabela de configurações. |

---

# PARTE VIII — HISTÓRICO E AUDITORIA

---

## 28. Tabela `desastre_historico_status`

### 28.1. Finalidade

Registra mudanças em campos críticos do cadastro de desastre, principalmente status e campos processuais.

### 28.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador do histórico. |
| `desastre_id` | `BIGINT UNSIGNED` | Sim | FK/IDX | `desastres.id` | Desastre afetado pela alteração. |
| `campo_alterado` | `VARCHAR(80)` | Sim | IDX | Backend | Nome técnico do campo alterado. |
| `status_anterior_codigo` | `VARCHAR(80)` | Não | - | Backend | Código anterior, quando aplicável. |
| `status_anterior_nome` | `VARCHAR(140)` | Não | - | Backend | Nome anterior exibível, quando aplicável. |
| `status_novo_codigo` | `VARCHAR(80)` | Não | - | Backend | Código novo, quando aplicável. |
| `status_novo_nome` | `VARCHAR(140)` | Não | - | Backend | Nome novo exibível, quando aplicável. |
| `observacao` | `TEXT` | Não | - | Formulário/backend | Justificativa ou observação da alteração. |
| `alterado_por` | `BIGINT UNSIGNED` | Não | FK/IDX | `usuarios.id` | Usuário responsável pela alteração. |
| `alterado_em` | `DATETIME` | Auto | IDX | Banco/backend | Data/hora da alteração. |

### 28.3. Regras específicas

1. O histórico deve ser criado automaticamente pelo Service responsável, não manualmente pela tela.
2. Toda alteração de status crítico deve registrar valor anterior e valor novo.
3. Alterações de analista e data de PGE também devem ser registradas.
4. Histórico não deve ser apagado em exclusão lógica do desastre.

---

## 29. Tabela `auditoria_logs`

### 29.1. Finalidade

Registra eventos críticos gerais do sistema, incluindo criação, edição, exclusão, upload, login administrativo, alterações de usuário e alterações de domínio.

### 29.2. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Auto | PK | Banco | Identificador do evento de auditoria. |
| `usuario_id` | `BIGINT UNSIGNED` | Não | FK/IDX | `usuarios.id` | Usuário que executou a ação. Pode ser nulo em evento anônimo/sistema. |
| `entidade` | `VARCHAR(80)` | Sim | IDX composta | Backend | Nome da entidade afetada: `desastres`, `usuarios`, `anexos`, etc. |
| `entidade_id` | `BIGINT UNSIGNED` | Não | IDX composta | Backend | ID do registro afetado. |
| `acao` | `VARCHAR(80)` | Sim | IDX | Backend | Ação realizada: `criar`, `editar`, `excluir`, `upload`, `login`, etc. |
| `descricao` | `TEXT` | Não | - | Backend | Descrição resumida do evento. |
| `dados_antes` | `JSON` ou `LONGTEXT` | Não | - | Backend | Snapshot dos dados antes da alteração. |
| `dados_depois` | `JSON` ou `LONGTEXT` | Não | - | Backend | Snapshot dos dados depois da alteração. |
| `ip` | `VARCHAR(45)` | Não | - | Requisição | IP do usuário. |
| `user_agent` | `VARCHAR(255)` | Não | - | Requisição | Navegador/dispositivo. |
| `criado_em` | `DATETIME` | Auto | IDX | Banco | Data/hora do evento. |

### 29.3. Ações recomendadas para auditoria

| Ação | Quando registrar |
|---|---|
| `criar` | Criação de desastre, usuário, domínio ou configuração. |
| `editar` | Alteração de cadastro relevante. |
| `excluir` | Exclusão lógica. |
| `upload` | Inclusão de anexo. |
| `excluir_anexo` | Exclusão lógica de anexo. |
| `alterar_senha` | Alteração de senha pelo próprio usuário. |
| `resetar_senha` | Redefinição administrativa de senha. |
| `login_sucesso` | Login aceito, se desejado em auditoria geral. |
| `login_falha` | Falha de login, preferencialmente também em `login_logs`. |
| `editar_status` | Alteração rápida de status na listagem. |

---

# PARTE IX — CONFIGURAÇÕES DO SISTEMA

---

## 30. Tabela `configuracoes_sistema`

### 30.1. Finalidade

Armazena parâmetros administrativos usados pela aplicação, evitando alteração de código para ajustes simples.

### 30.2. Configurações iniciais

| Chave | Valor | Tipo | Finalidade |
|---|---:|---|---|
| `prazo_pge_dias` | `7` | integer | Prazo operacional para cálculo do status de prazo PGE. |
| `paginacao_padrao` | `20` | integer | Quantidade padrão e máxima de registros por página. |
| `upload_tamanho_maximo_mb` | `20` | integer | Tamanho máximo permitido por arquivo anexado. |
| `sistema_nome` | `DGD` | string | Nome curto do sistema. |
| `sistema_orgao` | `CEDEC-PA` | string | Órgão gestor do sistema. |

### 30.3. Dicionário de campos

| Campo | Tipo | Obrig. | Chave | Origem | Regra/Descrição |
|---|---|---:|---|---|---|
| `id` | `INT UNSIGNED` | Auto | PK | Banco | Identificador da configuração. |
| `chave` | `VARCHAR(100)` | Sim | UK/IDX | Sistema/Admin | Nome técnico da configuração. Deve ser único. |
| `valor` | `TEXT` | Sim | - | Admin | Valor armazenado como texto e convertido conforme `tipo_dado`. |
| `tipo_dado` | `VARCHAR(30)` | Sim | - | Sistema/Admin | Tipo esperado: `string`, `integer`, `boolean`, `decimal`, `json`. |
| `descricao` | `TEXT` | Não | - | Admin | Explicação da configuração. |
| `atualizado_por` | `BIGINT UNSIGNED` | Não | FK | `usuarios.id` | Usuário que realizou a última alteração. |
| `criado_em` | `DATETIME` | Auto | - | Banco | Data/hora de criação. |
| `atualizado_em` | `DATETIME` | Auto | - | Banco | Data/hora da última atualização. |

### 30.4. Regras específicas

1. A aplicação deve carregar configurações por `chave`, não por `id`.
2. Alterações devem gerar auditoria.
3. Configurações sensíveis não devem ser usadas para armazenar senhas ou chaves secretas; isso deve ficar em `.env`.

---

# PARTE X — VIEWS OPERACIONAIS

---

## 31. View `vw_decretos_listagem`

### 31.1. Finalidade

Alimenta a listagem do módulo **Decretos**, já consolidando dados de município, UBM, tipo de decreto, COBRADE, homologação, reconhecimento, PGE, analista, recursos e quantitativos.

### 31.2. Dicionário de campos da view

| Campo | Tipo lógico | Origem | Regra/Descrição |
|---|---|---|---|
| `id` | Número | `desastres.id` | Identificador do desastre para ações de detalhe, edição e exclusão. |
| `protocolo_ano` | Número | `desastres.protocolo_ano` | Ano do protocolo. Usado para ordenação sequencial. |
| `protocolo_sequencial` | Número | `desastres.protocolo_sequencial` | Sequencial anual. Usado para ordenação. |
| `protocolo_dgd` | Texto | `desastres.protocolo_dgd` | Protocolo DGD exibido na listagem. |
| `municipio` | Texto | `municipios.nome` | Município do desastre. |
| `ubm_atuante` | Texto | `ubms.nome` | UBM vinculada, quando houver. |
| `tipo_decreto` | Texto | `tipos_decreto.nome` | Tipo de decreto. |
| `cobrade_grupo` | Texto | `cobrade_grupos.nome` | Grupo COBRADE. |
| `cobrade_subgrupo` | Texto | `cobrade_subgrupos.nome` | Subgrupo COBRADE. |
| `cobrade_tipo` | Texto | `cobrade_tipos.nome` | Tipo COBRADE. |
| `cobrade_subtipo` | Texto | `cobrade_subtipos.nome` | Subtipo COBRADE, usado como tipo de desastre na listagem. |
| `cobrade_codigo` | Texto | `cobrade_subtipos.codigo` | Código COBRADE. |
| `cobrade_descricao` | Texto | `cobrade_subtipos.descricao` | Descrição do subtipo. |
| `cobrade_simbologia` | Texto | `cobrade_subtipos.simbologia` | Simbologia associada. |
| `data_desastre` | Data | `desastres.data_desastre` | Data de ocorrência do desastre. |
| `protocolo_s2id` | Texto | `desastres.protocolo_s2id` | Protocolo S2ID, quando informado. |
| `numero_decreto_municipal` | Texto | `desastres.numero_decreto_municipal` | Número do decreto municipal. |
| `data_decreto_municipal` | Data | `desastres.data_decreto_municipal` | Data do decreto municipal. |
| `numero_decreto_homologacao_estadual` | Texto | `desastres.numero_decreto_homologacao_estadual` | Número do decreto estadual de homologação. |
| `data_decreto_homologacao` | Data | `desastres.data_decreto_homologacao` | Data do decreto estadual de homologação. |
| `homologacao` | Texto | `status_homologacao.nome` | Nome do status de homologação. |
| `homologacao_codigo` | Texto | `status_homologacao.codigo` | Código usado pela regra de prazo PGE. |
| `reconhecimento` | Texto | `status_reconhecimento.nome` | Nome do status de reconhecimento. |
| `reconhecimento_codigo` | Texto | `status_reconhecimento.codigo` | Código técnico do reconhecimento. |
| `protocolo_pae_pge` | Texto | `desastres.protocolo_pae_pge` | Protocolo PAE/PGE. |
| `data_envio_pge` | Data | `desastres.data_envio_pge` | Data de envio à PGE. |
| `status_envio_pge` | Texto | `status_envio_pge.nome` | Status administrativo/editável de envio à PGE. |
| `status_envio_pge_codigo` | Texto | `status_envio_pge.codigo` | Código do status administrativo PGE. |
| `analista` | Texto | `usuarios.nome` | Nome do analista responsável. |
| `recurso_resposta` | Texto | `status_recurso.nome` | Status do recurso de resposta. |
| `recurso_reconstrucao` | Texto | `status_recurso.nome` | Status do recurso de reconstrução. |
| `numero_obitos` | Número | `desastres.numero_obitos` | Quantidade de óbitos. |
| `numero_feridos` | Número | `desastres.numero_feridos` | Quantidade de feridos. |
| `numero_enfermos` | Número | `desastres.numero_enfermos` | Quantidade de enfermos. |
| `numero_desabrigados` | Número | `desastres.numero_desabrigados` | Quantidade de desabrigados. |
| `numero_desalojados` | Número | `desastres.numero_desalojados` | Quantidade de desalojados. |
| `numero_outros_afetados` | Número | `desastres.numero_outros_afetados` | Outros afetados. |
| `total_afetados` | Número | `desastres.total_afetados` | Total automático de afetados. |
| `total_dias_decreto` | Número calculado | `DATEDIFF(CURRENT_DATE, data_decreto_municipal)` | Total de dias desde o decreto municipal até a data corrente. Nulo se não houver data. |
| `duracao_pge_dias` | Número calculado | `DATEDIFF(COALESCE(data_envio_pge, CURRENT_DATE), data_decreto_municipal)` | Total de dias considerados para prazo PGE. |
| `status_prazo_pge_calculado` | Texto calculado | `CASE` da view | Resultado automático: `APROVADO`, `NO PRAZO`, `PENDENTE`, `SEM DATA` ou `NÃO INICIADO`. |
| `ativo` | Booleano | `desastres.ativo` | Indica se o registro está ativo. |
| `criado_em` | Data/hora | `desastres.criado_em` | Data/hora de criação do desastre. |
| `atualizado_em` | Data/hora | `desastres.atualizado_em` | Data/hora da última alteração. |

### 31.3. Regra do status de prazo PGE calculado

```text
SE homologacao_codigo = 'HOMOLOGADO'
    ENTÃO 'APROVADO'
SENÃO SE data_decreto_municipal IS NULL
    ENTÃO 'SEM DATA'
SENÃO SE duracao_pge_dias > 0 E duracao_pge_dias <= 7
    ENTÃO 'NO PRAZO'
SENÃO SE duracao_pge_dias > 7
    ENTÃO 'PENDENTE'
SENÃO
    'NÃO INICIADO'
```

### 31.4. Campos editáveis diretamente na listagem

| Campo exibido | Campo real na tabela | Quem pode editar |
|---|---|---|
| Homologação | `desastres.homologacao_status_id` | Gestor/Admin |
| Reconhecimento | `desastres.reconhecimento_status_id` | Gestor/Admin |
| Status de envio à PGE | `desastres.status_envio_pge_id` | Gestor/Admin |

### 31.5. Campos não editáveis diretamente na listagem

| Campo | Motivo |
|---|---|
| `protocolo_dgd` | Gerado automaticamente. |
| `total_afetados` | Calculado. |
| `total_dias_decreto` | Calculado. |
| `duracao_pge_dias` | Calculado. |
| `status_prazo_pge_calculado` | Calculado pela regra de prazo PGE. |

---

## 32. View `vw_painel_resumo`

### 32.1. Finalidade

Alimenta os indicadores básicos do Painel.

### 32.2. Dicionário de campos da view

| Campo | Tipo lógico | Origem | Regra/Descrição |
|---|---|---|---|
| `total_desastres` | Número | Contagem da view de decretos | Total de registros considerados. |
| `total_desastres_ativos` | Número | Soma condicional | Total de registros ativos. |
| `total_afetados` | Número | Soma de `total_afetados` | Total de pessoas afetadas nos registros ativos. |
| `total_obitos` | Número | Soma de `numero_obitos` | Total de óbitos. |
| `total_feridos` | Número | Soma de `numero_feridos` | Total de feridos. |
| `total_enfermos` | Número | Soma de `numero_enfermos` | Total de enfermos. |
| `total_desabrigados` | Número | Soma de `numero_desabrigados` | Total de desabrigados. |
| `total_desalojados` | Número | Soma de `numero_desalojados` | Total de desalojados. |
| `total_outros_afetados` | Número | Soma de `numero_outros_afetados` | Total de outros afetados. |
| `total_homologados` | Número | Contagem condicional | Total de registros com homologação `HOMOLOGADO`. |
| `total_pge_pendente` | Número | Contagem condicional | Total de registros com `status_prazo_pge_calculado = PENDENTE`. |
| `total_pge_no_prazo` | Número | Contagem condicional | Total de registros com `status_prazo_pge_calculado = NO PRAZO`. |

---

# PARTE XI — MAPEAMENTO DOS CAMPOS NAS TELAS

---

## 33. Tela de login

| Campo de tela | Campo/tabela | Obrigatório | Regra |
|---|---|---:|---|
| E-mail | `usuarios.email` | Sim | Deve existir, estar ativo e não excluído. |
| Senha | Validada contra `usuarios.senha_hash` | Sim | Validar com `password_verify()`. |
| IP | `login_logs.ip` | Auto | Capturado pela requisição. |
| Navegador | `login_logs.user_agent` | Auto | Capturado pela requisição. |

---

## 34. Tela Painel

| Elemento | Origem | Regra |
|---|---|---|
| Total de desastres | `vw_painel_resumo.total_desastres` | Exibir quantidade geral. |
| Desastres ativos | `vw_painel_resumo.total_desastres_ativos` | Exibir registros ativos. |
| Total de afetados | `vw_painel_resumo.total_afetados` | Exibir soma consolidada. |
| Óbitos | `vw_painel_resumo.total_obitos` | Exibir com destaque. |
| PGE pendente | `vw_painel_resumo.total_pge_pendente` | Indicador crítico. |
| PGE no prazo | `vw_painel_resumo.total_pge_no_prazo` | Indicador de controle. |
| Homologados | `vw_painel_resumo.total_homologados` | Indicador de conclusão. |

---

## 35. Tela Decretos — filtros

| Filtro | Campo/tabela | Tipo | Regra |
|---|---|---|---|
| Ano | `protocolo_ano` ou datas | Select/número | Filtrar por ano do protocolo ou período. |
| Município | `municipio_id` | Select | Listar municípios ativos. |
| Tipo de decreto | `tipo_decreto_id` | Select | Listar tipos ativos. |
| COBRADE | `cobrade_subtipo_id` | Select encadeado | Grupo → Subgrupo → Tipo → Subtipo. |
| Homologação | `homologacao_status_id` | Select | Listar status ativos. |
| Reconhecimento | `reconhecimento_status_id` | Select | Listar status ativos. |
| Status envio PGE | `status_envio_pge_id` | Select | Listar status ativos. |
| Status prazo PGE | `status_prazo_pge_calculado` | Select calculado | Opções: Aprovado, No prazo, Pendente, Sem data, Não iniciado. |
| Analista | `analista_id` | Select | Listar usuários gestores ativos. |
| Protocolo DGD | `protocolo_dgd` | Texto | Busca parcial ou exata. |
| Protocolo S2ID | `protocolo_s2id` | Texto | Busca parcial ou exata. |
| Decreto municipal | `numero_decreto_municipal` | Texto | Busca parcial. |

---

## 36. Tela Decretos — listagem

| Coluna | Origem | Editável | Regra |
|---|---|---:|---|
| Ordem sequencial por ano | `protocolo_ano`, `protocolo_sequencial` | Não | Ordenação padrão decrescente por ano/sequencial. |
| Protocolo DGD | `protocolo_dgd` | Não | Link para detalhe. |
| Município | `municipio` | Não | Exibição textual. |
| Tipo de desastre | `cobrade_subtipo`/`cobrade_codigo` | Não | Exibir nome e código. |
| Data do decreto municipal | `data_decreto_municipal` | Não | Formatar como `dd/mm/aaaa`. |
| Total de dias do decreto | `total_dias_decreto` | Não | Calculado. |
| Homologação | `homologacao` | Sim | Edição rápida por Gestor/Admin. |
| Reconhecimento | `reconhecimento` | Sim | Edição rápida por Gestor/Admin. |
| Total de afetados | `total_afetados` | Não | Calculado. |
| Total de dias para PGE | `duracao_pge_dias` | Não | Calculado. |
| Status de envio à PGE | `status_envio_pge` | Sim | Campo administrativo editável por Gestor/Admin. |
| Status de prazo PGE | `status_prazo_pge_calculado` | Não | Calculado automaticamente. |
| Analista | `analista` | Não | Alteração preferencial via edição completa. |
| Número do decreto municipal | `numero_decreto_municipal` | Não | Exibição. |
| Ações | `id` | Conforme perfil | Editar, detalhe, excluir. |

---

## 37. Tela Cadastro/Edição de desastre

### 37.1. Bloco Identificação

| Campo de tela | Campo real | Obrigatório | Perfil que preenche/edita |
|---|---|---:|---|
| Protocolo DGD | `protocolo_dgd` | Auto | Sistema. Apenas leitura. |
| Município | `municipio_id` | Sim | Operador no cadastro inicial; Gestor/Admin na edição. |
| UBM atuante | `ubm_id` | Não | Operador/Gestor/Admin. |
| Tipo de decreto | `tipo_decreto_id` | Sim | Operador/Gestor/Admin. |
| Data do desastre | `data_desastre` | Sim | Operador/Gestor/Admin. |

### 37.2. Bloco COBRADE

| Campo de tela | Campo real | Obrigatório | Regra |
|---|---|---:|---|
| Grupo COBRADE | Derivado | Sim | Usado para filtrar subgrupo. Não gravar em `desastres`. |
| Subgrupo COBRADE | Derivado | Sim | Usado para filtrar tipo. Não gravar em `desastres`. |
| Tipo COBRADE | Derivado | Sim | Usado para filtrar subtipo. Não gravar em `desastres`. |
| Subtipo COBRADE | `cobrade_subtipo_id` | Sim | Único campo COBRADE gravado no desastre. |
| Descrição | Derivada de `cobrade_subtipos.descricao` | Auto | Exibição informativa. |
| Simbologia | Derivada de `cobrade_subtipos.simbologia` | Auto | Exibição informativa. |

### 37.3. Bloco Decreto municipal e S2ID

| Campo de tela | Campo real | Obrigatório | Regra |
|---|---|---:|---|
| Protocolo S2ID | `protocolo_s2id` | Não | Texto livre controlado. |
| Número do decreto municipal | `numero_decreto_municipal` | Condicional | Recomendado para processo completo. |
| Data do decreto municipal | `data_decreto_municipal` | Condicional | Base para cálculo de prazo. |

### 37.4. Bloco Homologação, reconhecimento e PGE

| Campo de tela | Campo real | Obrigatório | Quem edita |
|---|---|---:|---|
| Número do decreto estadual de homologação | `numero_decreto_homologacao_estadual` | Condicional | Gestor/Admin. |
| Data do decreto de homologação | `data_decreto_homologacao` | Condicional | Gestor/Admin. |
| Homologação | `homologacao_status_id` | Sim | Gestor/Admin após cadastro. |
| Reconhecimento | `reconhecimento_status_id` | Sim | Gestor/Admin após cadastro. |
| Protocolo PAE/PGE | `protocolo_pae_pge` | Não | Gestor/Admin. |
| Data de envio para PGE | `data_envio_pge` | Condicional | Gestor/Admin. |
| Status de envio à PGE | `status_envio_pge_id` | Sim | Gestor/Admin. |
| Analista | `analista_id` | Não | Gestor/Admin. |

### 37.5. Bloco Recursos

| Campo de tela | Campo real | Obrigatório | Quem edita |
|---|---|---:|---|
| Recursos de ação de resposta | `recurso_resposta_status_id` | Sim | Gestor/Admin após cadastro. |
| Recursos de ação de reconstrução | `recurso_reconstrucao_status_id` | Sim | Gestor/Admin após cadastro. |

### 37.6. Bloco Afetados

| Campo de tela | Campo real | Obrigatório | Regra |
|---|---|---:|---|
| Número de óbitos | `numero_obitos` | Sim | Inteiro maior ou igual a zero. |
| Número de feridos | `numero_feridos` | Sim | Inteiro maior ou igual a zero. |
| Número de enfermos | `numero_enfermos` | Sim | Inteiro maior ou igual a zero. |
| Número de desabrigados | `numero_desabrigados` | Sim | Inteiro maior ou igual a zero. |
| Número de desalojados | `numero_desalojados` | Sim | Inteiro maior ou igual a zero. |
| Número de outros afetados | `numero_outros_afetados` | Sim | Inteiro maior ou igual a zero. |
| Total de afetados | `total_afetados` | Auto | Calculado automaticamente. Apenas leitura. |

### 37.7. Bloco Anexos

| Campo de tela | Campo real | Obrigatório | Regra |
|---|---|---:|---|
| Tipo de anexo | `tipo_anexo_id` | Sim | Selecionar em `tipos_anexo`. |
| Arquivo | Metadados em `desastre_anexos` | Sim | Upload validado. |
| Descrição do anexo | `descricao` | Não | Texto opcional. |

---

# PARTE XII — REGRAS TRANSVERSAIS DE VALIDAÇÃO

---

## 38. Regras para datas

| Regra | Aplicação |
|---|---|
| `data_desastre` não deve ser futura | Cadastro de desastre. |
| `data_decreto_municipal` não deve ser anterior de forma incoerente à `data_desastre` sem justificativa | Validação recomendada, não bloqueio absoluto. |
| `data_envio_pge` não deve ser anterior à `data_decreto_municipal` sem justificativa | Validação recomendada. |
| `data_decreto_homologacao` não deve ser anterior à `data_decreto_municipal` sem justificativa | Validação recomendada. |
| Campos `DATETIME` de auditoria não devem ser editáveis | Banco/backend. |

---

## 39. Regras para números

| Regra | Aplicação |
|---|---|
| Quantitativos humanos devem ser inteiros | Campos de afetados. |
| Quantitativos humanos não aceitam negativos | Campos de afetados. |
| Campos vazios de quantitativos devem ser tratados como zero | Backend/formulário. |
| `total_afetados` não deve ser enviado como campo gravável | Backend deve ignorar valor vindo do formulário. |
| Tamanho de arquivo deve respeitar `upload_tamanho_maximo_mb` | Upload de anexos. |

---

## 40. Regras para textos

| Regra | Aplicação |
|---|---|
| Remover espaços extras no início e fim | Todos os campos textuais. |
| Normalizar caixa quando necessário | Protocolo, e-mail, códigos e município normalizado. |
| Escapar HTML na saída | Todos os textos exibidos. |
| Não confiar em texto digitado para SQL | Usar PDO com prepared statements. |
| Campos de código devem ser estáveis | Domínios e status. |

---

## 41. Regras para status

| Regra | Aplicação |
|---|---|
| Status deve ser selecionado de tabela de domínio | Homologação, reconhecimento, PGE e recursos. |
| Não salvar status como texto livre | Tabela `desastres`. |
| Alteração de status crítico deve gerar histórico | `desastre_historico_status`. |
| Status inativo não deve aparecer em novos cadastros | Interface. |
| Status inativo deve continuar visível em registros antigos | Detalhe/listagem. |

---

## 42. Regras para exclusão lógica

| Entidade | Regra |
|---|---|
| Usuários | Marcar `excluido_em`, `excluido_por` e preferencialmente `ativo = 0`. |
| Desastres | Marcar `excluido_em`, `excluido_por` e ocultar da view/listagem padrão. |
| Anexos | Marcar `excluido_em`, `excluido_por` e `ativo = 0`; preservar metadados. |
| Domínios | Preferir `ativo = 0` em vez de apagar. |
| Auditoria | Não excluir por interface comum. |

---

# PARTE XIII — REGRAS DE PERMISSÃO POR DADO

---

## 43. Regras gerais por perfil

| Dado/Ação | Admin | Gestor | Operador |
|---|---:|---:|---:|
| Acessar painel | Sim | Sim | Sim |
| Ver listagem de decretos | Sim | Sim | Sim |
| Ver detalhe do desastre | Sim | Sim | Sim |
| Criar desastre | Sim | Sim | Sim |
| Editar desastre após gravação | Sim | Sim | Não |
| Excluir desastre | Sim | Sim | Não |
| Editar homologação | Sim | Sim | Não |
| Editar reconhecimento | Sim | Sim | Não |
| Editar status PGE | Sim | Sim | Não |
| Editar recursos | Sim | Sim | Não |
| Enviar anexos | Sim | Sim | Sim |
| Excluir anexos | Sim | Sim | Não |
| Administrar usuários | Sim | Não | Não |
| Consultar auditoria | Sim | Sim | Não |
| Administrar domínios | Sim | Não, salvo liberação | Não |

---

## 44. Campos que o Operador pode preencher no cadastro inicial

| Campo | Observação |
|---|---|
| `municipio_id` | Obrigatório. |
| `ubm_id` | Opcional. |
| `tipo_decreto_id` | Obrigatório. |
| `cobrade_subtipo_id` | Obrigatório. |
| `data_desastre` | Obrigatório. |
| `protocolo_s2id` | Opcional. |
| `numero_decreto_municipal` | Recomendado. |
| `data_decreto_municipal` | Recomendado quando houver decreto. |
| Campos de afetados | Obrigatórios com padrão zero. |
| Anexos | Permitido conforme permissão `anexos.upload`. |
| `observacoes` | Opcional. |

---

## 45. Campos de gestão restrita a Gestor/Admin após gravação

| Campo | Motivo |
|---|---|
| `homologacao_status_id` | Status processual crítico. |
| `reconhecimento_status_id` | Status processual crítico. |
| `status_envio_pge_id` | Controle de prazo/processo. |
| `protocolo_pae_pge` | Processo administrativo externo. |
| `data_envio_pge` | Impacta cálculo de prazo PGE. |
| `numero_decreto_homologacao_estadual` | Documento oficial de homologação. |
| `data_decreto_homologacao` | Documento oficial de homologação. |
| `analista_id` | Distribuição interna de responsabilidade. |
| `recurso_resposta_status_id` | Controle de recursos. |
| `recurso_reconstrucao_status_id` | Controle de recursos. |

---

# PARTE XIV — PADRÕES DE NOMENCLATURA E FORMATAÇÃO

---

## 46. Padrões de exibição

| Tipo de dado | Padrão de exibição |
|---|---|
| Data | `dd/mm/aaaa` |
| Data e hora | `dd/mm/aaaa HH:mm` |
| Número inteiro | Sem casas decimais. |
| Booleano | Exibir como `Ativo/Inativo`, `Sim/Não` ou badge equivalente. |
| Status | Exibir com nome e, quando útil, cor/badge. |
| Protocolo DGD | Exibir completo, sem quebra preferencialmente. |
| COBRADE | Exibir código + nome do subtipo. |
| Município | Exibir nome oficial. |

---

## 47. Padrões de armazenamento

| Tipo de dado | Padrão de armazenamento |
|---|---|
| Data | `YYYY-MM-DD`. |
| Data e hora | `YYYY-MM-DD HH:MM:SS`. |
| E-mail | Minúsculo. |
| Código de domínio | Caixa alta, sem acento, com underscore. |
| Protocolo | Caixa alta, sem acentos nos componentes normalizados. |
| Arquivo | Nome físico gerado pelo backend. |
| Senha | Hash seguro, nunca texto puro. |

---

# PARTE XV — REGRAS PARA RELATÓRIOS E EXPORTAÇÃO

---

## 48. Campos mínimos para relatório de desastre

| Grupo | Campos |
|---|---|
| Identificação | Protocolo DGD, município, UBM, data do desastre. |
| Classificação | Grupo, subgrupo, tipo, subtipo, código COBRADE, descrição, simbologia. |
| Decreto municipal | Número e data do decreto municipal. |
| Homologação | Status, número e data do decreto estadual de homologação. |
| Reconhecimento | Status de reconhecimento, protocolo S2ID. |
| PGE | Protocolo PAE/PGE, data de envio, status de envio, status de prazo calculado. |
| Recursos | Status de recurso de resposta e reconstrução. |
| Danos humanos | Óbitos, feridos, enfermos, desabrigados, desalojados, outros afetados e total. |
| Anexos | Relação de documentos enviados. |
| Auditoria | Criado por, criado em, atualizado por, atualizado em. |

---

## 49. Campos recomendados para exportação da listagem

| Campo exportado | Origem |
|---|---|
| Ano | `protocolo_ano` |
| Sequencial | `protocolo_sequencial` |
| Protocolo DGD | `protocolo_dgd` |
| Município | `municipio` |
| UBM atuante | `ubm_atuante` |
| Tipo de decreto | `tipo_decreto` |
| COBRADE código | `cobrade_codigo` |
| Tipo de desastre | `cobrade_subtipo` |
| Data do desastre | `data_desastre` |
| Protocolo S2ID | `protocolo_s2id` |
| Decreto municipal | `numero_decreto_municipal` |
| Data do decreto municipal | `data_decreto_municipal` |
| Total de dias do decreto | `total_dias_decreto` |
| Homologação | `homologacao` |
| Reconhecimento | `reconhecimento` |
| Protocolo PAE/PGE | `protocolo_pae_pge` |
| Data de envio PGE | `data_envio_pge` |
| Duração PGE dias | `duracao_pge_dias` |
| Status envio PGE | `status_envio_pge` |
| Status prazo PGE calculado | `status_prazo_pge_calculado` |
| Analista | `analista` |
| Recurso resposta | `recurso_resposta` |
| Recurso reconstrução | `recurso_reconstrucao` |
| Óbitos | `numero_obitos` |
| Feridos | `numero_feridos` |
| Enfermos | `numero_enfermos` |
| Desabrigados | `numero_desabrigados` |
| Desalojados | `numero_desalojados` |
| Outros afetados | `numero_outros_afetados` |
| Total afetados | `total_afetados` |

---

# PARTE XVI — CONSISTÊNCIA E INTEGRIDADE

---

## 50. Regras de integridade referencial

| Origem | Destino | Regra |
|---|---|---|
| `usuarios.perfil_id` | `perfis.id` | Usuário deve possuir perfil válido. |
| `perfil_permissoes.perfil_id` | `perfis.id` | Perfil não deve ser apagado se usado. |
| `perfil_permissoes.permissao_id` | `permissoes.id` | Permissão não deve ser apagada se usada. |
| `ubms.municipio_id` | `municipios.id` | UBM pode ficar sem município se o município for removido logicamente. |
| `desastres.municipio_id` | `municipios.id` | Desastre exige município válido. |
| `desastres.cobrade_subtipo_id` | `cobrade_subtipos.id` | Desastre exige classificação COBRADE válida. |
| `desastres.analista_id` | `usuarios.id` | Analista pode ser nulo se usuário for removido logicamente. |
| `desastre_anexos.desastre_id` | `desastres.id` | Anexo pertence a um desastre. |
| `desastre_historico_status.desastre_id` | `desastres.id` | Histórico pertence a um desastre. |
| `auditoria_logs.usuario_id` | `usuarios.id` | Auditoria preserva evento mesmo se usuário for desativado. |

---

## 51. Regras de unicidade

| Campo/combinação | Regra |
|---|---|
| `perfis.codigo` | Código de perfil único. |
| `permissoes.codigo` | Código de permissão único. |
| `usuarios.email` | E-mail único. |
| `usuarios.cpf` | CPF único quando informado. |
| `municipios.codigo_ibge` | Código IBGE único quando informado. |
| `municipios.nome + municipios.uf` | Município único por UF. |
| `cobrade_grupos.codigo` | Grupo COBRADE único. |
| `cobrade_subgrupos.grupo_id + codigo` | Subgrupo único dentro do grupo. |
| `cobrade_tipos.subgrupo_id + codigo` | Tipo único dentro do subgrupo. |
| `cobrade_subtipos.codigo` | Subtipo COBRADE único. |
| `tipos_decreto.codigo` | Tipo de decreto único. |
| `status_homologacao.codigo` | Status de homologação único. |
| `status_reconhecimento.codigo` | Status de reconhecimento único. |
| `status_recurso.codigo` | Status de recurso único. |
| `status_envio_pge.codigo` | Status PGE único. |
| `tipos_anexo.codigo` | Tipo de anexo único. |
| `desastres.protocolo_dgd` | Protocolo DGD único. |
| `desastres.protocolo_ano + protocolo_sequencial` | Sequência anual única. |
| `configuracoes_sistema.chave` | Configuração única por chave. |

---

# PARTE XVII — CRITÉRIOS DE ACEITE DO DICIONÁRIO

---

## 52. Critérios de aceite funcional

A implementação estará aderente a este dicionário quando:

1. Todos os campos das tabelas do Documento 05 estiverem implementados conforme este Documento 06.
2. Campos automáticos não forem editáveis pela interface.
3. Campos de status forem carregados de tabelas de domínio, sem texto livre.
4. O protocolo DGD for gerado automaticamente e sem duplicidade.
5. O total de afetados for calculado automaticamente.
6. A listagem de Decretos usar a view ou consulta equivalente à `vw_decretos_listagem`.
7. O Painel usar a view ou consulta equivalente à `vw_painel_resumo`.
8. A edição rápida de homologação, reconhecimento e status PGE respeitar permissões.
9. Alterações críticas gerarem histórico.
10. A exclusão de desastre, usuário e anexo for lógica.
11. Anexos forem salvos como arquivos no servidor, com metadados no banco.
12. O caminho físico dos anexos não for exposto publicamente.
13. Senhas forem armazenadas apenas como hash seguro.
14. Tentativas de login forem registradas.
15. O sistema aplicar paginação máxima de 20 registros na listagem.

---

## 53. Critérios de aceite técnico

| Critério | Resultado esperado |
|---|---|
| Banco com `utf8mb4` | Suporte correto a acentos e caracteres especiais. |
| Uso de `InnoDB` | Suporte a chaves estrangeiras e transações. |
| Prepared statements | Proteção contra SQL injection. |
| Validação no backend | Não depender apenas de HTML/JavaScript. |
| Auditoria | Eventos críticos gravados. |
| Logs de login | Tentativas de acesso registradas. |
| Upload seguro | Extensão, MIME e tamanho validados. |
| Exclusão lógica | Dados históricos preservados. |
| Separação entre status PGE editável e calculado | Evita inconsistência operacional. |
| COBRADE hierárquico | Classificação consistente e filtrável. |

---

# PARTE XVIII — PONTOS DE ATENÇÃO PARA IMPLEMENTAÇÃO

---

## 54. Pontos críticos

1. **Não permitir edição manual do `protocolo_dgd`.**  
   O protocolo é controle interno do sistema e deve ser gerado automaticamente.

2. **Não salvar status como texto.**  
   Homologação, reconhecimento, PGE e recursos devem sempre usar IDs de tabelas de domínio.

3. **Não confundir `status_envio_pge_id` com `status_prazo_pge_calculado`.**  
   O primeiro é administrativo/editável. O segundo é automático.

4. **Não permitir total de afetados digitado manualmente.**  
   O total deve ser calculado para evitar divergência.

5. **Não armazenar anexos como BLOB.**  
   O banco deve guardar metadados; o arquivo fica em diretório protegido.

6. **Não apagar fisicamente registros operacionais.**  
   Desastres, usuários e anexos precisam preservar histórico.

7. **Não depender apenas de JavaScript para validação.**  
   Toda regra crítica deve ser validada no backend.

8. **Não expor dados de sessão, senha hash ou caminho de arquivo.**  
   Esses dados são sensíveis e devem ficar fora das telas comuns.

---

## 55. Recomendação técnica final

O DGD deve usar este dicionário como contrato entre banco, backend e interface. Cada campo definido neste documento deve ter comportamento coerente em três camadas:

```text
Banco de dados → regra de integridade e tipo correto
Backend PHP MVC → validação, permissão e auditoria
Interface HTML/CSS/JavaScript → exibição, formulário e feedback ao usuário
```

A maior fragilidade potencial do projeto não está na criação das tabelas, mas na **interpretação incorreta dos campos críticos**. Por isso, a separação entre dados digitáveis, dados calculados, status editáveis, status automáticos e dados auditáveis deve ser preservada desde a primeira versão.

---

## 56. Próximo documento da sequência

O próximo artefato técnico da sequência é:

```text
PROMPT OFICIAL DE COMANDO CODEX - CORTEX
```

Esse prompt deverá consolidar os documentos 01 a 06 em uma instrução técnica única para orientar geração de código, estrutura MVC, banco de dados, telas, permissões, padrões visuais e regras de negócio do DGD.
