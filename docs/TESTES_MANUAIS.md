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
| Operador cadastra desastre | Permitido. |
| Operador acessa edicao | Bloqueado. |
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
