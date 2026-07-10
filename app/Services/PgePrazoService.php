<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

class PgePrazoService
{
    private const PRAZO_DIAS = 7;

    public function duracao(?string $dataEnvioPge, ?string $dataConclusaoPge = null): ?int
    {
        if (!$dataEnvioPge) {
            return null;
        }

        $inicio = new DateTimeImmutable($dataEnvioPge);
        $fim = $dataConclusaoPge ? new DateTimeImmutable($dataConclusaoPge) : new DateTimeImmutable('today');

        return (int) $inicio->diff($fim)->format('%r%a');
    }

    public function statusCodigo(?string $homologacaoCodigo, ?string $dataEnvioPge, ?string $dataConclusaoPge = null): string
    {
        if ($homologacaoCodigo === 'HOMOLOGADO') {
            return 'APROVADO';
        }

        if ($homologacaoCodigo === 'NAO_HOMOLOGADO') {
            return 'REPROVADO';
        }

        if ($homologacaoCodigo !== 'ENVIADO_PGE') {
            return 'NAO_REGISTRADO';
        }

        $duracao = $this->duracao($dataEnvioPge, $dataConclusaoPge);

        if ($duracao === null) {
            return 'NAO_REGISTRADO';
        }

        if ($duracao >= 0 && $duracao <= self::PRAZO_DIAS) {
            return 'NO_PRAZO';
        }

        if ($duracao > self::PRAZO_DIAS) {
            return 'PENDENTE';
        }

        return 'NAO_REGISTRADO';
    }

    public function status(?string $homologacaoCodigo, ?string $statusEnvioPgeCodigo, ?string $dataEnvioPge, ?string $dataConclusaoPge = null): string
    {
        return match ($this->statusCodigo($homologacaoCodigo, $dataEnvioPge, $dataConclusaoPge)) {
            'APROVADO' => 'Aprovado',
            'REPROVADO' => 'Reprovado',
            'NO_PRAZO' => 'No prazo',
            'PENDENTE' => 'Pendente',
            default => 'Não registrado',
        };
    }
}
