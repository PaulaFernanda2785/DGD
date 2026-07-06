# 04 — DOCUMENTO TÉCNICO
# ARQUITETURA MVC COMPLETA DO SISTEMA DGD

**Sistema:** DGD — Sistema de Gerenciamento de Desastres  
**Órgão gestor:** Coordenadoria Estadual de Defesa Civil do Estado do Pará — CEDEC-PA  
**Público-alvo:** Defesa Civil do Pará  
**Tipo de documento:** Arquitetura MVC completa do sistema  
**Versão:** 1.0  
**Formato:** Markdown  
**Status:** Especificação técnica inicial para desenvolvimento em PHP MVC, JavaScript, CSS, HTML e MySQL  

---

## 1. Finalidade do documento

Este documento define a **arquitetura MVC completa** do **DGD — Sistema de Gerenciamento de Desastres**, estabelecendo a organização do código-fonte, a separação de responsabilidades, o fluxo de requisições, a estrutura de pastas, os componentes internos, os controladores, os modelos, as visões, os serviços, as rotas, os mecanismos de segurança, as regras de implantação e os padrões mínimos de desenvolvimento.

O objetivo é orientar o desenvolvimento do sistema com uma base técnica clara, sustentável e compatível com o ambiente previsto:

1. **Desenvolvimento:** Wampserver com MySQL.
2. **Produção:** Hostinger com PHP e phpMyAdmin.
3. **Linguagens e tecnologias:** PHP, JavaScript, CSS, HTML e MySQL.

A arquitetura proposta considera que o DGD será inicialmente um sistema web administrativo, com página pública de login e área autenticada composta por Painel, Decretos, Usuários e Alterar Senha.

---

## 2. Relação com os documentos anteriores

Este documento complementa os documentos técnicos já definidos para o DGD.

| Documento | Relação com a arquitetura MVC |
|---|---|
| 01 — Definição Conceitual do Sistema | Define o propósito, escopo funcional e regras conceituais do DGD. |
| 02 — Mapa Completo dos Módulos, Páginas e Hierarquia de Navegação | Define as páginas, fluxos de navegação, listagens, filtros e ações. |
| 03 — Perfis de Usuário e Matriz de Permissões | Define os perfis Admin, Gestor e Operador, bem como as permissões por módulo, ação e campo. |
| 04 — Arquitetura MVC Completa do Sistema | Define como o sistema será organizado tecnicamente em código, rotas, classes, serviços, views, segurança e implantação. |

A arquitetura deve implementar fielmente os conceitos, páginas e permissões descritos nos documentos anteriores.

---

## 3. Escopo deste documento

Este documento abrange:

1. Padrão arquitetural MVC.
2. Organização física do projeto.
3. Estrutura de pastas e arquivos.
4. Fluxo de requisições.
5. Camadas do sistema.
6. Controladores oficiais.
7. Modelos e repositórios oficiais.
8. Serviços de regra de negócio.
9. Views e componentes de interface.
10. Rotas oficiais.
11. Autenticação e autorização.
12. Controle de sessão.
13. Proteção contra CSRF, XSS, SQL Injection e upload indevido.
14. Tratamento de anexos.
15. Auditoria de ações críticas.
16. Tratamento de erros e logs.
17. Configuração por ambiente.
18. Estratégia de implantação em Wampserver e Hostinger.
19. Padrões mínimos de codificação.
20. Critérios de aceite técnico.

Não fazem parte deste documento:

1. Estrutura completa do banco de dados.
2. Dicionário detalhado de dados.
3. Código-fonte final completo.
4. Manual de usuário.
5. Integração automática com S2ID, PGE ou outros sistemas externos.
6. Implementação de API pública.
7. Aplicativo mobile.

A modelagem completa do banco será tratada no **Documento 05 — Estrutura Completa do Banco de Dados**.

---

## 4. Decisão arquitetural principal

O DGD deverá ser desenvolvido em **PHP com arquitetura MVC**, com separação explícita entre:

| Camada | Responsabilidade |
|---|---|
| Model | Representar acesso e manipulação dos dados. |
| View | Exibir HTML, formulários, listagens, mensagens e componentes visuais. |
| Controller | Receber requisições, validar fluxo, acionar serviços e retornar views ou redirecionamentos. |
| Service | Concentrar regras de negócio críticas. |
| Repository | Concentrar consultas SQL e persistência de dados. |
| Core | Fornecer infraestrutura básica: roteamento, conexão, sessão, autenticação, permissões e renderização. |

A arquitetura adotada não deve colocar regra de negócio diretamente em arquivos HTML, nem consultas SQL diretamente em views. Também não deve concentrar toda a regra dentro dos controllers. O controller deve coordenar o fluxo, mas as regras críticas devem permanecer em serviços próprios.

---

## 5. Visão geral da arquitetura

```text
Navegador do usuário
        ↓
public/index.php
        ↓
Router
        ↓
Middlewares
        ├── Sessão
        ├── Autenticação
        ├── Permissão
        └── CSRF
        ↓
Controller
        ↓
Service
        ↓
Repository / Model
        ↓
Database MySQL
        ↓
Controller
        ↓
View
        ↓
HTML + CSS + JavaScript
        ↓
Navegador do usuário
```

Fluxo simplificado:

1. O usuário acessa uma URL.
2. O arquivo `public/index.php` recebe a requisição.
3. O `Router` identifica a rota solicitada.
4. Os middlewares verificam sessão, autenticação, permissão e token CSRF.
5. O controller responsável é executado.
6. O controller chama serviços de negócio quando necessário.
7. Os serviços consultam ou gravam dados por meio de repositories/models.
8. O resultado é enviado para uma view.
9. A view renderiza a interface HTML.
10. O navegador recebe a resposta final.

---

## 6. Princípios técnicos obrigatórios

A arquitetura do DGD deverá seguir os seguintes princípios:

1. **Separação de responsabilidades:** cada camada deve ter função clara.
2. **Negação por padrão:** páginas e ações devem ser bloqueadas até que haja permissão explícita.
3. **Regra crítica no backend:** totais automáticos, prazo PGE, permissões e status calculados não podem depender apenas de JavaScript.
4. **Consultas parametrizadas:** toda consulta ao banco deve usar PDO com prepared statements.
5. **Senhas com hash seguro:** senhas não podem ser gravadas em texto puro.
6. **Controle de sessão:** páginas internas devem exigir autenticação.
7. **Proteção CSRF:** formulários de gravação devem possuir token de segurança.
8. **Escapamento de saída:** todo dado exibido em HTML deve ser escapado.
9. **Upload controlado:** anexos devem ser validados por tipo, tamanho, extensão e permissão.
10. **Auditoria de ações críticas:** edições, exclusões, alterações de status e login devem ser registradas.
11. **Ambientes separados:** desenvolvimento e produção devem usar arquivos de configuração próprios.
12. **Compatibilidade com hospedagem compartilhada:** a aplicação deve funcionar em Hostinger sem depender obrigatoriamente de servidor dedicado.
13. **Baixo acoplamento:** regras como protocolo DGD e status PGE devem estar isoladas em serviços próprios.
14. **Manutenção simples:** nomes de arquivos, classes e rotas devem ser previsíveis.

---

## 7. Tecnologias oficiais do projeto

| Camada | Tecnologia recomendada |
|---|---|
| Backend | PHP 8.x compatível com o servidor de produção |
| Banco de dados | MySQL ou MariaDB |
| Administração do banco | phpMyAdmin |
| Frontend | HTML5, CSS3 e JavaScript |
| Servidor local | Wampserver |
| Produção | Hostinger |
| Acesso ao banco | PDO |
| Autenticação | Sessão PHP com hash de senha |
| Upload de arquivos | Upload controlado via PHP |
| Rotas | Front controller com `.htaccess` ou fallback por query string |
| Estilo visual | Padrão institucional inspirado no sistema PLANCON |

### 7.1 Uso de bibliotecas externas

A versão inicial pode ser desenvolvida sem framework pesado. Essa decisão reduz dependência técnica e facilita publicação em hospedagem compartilhada.

O uso de **Composer** é recomendável, mas não obrigatório. Caso o ambiente de produção não suporte instalação via terminal, o sistema deverá possuir autoload próprio ou o diretório `vendor` deverá ser enviado junto com o projeto, quando houver dependências externas.

Para a versão inicial, recomenda-se evitar dependências desnecessárias.

---

## 8. Estrutura geral de diretórios

A estrutura recomendada é:

```text
dgd/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Helpers/
│   ├── Middlewares/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   └── Views/
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── permissions.php
│   ├── routes.php
│   ├── upload.php
│   └── status.php
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── backups/
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       ├── js/
│       ├── img/
│       └── icons/
│
├── storage/
│   ├── logs/
│   ├── uploads/
│   │   └── decretos/
│   ├── cache/
│   └── tmp/
│
├── vendor/
│
├── .env
├── .env.example
├── composer.json
└── README.md
```

### 8.1 Observação sobre hospedagem compartilhada

Em produção, o diretório público do servidor deve apontar preferencialmente para:

```text
/public
```

Caso a hospedagem use `public_html`, a publicação recomendada será:

```text
/home/usuario/dgd-app/
├── app/
├── config/
├── database/
├── storage/
└── public_html/
    ├── index.php
    ├── .htaccess
    └── assets/
```

Se não for possível manter `app`, `config` e `storage` fora da raiz pública, deverá haver bloqueio por `.htaccess` para impedir acesso direto a arquivos sensíveis.

---

## 9. Estrutura detalhada da pasta `app`

```text
app/
├── Controllers/
│   ├── AuthController.php
│   ├── PainelController.php
│   ├── DecretoController.php
│   ├── UsuarioController.php
│   ├── SenhaController.php
│   ├── AnexoController.php
│   └── CobradeController.php
│
├── Core/
│   ├── App.php
│   ├── Router.php
│   ├── Controller.php
│   ├── Model.php
│   ├── Database.php
│   ├── Request.php
│   ├── Response.php
│   ├── View.php
│   ├── Session.php
│   ├── Auth.php
│   ├── Permission.php
│   ├── Csrf.php
│   ├── Validator.php
│   └── Logger.php
│
├── Helpers/
│   ├── DateHelper.php
│   ├── FormatHelper.php
│   ├── HtmlHelper.php
│   ├── StatusHelper.php
│   └── UrlHelper.php
│
├── Middlewares/
│   ├── AuthMiddleware.php
│   ├── GuestMiddleware.php
│   ├── PermissionMiddleware.php
│   └── CsrfMiddleware.php
│
├── Models/
│   ├── Usuario.php
│   ├── Perfil.php
│   ├── Decreto.php
│   ├── Municipio.php
│   ├── Ubm.php
│   ├── Cobrade.php
│   ├── Anexo.php
│   └── Auditoria.php
│
├── Repositories/
│   ├── UsuarioRepository.php
│   ├── PerfilRepository.php
│   ├── DecretoRepository.php
│   ├── MunicipioRepository.php
│   ├── UbmRepository.php
│   ├── CobradeRepository.php
│   ├── AnexoRepository.php
│   └── AuditoriaRepository.php
│
├── Services/
│   ├── AuthService.php
│   ├── UsuarioService.php
│   ├── DecretoService.php
│   ├── ProtocoloDgdService.php
│   ├── PgePrazoService.php
│   ├── AfetadosService.php
│   ├── HomologacaoService.php
│   ├── ReconhecimentoService.php
│   ├── RecursoService.php
│   ├── AnexoService.php
│   ├── CobradeService.php
│   ├── PainelService.php
│   └── AuditoriaService.php
│
└── Views/
    ├── layouts/
    ├── components/
    ├── auth/
    ├── painel/
    ├── decretos/
    ├── usuarios/
    ├── senha/
    └── errors/
```

---

## 10. Responsabilidade de cada camada

### 10.1 `Controllers`

Os controllers recebem a requisição, validam o fluxo básico, chamam serviços e retornam uma view, redirect ou resposta JSON.

O controller não deve conter:

1. SQL direto.
2. HTML extenso.
3. Regras complexas de negócio.
4. Cálculos centrais como protocolo DGD, total de afetados ou status PGE.

Exemplo de responsabilidades adequadas de um controller:

```text
DecretoController
├── index()        → listar registros
├── create()       → exibir formulário de cadastro
├── store()        → salvar novo registro
├── show()         → exibir detalhe
├── edit()         → exibir formulário de edição
├── update()       → atualizar registro
├── destroy()      → exclusão lógica
└── updateStatus() → edição rápida de status permitidos
```

### 10.2 `Services`

Os services concentram regras de negócio.

Exemplos:

| Service | Responsabilidade |
|---|---|
| `AuthService` | Autenticar usuário, validar senha e encerrar sessão. |
| `DecretoService` | Orquestrar cadastro, edição, exclusão e validação de desastre/decreto. |
| `ProtocoloDgdService` | Gerar protocolo automático sequencial por ano. |
| `PgePrazoService` | Calcular duração PGE e status de prazo PGE. |
| `AfetadosService` | Calcular total automático de afetados. |
| `AnexoService` | Validar, gravar, listar e excluir anexos. |
| `AuditoriaService` | Registrar ações críticas. |
| `PainelService` | Consolidar indicadores do painel inicial. |
| `CobradeService` | Controlar hierarquia COBRADE e consulta dinâmica. |

### 10.3 `Repositories`

Os repositories concentram consultas SQL.

Exemplo:

```text
DecretoRepository
├── paginate($filtros, $pagina, $limite)
├── findById($id)
├── create($dados)
├── update($id, $dados)
├── softDelete($id)
├── nextSequenceByYear($ano)
├── updateStatusHomologacao($id, $status)
├── updateStatusReconhecimento($id, $status)
└── countPendenciasPge()
```

### 10.4 `Models`

Os models representam entidades do sistema e podem conter atributos, conversões simples e regras locais de consistência.

Exemplo de entidades:

1. Usuario.
2. Perfil.
3. Decreto.
4. Município.
5. UBM.
6. COBRADE.
7. Anexo.
8. Auditoria.

A modelagem exata das tabelas será definida no Documento 05.

### 10.5 `Views`

As views são arquivos responsáveis pela apresentação da interface.

As views podem conter:

1. HTML.
2. Chamadas a componentes visuais.
3. Exibição de variáveis escapadas.
4. Laços simples de apresentação.
5. Condicionais simples para exibir botões conforme permissão.

As views não devem conter:

1. SQL.
2. Regras de negócio complexas.
3. Cálculos de status críticos.
4. Validação de permissão exclusiva.

A view pode esconder um botão, mas o backend deve continuar bloqueando a rota correspondente.

---

## 11. Arquitetura das views

Estrutura recomendada:

```text
app/Views/
├── layouts/
│   ├── public.php
│   └── app.php
│
├── components/
│   ├── header.php
│   ├── sidebar.php
│   ├── breadcrumb.php
│   ├── flash.php
│   ├── pagination.php
│   ├── modal_confirmacao.php
│   ├── status_badge.php
│   ├── form_errors.php
│   └── table_actions.php
│
├── auth/
│   └── login.php
│
├── painel/
│   └── index.php
│
├── decretos/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── show.php
│   └── partials/
│       ├── filtros.php
│       ├── form_identificacao.php
│       ├── form_cobrade.php
│       ├── form_decreto.php
│       ├── form_homologacao.php
│       ├── form_reconhecimento.php
│       ├── form_pge.php
│       ├── form_recursos.php
│       ├── form_afetados.php
│       └── form_anexos.php
│
├── usuarios/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── show.php
│
├── senha/
│   └── edit.php
│
└── errors/
    ├── 403.php
    ├── 404.php
    └── 500.php
```

### 11.1 Layout público

Utilizado pela tela de login.

Deve conter:

1. Cabeçalho simples.
2. Identidade visual do DGD/Defesa Civil.
3. Formulário centralizado.
4. Mensagens de erro.
5. Rodapé institucional.

### 11.2 Layout autenticado

Utilizado pelas páginas internas.

Deve conter:

1. Cabeçalho superior.
2. Menu lateral ou superior.
3. Identificação do usuário logado.
4. Link para alterar senha.
5. Botão de sair.
6. Breadcrumb.
7. Área de conteúdo.
8. Mensagens de sucesso, erro, alerta e informação.
9. Rodapé institucional.

### 11.3 Componentes reutilizáveis

Componentes obrigatórios:

| Componente | Finalidade |
|---|---|
| `flash.php` | Exibir mensagens temporárias de sucesso, erro e alerta. |
| `pagination.php` | Controlar paginação de listagens. |
| `status_badge.php` | Exibir status com padronização visual. |
| `form_errors.php` | Exibir erros de validação. |
| `table_actions.php` | Padronizar botões de ação em tabelas. |
| `modal_confirmacao.php` | Confirmar ações críticas como exclusão. |

---

## 12. Estrutura da pasta `public`

```text
public/
├── index.php
├── .htaccess
└── assets/
    ├── css/
    │   ├── base.css
    │   ├── layout.css
    │   ├── forms.css
    │   ├── tables.css
    │   ├── buttons.css
    │   ├── status.css
    │   └── responsive.css
    │
    ├── js/
    │   ├── app.js
    │   ├── csrf.js
    │   ├── decretos.js
    │   ├── usuarios.js
    │   ├── cobrade.js
    │   ├── anexos.js
    │   └── masks.js
    │
    ├── img/
    │   ├── logo-dgd.png
    │   ├── logo-defesa-civil-pa.png
    │   └── brasao-pa.png
    │
    └── icons/
```

### 12.1 Regra de segurança da pasta `public`

Somente a pasta `public` deve estar acessível diretamente pelo navegador.

Não devem ficar públicos:

1. Arquivos de configuração.
2. Arquivos `.env`.
3. Classes PHP internas.
4. Backups de banco.
5. Logs.
6. Anexos sensíveis.
7. Migrations.
8. Seeders.

---

## 13. Arquivo de entrada `public/index.php`

O arquivo `public/index.php` será o ponto único de entrada da aplicação.

Responsabilidades:

1. Iniciar configuração básica.
2. Carregar autoload.
3. Iniciar sessão.
4. Carregar rotas.
5. Executar o roteador.
6. Retornar a resposta.

Estrutura mínima recomendada:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/App.php';
require_once __DIR__ . '/../app/Core/Autoload.php';

use App\Core\App;

$app = new App();
$app->run();
```

Se Composer for utilizado, o carregamento pode ser:

```php
require_once __DIR__ . '/../vendor/autoload.php';
```

---

## 14. Arquivo `.htaccess`

O arquivo `.htaccess` deve redirecionar requisições para `public/index.php`.

Exemplo recomendado:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]

Options -Indexes
```

Caso o servidor não permita reescrita amigável, a aplicação deve aceitar fallback por query string:

```text
/index.php?r=decretos/index
/index.php?r=decretos/create
/index.php?r=usuarios/index
```

A decisão final dependerá da configuração disponível na hospedagem de produção.

---

## 15. Rotas oficiais do sistema

As rotas devem ser centralizadas no arquivo:

```text
config/routes.php
```

### 15.1 Rotas públicas

| Método | Rota | Controller | Ação | Acesso |
|---|---|---|---|---|
| GET | `/login` | `AuthController` | `login` | Público |
| POST | `/login` | `AuthController` | `authenticate` | Público com CSRF |
| POST | `/logout` | `AuthController` | `logout` | Autenticado |

### 15.2 Rotas autenticadas principais

| Método | Rota | Controller | Ação | Permissão |
|---|---|---|---|---|
| GET | `/painel` | `PainelController` | `index` | `painel.visualizar` |
| GET | `/decretos` | `DecretoController` | `index` | `decretos.visualizar` |
| GET | `/decretos/novo` | `DecretoController` | `create` | `decretos.criar` |
| POST | `/decretos` | `DecretoController` | `store` | `decretos.criar` |
| GET | `/decretos/{id}` | `DecretoController` | `show` | `decretos.detalhar` |
| GET | `/decretos/{id}/editar` | `DecretoController` | `edit` | `decretos.editar` |
| POST | `/decretos/{id}/editar` | `DecretoController` | `update` | `decretos.editar` |
| POST | `/decretos/{id}/excluir` | `DecretoController` | `destroy` | `decretos.excluir` |
| POST | `/decretos/{id}/status` | `DecretoController` | `updateStatus` | `decretos.editar_status` |

### 15.3 Rotas de anexos

| Método | Rota | Controller | Ação | Permissão |
|---|---|---|---|---|
| POST | `/decretos/{id}/anexos` | `AnexoController` | `store` | `anexos.enviar` |
| GET | `/anexos/{id}/download` | `AnexoController` | `download` | `anexos.visualizar` |
| POST | `/anexos/{id}/excluir` | `AnexoController` | `destroy` | `anexos.excluir` |

### 15.4 Rotas de usuários

| Método | Rota | Controller | Ação | Permissão |
|---|---|---|---|---|
| GET | `/usuarios` | `UsuarioController` | `index` | `usuarios.visualizar` |
| GET | `/usuarios/novo` | `UsuarioController` | `create` | `usuarios.criar` |
| POST | `/usuarios` | `UsuarioController` | `store` | `usuarios.criar` |
| GET | `/usuarios/{id}` | `UsuarioController` | `show` | `usuarios.detalhar` |
| GET | `/usuarios/{id}/editar` | `UsuarioController` | `edit` | `usuarios.editar` |
| POST | `/usuarios/{id}/editar` | `UsuarioController` | `update` | `usuarios.editar` |
| POST | `/usuarios/{id}/inativar` | `UsuarioController` | `disable` | `usuarios.inativar` |
| POST | `/usuarios/{id}/reativar` | `UsuarioController` | `enable` | `usuarios.reativar` |

### 15.5 Rotas de senha

| Método | Rota | Controller | Ação | Permissão |
|---|---|---|---|---|
| GET | `/alterar-senha` | `SenhaController` | `edit` | Usuário autenticado |
| POST | `/alterar-senha` | `SenhaController` | `update` | Usuário autenticado |

### 15.6 Rotas auxiliares de COBRADE

| Método | Rota | Controller | Ação | Finalidade |
|---|---|---|---|---|
| GET | `/cobrade/grupos` | `CobradeController` | `grupos` | Listar grupos COBRADE. |
| GET | `/cobrade/subgrupos` | `CobradeController` | `subgrupos` | Listar subgrupos por grupo. |
| GET | `/cobrade/tipos` | `CobradeController` | `tipos` | Listar tipos por subgrupo. |
| GET | `/cobrade/subtipos` | `CobradeController` | `subtipos` | Listar subtipos por tipo. |
| GET | `/cobrade/{id}/detalhe` | `CobradeController` | `detalhe` | Retornar descrição e simbologia. |

Essas rotas podem retornar JSON para uso em JavaScript no formulário de cadastro de desastre.

---

## 16. Exemplo de configuração de rotas

Exemplo conceitual para `config/routes.php`:

```php
<?php

use App\Controllers\AuthController;
use App\Controllers\PainelController;
use App\Controllers\DecretoController;
use App\Controllers\UsuarioController;
use App\Controllers\SenhaController;
use App\Controllers\AnexoController;
use App\Controllers\CobradeController;

return [
    ['GET',  '/login', [AuthController::class, 'login'], ['guest']],
    ['POST', '/login', [AuthController::class, 'authenticate'], ['guest', 'csrf']],
    ['POST', '/logout', [AuthController::class, 'logout'], ['auth', 'csrf']],

    ['GET', '/painel', [PainelController::class, 'index'], ['auth', 'perm:painel.visualizar']],

    ['GET',  '/decretos', [DecretoController::class, 'index'], ['auth', 'perm:decretos.visualizar']],
    ['GET',  '/decretos/novo', [DecretoController::class, 'create'], ['auth', 'perm:decretos.criar']],
    ['POST', '/decretos', [DecretoController::class, 'store'], ['auth', 'csrf', 'perm:decretos.criar']],
    ['GET',  '/decretos/{id}', [DecretoController::class, 'show'], ['auth', 'perm:decretos.detalhar']],
    ['GET',  '/decretos/{id}/editar', [DecretoController::class, 'edit'], ['auth', 'perm:decretos.editar']],
    ['POST', '/decretos/{id}/editar', [DecretoController::class, 'update'], ['auth', 'csrf', 'perm:decretos.editar']],
    ['POST', '/decretos/{id}/excluir', [DecretoController::class, 'destroy'], ['auth', 'csrf', 'perm:decretos.excluir']],
    ['POST', '/decretos/{id}/status', [DecretoController::class, 'updateStatus'], ['auth', 'csrf', 'perm:decretos.editar_status']],

    ['POST', '/decretos/{id}/anexos', [AnexoController::class, 'store'], ['auth', 'csrf', 'perm:anexos.enviar']],
    ['GET',  '/anexos/{id}/download', [AnexoController::class, 'download'], ['auth', 'perm:anexos.visualizar']],
    ['POST', '/anexos/{id}/excluir', [AnexoController::class, 'destroy'], ['auth', 'csrf', 'perm:anexos.excluir']],

    ['GET',  '/usuarios', [UsuarioController::class, 'index'], ['auth', 'perm:usuarios.visualizar']],
    ['GET',  '/usuarios/novo', [UsuarioController::class, 'create'], ['auth', 'perm:usuarios.criar']],
    ['POST', '/usuarios', [UsuarioController::class, 'store'], ['auth', 'csrf', 'perm:usuarios.criar']],
    ['GET',  '/usuarios/{id}', [UsuarioController::class, 'show'], ['auth', 'perm:usuarios.detalhar']],
    ['GET',  '/usuarios/{id}/editar', [UsuarioController::class, 'edit'], ['auth', 'perm:usuarios.editar']],
    ['POST', '/usuarios/{id}/editar', [UsuarioController::class, 'update'], ['auth', 'csrf', 'perm:usuarios.editar']],

    ['GET',  '/alterar-senha', [SenhaController::class, 'edit'], ['auth']],
    ['POST', '/alterar-senha', [SenhaController::class, 'update'], ['auth', 'csrf']],

    ['GET', '/cobrade/grupos', [CobradeController::class, 'grupos'], ['auth']],
    ['GET', '/cobrade/subgrupos', [CobradeController::class, 'subgrupos'], ['auth']],
    ['GET', '/cobrade/tipos', [CobradeController::class, 'tipos'], ['auth']],
    ['GET', '/cobrade/subtipos', [CobradeController::class, 'subtipos'], ['auth']],
];
```

---

## 17. Controladores oficiais

### 17.1 `AuthController`

Responsável pela autenticação.

Métodos:

| Método | Finalidade |
|---|---|
| `login()` | Exibir tela pública de login. |
| `authenticate()` | Validar credenciais, iniciar sessão e redirecionar para o painel. |
| `logout()` | Encerrar sessão e retornar ao login. |

Fluxo de autenticação:

```text
Usuário informa login e senha
        ↓
AuthController::authenticate()
        ↓
AuthService::attempt()
        ↓
UsuarioRepository::findByEmailOrLogin()
        ↓
password_verify()
        ↓
Sessão criada
        ↓
Redirecionamento para /painel
```

### 17.2 `PainelController`

Responsável pela página inicial autenticada.

Método principal:

```text
index()
```

Dados esperados:

1. Total de desastres cadastrados.
2. Total de homologações pendentes.
3. Total de reconhecimentos pendentes.
4. Total de processos enviados à PGE.
5. Total de processos pendentes de prazo PGE.
6. Desastres recentes.
7. Pendências por analista.
8. Total de afetados no período filtrado.

### 17.3 `DecretoController`

Responsável pelo módulo central do DGD.

Métodos obrigatórios:

| Método | Responsabilidade |
|---|---|
| `index()` | Listar desastres/decretos com filtros e paginação. |
| `create()` | Exibir formulário de cadastro. |
| `store()` | Validar e gravar novo desastre/decreto. |
| `show($id)` | Exibir detalhe completo. |
| `edit($id)` | Exibir formulário de edição. |
| `update($id)` | Atualizar registro conforme permissão. |
| `destroy($id)` | Executar exclusão lógica. |
| `updateStatus($id)` | Atualizar status editáveis permitidos na listagem. |

O método `store()` deve acionar obrigatoriamente:

1. `ProtocoloDgdService`.
2. `AfetadosService`.
3. `PgePrazoService`, quando houver dados suficientes.
4. `DecretoService`.
5. `AnexoService`, quando houver anexos.
6. `AuditoriaService`.

### 17.4 `UsuarioController`

Responsável por usuários do sistema.

Métodos:

| Método | Finalidade |
|---|---|
| `index()` | Listar usuários. |
| `create()` | Exibir formulário de novo usuário. |
| `store()` | Gravar usuário. |
| `show($id)` | Exibir detalhe do usuário. |
| `edit($id)` | Exibir edição. |
| `update($id)` | Atualizar dados permitidos. |
| `disable($id)` | Inativar usuário. |
| `enable($id)` | Reativar usuário. |

A exclusão física de usuário não é recomendada. O correto é usar inativação.

### 17.5 `SenhaController`

Responsável pela alteração da senha do usuário autenticado.

Métodos:

| Método | Finalidade |
|---|---|
| `edit()` | Exibir formulário de alteração de senha. |
| `update()` | Validar senha atual, nova senha e confirmação. |

Regras:

1. Exigir senha atual.
2. Confirmar nova senha.
3. Aplicar política mínima de segurança.
4. Gravar hash, não texto puro.
5. Registrar auditoria de alteração de senha.

### 17.6 `AnexoController`

Responsável pelos documentos anexados aos registros.

Métodos:

| Método | Finalidade |
|---|---|
| `store($decretoId)` | Enviar anexo vinculado a um desastre/decreto. |
| `download($id)` | Baixar anexo mediante permissão. |
| `destroy($id)` | Excluir logicamente ou inativar anexo. |

Tipos de anexo previstos:

1. Decreto municipal.
2. Ofício de homologação.
3. Parecer estadual.
4. Parecer municipal.
5. Outros documentos.

### 17.7 `CobradeController`

Responsável por consultas auxiliares da classificação COBRADE.

Métodos:

| Método | Finalidade |
|---|---|
| `grupos()` | Retornar grupos COBRADE. |
| `subgrupos()` | Retornar subgrupos por grupo. |
| `tipos()` | Retornar tipos por subgrupo. |
| `subtipos()` | Retornar subtipos por tipo. |
| `detalhe($id)` | Retornar descrição, código e simbologia. |

Preferencialmente, esse controller deve retornar JSON para uso no formulário de cadastro.

---

## 18. Serviços oficiais

### 18.1 `AuthService`

Responsável por autenticação.

Funções recomendadas:

```text
attempt($login, $senha)
logout()
user()
check()
id()
```

Regras:

1. Verificar usuário ativo.
2. Usar `password_verify`.
3. Regenerar ID da sessão após login.
4. Registrar login bem-sucedido e falha de login.
5. Redirecionar usuário autenticado para `/painel`.

### 18.2 `UsuarioService`

Responsável pela criação, edição, inativação e validação de usuários.

Regras:

1. Login/e-mail não pode duplicar.
2. Perfil deve ser válido.
3. Senha inicial deve ser armazenada como hash.
4. Usuário inativo não pode acessar o sistema.
5. Admin não deve inativar a si mesmo sem regra de proteção.

### 18.3 `DecretoService`

Serviço orquestrador do cadastro de desastre/decreto.

Responsabilidades:

1. Validar campos obrigatórios.
2. Solicitar geração de protocolo DGD.
3. Calcular total de afetados.
4. Calcular total de dias do decreto.
5. Calcular duração PGE.
6. Calcular status de prazo PGE.
7. Persistir dados.
8. Encaminhar anexos para o `AnexoService`.
9. Registrar auditoria.
10. Aplicar permissões de edição por perfil.

### 18.4 `ProtocoloDgdService`

Responsável pela geração automática do protocolo DGD.

Formato recomendado:

```text
DGD-AAAA-000001-AAAAMMDD-MUNICIPIO
```

Exemplo:

```text
DGD-2026-000001-20260315-BELEM
```

Componentes:

| Parte | Origem |
|---|---|
| `DGD` | Prefixo fixo do sistema. |
| `AAAA` | Ano da data do desastre. |
| `000001` | Sequencial anual. |
| `AAAAMMDD` | Data do desastre. |
| `MUNICIPIO` | Nome normalizado do município. |

Regras técnicas:

1. O sequencial deve reiniciar a cada ano.
2. O sequencial deve ser controlado no banco de dados para evitar duplicidade.
3. O protocolo não deve ser editável pelo usuário.
4. Em caso de alteração posterior da data do desastre ou município, o protocolo não deve ser alterado automaticamente sem decisão administrativa expressa.
5. A geração deve ocorrer no backend, dentro de transação.

### 18.5 `AfetadosService`

Responsável pelo cálculo automático de afetados.

Campos de entrada:

1. Número de óbitos.
2. Número de feridos.
3. Número de enfermos.
4. Número de desabrigados.
5. Número de desalojados.
6. Número de outros afetados.

Regra:

```text
total_afetados = obitos
                + feridos
                + enfermos
                + desabrigados
                + desalojados
                + outros_afetados
```

Regras técnicas:

1. Valores nulos devem ser tratados como zero.
2. Valores negativos não devem ser aceitos.
3. O cálculo deve ser repetido no backend antes de gravar.
4. O JavaScript pode apenas auxiliar visualmente.

### 18.6 `PgePrazoService`

Responsável pelo cálculo do prazo relacionado à PGE.

Campos envolvidos:

1. Homologação.
2. Protocolo PAE/PGE.
3. Data de envio para PGE.
4. Data de decreto municipal.
5. Data de homologação.
6. Total de dias para a PGE.
7. Status de prazo PGE.

Regra informada para status PGE:

```text
SE homologação = Homologado
    status_prazo_pge = APROVADO
SENÃO SE duração_pge_dias <= 7 E duração_pge_dias > 0
    status_prazo_pge = NO PRAZO
SENÃO SE duração_pge_dias > 7
    status_prazo_pge = PENDENTE
```

Recomendação técnica adicional:

```text
SENÃO
    status_prazo_pge = NÃO REGISTRADO
```

Esse fallback evita que registros sem data ou sem contagem fiquem com status vazio.

Ponto crítico: o marco inicial e o marco final do cálculo de `duracao_pge_dias` devem ser definidos no Documento 05 ou em regra complementar validada pela CEDEC-PA. A arquitetura deve deixar esse cálculo isolado no `PgePrazoService`, para permitir ajuste sem alterar controllers ou views.

### 18.7 `HomologacaoService`

Responsável por validar e atualizar status de homologação.

Status previstos:

1. Solicitado.
2. Não solicitado.
3. Pendente - despacho.
4. Pendente - parecer.
5. Em análise DGD.
6. Enviado PGE.
7. Homologado.
8. Não homologado.
9. Não registrado.

Regras:

1. Somente perfis autorizados podem alterar.
2. Alteração deve gerar auditoria.
3. Status homologado pode influenciar status PGE.
4. Mudança crítica deve registrar data, usuário e valor anterior.

### 18.8 `ReconhecimentoService`

Responsável por validar e atualizar status de reconhecimento.

Status previstos:

1. Solicitado.
2. Reconhecido.
3. Em análise SEDEC.
4. Enviado para reconhecimento.
5. Aguardando ajuste município.
6. Não reconhecido.
7. Aguardando análise.
8. Registrado.
9. Não registrado.

### 18.9 `RecursoService`

Responsável pelos status de recursos de ação de resposta e reconstrução.

Status previstos:

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

### 18.10 `AnexoService`

Responsável pelo upload e controle de documentos.

Responsabilidades:

1. Validar permissão.
2. Validar extensão.
3. Validar MIME type.
4. Validar tamanho máximo.
5. Gerar nome físico seguro.
6. Gravar arquivo em `storage/uploads`.
7. Gravar metadados no banco.
8. Impedir acesso direto sem permissão.
9. Registrar auditoria.

### 18.11 `AuditoriaService`

Responsável por registrar eventos críticos.

Eventos mínimos:

1. Login bem-sucedido.
2. Falha de login.
3. Logout.
4. Cadastro de desastre/decreto.
5. Edição de desastre/decreto.
6. Exclusão lógica de desastre/decreto.
7. Alteração de homologação.
8. Alteração de reconhecimento.
9. Alteração de status PGE.
10. Upload de anexo.
11. Exclusão de anexo.
12. Cadastro de usuário.
13. Edição de usuário.
14. Inativação de usuário.
15. Alteração de senha.

---

## 19. Repositories oficiais

### 19.1 `DecretoRepository`

Métodos esperados:

```text
paginate(array $filtros, int $pagina, int $limite): array
count(array $filtros): int
findById(int $id): ?array
create(array $dados): int
update(int $id, array $dados): bool
softDelete(int $id, int $usuarioId): bool
nextSequenceByYear(int $ano): int
existsProtocolo(string $protocolo): bool
updateStatus(int $id, string $campo, string $valor): bool
```

### 19.2 `UsuarioRepository`

Métodos esperados:

```text
paginate(array $filtros, int $pagina, int $limite): array
findById(int $id): ?array
findByLogin(string $login): ?array
findByEmail(string $email): ?array
create(array $dados): int
update(int $id, array $dados): bool
updateSenha(int $id, string $hash): bool
disable(int $id): bool
enable(int $id): bool
```

### 19.3 `CobradeRepository`

Métodos esperados:

```text
getGrupos(): array
getSubgruposByGrupo(int $grupoId): array
getTiposBySubgrupo(int $subgrupoId): array
getSubtiposByTipo(int $tipoId): array
findById(int $id): ?array
```

### 19.4 `AnexoRepository`

Métodos esperados:

```text
findById(int $id): ?array
listByDecreto(int $decretoId): array
create(array $dados): int
softDelete(int $id, int $usuarioId): bool
```

### 19.5 `AuditoriaRepository`

Métodos esperados:

```text
create(array $dados): int
listByRegistro(string $entidade, int $entidadeId): array
listByUsuario(int $usuarioId): array
```

---

## 20. Configurações do sistema

### 20.1 `config/app.php`

Deve conter configurações gerais:

```php
<?php

return [
    'name' => 'DGD — Sistema de Gerenciamento de Desastres',
    'short_name' => 'DGD',
    'timezone' => 'America/Belem',
    'environment' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'base_url' => env('APP_URL', ''),
    'items_per_page' => 20,
];
```

### 20.2 `config/database.php`

Deve conter conexão com MySQL/MariaDB:

```php
<?php

return [
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'dgd'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

### 20.3 `config/upload.php`

Deve conter regras para anexos:

```php
<?php

return [
    'max_size_mb' => 10,
    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
    'path' => __DIR__ . '/../storage/uploads/decretos',
];
```

### 20.4 `config/status.php`

Deve centralizar listas oficiais de status.

```php
<?php

return [
    'homologacao' => [
        'Solicitado',
        'Não solicitado',
        'Pendente - despacho',
        'Pendente - parecer',
        'Em análise DGD',
        'Enviado PGE',
        'Homologado',
        'Não homologado',
        'Não registrado',
    ],

    'reconhecimento' => [
        'Solicitado',
        'Reconhecido',
        'Em análise SEDEC',
        'Enviado para reconhecimento',
        'Aguardando ajuste município',
        'Não reconhecido',
        'Aguardando análise',
        'Registrado',
        'Não registrado',
    ],

    'recursos' => [
        'Solicitado',
        'Plano aprovado',
        'Recurso deferido',
        'Recurso indeferido',
        'Aguardando ajustes',
        'Em análise SEDEC',
        'Registro de revisão',
        'Empenho',
        'Não solicitado',
        'Não registrado',
    ],

    'prazo_pge' => [
        'APROVADO',
        'NO PRAZO',
        'PENDENTE',
        'NÃO REGISTRADO',
    ],
];
```

### 20.5 `config/permissions.php`

Deve centralizar permissões por perfil, compatível com o Documento 03.

Exemplo:

```php
<?php

return [
    'Admin' => ['*'],

    'Gestor' => [
        'painel.visualizar',
        'decretos.visualizar',
        'decretos.detalhar',
        'decretos.criar',
        'decretos.editar',
        'decretos.excluir',
        'decretos.editar_status',
        'anexos.visualizar',
        'anexos.enviar',
        'anexos.excluir',
    ],

    'Operador' => [
        'painel.visualizar',
        'decretos.visualizar',
        'decretos.detalhar',
        'decretos.criar',
        'anexos.visualizar',
        'anexos.enviar',
    ],
];
```

A lista final deverá seguir integralmente o Documento 03.

---

## 21. Conexão com banco de dados

A conexão deve usar PDO.

Estrutura recomendada para `app/Core/Database.php`:

```php
<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$connection;
    }
}
```

Regras obrigatórias:

1. Não usar `mysqli_query` espalhado pelo sistema.
2. Não concatenar valores do usuário em SQL.
3. Usar prepared statements em todas as consultas.
4. Tratar exceções sem expor senha, host ou SQL bruto ao usuário final.

---

## 22. Autenticação

### 22.1 Fluxo de login

```text
GET /login
    ↓
Exibe formulário
    ↓
POST /login
    ↓
Valida CSRF
    ↓
Busca usuário ativo
    ↓
Valida senha com password_verify
    ↓
Regenera sessão
    ↓
Grava dados mínimos na sessão
    ↓
Redireciona para /painel
```

### 22.2 Dados mínimos na sessão

A sessão deve armazenar apenas dados necessários:

```text
usuario_id
usuario_nome
usuario_login
perfil_id
perfil_nome
ultimo_acesso
```

Não armazenar senha, hash de senha ou dados sensíveis desnecessários.

### 22.3 Encerramento de sessão

No logout:

1. Registrar auditoria.
2. Limpar variáveis de sessão.
3. Destruir sessão.
4. Redirecionar para login.

---

## 23. Autorização e permissões

O sistema deve aplicar RBAC conforme Documento 03.

Fluxo:

```text
Usuário autenticado
        ↓
Perfil identificado
        ↓
Permissão solicitada pela rota
        ↓
PermissionService verifica permissão
        ↓
Acesso liberado ou resposta 403
```

A autorização deve ocorrer:

1. Na rota.
2. No controller, para ações sensíveis.
3. No service, para regras específicas de campo.
4. Na view, apenas para exibição ou ocultação de botões.

A view não deve ser a única barreira de segurança.

---

## 24. Middleware de autenticação

Responsável por impedir acesso de usuário não autenticado.

Exemplo lógico:

```php
if (!Auth::check()) {
    redirect('/login');
}
```

Aplicável a:

1. Painel.
2. Decretos.
3. Usuários.
4. Alterar senha.
5. Anexos.
6. COBRADE, quando usado internamente.

---

## 25. Middleware de permissão

Responsável por verificar se o perfil possui a permissão exigida pela rota.

Exemplo lógico:

```php
if (!Permission::can($requiredPermission)) {
    Response::view('errors/403', [], 403);
    exit;
}
```

Resposta esperada para bloqueio:

1. Código HTTP 403.
2. Mensagem clara.
3. Sem revelar detalhes internos.
4. Registro opcional de tentativa negada em auditoria.

---

## 26. Proteção CSRF

Todos os formulários que alteram dados devem conter token CSRF.

Aplicável a:

1. Login.
2. Cadastro de desastre.
3. Edição de desastre.
4. Exclusão de desastre.
5. Edição rápida de status.
6. Upload de anexo.
7. Exclusão de anexo.
8. Cadastro de usuário.
9. Edição de usuário.
10. Inativação/reativação de usuário.
11. Alteração de senha.
12. Logout.

Exemplo de campo oculto:

```php
<input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
```

O backend deve validar o token antes de processar a ação.

---

## 27. Segurança contra XSS

Todo conteúdo exibido na tela deve ser escapado.

Exemplo:

```php
<?= htmlspecialchars($decreto['municipio_nome'], ENT_QUOTES, 'UTF-8'); ?>
```

Campos com maior risco:

1. Município, quando textual.
2. Descrição do desastre.
3. Número de decreto.
4. Protocolo S2ID.
5. Protocolo PAE/PGE.
6. Nome de arquivo enviado.
7. Nome de usuário.
8. Observações administrativas.

---

## 28. Segurança contra SQL Injection

Toda consulta deve usar parâmetros.

Exemplo correto:

```php
$sql = 'SELECT * FROM decretos WHERE municipio_id = :municipio_id';
$stmt = $pdo->prepare($sql);
$stmt->execute(['municipio_id' => $municipioId]);
```

Exemplo proibido:

```php
$sql = "SELECT * FROM decretos WHERE municipio_id = $municipioId";
```

---

## 29. Tratamento de anexos

### 29.1 Local de armazenamento

Os anexos devem ser armazenados preferencialmente fora da pasta pública:

```text
storage/uploads/decretos/
```

Organização recomendada:

```text
storage/uploads/decretos/
├── 2026/
│   ├── 000001/
│   │   ├── decreto_municipal_arquivo_hash.pdf
│   │   ├── oficio_homologacao_arquivo_hash.pdf
│   │   └── outros_documentos_arquivo_hash.pdf
│   └── 000002/
└── 2027/
```

### 29.2 Download protegido

O download não deve apontar diretamente para o caminho físico do arquivo.

Fluxo correto:

```text
GET /anexos/{id}/download
        ↓
AnexoController::download()
        ↓
Verifica autenticação
        ↓
Verifica permissão
        ↓
Busca metadados do anexo
        ↓
Verifica existência do arquivo
        ↓
Envia arquivo com headers seguros
```

### 29.3 Validações obrigatórias de upload

1. Tamanho máximo.
2. Extensão permitida.
3. MIME type permitido.
4. Nome original sanitizado.
5. Nome físico aleatório ou baseado em hash.
6. Bloqueio de arquivos executáveis.
7. Proibição de `.php`, `.phtml`, `.js`, `.html`, `.exe`, `.bat`, `.sh`.
8. Registro do usuário responsável pelo envio.
9. Registro da data e hora de envio.

---

## 30. Auditoria

A auditoria deve registrar ações críticas para rastreabilidade.

### 30.1 Campos mínimos da auditoria

| Campo | Finalidade |
|---|---|
| `id` | Identificador da auditoria. |
| `usuario_id` | Usuário que executou a ação. |
| `acao` | Tipo de ação realizada. |
| `entidade` | Entidade afetada. |
| `entidade_id` | ID do registro afetado. |
| `valor_anterior` | Dados anteriores, quando aplicável. |
| `valor_novo` | Dados novos, quando aplicável. |
| `ip` | IP da requisição. |
| `user_agent` | Navegador/dispositivo. |
| `created_at` | Data e hora da ação. |

### 30.2 Ações obrigatórias auditáveis

| Ação | Obrigatória |
|---|---|
| Login | Sim |
| Falha de login | Sim |
| Logout | Recomendável |
| Cadastro de desastre | Sim |
| Edição de desastre | Sim |
| Exclusão de desastre | Sim |
| Alteração de homologação | Sim |
| Alteração de reconhecimento | Sim |
| Alteração de status PGE | Sim |
| Upload de anexo | Sim |
| Exclusão de anexo | Sim |
| Cadastro de usuário | Sim |
| Edição de usuário | Sim |
| Inativação de usuário | Sim |
| Alteração de senha | Sim |

---

## 31. Exclusão lógica

Registros críticos não devem ser apagados fisicamente do banco na operação normal.

A exclusão deve ser lógica, usando campos como:

```text
deleted_at
deleted_by
ativo
```

Aplicável a:

1. Desastres/decretos.
2. Usuários.
3. Anexos.
4. Registros auxiliares, quando necessário.

A exclusão física só deve ocorrer por procedimento técnico controlado, fora da operação cotidiana.

---

## 32. Regras de negócio implementadas na arquitetura

### 32.1 Protocolo DGD automático

Camada responsável:

```text
ProtocoloDgdService
```

O controller não deve gerar o protocolo.

### 32.2 Total de afetados

Camada responsável:

```text
AfetadosService
```

O JavaScript pode mostrar prévia, mas o cálculo final será do backend.

### 32.3 Status de prazo PGE

Camada responsável:

```text
PgePrazoService
```

A listagem deve apenas exibir o resultado calculado.

### 32.4 Homologação

Camadas responsáveis:

```text
HomologacaoService
DecretoService
AuditoriaService
```

### 32.5 Reconhecimento

Camadas responsáveis:

```text
ReconhecimentoService
DecretoService
AuditoriaService
```

### 32.6 Recursos de resposta e reconstrução

Camada responsável:

```text
RecursoService
```

### 32.7 Permissões por campo

Camada responsável:

```text
PermissionService
DecretoService
```

A interface pode desabilitar campos conforme perfil, mas o backend deve validar novamente.

---

## 33. Paginação

A listagem de decretos deve ter paginação máxima de 20 registros por página, conforme especificação funcional.

Parâmetros:

```text
pagina atual
limite = 20
total de registros
total de páginas
filtros aplicados
```

A paginação deve preservar filtros.

Exemplo:

```text
/decretos?municipio_id=10&homologacao=Pendente&page=2
```

---

## 34. Filtros da listagem de decretos

A camada de controller deve receber os filtros, sanitizar, validar e repassar ao repository.

Filtros esperados:

1. Ano.
2. Protocolo DGD.
3. Município.
4. UBM atuante.
5. Tipo de decreto.
6. Tipo de desastre/COBRADE.
7. Homologação.
8. Reconhecimento.
9. Status PGE.
10. Analista.
11. Número do decreto municipal.
12. Data inicial.
13. Data final.

O repository deve montar a consulta dinamicamente com parâmetros seguros.

---

## 35. Colunas da listagem de decretos

A view `decretos/index.php` deve exibir:

1. Ordem sequencial por ano.
2. Protocolo DGD.
3. Município.
4. Tipo de desastre.
5. Data do decreto municipal.
6. Total de dias do decreto.
7. Homologação editável conforme perfil.
8. Reconhecimento editável conforme perfil.
9. Total de afetados.
10. Total de dias para a PGE.
11. Status de envio à PGE editável conforme perfil.
12. Status de prazo PGE calculado.
13. Analista.
14. Número do decreto municipal.
15. Ações.

Ações:

| Ação | Perfis |
|---|---|
| Ver detalhe | Admin, Gestor, Operador |
| Editar | Admin, Gestor |
| Excluir | Admin, Gestor |

---

## 36. Diferença entre status de envio PGE e status de prazo PGE

A arquitetura deve separar dois conceitos:

| Campo | Natureza | Edição |
|---|---|---|
| Status de envio à PGE | Administrativo | Pode ser editável conforme perfil. |
| Status de prazo PGE | Calculado | Não deve ser editado manualmente. |

Essa separação evita inconsistência entre o que foi informado pelo usuário e o que foi calculado pela regra de prazo.

---

## 37. Arquitetura JavaScript

O JavaScript deve ser usado para melhorar a experiência do usuário, sem substituir validações do backend.

Arquivos recomendados:

| Arquivo | Finalidade |
|---|---|
| `app.js` | Inicializações gerais. |
| `decretos.js` | Comportamentos do formulário e listagem de decretos. |
| `cobrade.js` | Carregamento dinâmico de grupos, subgrupos, tipos e subtipos. |
| `anexos.js` | Validação visual de anexos antes do envio. |
| `usuarios.js` | Comportamentos da tela de usuários. |
| `masks.js` | Máscaras simples de campos. |
| `csrf.js` | Inclusão de token CSRF em requisições assíncronas, quando necessário. |

### 37.1 Regras JavaScript permitidas

1. Calcular prévia do total de afetados.
2. Atualizar selects dependentes da COBRADE.
3. Exibir/ocultar campos conforme status.
4. Validar tamanho visual de arquivo.
5. Confirmar exclusão.
6. Melhorar filtros e interações de tabela.
7. Enviar atualização rápida de status via `fetch`, se autorizado.

### 37.2 Regras JavaScript proibidas como única validação

1. Permissão de acesso.
2. Cálculo final de afetados.
3. Cálculo final de status PGE.
4. Validação final de upload.
5. Autorização de edição.
6. Geração de protocolo DGD.

---

## 38. Arquitetura CSS

O CSS deve seguir padrão modular e institucional.

Arquivos recomendados:

| Arquivo | Finalidade |
|---|---|
| `base.css` | Reset, tipografia, cores base. |
| `layout.css` | Cabeçalho, menu, estrutura principal. |
| `forms.css` | Inputs, selects, labels, fieldsets. |
| `tables.css` | Tabelas, filtros e paginação. |
| `buttons.css` | Botões e ações. |
| `status.css` | Badges e estados visuais. |
| `responsive.css` | Ajustes para telas menores. |

Padrão visual esperado:

1. Interface limpa.
2. Menus objetivos.
3. Formulários segmentados por blocos.
4. Listagens com filtros superiores.
5. Botões de ação padronizados.
6. Status visualmente distinguíveis.
7. Aderência ao estilo administrativo do PLANCON.

---

## 39. Arquitetura do módulo Decretos

O módulo Decretos é o núcleo funcional do DGD.

### 39.1 Camadas envolvidas

```text
DecretoController
        ↓
DecretoService
        ├── ProtocoloDgdService
        ├── AfetadosService
        ├── PgePrazoService
        ├── HomologacaoService
        ├── ReconhecimentoService
        ├── RecursoService
        ├── AnexoService
        └── AuditoriaService
        ↓
DecretoRepository
        ↓
Banco de dados
        ↓
Views/decretos
```

### 39.2 Blocos do formulário

A view de cadastro/edição deve ser separada em partials:

1. Identificação do registro.
2. Localização e UBM.
3. Classificação COBRADE.
4. Dados do desastre.
5. Decreto municipal.
6. Homologação estadual.
7. Reconhecimento federal.
8. Tramitação PGE.
9. Analista.
10. Recursos de resposta.
11. Recursos de reconstrução.
12. Danos humanos e afetados.
13. Anexos.

### 39.3 Campos automáticos

Campos calculados pelo backend:

1. Protocolo DGD.
2. Sequencial anual.
3. Total de afetados.
4. Total de dias do decreto.
5. Total de dias para a PGE.
6. Status de prazo PGE.
7. Data de cadastro.
8. Usuário de cadastro.
9. Data de atualização.
10. Usuário de atualização.

---

## 40. Arquitetura do módulo Usuários

### 40.1 Camadas envolvidas

```text
UsuarioController
        ↓
UsuarioService
        ├── AuthService
        └── AuditoriaService
        ↓
UsuarioRepository
        ↓
Banco de dados
        ↓
Views/usuarios
```

### 40.2 Regras principais

1. Somente Admin deve gerenciar usuários.
2. Usuário deve possuir perfil válido.
3. Usuário deve possuir status ativo ou inativo.
4. Senha deve ser armazenada como hash.
5. Não deve haver exclusão física de usuário.
6. Edição de perfil deve gerar auditoria.
7. Alteração de senha administrativa, se existir, deve ser auditada.

---

## 41. Arquitetura do módulo Painel

### 41.1 Camadas envolvidas

```text
PainelController
        ↓
PainelService
        ↓
DecretoRepository
        ↓
Banco de dados
        ↓
Views/painel/index.php
```

### 41.2 Indicadores iniciais

1. Total de registros de desastre/decreto.
2. Total por tipo de decreto.
3. Total de homologações pendentes.
4. Total de reconhecimentos pendentes.
5. Total de registros enviados à PGE.
6. Total de registros pendentes de prazo PGE.
7. Total de afetados.
8. Registros recentes.
9. Pendências por analista.
10. Pendências por município.

### 41.3 Regra de desempenho

O painel não deve executar consultas pesadas repetidamente. Indicadores devem usar consultas objetivas, com índices previstos no banco.

---

## 42. Arquitetura da página Alterar Senha

### 42.1 Camadas envolvidas

```text
SenhaController
        ↓
UsuarioService
        ├── AuthService
        └── AuditoriaService
        ↓
UsuarioRepository
        ↓
Banco de dados
        ↓
Views/senha/edit.php
```

### 42.2 Regras mínimas

1. Usuário precisa estar autenticado.
2. Deve informar senha atual.
3. Deve informar nova senha.
4. Deve confirmar nova senha.
5. Nova senha não pode ser igual à senha atual.
6. Hash deve ser atualizado com `password_hash`.
7. Ação deve ser auditada.

---

## 43. Tratamento de erros

### 43.1 Tipos de erro

| Código | Situação | View |
|---|---|---|
| 403 | Sem permissão | `errors/403.php` |
| 404 | Rota ou registro inexistente | `errors/404.php` |
| 419 | CSRF inválido ou sessão expirada | `errors/419.php` ou mensagem própria |
| 500 | Erro interno | `errors/500.php` |

### 43.2 Ambiente de desenvolvimento

No Wampserver, erros podem ser exibidos para depuração, desde que o ambiente esteja configurado como desenvolvimento.

### 43.3 Ambiente de produção

Em produção:

1. Não exibir stack trace ao usuário.
2. Registrar erro em log.
3. Exibir mensagem amigável.
4. Não revelar SQL, caminho físico, senha, host ou detalhes de configuração.

---

## 44. Logs

Os logs devem ser armazenados em:

```text
storage/logs/
```

Tipos recomendados:

```text
app-AAAA-MM-DD.log
auth-AAAA-MM-DD.log
error-AAAA-MM-DD.log
upload-AAAA-MM-DD.log
```

Logs não substituem auditoria. Auditoria é dado de rastreabilidade funcional; log é dado técnico de diagnóstico.

---

## 45. Variáveis de ambiente

O sistema deve usar arquivo `.env` para configurações sensíveis.

Exemplo de `.env.example`:

```env
APP_NAME="DGD"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/dgd/public
APP_TIMEZONE=America/Belem

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=dgd
DB_USERNAME=root
DB_PASSWORD=

SESSION_NAME=DGDSESSID
UPLOAD_MAX_SIZE_MB=10
```

O arquivo `.env` real não deve ser versionado nem exposto publicamente.

---

## 46. Diferença entre ambiente de desenvolvimento e produção

| Item | Desenvolvimento | Produção |
|---|---|---|
| Servidor | Wampserver | Hostinger |
| Banco | MySQL local | MySQL/MariaDB do host |
| Debug | Ativado | Desativado |
| Logs | Verbosos | Controlados |
| URL base | Localhost | Domínio oficial |
| Upload | Pasta local | Pasta protegida no servidor |
| Backup | Manual/teste | Rotina obrigatória |
| Erros na tela | Permitido | Proibido |

---

## 47. Implantação em Wampserver

### 47.1 Estrutura local recomendada

```text
C:\wamp64\www\dgd\
├── app\
├── config\
├── database\
├── public\
├── storage\
└── .env
```

Acesso local:

```text
http://localhost/dgd/public
```

Recomendação superior: configurar VirtualHost apontando diretamente para `public`.

Exemplo:

```text
http://dgd.local
```

### 47.2 Banco local

1. Criar banco `dgd` no phpMyAdmin.
2. Executar scripts de estrutura do Documento 05.
3. Executar seeders de perfis, usuário Admin inicial, municípios, UBM e COBRADE.
4. Configurar `.env` com credenciais locais.

---

## 48. Implantação em Hostinger

### 48.1 Estrutura recomendada

```text
/home/usuario/domains/dominio/
├── app/
├── config/
├── database/
├── storage/
└── public_html/
    ├── index.php
    ├── .htaccess
    └── assets/
```

### 48.2 Procedimento de publicação

1. Enviar arquivos do projeto.
2. Garantir que `app`, `config`, `database` e `storage` não fiquem acessíveis diretamente.
3. Configurar `.env` de produção.
4. Criar banco no painel da hospedagem.
5. Importar estrutura via phpMyAdmin.
6. Importar seeders oficiais.
7. Ajustar permissões de escrita em `storage/uploads` e `storage/logs`.
8. Testar login.
9. Testar cadastro de desastre.
10. Testar upload e download protegido.
11. Testar permissões dos três perfis.
12. Desativar debug.

### 48.3 Pontos de atenção na Hostinger

1. Validar versão do PHP disponível.
2. Validar limite de upload do PHP.
3. Validar limite de execução.
4. Validar permissões de escrita.
5. Validar funcionamento de `.htaccess`.
6. Validar caminho absoluto de `storage`.
7. Não manter backup `.sql` em pasta pública.
8. Não expor `.env`.

---

## 49. Banco de dados e phpMyAdmin

O phpMyAdmin será usado como ferramenta administrativa, não como camada da aplicação.

Uso permitido:

1. Criar banco.
2. Importar estrutura.
3. Importar dados base.
4. Verificar registros.
5. Fazer backup controlado.
6. Executar manutenção técnica autorizada.

Uso não recomendado:

1. Alterar registros operacionais sem auditoria.
2. Corrigir status manualmente sem registro.
3. Apagar registros críticos.
4. Alterar senhas diretamente sem hash.
5. Modificar estrutura sem controle de versão.

A aplicação deve ser a interface oficial de operação.

---

## 50. Transações de banco

Operações críticas devem usar transação.

Aplicável a:

1. Cadastro de desastre com geração de protocolo.
2. Cadastro de desastre com anexos.
3. Atualização de status com auditoria.
4. Exclusão lógica com auditoria.
5. Cadastro de usuário com auditoria.

Exemplo lógico:

```php
$pdo->beginTransaction();

try {
    // gerar protocolo
    // gravar decreto
    // gravar anexos
    // gravar auditoria

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## 51. Controle de concorrência do protocolo DGD

A geração do sequencial anual deve evitar duplicidade quando dois usuários cadastrarem registros ao mesmo tempo.

Estratégias possíveis:

1. Usar tabela própria de sequenciais por ano.
2. Usar transação e bloqueio de linha.
3. Usar índice único no protocolo.
4. Repetir geração em caso de colisão controlada.

Estratégia recomendada:

```text
tabela: protocolo_sequencias
campos: ano, ultimo_numero
```

Fluxo:

```text
Inicia transação
        ↓
Bloqueia sequência do ano
        ↓
Incrementa último número
        ↓
Gera protocolo
        ↓
Grava desastre
        ↓
Confirma transação
```

O banco também deve possuir índice único para `protocolo_dgd`.

---

## 52. Validação de dados

A validação deve ocorrer em duas camadas:

1. Frontend: apoio visual ao usuário.
2. Backend: validação obrigatória antes de gravar.

### 52.1 Validações obrigatórias no cadastro de desastre

1. Município obrigatório.
2. UBM atuante obrigatória, se definido pela regra de negócio.
3. Tipo de decreto obrigatório.
4. Classificação COBRADE obrigatória.
5. Data do desastre obrigatória.
6. Número do decreto municipal obrigatório, se houver decreto.
7. Data do decreto municipal obrigatória, se houver decreto.
8. Campos numéricos de afetados devem ser inteiros maiores ou iguais a zero.
9. Anexos devem obedecer regras de upload.
10. Status devem pertencer às listas oficiais.

### 52.2 Validações obrigatórias no cadastro de usuário

1. Nome obrigatório.
2. Login obrigatório.
3. E-mail válido, se usado.
4. Perfil obrigatório.
5. Senha obrigatória no cadastro inicial.
6. Login/e-mail único.
7. Status ativo/inativo válido.

---

## 53. Padrão de respostas e mensagens

Mensagens devem ser objetivas e institucionais.

Exemplos:

| Situação | Mensagem |
|---|---|
| Cadastro salvo | `Registro cadastrado com sucesso.` |
| Edição salva | `Registro atualizado com sucesso.` |
| Exclusão lógica | `Registro excluído com sucesso.` |
| Sem permissão | `Você não possui permissão para executar esta ação.` |
| CSRF inválido | `Sessão expirada ou requisição inválida. Atualize a página e tente novamente.` |
| Upload inválido | `O arquivo enviado não atende aos critérios permitidos.` |
| Registro não encontrado | `Registro não localizado.` |

---

## 54. Padrão de nomenclatura

### 54.1 Classes

Usar PascalCase:

```text
DecretoController
DecretoService
DecretoRepository
PgePrazoService
```

### 54.2 Métodos

Usar camelCase:

```text
calcularTotalAfetados()
gerarProtocolo()
atualizarStatusHomologacao()
```

### 54.3 Tabelas e campos

Usar snake_case:

```text
usuarios
decretos
protocolo_dgd
data_desastre
total_afetados
```

### 54.4 Arquivos de view

Usar letras minúsculas e nomes descritivos:

```text
index.php
create.php
edit.php
show.php
form_afetados.php
```

---

## 55. Convenções de data e hora

A aplicação deve usar o fuso horário:

```text
America/Belem
```

Campos de data:

1. Armazenar em formato `YYYY-MM-DD` para datas.
2. Armazenar em formato `YYYY-MM-DD HH:MM:SS` para data/hora.
3. Exibir ao usuário em formato brasileiro `DD/MM/YYYY`.
4. Validar datas antes de gravar.
5. Evitar cálculo de prazo em JavaScript como regra final.

---

## 56. Arquitetura de status

Os status devem ser centralizados e controlados.

Não se recomenda espalhar strings de status pelo código. O sistema deve usar configuração, constantes ou tabela de domínio.

Exemplo de abordagem:

```text
config/status.php
        ↓
StatusHelper
        ↓
Services
        ↓
Views
```

Vantagem:

1. Evita divergência de grafia.
2. Facilita ajuste futuro.
3. Reduz erro em filtros.
4. Permite padronização visual.

---

## 57. Dados auxiliares

O sistema deve possuir dados auxiliares para funcionamento:

1. Perfis.
2. Permissões.
3. Municípios do Pará.
4. UBM atuante.
5. Grupos COBRADE.
6. Subgrupos COBRADE.
7. Tipos COBRADE.
8. Subtipos COBRADE.
9. Status oficiais.
10. Tipos de anexos.

Esses dados devem ser carregados por seeders ou scripts SQL controlados.

---

## 58. Seeders iniciais

Seeders mínimos para implantação:

```text
001_perfis.sql
002_permissoes.sql
003_usuario_admin.sql
004_municipios_para.sql
005_ubm.sql
006_cobrade.sql
007_status.sql
008_tipos_anexos.sql
```

O usuário Admin inicial deve ter senha temporária alterada obrigatoriamente no primeiro acesso, se essa regra for implementada.

---

## 59. Migrations

Mesmo que o banco seja administrado via phpMyAdmin, recomenda-se manter scripts de criação versionados em:

```text
database/migrations/
```

Padrão de nome:

```text
YYYYMMDD_001_create_usuarios_table.sql
YYYYMMDD_002_create_decretos_table.sql
YYYYMMDD_003_create_anexos_table.sql
```

Isso evita perda de controle sobre a evolução do banco.

---

## 60. Backup

A arquitetura deve prever backup do banco e, separadamente, dos anexos.

### 60.1 Itens a proteger

1. Banco de dados.
2. Pasta `storage/uploads`.
3. Arquivo `.env` de produção.
4. Logs relevantes.
5. Scripts de estrutura.

### 60.2 Cuidados

1. Backup não deve ficar em pasta pública.
2. Backup deve ter data no nome.
3. Backup deve ser testado periodicamente.
4. Backup de anexos deve preservar estrutura de pastas.
5. Restauração deve ser documentada.

---

## 61. Estrutura mínima do Core

### 61.1 `App.php`

Responsável por inicializar a aplicação.

Funções:

1. Carregar configurações.
2. Iniciar sessão.
3. Carregar rotas.
4. Executar router.
5. Tratar exceções globais.

### 61.2 `Router.php`

Responsável por mapear URL para controller e método.

Funções:

1. Registrar rotas.
2. Identificar método HTTP.
3. Identificar caminho.
4. Extrair parâmetros.
5. Executar middlewares.
6. Chamar controller.
7. Retornar 404 quando não houver rota.

### 61.3 `Controller.php`

Classe base dos controllers.

Funções:

1. Renderizar views.
2. Redirecionar.
3. Retornar JSON.
4. Validar permissão auxiliar.
5. Acessar request e sessão.

### 61.4 `View.php`

Responsável pela renderização.

Funções:

1. Carregar layout.
2. Injetar dados.
3. Renderizar partials.
4. Escapar conteúdo via helpers.

### 61.5 `Request.php`

Responsável por capturar dados da requisição.

Funções:

1. Obter método HTTP.
2. Obter rota.
3. Obter parâmetros GET.
4. Obter dados POST.
5. Obter arquivos enviados.
6. Sanitizar entradas básicas.

### 61.6 `Response.php`

Responsável por resposta HTTP.

Funções:

1. Definir status code.
2. Redirecionar.
3. Retornar JSON.
4. Retornar arquivo para download.

---

## 62. Padrão de controller

Exemplo conceitual:

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\DecretoService;

class DecretoController extends Controller
{
    private DecretoService $decretoService;

    public function __construct()
    {
        $this->decretoService = new DecretoService();
    }

    public function index(): void
    {
        $filtros = $this->request()->query();
        $pagina = (int) ($filtros['page'] ?? 1);

        $resultado = $this->decretoService->listar($filtros, $pagina, 20);

        $this->view('decretos/index', [
            'registros' => $resultado['registros'],
            'paginacao' => $resultado['paginacao'],
            'filtros' => $filtros,
        ]);
    }
}
```

---

## 63. Padrão de service

Exemplo conceitual:

```php
<?php

namespace App\Services;

use App\Repositories\DecretoRepository;

class DecretoService
{
    private DecretoRepository $repository;
    private ProtocoloDgdService $protocoloService;
    private AfetadosService $afetadosService;
    private PgePrazoService $pgePrazoService;

    public function __construct()
    {
        $this->repository = new DecretoRepository();
        $this->protocoloService = new ProtocoloDgdService();
        $this->afetadosService = new AfetadosService();
        $this->pgePrazoService = new PgePrazoService();
    }

    public function cadastrar(array $dados, int $usuarioId): int
    {
        $dados['protocolo_dgd'] = $this->protocoloService->gerar($dados);
        $dados['total_afetados'] = $this->afetadosService->calcular($dados);
        $dados['status_prazo_pge'] = $this->pgePrazoService->calcularStatus($dados);
        $dados['created_by'] = $usuarioId;

        return $this->repository->create($dados);
    }
}
```

---

## 64. Padrão de repository

Exemplo conceitual:

```php
<?php

namespace App\Repositories;

use App\Core\Database;

class DecretoRepository
{
    public function findById(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT * FROM decretos WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        $registro = $stmt->fetch();

        return $registro ?: null;
    }
}
```

---

## 65. Padrão de view

Exemplo conceitual:

```php
<h1>Decretos</h1>

<?php include view_path('components/flash.php'); ?>
<?php include view_path('decretos/partials/filtros.php'); ?>

<table class="table">
    <thead>
        <tr>
            <th>Protocolo DGD</th>
            <th>Município</th>
            <th>Tipo de desastre</th>
            <th>Homologação</th>
            <th>Reconhecimento</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $registro): ?>
            <tr>
                <td><?= e($registro['protocolo_dgd']); ?></td>
                <td><?= e($registro['municipio_nome']); ?></td>
                <td><?= e($registro['cobrade_descricao']); ?></td>
                <td><?= status_badge($registro['homologacao']); ?></td>
                <td><?= status_badge($registro['reconhecimento']); ?></td>
                <td>
                    <a href="<?= url('/decretos/' . $registro['id']); ?>">Ver detalhe</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

A função `e()` deve escapar conteúdo para HTML.

---

## 66. Controle de permissões na view

A view pode condicionar botões:

```php
<?php if (can('decretos.editar')): ?>
    <a href="<?= url('/decretos/' . $registro['id'] . '/editar'); ?>">Editar</a>
<?php endif; ?>
```

Mas a rota também deve exigir permissão. Ocultar botão não é segurança suficiente.

---

## 67. Respostas JSON

Algumas rotas auxiliares podem retornar JSON.

Exemplos:

1. COBRADE dependente.
2. Edição rápida de status.
3. Validação de protocolo externo, se implementada futuramente.

Padrão de resposta de sucesso:

```json
{
  "success": true,
  "message": "Registro atualizado com sucesso.",
  "data": {}
}
```

Padrão de resposta de erro:

```json
{
  "success": false,
  "message": "Não foi possível processar a solicitação.",
  "errors": {}
}
```

---

## 68. Edição rápida de status na listagem

A edição rápida deve seguir o fluxo:

```text
Usuário altera status na tabela
        ↓
JavaScript envia POST com CSRF
        ↓
DecretoController::updateStatus()
        ↓
PermissionMiddleware valida permissão
        ↓
DecretoService valida campo e valor
        ↓
Repository atualiza registro
        ↓
AuditoriaService registra alteração
        ↓
Retorna JSON
        ↓
Tela atualiza badge/status
```

Campos passíveis de edição rápida conforme permissão:

1. Homologação.
2. Reconhecimento.
3. Status de envio à PGE.

Campos que não devem ser editados rapidamente:

1. Protocolo DGD.
2. Total de afetados.
3. Total de dias PGE.
4. Status de prazo PGE calculado.
5. Município.
6. Data do desastre.
7. Dados COBRADE.

---

## 69. Organização dos anexos por tipo

Tipos funcionais:

| Código | Tipo de anexo |
|---|---|
| `decreto_municipal` | Decreto municipal |
| `oficio_homologacao` | Ofício de homologação |
| `parecer_estadual` | Parecer estadual |
| `parecer_municipal` | Parecer municipal |
| `outros` | Outros documentos |

Cada anexo deve possuir metadados:

1. Tipo do anexo.
2. Nome original.
3. Nome físico.
4. Caminho físico.
5. Extensão.
6. MIME type.
7. Tamanho.
8. Usuário responsável.
9. Data de envio.
10. Registro vinculado.

---

## 70. Controle de acesso aos anexos

Regras:

1. Usuário não autenticado não acessa anexo.
2. Usuário sem permissão não acessa anexo.
3. Anexo excluído logicamente não deve ser baixado.
4. Caminho físico não deve ser exibido na interface.
5. Download deve ser registrado, se a CEDEC-PA desejar rastreabilidade de acesso documental.

---

## 71. Arquitetura de filtros

Filtros devem ser tratados por classe ou método específico, evitando repetição.

Fluxo:

```text
Request GET
        ↓
DecretoController::index()
        ↓
FiltroDecretoDTO ou array validado
        ↓
DecretoService::listar()
        ↓
DecretoRepository::paginate()
        ↓
View com filtros preservados
```

Regras:

1. Filtros vazios devem ser ignorados.
2. Datas devem ser validadas.
3. IDs devem ser convertidos para inteiro.
4. Status deve estar em lista oficial.
5. Ordenação deve ser controlada por whitelist.

---

## 72. Ordenação

A listagem deve usar ordenação segura.

Ordenações permitidas:

1. Ano.
2. Sequencial anual.
3. Protocolo DGD.
4. Município.
5. Data do desastre.
6. Data do decreto municipal.
7. Homologação.
8. Reconhecimento.
9. Analista.
10. Total de afetados.

Não permitir que o usuário injete nome de coluna diretamente na SQL.

---

## 73. Arquitetura de dashboard

O painel deve usar queries agregadas.

Exemplos de métodos no `PainelService`:

```text
getIndicadoresGerais()
getPendenciasPge()
getPendenciasHomologacao()
getPendenciasReconhecimento()
getRegistrosRecentes()
getPendenciasPorAnalista()
```

A view não deve calcular indicadores a partir de listas completas. O cálculo deve ocorrer no banco ou no service.

---

## 74. Política de sessão

Regras recomendadas:

1. Regenerar ID da sessão após login.
2. Definir tempo de inatividade.
3. Encerrar sessão expirada.
4. Proteger cookies com `HttpOnly`.
5. Usar `Secure` quando houver HTTPS.
6. Invalidar sessão após logout.

Campos de controle:

```text
last_activity
login_at
ip
user_agent
```

---

## 75. HTTPS

Em produção, o DGD deve operar com HTTPS.

Motivos:

1. Proteção de credenciais.
2. Proteção de sessão.
3. Proteção de documentos anexados.
4. Redução de risco de interceptação.

Caso a hospedagem permita força de HTTPS por painel ou `.htaccess`, essa configuração deve ser aplicada.

---

## 76. Regras de senha

Regras mínimas recomendadas:

1. Tamanho mínimo de 8 caracteres.
2. Confirmação obrigatória.
3. Armazenamento com `password_hash`.
4. Validação com `password_verify`.
5. Bloqueio de senha vazia.
6. Alteração de senha auditada.

Regras mais fortes podem ser adotadas pela CEDEC-PA, mas é necessário equilibrar segurança e operação real, evitando regras tão complexas que induzam usuários a registrar senhas em papel.

---

## 77. Controle de primeira senha

Recomendação para versões posteriores:

1. Admin cria usuário com senha temporária.
2. Usuário faz login.
3. Sistema exige troca de senha.
4. A nova senha é gravada com hash.
5. Auditoria registra a alteração.

Campo sugerido:

```text
must_change_password
```

---

## 78. Controle de usuário ativo

Usuários inativos:

1. Não podem fazer login.
2. Não devem aparecer como analistas disponíveis, salvo em histórico.
3. Devem permanecer vinculados aos registros antigos para rastreabilidade.
4. Não devem ser apagados fisicamente.

---

## 79. Analista do registro

O campo analista deve listar usuários com perfil Gestor, conforme regra funcional.

Camadas envolvidas:

```text
UsuarioRepository::listGestoresAtivos()
DecretoController::create/edit()
Views/decretos/partials/form_pge.php
```

Regras:

1. Apenas usuários ativos devem ser selecionáveis.
2. Usuários inativos devem permanecer visíveis no histórico do registro, se já vinculados.
3. Alteração de analista deve ser auditada.

---

## 80. COBRADE

A estrutura COBRADE deve ser hierárquica:

```text
Grupo
    ↓
Subgrupo
    ↓
Tipo
    ↓
Subtipo
    ↓
Descrição / Código / Simbologia
```

Arquitetura:

```text
CobradeController
        ↓
CobradeService
        ↓
CobradeRepository
        ↓
Tabelas COBRADE
```

O formulário deve impedir seleção inconsistente. Por exemplo, um subtipo deve pertencer ao tipo selecionado.

---

## 81. Padrão de formulários

Cada formulário deve conter:

1. Token CSRF.
2. Campos agrupados por bloco.
3. Indicação de campos obrigatórios.
4. Mensagens de validação.
5. Botão salvar.
6. Botão cancelar/voltar.
7. Campos desabilitados conforme perfil.
8. Campos automáticos apenas para leitura.

Botões recomendados:

| Botão | Uso |
|---|---|
| Salvar | Gravar formulário. |
| Cancelar | Retornar sem salvar. |
| Voltar | Retornar para listagem ou detalhe. |
| Excluir | Ação crítica com confirmação. |
| Ver detalhe | Abrir registro em leitura. |

---

## 82. Padrão de listagens

Toda listagem deve conter:

1. Título da página.
2. Breadcrumb.
3. Botão de novo cadastro, se permitido.
4. Filtros superiores.
5. Tabela.
6. Paginação.
7. Mensagem quando não houver registros.
8. Ações por linha conforme perfil.

---

## 83. Padrão de detalhe

A página de detalhe deve ser prioritariamente de leitura.

Deve exibir:

1. Dados de identificação.
2. Dados do município e UBM.
3. COBRADE.
4. Dados do decreto municipal.
5. Homologação.
6. Reconhecimento.
7. PGE.
8. Recursos.
9. Afetados.
10. Anexos.
11. Auditoria resumida, se implementada.
12. Botões de voltar, editar e excluir conforme perfil.

---

## 84. Padrão de exclusão

A exclusão deve exigir:

1. Perfil autorizado.
2. Requisição POST.
3. Token CSRF.
4. Confirmação do usuário.
5. Exclusão lógica.
6. Auditoria.
7. Mensagem de sucesso ou erro.

Não deve haver exclusão por link GET.

Exemplo proibido:

```text
/decretos/10/excluir
```

Exemplo correto:

```text
POST /decretos/10/excluir
```

---

## 85. Padrão de importação da base COBRADE

A base COBRADE deve ser importada por script controlado.

Recomendação:

```text
database/seeders/006_cobrade.sql
```

Itens mínimos:

1. Código.
2. Grupo.
3. Subgrupo.
4. Tipo.
5. Subtipo.
6. Descrição.
7. Simbologia.
8. Status ativo.

A base utilizada no PLANCON pode servir como referência estrutural, desde que seja validada antes da importação.

---

## 86. Compatibilidade com o padrão PLANCON

A arquitetura deve permitir reaproveitar padrão visual e estrutural de sistemas anteriores, especialmente:

1. Layout administrativo.
2. Menu simples.
3. Formulários segmentados.
4. Tabelas com filtros superiores.
5. Paginação.
6. Ações por perfil.
7. Badges de status.
8. Identidade institucional.

Não se recomenda copiar código antigo sem revisão. Sistemas anteriores podem conter padrões inseguros ou desatualizados. O reaproveitamento deve ser estrutural e visual, não necessariamente literal.

---

## 87. Critérios mínimos de qualidade

A implementação deve atender:

1. Código organizado por camadas.
2. Nenhum SQL em view.
3. Nenhuma senha em texto puro.
4. Nenhum formulário crítico sem CSRF.
5. Nenhuma rota interna sem autenticação.
6. Nenhuma ação crítica sem auditoria.
7. Upload validado.
8. Listagem de decretos paginada em 20 registros.
9. Permissões aplicadas no backend.
10. Protocolo DGD gerado automaticamente.
11. Total de afetados calculado automaticamente.
12. Status de prazo PGE calculado automaticamente.
13. Erros de produção sem exposição técnica.
14. Configuração separada por ambiente.

---

## 88. Riscos técnicos da arquitetura

| Risco | Impacto | Mitigação |
|---|---|---|
| Código PHP procedural sem padrão | Dificulta manutenção | Usar MVC com responsabilidades claras. |
| SQL espalhado pelo sistema | Aumenta falhas e retrabalho | Concentrar queries em repositories. |
| Regra de negócio em JavaScript | Gera inconsistência | Calcular no backend. |
| Anexos públicos | Exposição documental | Armazenar fora do public e baixar via controller. |
| Falta de auditoria | Perda de rastreabilidade | Registrar ações críticas. |
| Permissão só na interface | Bypass por URL | Validar permissões em middleware/backend. |
| Protocolo duplicado | Falha administrativa | Usar transação e índice único. |
| Dependência excessiva de framework | Dificulta hospedagem compartilhada | Usar MVC próprio ou dependências mínimas. |
| Banco alterado manualmente no phpMyAdmin | Inconsistência | Usar migrations e auditoria via aplicação. |

---

## 89. Decisões técnicas recomendadas

| Tema | Decisão recomendada |
|---|---|
| Arquitetura | MVC com camada de Services e Repositories. |
| Banco | MySQL/MariaDB. |
| Conexão | PDO. |
| Rotas | Front controller com `.htaccess`. |
| Segurança | Sessão, CSRF, prepared statements, hash de senha e escape HTML. |
| Upload | Storage protegido fora da pasta pública. |
| Exclusão | Lógica, não física. |
| Auditoria | Obrigatória em ações críticas. |
| Paginação | 20 registros por página. |
| Configuração | `.env` por ambiente. |
| Implantação | Wampserver no desenvolvimento e Hostinger em produção. |
| COBRADE | Tabelas hierárquicas e endpoints auxiliares. |
| Status PGE | Separar status administrativo editável de status de prazo calculado. |

---

## 90. Estrutura mínima de implantação da versão inicial

A versão inicial deve entregar tecnicamente:

```text
Login funcional
Painel autenticado
Módulo Decretos completo
Módulo Usuários para Admin
Alterar senha
Controle de permissões
Upload de anexos
Auditoria básica
Banco MySQL estruturado
Layout institucional
```

---

## 91. Checklist técnico para desenvolvimento

| Item | Obrigatório |
|---|---|
| Estrutura MVC criada | Sim |
| `public/index.php` como entrada | Sim |
| `.htaccess` configurado | Sim |
| Conexão PDO | Sim |
| `.env` separado | Sim |
| Rotas centralizadas | Sim |
| Controllers oficiais | Sim |
| Services oficiais | Sim |
| Repositories oficiais | Sim |
| Views por módulo | Sim |
| Layout público e autenticado | Sim |
| CSRF em formulários | Sim |
| RBAC implementado | Sim |
| Upload protegido | Sim |
| Auditoria | Sim |
| Paginação 20 | Sim |
| Logs | Sim |
| Tratamento de erros | Sim |

---

## 92. Critérios de aceite técnico

A arquitetura será considerada corretamente implementada quando:

1. O sistema abrir a tela pública de login sem exigir autenticação.
2. O login validar usuário e senha usando hash.
3. Usuário autenticado for redirecionado para o Painel.
4. Usuário não autenticado não acessar páginas internas.
5. Perfil Operador não conseguir acessar rotas de edição ou exclusão proibidas.
6. Perfil Gestor conseguir editar e excluir registros de desastre/decreto conforme regra.
7. Perfil Admin conseguir administrar usuários.
8. A listagem de decretos exibir no máximo 20 registros por página.
9. O cadastro de desastre gerar protocolo DGD automaticamente.
10. O total de afetados for calculado no backend.
11. O status de prazo PGE for calculado por serviço próprio.
12. O upload aceitar apenas arquivos permitidos.
13. Anexos não forem acessíveis diretamente por URL pública.
14. Ações críticas forem gravadas em auditoria.
15. Erros de produção não exibirem detalhes técnicos.
16. A aplicação funcionar em Wampserver.
17. A aplicação puder ser publicada em Hostinger com ajustes de `.env`.
18. As rotas estiverem centralizadas.
19. SQL estiver concentrado em repositories ou camada equivalente.
20. Views não contiverem regra de negócio crítica.

---

## 93. Pendências para o Documento 05

O próximo documento deverá definir a estrutura completa do banco de dados, incluindo:

1. Tabelas.
2. Campos.
3. Tipos de dados.
4. Chaves primárias.
5. Chaves estrangeiras.
6. Índices.
7. Tabelas auxiliares.
8. Tabelas COBRADE.
9. Tabelas de anexos.
10. Tabela de auditoria.
11. Tabela de usuários.
12. Tabela de perfis.
13. Tabela de permissões.
14. Tabela de protocolo sequencial.
15. Regras de exclusão lógica.
16. Scripts SQL iniciais.

---

## 94. Conclusão

A arquitetura MVC definida neste documento estabelece uma base técnica adequada para o desenvolvimento do DGD em PHP, mantendo compatibilidade com Wampserver, MySQL, phpMyAdmin e produção em Hostinger.

O desenho proposto evita concentração excessiva de regras em telas ou controllers, separa responsabilidades, protege ações críticas, organiza uploads, prevê auditoria e estrutura o módulo Decretos como núcleo funcional do sistema.

A decisão mais importante da arquitetura é manter as regras críticas em serviços próprios, especialmente:

1. Geração do protocolo DGD.
2. Cálculo do total de afetados.
3. Cálculo do prazo/status PGE.
4. Controle de permissões.
5. Tratamento de anexos.
6. Auditoria.

Com essa separação, o sistema fica mais seguro, mais testável e mais fácil de evoluir nos próximos módulos.
