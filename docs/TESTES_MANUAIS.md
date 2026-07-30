# Testes Manuais do DGD

## 1. Preparacao

1. Importar banco.
2. Criar Admin inicial.
3. Configurar `.env`.
4. Acessar `/login`.

---

## 2. Autenticacao

| Teste | Resultado esperado |
|---|---|
| Acessar `/login` | Tela publica abre sem autenticar. |
| Login com senha errada | Exibe mensagem generica e registra `login_logs`. |
| 5 falhas de login | Usuario fica bloqueado temporariamente. |
| Login correto com 2FA ja configurado | Redireciona para validacao do codigo autenticador e, apos codigo valido, abre o Painel. |
| Primeiro login sem 2FA e com troca de senha obrigatoria | Redireciona primeiro para cadastro do 2FA e, apos confirmar o codigo, volta para Login. |
| Segundo login do usuario novo | Solicita o codigo do segundo fator e, apos codigo valido, abre somente a tela de alterar senha. |
| Usuario com troca de senha obrigatoria tenta acessar outra pagina | Redireciona para `/alterar-senha` ate cadastrar a nova senha. |
| Logout | Encerra sessao por POST com CSRF. |
| Acessar `/painel` sem sessao | Redireciona para Login. |

---

## 3. Usuarios

| Teste | Resultado esperado |
|---|---|
| Admin acessa `/usuarios` | Permitido. |
| Gestor acessa `/usuarios` | Bloqueado com 403. |
| Operador acessa `/usuarios` | Bloqueado com 403. |
| Criar usuario | Salva com senha hash. |
| Editar usuario | Atualiza dados e audita. |
| Excluir usuario | Exclusao logica. |
| Inativar ultimo Admin ativo | Bloqueado. |

---

## 4. Alterar senha

| Teste | Resultado esperado |
|---|---|
| Senha atual errada | Exibe erro. |
| Nova senha menor que 8 | Exibe erro. |
| Confirmacao divergente | Exibe erro. |
| Nova senha valida | Atualiza hash e audita. |

---

## 5. Decretos

| Teste | Resultado esperado |
|---|---|
| Admin cadastra desastre | Gera protocolo DGD automatico. |
| Abrir formulario de novo cadastro | Exibe cabecalho moderno, secoes numeradas, campos obrigatorios identificados, dados da COMPDEC, COBRADE e anexos na mesma tela. |
| Redimensionar formulario de novo cadastro | Layout se ajusta sem sobrepor textos ou campos em desktop, notebook, tablet e celular. |
| Gestor cadastra desastre | Permitido. |
| Operador acessa `/decretos/novo` ou envia `POST /decretos` | Bloqueado com 403; o botão de novo cadastro não é exibido. |
| Operador acessa edicao | Bloqueado. |
| Operador acessa `/tipos-ajuda` diretamente | Bloqueado com 403 e o item não aparece no menu. |
| Admin ou Gestor acessa `/tipos-ajuda` | Permitido. |
| Listagem | Maximo de 20 registros por pagina. |
| Abrir detalhe de decreto | Exibe layout em secoes modernas, cards de resumo, danos humanos e anexos sem sobreposicao. |
| Protocolo de municipio com acento | Municipio como Belem/Sao Felix do Xingu gera protocolo com BELEM/SAO_FELIX_DO_XINGU, sem underscore indevido no acento. |
| Filtros | Aplicados no backend. |
| Excluir desastre | Exclusao logica e auditoria. |
| Editar status na listagem | Permitido para Admin/Gestor. |
| Editar decreto | Antes de salvar, abre modal de historico com resumo dos campos alterados e campo de observacao. |
| Salvar status na listagem | Antes de salvar, abre modal de historico com o novo status selecionado. |
| Abrir detalhe apos edicao | Historico exibe campo alterado, valor anterior, valor novo, usuario, data/hora e observacao. |

---

## 6. Regras automaticas

| Teste | Resultado esperado |
|---|---|
| Protocolo DGD | Formato `DGD-AAAA-000001-AAAAMMDD-MUNICIPIO`. |
| Total de afetados | Soma obitos, feridos, enfermos, desabrigados, desalojados e outros. |
| Status prazo PGE | Calculado, nao editavel. |
| Status envio PGE | Editavel somente por Admin/Gestor. |
| Informar data de publicação e dias de vigência no novo cadastro | Os dois valores são salvos e exibidos no detalhe e no relatório do decreto. |
| Alterar data de publicação e dias de vigência na edição | Os valores atualizados são persistidos e registrados no histórico. |
| Informar data de publicação futura | O cadastro é recusado com mensagem de validação. |
| Informar vigência decimal, negativa, zero ou maior que 65535 | O cadastro é recusado com mensagem de validação. |
| Publicação hoje com 30 dias de vigência | O formulário exibe `Decreto vigente`, 30 dias restantes e a data final calculada. |
| Data de referência 29 dias após uma publicação com 30 dias de vigência | O sistema exibe `Vence hoje` e 1 dia restante. |
| Dia seguinte ao último dia vigente | O sistema exibe `Decreto vencido` e -1 dia restante, sem passar pelo dia zero. |
| Abrir detalhe, impressão ou PDF | Status, dias restantes e data final usam o mesmo cálculo apresentado no formulário. |
| Abrir a listagem de decretos | Cada card exibe status da vigência, dias restantes e data final, com destaque verde, amarelo ou vermelho. |
| Filtrar por `Decreto vigente`, `Vence hoje` ou `Decreto vencido` | A paginação e os resultados mostram somente o status selecionado. |
| Conferir os indicadores de vigência | Os totais de vigentes, vencem hoje e vencidos respeitam os demais filtros ativos. |
| Executar `2026_07_29_create_decreto_vigencia_view.sql` duas vezes | A segunda execução termina sem erro e mantém a view atualizada. |
| Aplicar qualquer filtro após a migração no deploy | A listagem responde sem erro interno, inclusive para decretos com dias negativos. |
| Abrir detalhe, impressão e PDF | O status da vigência aparece entre os indicadores principais. |
| Abrir o Painel | Os cards exibem totais de decretos vigentes, que vencem hoje e vencidos com cores distintas. |
| Filtrar o Painel por status de vigência | Indicadores, registros recentes, mapa e relatório respeitam o status selecionado. |
| Conferir registros recentes do Painel | Cada card separa Institucional, Status PGE e Vigência; homologação e reconhecimento possuem rótulos próprios e o prazo vencido é apresentado em linguagem natural. |
| Abrir um ponto de desastre no mapa | O modal mostra status da vigência, dias restantes, data final e status PGE do decreto mais recente do município. |
| Abrir ou recarregar a página Painel | Somente a camada Desastres inicia ativa; COMPDECs e UBMs iniciam desmarcadas e podem ser ativadas manualmente. |
| Gerar relatório ou PDF do Painel | Os indicadores de vigência aparecem no resumo e os status institucional, PGE e vigência têm destaque por cor nas tabelas. |

---

## 7. Anexos

| Teste | Resultado esperado |
|---|---|
| Selecionar anexos no formulario de novo cadastro | Arquivos aparecem na lista do tipo de anexo correspondente e sao salvos apos criar o desastre. |
| Selecionar anexo no detalhe do decreto | Arquivo aparece no bloco de upload antes do envio. |
| Arrastar ou colar anexo no detalhe do decreto | Arquivo aparece no bloco de upload e pode ser enviado. |
| Enviar anexo pelo detalhe | Antes de enviar, abre modal de historico exibindo o nome do arquivo e campo de observacao. |
| Anexo enviado | Historico do detalhe registra anexo incluido, usuario, data/hora e observacao. |
| Ver anexo no detalhe | Abre o arquivo em nova aba quando o navegador suportar visualizacao inline. |
| Arrastar anexos para o formulario | Arquivos sao adicionados ao tipo de anexo escolhido antes do envio. |
| Colar imagem/arquivo em um bloco de anexo focado | Arquivo colado aparece na lista e pode ser enviado. |
| Remover arquivo antes de enviar | Arquivo sai da lista e nao e enviado. |
| Upload PDF valido | Salva arquivo fora de `public` e metadados no banco. |
| Upload PHP/JS/HTML | Bloqueado. |
| Upload acima do limite | Bloqueado. |
| Download sem login | Bloqueado. |
| Download com login | Baixa via controller. |
| Excluir anexo | Exclusao logica e auditoria. |

---

## 8. Seguranca

| Teste | Resultado esperado |
|---|---|
| POST sem CSRF | Bloqueado com 419. |
| SQL injection em filtros | Nao altera query; usa prepared statements. |
| XSS em campos textuais | Saida escapada em HTML. |
| Acesso direto a `app/` | Bloqueado pelo servidor Apache. |
| Acesso direto a `storage/` | Bloqueado pelo servidor Apache. |

---

## 9. Pendencias de validacao CEDEC-PA

1. Base COBRADE completa.
2. Lista final de UBMs.
3. Regra final do prazo PGE.
4. Politica de senha institucional.
5. Textos finais da interface.
