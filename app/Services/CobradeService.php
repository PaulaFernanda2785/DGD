<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CobradeRepository;

class CobradeService
{
    private CobradeRepository $repository;

    public function __construct()
    {
        $this->repository = new CobradeRepository();
    }

    public function grupos(): array
    {
        return $this->repository->grupos();
    }

    public function subgrupos(?int $grupoId): array
    {
        return $this->repository->subgrupos($grupoId);
    }

    public function tipos(?int $subgrupoId): array
    {
        return $this->repository->tipos($subgrupoId);
    }

    public function subtipos(?int $tipoId): array
    {
        return $this->repository->subtipos($tipoId);
    }

    public function detalhe(int $id): ?array
    {
        return $this->repository->detalhe($id);
    }
}
