# 01 — DOCUMENTO TÉCNICO
# DEFINIÇÃO CONCEITUAL DO SISTEMA DGD

**Sistema:** DGD — Sistema de Gerenciamento de Desastres  
**Órgão gestor:** Coordenadoria Estadual de Defesa Civil do Estado do Pará — CEDEC-PA  
**Público-alvo:** Defesa Civil do Pará  
**Tipo de documento:** Definição conceitual do sistema  
**Versão:** 1.0  
**Formato:** Markdown  
**Status:** Base conceitual inicial para especificação, modelagem e desenvolvimento  

---

## 1. Finalidade do documento

Este documento define a concepção funcional do **DGD — Sistema de Gerenciamento de Desastres**, estabelecendo o escopo inicial, a organização das páginas, os perfis de usuário, os principais fluxos de operação, as regras conceituais de negócio e as premissas para desenvolvimento.

O DGD será utilizado pela **Defesa Civil do Estado do Pará — CEDEC-PA** para registrar, acompanhar e gerenciar desastres ocorridos no Estado do Pará, com foco no controle de decretos municipais, homologações estaduais, reconhecimento federal, tramitação junto à PGE e acompanhamento de solicitações de recursos relacionados às ações de resposta e reconstrução.

---

## 2. Contexto institucional

A CEDEC-PA necessita de um sistema interno para consolidar o acompanhamento dos desastres registrados nos municípios paraenses, mantendo histórico organizado dos decretos, dos dados de afetados, dos documentos anexados, dos protocolos externos e da situação administrativa de cada processo.

O DGD não substitui sistemas externos como o **S2ID**, mas atua como sistema estadual de controle, organização, consulta e acompanhamento operacional dos processos relacionados a desastres, homologações, reconhecimentos e envio à Procuradoria Geral do Estado — PGE.

O sistema deverá ser desenvolvido com estrutura visual e operacional compatível com sistemas anteriormente utilizados pela Defesa Civil, adotando como referência estrutural o padrão de organização do sistema PLANCON: menus objetivos, telas administrativas, filtros superiores, listagens paginadas, ações por perfil, formulários segmentados e linguagem visual institucional.

---

## 3. Objetivo geral do sistema

O objetivo do DGD é permitir que a CEDEC-PA cadastre, controle, acompanhe e consulte, de forma padronizada, os registros de desastres ocorridos no Estado do Pará, vinculando cada ocorrência aos dados administrativos, técnicos, documentais e processuais necessários para gestão estadual.

---

## 4. Objetivos específicos

O sistema deverá permitir:

1. Cadastrar desastres com protocolo DGD automático.
2. Registrar município afetado, UBM atuante e tipo de decreto.
3. Registrar a classificação COBRADE do desastre.
4. Controlar datas e números dos decretos municipais e estaduais.
5. Controlar status de homologação estadual.
6. Controlar status de reconhecimento federal.
7. Registrar protocolo S2ID.
8. Registrar protocolo PAE/PGE.
9. Acompanhar o envio de processos para a PGE.
10. Calcular automaticamente o total de afetados.
11. Calcular automaticamente o total de dias relacionados ao controle PGE.
12. Gerar status PGE automático com base em regra parametrizada.
13. Permitir edição controlada dos status de homologação, reconhecimento e envio à PGE.
14. Permitir anexação de documentos obrigatórios e complementares.
15. Exibir listagem paginada de decretos e desastres.
16. Aplicar permissões conforme perfil do usuário.
17. Manter histórico mínimo de alterações relevantes para rastreabilidade administrativa.

---

## 5. Escopo funcional inicial

O escopo funcional inicial do DGD compreende as seguintes áreas:

| Área | Finalidade |
|---|---|
| Login | Autenticação de usuários autorizados. |
| Painel | Visão geral dos registros, indicadores e alertas operacionais. |
| Decretos | Cadastro, listagem, edição, detalhamento e exclusão controlada de registros de desastre/decreto. |
| Usuários | Gerenciamento de usuários do sistema. |
| Alterar senha | Alteração de senha do usuário autenticado. |

A área principal do sistema será o módulo **Decretos**, pois nele ocorrerá o cadastro e o gerenciamento dos desastres.

---

## 6. Escopo não contemplado na versão inicial

A versão inicial não contempla, salvo decisão posterior:

1. Integração automática via API com o S2ID.
2. Integração automática via API com sistemas da PGE.
3. Geração automática de minutas de decreto.
4. Assinatura digital dentro do sistema.
5. Publicação automática em Diário Oficial.
6. Georreferenciamento avançado dos desastres.
7. Módulo público de consulta aberta.
8. Aplicativo móvel.
9. Fluxo completo de tramitação processual eletrônica.
10. Controle financeiro detalhado da execução de recursos.

Esses itens poderão ser tratados como evolução futura.

---

## 7. Perfis conceituais de usuário

O sistema terá três perfis principais:

| Perfil | Definição conceitual |
|---|---|
| Admin | Perfil técnico-administrativo com acesso completo às funcionalidades do sistema, incluindo gestão de usuários e exclusões. |
| Gestor | Perfil de gestão operacional, com permissão para cadastrar, editar, acompanhar e validar registros de desastre/decreto. |
| Operador | Perfil de uso operacional, com permissão para consultar registros e realizar cadastros conforme regra definida na matriz de permissões. |

A matriz detalhada de permissões será definida no Documento 03.

---

## 8. Hierarquia conceitual de navegação

A navegação deverá ser simples, com menu lateral ou superior, obedecendo à seguinte hierarquia inicial:

```text
DGD
├── Login público
└── Área autenticada
    ├── Painel
    ├── Decretos
    │   ├── Listagem de decretos/desastres
    │   ├── Filtros de busca
    │   ├── Cadastro de desastre
    │   ├── Edição de desastre
    │   ├── Detalhe do desastre
    │   └── Exclusão controlada
    ├── Usuários
    │   ├── Listagem de usuários
    │   ├── Cadastro de usuário
    │   ├── Edição de usuário
    │   └── Ativação/inativação de usuário
    └── Alterar senha
```

A estrutura completa de módulos, páginas e navegação será detalhada no Documento 02.

---

## 9. Página pública

A única página pública prevista é a **tela de login**.

### 9.1 Funções da tela de login

A tela de login deverá permitir:

1. Informar usuário, e-mail ou identificador de acesso.
2. Informar senha.
3. Validar credenciais.
4. Bloquear acesso não autorizado.
5. Encaminhar usuário autenticado ao Painel.
6. Exibir mensagens de erro de autenticação.

### 9.2 Restrições da tela pública

A página pública não deverá expor dados de desastres, municípios, usuários, anexos, decretos ou protocolos.

---

## 10. Área autenticada

A área autenticada será acessível apenas por usuários com cadastro ativo. Após o login, o usuário será encaminhado para o **Painel**.

A sessão autenticada deverá manter controle de:

1. Identificador do usuário.
2. Nome do usuário.
3. Perfil.
4. Situação ativa/inativa.
5. Data e hora de autenticação.
6. Controle de expiração da sessão.

---

## 11. Página Painel

O Painel será a página inicial da área autenticada.

### 11.1 Finalidade

Exibir uma visão sintética da situação dos registros cadastrados no DGD, permitindo acompanhamento rápido pela equipe da CEDEC-PA.

### 11.2 Indicadores sugeridos

O Painel poderá apresentar:

1. Total de desastres cadastrados no ano corrente.
2. Total de decretos municipais registrados.
3. Total de homologações solicitadas.
4. Total de processos enviados à PGE.
5. Total de registros homologados.
6. Total de registros não homologados.
7. Total de reconhecimentos federais solicitados.
8. Total de reconhecimentos federais reconhecidos.
9. Total de registros com status PGE pendente.
10. Total de afetados no período filtrado.

### 11.3 Filtros sugeridos

O Painel poderá conter filtros por:

1. Ano.
2. Município.
3. Tipo de decreto.
4. Tipo de desastre.
5. Status de homologação.
6. Status de reconhecimento.
7. Status PGE.
8. Analista.

---

## 12. Página Decretos

A página **Decretos** será o módulo principal do sistema.

Apesar do nome do módulo ser “Decretos”, seu conteúdo funcional corresponde ao gerenciamento completo do registro de desastre, pois cada cadastro vincula ocorrência, decreto municipal, homologação estadual, reconhecimento federal, PGE, dados humanos afetados e anexos.

---

## 13. Cadastro de desastre

O cadastro de desastre deverá possuir formulário segmentado para melhorar usabilidade e reduzir erro de preenchimento.

### 13.1 Blocos conceituais do formulário

Sugere-se organizar o cadastro nos seguintes blocos:

```text
1. Identificação do registro
2. Localização e atuação
3. Classificação do desastre — COBRADE
4. Dados do desastre
5. Decreto municipal
6. Homologação estadual
7. Reconhecimento federal
8. Tramitação PGE
9. Recursos de resposta e reconstrução
10. Danos humanos e afetados
11. Anexos
12. Controle interno
```

---

## 14. Identificação do registro

### 14.1 Protocolo DGD

O protocolo DGD será gerado automaticamente pelo sistema.

### 14.2 Composição sugerida do protocolo

Formato inicial proposto:

```text
DGD-AAAA-000001-AAAAMMDD-MUNICIPIO
```

Onde:

| Parte | Descrição |
|---|---|
| DGD | Sigla fixa do sistema. |
| AAAA | Ano de referência do cadastro ou do desastre. |
| 000001 | Sequencial anual automático. |
| AAAAMMDD | Data do desastre em formato numérico. |
| MUNICIPIO | Nome do município normalizado, sem acentos e em caixa alta. |

Exemplo:

```text
DGD-2026-000001-20260215-ALTAMIRA
```

### 14.3 Regra do sequencial

O sequencial deverá reiniciar a cada ano.

Exemplo:

```text
DGD-2026-000001-...
DGD-2026-000002-...
DGD-2027-000001-...
```

---

## 15. Localização e atuação

O cadastro deverá registrar:

| Campo | Finalidade |
|---|---|
| Município | Município paraense afetado pelo desastre. |
| UBM atuante | Unidade operacional atuante no atendimento ou acompanhamento. |
| Analista | Usuário com perfil Gestor responsável pela análise/acompanhamento do registro. |

A lista de analistas deverá ser alimentada a partir dos usuários cadastrados com perfil **Gestor**.

---

## 16. Tipo de decreto

O campo **tipo de decreto** deverá possuir domínio controlado:

1. Situação de Emergência.
2. Estado de Calamidade Pública.

Esse campo deve ser obrigatório quando houver decreto municipal registrado.

---

## 17. Classificação do desastre — COBRADE

O cadastro deverá permitir a seleção estruturada da classificação COBRADE.

### 17.1 Campos previstos

| Campo | Descrição |
|---|---|
| Grupo COBRADE | Grupo principal do desastre. |
| Subgrupo COBRADE | Subgrupo vinculado ao grupo selecionado. |
| Tipo COBRADE | Tipo vinculado ao subgrupo selecionado. |
| Subtipo COBRADE | Subtipo vinculado ao tipo selecionado. |
| Descrição | Descrição textual da classificação. |
| Simbologia | Símbolo/imagem associada ao tipo de desastre. |

### 17.2 Regra conceitual

A seleção deve ser hierárquica. Ao escolher um grupo, o sistema deve carregar apenas os subgrupos compatíveis. Ao escolher um subgrupo, deve carregar apenas os tipos compatíveis. Ao escolher tipo e subtipo, a descrição e simbologia devem ser preenchidas ou disponibilizadas automaticamente conforme tabela de referência.

### 17.3 Base de dados COBRADE

A base COBRADE deverá ser reaproveitada do modelo utilizado no sistema PLANCON, com estrutura compatível para importação no banco do DGD.

Caso a base PLANCON não esteja disponível durante o desenvolvimento, deverá ser criada uma tabela própria de referência COBRADE no DGD, preservando os campos necessários para integração futura.

---

## 18. Dados do desastre

Campos previstos:

| Campo | Tipo conceitual | Obrigatoriedade inicial |
|---|---:|---|
| Data do desastre | Data | Obrigatório |
| Protocolo S2ID | Texto | Opcional/condicional |
| Número do decreto municipal | Texto | Obrigatório quando houver decreto |
| Data do decreto municipal | Data | Obrigatório quando houver decreto |
| Número do decreto estadual de homologação | Texto | Opcional/condicional |
| Data do decreto de homologação | Data | Opcional/condicional |

---

## 19. Homologação estadual

O campo **homologação** deverá possuir domínio controlado.

### 19.1 Status previstos

1. Solicitado.
2. Não solicitado.
3. Pendente — despacho.
4. Pendente — parecer.
5. Em análise DGD.
6. Enviado PGE.
7. Homologado.
8. Não homologado.
9. Não registrado.

### 19.2 Regra conceitual

O status de homologação representa a situação administrativa estadual do processo dentro do fluxo acompanhado pela CEDEC-PA.

A alteração do status deverá ser permitida apenas a perfis autorizados, conforme matriz de permissões.

---

## 20. Reconhecimento federal

O campo **reconhecimento** deverá possuir domínio controlado.

### 20.1 Status previstos

1. Solicitado.
2. Reconhecido.
3. Em análise SEDEC.
4. Enviado para reconhecimento.
5. Aguardando ajuste município.
6. Não reconhecido.
7. Aguardando análise.
8. Registrado.
9. Não registrado.

### 20.2 Regra conceitual

O status de reconhecimento representa a situação de acompanhamento do reconhecimento federal ou do registro relacionado ao desastre.

A informação deve ser controlada independentemente da homologação estadual, pois os fluxos possuem naturezas administrativas distintas.

---

## 21. Tramitação PGE

O DGD deverá controlar dados básicos da tramitação junto à PGE.

### 21.1 Campos previstos

| Campo | Descrição |
|---|---|
| Protocolo PAE/PGE | Número ou código do processo administrativo. |
| Data de envio para PGE | Data em que o processo foi encaminhado à PGE. |
| Total de dias para PGE | Campo calculado conforme regra do sistema. |
| Status de envio à PGE | Campo editável, com apoio de regra automática de status. |

---

## 22. Regra de status PGE

O status PGE deverá ser calculado a partir do campo **homologação** e do campo **duração PGE em dias**.

### 22.1 Regra lógica informada

```text
SE Homologação = Homologado
    Status PGE = APROVADO
SENÃO SE Duração PGE em dias <= 7 E Duração PGE em dias > 0
    Status PGE = NO PRAZO
SENÃO SE Duração PGE em dias > 7
    Status PGE = PENDENTE
```

### 22.2 Fórmula de referência

```text
IFS(
  [HOMOLOGACAO] = 'Homologado', 'APROVADO',
  AND([DURACAO_PGE_DIAS] <= 7, [DURACAO_PGE_DIAS] > 0), 'NO PRAZO',
  [DURACAO_PGE_DIAS] > 7, 'PENDENTE'
)
```

### 22.3 Interpretação operacional inicial

A duração PGE em dias deverá ser calculada por regra parametrizável. Como padrão inicial, recomenda-se:

```text
Se houver data de envio para PGE:
    duração = data de envio para PGE - data do decreto municipal
Senão, se houver data do decreto municipal e homologação ainda não concluída:
    duração = data atual - data do decreto municipal
Senão:
    duração = 0 ou nulo
```

Esta regra deve ser validada pela equipe gestora antes da implementação definitiva.

---

## 23. Recursos de ação de resposta

O sistema deverá registrar a situação dos recursos de ação de resposta.

### 23.1 Status previstos

1. Solicitado.
2. Plano aprovado.
3. Recurso deferido.
4. Recurso indeferido.
5. Aguardando ajustes.
6. Em análise SEDEC.
7. Registro de revisão.
8. Empenho.
9. Não solicitado.
10. Não registrado.

---

## 24. Recursos de ação de reconstrução

O sistema deverá registrar a situação dos recursos de ação de reconstrução.

### 24.1 Status previstos

1. Solicitado.
2. Plano aprovado.
3. Recurso deferido.
4. Recurso indeferido.
5. Aguardando ajustes.
6. Em análise SEDEC.
7. Registro de revisão.
8. Empenho.
9. Não solicitado.
10. Não registrado.

---

## 25. Danos humanos e afetados

O DGD deverá registrar os quantitativos humanos relacionados ao desastre.

### 25.1 Campos previstos

| Campo | Regra |
|---|---|
| Número de óbitos | Inteiro, mínimo 0. |
| Número de feridos | Inteiro, mínimo 0. |
| Número de enfermos | Inteiro, mínimo 0. |
| Número de desabrigados | Inteiro, mínimo 0. |
| Número de desalojados | Inteiro, mínimo 0. |
| Número de outros afetados | Inteiro, mínimo 0. |
| Total de afetados | Calculado automaticamente. |

### 25.2 Regra de totalização

```text
Total de afetados = óbitos + feridos + enfermos + desabrigados + desalojados + outros afetados
```

O campo **total de afetados** não deverá ser editado manualmente.

---

## 26. Anexos

O sistema deverá permitir anexação de documentos relacionados ao registro.

### 26.1 Tipos de anexos previstos

1. Decreto municipal.
2. Ofício de homologação.
3. Parecer estadual.
4. Parecer municipal.
5. Outros documentos.

### 26.2 Regras conceituais para anexos

1. Cada anexo deve pertencer a um desastre/decreto.
2. Cada anexo deve possuir tipo documental.
3. Cada anexo deve armazenar nome original do arquivo.
4. Cada anexo deve armazenar nome físico ou caminho de armazenamento.
5. Cada anexo deve registrar usuário responsável pelo envio.
6. Cada anexo deve registrar data e hora do envio.
7. O sistema deve restringir tipos de arquivo permitidos.
8. O sistema deve impedir upload de arquivos potencialmente executáveis.

---

## 27. Listagem de decretos/desastres

A listagem deverá exibir registros em ordem sequencial por ano, com paginação máxima de 20 registros por página.

### 27.1 Colunas previstas

1. Ordem sequencial por ano.
2. Protocolo DGD.
3. Município.
4. Tipo de desastre.
5. Data do decreto municipal.
6. Total de dias do decreto.
7. Homologação.
8. Reconhecimento.
9. Total de afetados.
10. Total de dias para a PGE.
11. Status de envio à PGE.
12. Analista.
13. Número do decreto municipal.
14. Ações.

### 27.2 Campos editáveis diretamente na listagem

A listagem poderá permitir edição rápida dos seguintes campos, conforme perfil:

1. Homologação.
2. Reconhecimento.
3. Status de envio à PGE.

Essa edição rápida deve registrar data, usuário e valor alterado para fins de rastreabilidade.

---

## 28. Ações da listagem

A listagem deverá apresentar as seguintes ações:

| Ação | Finalidade | Restrição conceitual |
|---|---|---|
| Editar | Alterar dados do registro. | Gestor e Admin. |
| Ver detalhe | Consultar registro completo. | Todos os perfis autenticados autorizados. |
| Excluir | Remover ou inativar registro. | Gestor e Admin. |

Recomenda-se que a exclusão física seja evitada em produção. O padrão preferencial deve ser **exclusão lógica**, com campo de status ativo/inativo ou excluído.

---

## 29. Filtros do módulo Decretos

Filtros sugeridos:

1. Ano.
2. Município.
3. Protocolo DGD.
4. Protocolo S2ID.
5. Protocolo PAE/PGE.
6. Tipo de decreto.
7. Grupo COBRADE.
8. Tipo de desastre.
9. Homologação.
10. Reconhecimento.
11. Status PGE.
12. Analista.
13. Intervalo de data do desastre.
14. Intervalo de data do decreto municipal.
15. Registros com PGE pendente.

---

## 30. Página Usuários

A página **Usuários** deverá permitir o gerenciamento dos usuários internos do DGD.

### 30.1 Funcionalidades previstas

1. Listar usuários.
2. Cadastrar usuário.
3. Editar usuário.
4. Definir perfil.
5. Definir situação ativa/inativa.
6. Redefinir senha ou gerar senha temporária, conforme política adotada.
7. Consultar data de criação e último acesso.

### 30.2 Campos conceituais do usuário

1. Nome.
2. E-mail.
3. Login.
4. Perfil.
5. Status.
6. Senha criptografada.
7. Data de criação.
8. Data de atualização.
9. Último acesso.

---

## 31. Página Alterar senha

A página **Alterar senha** deverá permitir que o usuário autenticado altere sua própria senha.

### 31.1 Campos previstos

1. Senha atual.
2. Nova senha.
3. Confirmação da nova senha.

### 31.2 Regras mínimas

1. A senha atual deve ser validada antes da alteração.
2. A nova senha e a confirmação devem ser iguais.
3. A senha deve ser armazenada com hash seguro.
4. O sistema deve impedir armazenamento de senha em texto puro.
5. O sistema deve exibir mensagem clara de sucesso ou erro.

---

## 32. Padrão de interface

O DGD deverá seguir padrão administrativo institucional, com base estrutural semelhante ao sistema PLANCON.

### 32.1 Diretrizes visuais

1. Interface limpa e funcional.
2. Identidade visual compatível com a Defesa Civil do Pará.
3. Menus objetivos.
4. Cores institucionais.
5. Cards para indicadores no Painel.
6. Tabelas administrativas com filtros.
7. Formulários organizados por blocos.
8. Botões de ação padronizados.
9. Feedback visual para sucesso, alerta e erro.
10. Layout responsivo para uso em notebooks e desktops.

### 32.2 Componentes padrão

1. Cabeçalho autenticado.
2. Menu de navegação.
3. Área de conteúdo.
4. Rodapé institucional.
5. Cards informativos.
6. Tabelas com paginação.
7. Campos de busca.
8. Filtros avançados.
9. Modais de confirmação.
10. Mensagens de validação.

---

## 33. Tecnologias previstas

O sistema deverá ser desenvolvido com as tecnologias informadas:

| Camada | Tecnologia |
|---|---|
| Backend | PHP |
| Frontend | HTML, CSS, JavaScript |
| Banco de dados | MySQL |
| Administração do banco | phpMyAdmin |
| Ambiente local | WampServer |
| Ambiente de produção | Hostinger com phpMyAdmin |

A arquitetura completa será detalhada no Documento 04.

---

## 34. Ambientes

### 34.1 Desenvolvimento

Ambiente previsto:

```text
WampServer
├── Apache
├── PHP
├── MySQL
└── phpMyAdmin
```

### 34.2 Produção

Ambiente previsto:

```text
Hostinger
├── Hospedagem PHP
├── Banco MySQL
└── phpMyAdmin
```

### 34.3 Observação técnica

A implementação deve respeitar as limitações do ambiente de hospedagem, principalmente em relação a permissões de diretórios, tamanho máximo de upload, versão do PHP, configuração do MySQL, envio de e-mails e regras de segurança.

---

## 35. Regras conceituais de segurança

O DGD deverá adotar regras mínimas de segurança:

1. Autenticação obrigatória para área interna.
2. Senhas armazenadas com hash seguro.
3. Controle de sessão.
4. Restrição de acesso por perfil.
5. Validação de dados no servidor.
6. Proteção contra SQL Injection.
7. Proteção contra Cross-Site Scripting — XSS.
8. Proteção contra Cross-Site Request Forgery — CSRF em formulários críticos.
9. Controle de upload de anexos.
10. Registro de alterações críticas.
11. Bloqueio de acesso direto a arquivos internos sensíveis.

---

## 36. Regras conceituais de auditoria

O sistema deverá registrar eventos relevantes, especialmente:

1. Login realizado.
2. Falha de login.
3. Cadastro de desastre.
4. Edição de desastre.
5. Alteração de homologação.
6. Alteração de reconhecimento.
7. Alteração de status PGE.
8. Upload de anexo.
9. Exclusão lógica de registro.
10. Cadastro, edição ou inativação de usuário.

Cada evento deve registrar, no mínimo:

1. Usuário responsável.
2. Data e hora.
3. Entidade afetada.
4. Ação executada.
5. Valor anterior, quando aplicável.
6. Valor novo, quando aplicável.

---

## 37. Premissas do sistema

1. O DGD será um sistema web administrativo.
2. O acesso será restrito a usuários autorizados da Defesa Civil do Pará.
3. A primeira versão terá autenticação própria.
4. O módulo Decretos será o núcleo funcional do sistema.
5. O cadastro de desastre será vinculado a um protocolo DGD automático.
6. A base COBRADE deverá ser importada ou replicada do PLANCON.
7. O sistema utilizará MySQL como banco de dados relacional.
8. A hospedagem inicial será em ambiente PHP/MySQL compatível com Hostinger.
9. O sistema deverá ser construído de forma modular para permitir evolução futura.
10. As regras operacionais poderão ser ajustadas após validação com a equipe gestora.

---

## 38. Riscos e pontos de atenção

| Risco | Impacto | Mitigação recomendada |
|---|---|---|
| Base COBRADE do PLANCON indisponível | Atraso na implementação da classificação de desastres | Criar tabela COBRADE provisória compatível com importação futura. |
| Ausência de integração com S2ID | Necessidade de digitação manual de protocolos e status | Padronizar campos e permitir conferência manual. |
| Alteração manual de status sem rastreabilidade | Perda de controle administrativo | Registrar logs de alteração. |
| Exclusão indevida de registros | Perda de histórico institucional | Usar exclusão lógica. |
| Upload inseguro de anexos | Risco de segurança | Restringir extensões, tamanho e acesso público direto. |
| Regras de prazo PGE mal definidas | Indicadores incorretos | Parametrizar regra e validar com a CEDEC-PA. |
| Permissões genéricas demais | Acesso indevido a funções críticas | Implementar matriz formal de permissões. |
| Hospedagem compartilhada limitada | Restrições de upload, memória e execução | Validar limites do plano de hospedagem antes da implantação. |

---

## 39. Critérios conceituais de aceite

A concepção inicial do DGD será considerada atendida quando o sistema permitir:

1. Autenticação de usuários por perfil.
2. Cadastro completo de desastre/decreto.
3. Geração automática de protocolo DGD.
4. Seleção de classificação COBRADE.
5. Registro de homologação e reconhecimento.
6. Registro de dados PGE.
7. Cálculo de total de afetados.
8. Cálculo de status PGE conforme regra definida.
9. Upload de anexos.
10. Listagem paginada com filtros.
11. Visualização de detalhes.
12. Edição restrita por perfil.
13. Exclusão restrita por perfil.
14. Gestão de usuários.
15. Alteração de senha.

---

## 40. Pendências para documentos seguintes

Os próximos documentos deverão detalhar:

| Documento | Conteúdo |
|---|---|
| 02 | Mapa completo dos módulos, páginas e hierarquia de navegação. |
| 03 | Perfis de usuário e matriz de permissões. |
| 04 | Arquitetura MVC completa do sistema. |
| 05 | Estrutura completa do banco de dados. |
| 06 | Dicionário de dados completo do sistema. |
| Prompt Codex/Cortex | Prompt oficial para geração do sistema com base nos documentos técnicos. |

---

## 41. Referências técnicas e normativas consideradas

1. Sistema Integrado de Informações sobre Desastres — S2ID.
2. Serviço federal de solicitação de reconhecimento de Situação de Emergência ou Estado de Calamidade Pública.
3. Classificação e Codificação Brasileira de Desastres — COBRADE.
4. Decreto Estadual do Pará nº 4028, de 2024.
5. Escopo funcional informado pela CEDEC-PA para o DGD.
6. Padrão estrutural administrativo inspirado no sistema PLANCON, conforme orientação do projeto.

---

## 42. Conclusão

O DGD deverá ser tratado como sistema estadual de controle operacional e administrativo dos desastres registrados no Pará, com foco na organização das informações essenciais para acompanhamento dos decretos municipais, homologações estaduais, reconhecimento federal, envio à PGE, recursos de resposta, recursos de reconstrução, danos humanos e documentação comprobatória.

A concepção proposta prioriza simplicidade operacional, rastreabilidade, padronização dos cadastros e aderência à rotina da Defesa Civil do Pará. A arquitetura e o banco de dados deverão ser desenhados para permitir evolução futura, especialmente integrações externas, relatórios avançados, painéis analíticos e automações processuais.
