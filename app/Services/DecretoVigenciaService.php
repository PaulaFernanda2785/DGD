<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

class DecretoVigenciaService
{
    public function calcular(mixed $dataPublicacao, mixed $diasVigencia, ?DateTimeImmutable $referencia = null): array
    {
        $dataPublicacao = trim((string) $dataPublicacao);
        $diasVigencia = filter_var($diasVigencia, FILTER_VALIDATE_INT);
        $publicacao = $this->dataValida($dataPublicacao);

        if ($publicacao === null || $diasVigencia === false || $diasVigencia < 1) {
            return [
                'vigencia_dias_restantes' => null,
                'vigencia_status_codigo' => 'NAO_INFORMADO',
                'vigencia_status' => 'Aguardando dados',
                'data_fim_vigencia' => null,
            ];
        }

        $hoje = ($referencia ?? new DateTimeImmutable('today'))->setTime(0, 0);
        $diasDecorridos = (int) $publicacao->diff($hoje)->format('%r%a');
        $diasRestantesCalculados = $diasVigencia - $diasDecorridos;

        if ($diasRestantesCalculados > 1) {
            $statusCodigo = 'VIGENTE';
            $status = 'Decreto vigente';
            $diasRestantes = $diasRestantesCalculados;
        } elseif ($diasRestantesCalculados === 1) {
            $statusCodigo = 'VENCE_HOJE';
            $status = 'Vence hoje';
            $diasRestantes = 1;
        } else {
            $statusCodigo = 'VENCIDO';
            $status = 'Decreto vencido';
            $diasRestantes = $diasRestantesCalculados - 1;
        }

        return [
            'vigencia_dias_restantes' => $diasRestantes,
            'vigencia_status_codigo' => $statusCodigo,
            'vigencia_status' => $status,
            'data_fim_vigencia' => $publicacao->modify('+' . ($diasVigencia - 1) . ' days')->format('Y-m-d'),
        ];
    }

    public function enriquecerRegistro(array $registro, ?DateTimeImmutable $referencia = null): array
    {
        return array_replace($registro, $this->calcular(
            $registro['data_publicacao_decreto'] ?? null,
            $registro['dias_vigencia_decreto'] ?? null,
            $referencia
        ));
    }

    public function enriquecerRegistros(array $registros, ?DateTimeImmutable $referencia = null): array
    {
        return array_map(
            fn (array $registro): array => $this->enriquecerRegistro($registro, $referencia),
            $registros
        );
    }

    private function dataValida(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $data !== false && $data->format('Y-m-d') === $value ? $data : null;
    }
}
