<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

class PgePrazoService
{
    public function duracao(?string $dataDecretoMunicipal, ?string $dataEnvioPge): ?int
    {
        if (!$dataDecretoMunicipal) {
            return null;
        }

        $inicio = new DateTimeImmutable($dataDecretoMunicipal);
        $fim = $dataEnvioPge ? new DateTimeImmutable($dataEnvioPge) : new DateTimeImmutable('today');

        return (int) $inicio->diff($fim)->format('%r%a');
    }

    public function status(?string $homologacaoCodigo, ?string $dataDecretoMunicipal, ?string $dataEnvioPge): string
    {
        if ($homologacaoCodigo === 'HOMOLOGADO') {
            return 'APROVADO';
        }

        $duracao = $this->duracao($dataDecretoMunicipal, $dataEnvioPge);

        if ($duracao === null) {
            return 'SEM DATA';
        }

        if ($duracao >= 1 && $duracao <= 7) {
            return 'NO PRAZO';
        }

        if ($duracao > 7) {
            return 'PENDENTE';
        }

        return 'NAO INICIADO';
    }
}
