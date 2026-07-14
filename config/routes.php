<?php

declare(strict_types=1);

use App\Controllers\AnexoController;
use App\Controllers\AuthController;
use App\Controllers\CobradeController;
use App\Controllers\CompdecController;
use App\Controllers\DecretoController;
use App\Controllers\PainelController;
use App\Controllers\PasswordRecoveryController;
use App\Controllers\SenhaController;
use App\Controllers\TwoFactorController;
use App\Controllers\UsuarioController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\PermissionMiddleware;

return [
    ['GET', '/', [AuthController::class, 'login'], [GuestMiddleware::class]],
    ['GET', '/login', [AuthController::class, 'login'], [GuestMiddleware::class]],
    ['POST', '/login', [AuthController::class, 'authenticate'], [GuestMiddleware::class, CsrfMiddleware::class]],
    ['GET', '/esqueci-senha', [PasswordRecoveryController::class, 'forgot'], [GuestMiddleware::class]],
    ['POST', '/esqueci-senha', [PasswordRecoveryController::class, 'request'], [GuestMiddleware::class, CsrfMiddleware::class]],
    ['GET', '/recuperar-senha/{token}', [PasswordRecoveryController::class, 'resetForm'], [GuestMiddleware::class]],
    ['POST', '/recuperar-senha/{token}', [PasswordRecoveryController::class, 'reset'], [GuestMiddleware::class, CsrfMiddleware::class]],
    ['GET', '/2fa/configurar', [TwoFactorController::class, 'configure'], []],
    ['POST', '/2fa/configurar', [TwoFactorController::class, 'saveConfiguration'], [CsrfMiddleware::class]],
    ['GET', '/2fa/verificar', [TwoFactorController::class, 'verify'], []],
    ['POST', '/2fa/verificar', [TwoFactorController::class, 'validate'], [CsrfMiddleware::class]],
    ['GET', '/2fa/cancelar', [TwoFactorController::class, 'cancel'], []],
    ['POST', '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]],

    ['GET', '/painel', [PainelController::class, 'index'], [AuthMiddleware::class, [PermissionMiddleware::class, 'painel.visualizar']]],

    ['GET', '/decretos', [DecretoController::class, 'index'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.visualizar']]],
    ['GET', '/decretos/novo', [DecretoController::class, 'create'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.criar']]],
    ['POST', '/decretos', [DecretoController::class, 'store'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.criar'], CsrfMiddleware::class]],
    ['GET', '/decretos/{id}/relatorio-impressao', [DecretoController::class, 'printReport'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.detalhe']]],
    ['GET', '/decretos/{id}', [DecretoController::class, 'show'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.detalhe']]],
    ['GET', '/decretos/{id}/editar', [DecretoController::class, 'edit'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.editar']]],
    ['POST', '/decretos/{id}/editar', [DecretoController::class, 'update'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.editar'], CsrfMiddleware::class]],
    ['POST', '/decretos/{id}/excluir', [DecretoController::class, 'destroy'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.excluir'], CsrfMiddleware::class]],
    ['POST', '/decretos/{id}/status', [DecretoController::class, 'updateStatus'], [AuthMiddleware::class, [PermissionMiddleware::class, 'decretos.editar_status_listagem'], CsrfMiddleware::class]],

    ['GET', '/compdecs', [CompdecController::class, 'index'], [AuthMiddleware::class, [PermissionMiddleware::class, 'compdecs.visualizar']]],
    ['GET', '/compdecs/municipio/{municipioId}', [CompdecController::class, 'municipio'], [AuthMiddleware::class]],
    ['GET', '/compdecs/{id}', [CompdecController::class, 'show'], [AuthMiddleware::class, [PermissionMiddleware::class, 'compdecs.visualizar']]],
    ['GET', '/compdecs/{id}/editar', [CompdecController::class, 'edit'], [AuthMiddleware::class, [PermissionMiddleware::class, 'compdecs.editar']]],
    ['POST', '/compdecs/{id}/editar', [CompdecController::class, 'update'], [AuthMiddleware::class, [PermissionMiddleware::class, 'compdecs.editar'], CsrfMiddleware::class]],

    ['POST', '/decretos/{id}/anexos', [AnexoController::class, 'store'], [AuthMiddleware::class, [PermissionMiddleware::class, 'anexos.upload'], CsrfMiddleware::class]],
    ['GET', '/anexos/{id}/ver', [AnexoController::class, 'showFile'], [AuthMiddleware::class]],
    ['GET', '/anexos/{id}/download', [AnexoController::class, 'download'], [AuthMiddleware::class]],
    ['POST', '/anexos/{id}/excluir', [AnexoController::class, 'destroy'], [AuthMiddleware::class, [PermissionMiddleware::class, 'anexos.excluir'], CsrfMiddleware::class]],

    ['GET', '/usuarios', [UsuarioController::class, 'index'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.visualizar']]],
    ['GET', '/usuarios/novo', [UsuarioController::class, 'create'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.criar']]],
    ['POST', '/usuarios', [UsuarioController::class, 'store'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.criar'], CsrfMiddleware::class]],
    ['GET', '/usuarios/{id}', [UsuarioController::class, 'show'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.visualizar']]],
    ['GET', '/usuarios/{id}/editar', [UsuarioController::class, 'edit'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.editar']]],
    ['POST', '/usuarios/{id}/editar', [UsuarioController::class, 'update'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.editar'], CsrfMiddleware::class]],
    ['POST', '/usuarios/{id}/status', [UsuarioController::class, 'status'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.editar'], CsrfMiddleware::class]],
    ['POST', '/usuarios/{id}/excluir', [UsuarioController::class, 'destroy'], [AuthMiddleware::class, [PermissionMiddleware::class, 'usuarios.excluir'], CsrfMiddleware::class]],

    ['GET', '/alterar-senha', [SenhaController::class, 'edit'], [AuthMiddleware::class, [PermissionMiddleware::class, 'senha.alterar_propria']]],
    ['POST', '/alterar-senha', [SenhaController::class, 'update'], [AuthMiddleware::class, [PermissionMiddleware::class, 'senha.alterar_propria'], CsrfMiddleware::class]],

    ['GET', '/cobrade/grupos', [CobradeController::class, 'grupos'], []],
    ['GET', '/cobrade/subgrupos', [CobradeController::class, 'subgrupos'], []],
    ['GET', '/cobrade/tipos', [CobradeController::class, 'tipos'], []],
    ['GET', '/cobrade/subtipos', [CobradeController::class, 'subtipos'], []],
    ['GET', '/cobrade/{id}/detalhe', [CobradeController::class, 'detalhe'], []],
];
