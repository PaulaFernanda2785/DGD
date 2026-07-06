<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Request;
use App\Repositories\AuditoriaRepository;

class AuditoriaService
{
    private AuditoriaRepository $repository;

    public function __construct()
    {
        $this->repository = new AuditoriaRepository();
    }

    public function registrar(string $modulo, string $acao, array $context = [], ?Request $request = null): void
    {
        $usuario = Auth::user();

        $this->repository->create([
            'usuario_id' => $usuario['id'] ?? null,
            'perfil_codigo' => $usuario['perfil_codigo'] ?? null,
            'modulo' => $modulo,
            'acao' => $acao,
            'entidade' => $context['entidade'] ?? null,
            'entidade_id' => $context['entidade_id'] ?? null,
            'valor_anterior' => $context['valor_anterior'] ?? null,
            'valor_novo' => $context['valor_novo'] ?? null,
            'justificativa' => $context['justificativa'] ?? null,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
