<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Repositories\DecretoHistoricoRepository;

class DecretoHistoricoService
{
    private DecretoHistoricoRepository $historico;

    public function __construct()
    {
        $this->historico = new DecretoHistoricoRepository();
    }

    public function registrar(int $desastreId, string $campo, mixed $valorAnterior, mixed $valorNovo, ?string $observacao = null): void
    {
        $this->historico->create([
            'desastre_id' => $desastreId,
            'campo' => $campo,
            'valor_anterior' => $this->formatarValor($valorAnterior),
            'valor_novo' => $this->formatarValor($valorNovo),
            'usuario_id' => Auth::id(),
            'justificativa' => trim((string) $observacao) ?: null,
        ]);
    }

    public function listar(int $desastreId): array
    {
        $registros = $this->historico->byDesastre($desastreId);

        usort($registros, static function (array $a, array $b): int {
            $dataComparacao = strtotime((string) ($b['criado_em'] ?? '')) <=> strtotime((string) ($a['criado_em'] ?? ''));

            if ($dataComparacao !== 0) {
                return $dataComparacao;
            }

            return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
        });

        return $registros;
    }

    private function formatarValor(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
        }

        return (string) $value;
    }
}
