<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TipoAjudaRepository;

class TipoAjudaService
{
    public function listar(array $query): array
    {
        $filtros = ['busca' => mb_substr(trim((string) ($query['busca'] ?? '')), 0, 120), 'status' => (string) ($query['status'] ?? ''), 'unidade' => mb_substr(trim((string) ($query['unidade'] ?? '')), 0, 50)];
        if (!in_array($filtros['status'], ['ativo', 'inativo'], true)) { $filtros['status'] = ''; }
        return (new TipoAjudaRepository())->listar($filtros) + ['filtros' => $filtros];
    }
}
