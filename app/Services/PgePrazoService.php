<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

class PgePrazoService
{
    public function duracao(?string $dataEnvioPge, ?string $dataConclusaoPge = null): ?int
    {
        if (!$dataEnvioPge) {
            return null;
        }

        $inicio = new DateTimeImmutable($dataEnvioPge);
        $fim = $dataConclusaoPge ? new DateTimeImmutable($dataConclusaoPge) : new DateTimeImmutable('today');

        return (int) $inicio->diff($fim)->format('%r%a');
    }

    public function status(?string $homologacaoCodigo, ?string $statusEnvioPgeCodigo, ?string $dataEnvioPge, ?string $dataConclusaoPge = null): string
    {
        if ($statusEnvioPgeCodigo === 'CONCLUIDO') {
            return 'CONCLUÍDO';
        }

        if ($homologacaoCodigo === 'HOMOLOGADO') {
            return 'CONCLUÍDO';
        }

        $duracao = $this->duracao($dataEnvioPge, $dataConclusaoPge);

        if ($duracao === null) {
            return 'NAO INICIADO';
        }

        if ($duracao >= 0 && $duracao <= 7) {
            return 'NO PRAZO';
        }

        if ($duracao > 7) {
            return 'PENDENTE';
        }

        return 'NAO INICIADO';
    }
}
