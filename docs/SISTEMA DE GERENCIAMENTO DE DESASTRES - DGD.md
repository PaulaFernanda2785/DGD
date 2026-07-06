*Sistema:* DGD — Sistema de gerenciamento de desastres
**Tipo de documento:** definição do fluxo do gerenciando de desastres
**Objetivo:** definir o padrão das páginas, a hierarquia de navegação, a distribuição dos elementos de interface e a lógica de circulação do usuário nas áreas de cadastros dos desastres.
**Publico alvo:**  Defesa Civil do Pará 
**Gestor e Operador do sistema:** Defesa Civil do estado do Pará - CEDEC-PA
**Descrição:** A Defesa Civil do Pará utilizará o sistema para cadastrar os desastres ocorridos no estado do Pará e fazer o gerenciamento das vigências dos decretos, do prazo da homologação junto a PGE - Procuradoria Geral do Estado do Pará. 

**Estilo:** padrão sistema de desasa civil do Pará tendo como base o sistema do plancon.

*Páginas*
1° usuários
2° alterar senha
3° Painel 
4° Decretos

*perfil*
1° Admin
2° Gestor
3° Operador

*Página pública*
1° tela de login

*Tecnologias*
1° PHP
2° Java Script
3° CSS
4° HTML
5° PHPmyadmin

*Ambiente*
1° desenvolvimento:  Wampserver com mysql
2° produção: hostinger com phpmyadmin

***cadastro do Desastre 
* protocolo DGD (automático: sequencial por ano, DGD,  data do desastre,  município)
* município
* UBM atuante
* tipo de decreto (situação de emergência, estado de calamidade pública)
* tipo de desastre : grupo COBRADE, subgrupo COBRADE, tipo COBRADE, subtipo COBRADE, descrição, simbologia (pegar o modelo utilizado no sistema do plancon e com a base de dados (tabela dos desastres do COBRADE que foi desenvolvido no plancon)
* data do desastre
* protocolo S2ID
* número do decreto municipal
* data do decreto municipal
* número do decreto estadual de homologação
* data do decreto de homologação
* homologação (solicitado, não solicitado, pendente - despacho, pendente - parecer, em analise DGD, enviado PGE, homologado, não homologado, não registrado)
* reconhecimento (solicitado, reconhecido, em analise SEDEC, enviado para reconhecimento, aguardando ajuste município, não reconhecido, aguardando análise, registrado, não registrado )
* protocolo PAE/PGE
* data de envio para PGE
* analista (lista de usuários com perfil gestor)
* recursos de ação de resposta (solicitado, plano aprovado, recurso deferido, recursos indeferido, aguardando ajustes, em analise SEDEC, registro de revisão, empenho, não solicitado, não registrado )
* recursos de ação de reconstrução (solicitado, plano aprovado, recurso deferido, recursos indeferido, aguardando ajustes, em analise SEDEC, registro de revisão, empenho, não solicitado, não registrado )
* número de óbitos
* número de feridos
* número de enfermos
* número de desabrigado
* número de desalojado
* número de outros afetados
* total de afetados (automático na soma dos números)
* anexos (decreto municipal, oficio de homologação, parecer estadual, parecer municipal, outros documentos)

***decretos 

* filtros
* cadastro de desastre
* listagem (ordem sequencial por ano, protocolo DGD, município,  tipo de desastre, data do decreto municipal, total de dias do decreto, homologação (editável) , reconhecimento (editável) , total de afetados, total de dias para a PGE, status de envio a PGE (editável) , analista, número do decreto municipal ) listagem com as ações: editar (gestor e admin) , ver detalhe, excluir (gestor e admin) 
* status PGE vem do total de dias para a PGE acima de 7 dias : 
   IFS([HOMOLOGACAO]='Homologado','APROVADO',
AND([DURACAO_PGE_DIAS]<=7,[DURACAO_PGE_DIAS]>0),'NO PRAZO',
[DURACAO_PGE_DIAS]>7,'PENDENTE')

* paginação no máximo 20


**Documentos para serem confeccionados:** 
* 01 - DOCUMENTO TÉCNICO – DEFINIÇÃO CONCEITUAL DO SISTEMA
* 02 – Mapa completo dos módulos, páginas e hierarquia de navegação
* 03 - DOCUMENTO TÉCNICO – PERFIS DE USUÁRIO E MATRIZ DE PERMISSÕES DO SISTEMA
* 04 - DOCUMENTO TÉCNICO – ARQUITETURA MVC COMPLETA DO SISTEMA.
* 05 - DOCUMENTO TÉCNICO – ESTRUTURA COMPLETA DO BANCO DE DADOS
* 06 - DOCUMENTO TÉCNICO – DICIONÁRIO DE DADOS COMPLETO DO SISTEMA
* PROMPT OFICIAL DE COMANDO CODEX - CORTEX


obs. desenvolver um por um os documentos técnicos em md.
obs. desenvolver conforme outros sistema já desenvolvidos como base estrutural e estilo