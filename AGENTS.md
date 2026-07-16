# Instruções do projeto DGD

## Arquivos para o servidor compartilhado

Ao concluir uma alteração que também seja sincronizada com o deploy:

1. Apresentar uma seção final chamada `Arquivos para subir`.
2. Listar um arquivo por linha, usando o caminho relativo de destino no servidor.
3. Para arquivos da aplicação, iniciar o caminho com `dgd_app/`.
4. Para arquivos públicos, iniciar o caminho com `public_html/`.
5. Não apresentar caminhos absolutos da máquina local nessa lista.

Exemplo:

```text
dgd_app/app/Views/decretos/partials/print_report.php
public_html/assets/css/app.css
```
