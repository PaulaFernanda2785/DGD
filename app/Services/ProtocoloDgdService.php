<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class ProtocoloDgdService
{
    public function gerar(string $dataDesastre, string $municipioNome): array
    {
        $ano = (int) date('Y', strtotime($dataDesastre));
        $sequencial = $this->proximoSequencial($ano);
        $data = date('Ymd', strtotime($dataDesastre));
        $municipio = $this->normalizarMunicipio($municipioNome);

        return [
            'protocolo_dgd' => sprintf('DGD-%d-%06d-%s-%s', $ano, $sequencial, $data, $municipio),
            'protocolo_ano' => $ano,
            'protocolo_sequencial' => $sequencial,
        ];
    }

    public function corrigirMunicipioEmProtocolo(string $protocolo, string $municipioNome): string
    {
        if (!preg_match('/^(DGD-\d{4}-\d{6}-\d{8})-.+$/', $protocolo, $matches)) {
            return $protocolo;
        }

        return $matches[1] . '-' . $this->normalizarMunicipio($municipioNome);
    }

    /**
     * Reorganiza os protocolos ativos por ano, em ordem de data do desastre.
     * A data de criação e o ID são usados para desempatar registros do mesmo dia.
     *
     * @param list<int> $anos
     * @return list<array{id: int, protocolo_anterior: string, protocolo_novo: string}>
     */
    public function reorganizarAnos(array $anos): array
    {
        $anos = array_values(array_unique(array_filter(
            array_map('intval', $anos),
            static fn (int $ano): bool => $ano >= 1900 && $ano <= 9999
        )));

        if ($anos === []) {
            return [];
        }

        $pdo = Database::connection();
        $this->bloquearAnos($anos);
        $alteracoes = [];

        foreach ($anos as $ano) {
            $stmt = $pdo->prepare(
                'SELECT d.id, d.protocolo_dgd, d.data_desastre, m.nome AS municipio
                 FROM desastres d
                 INNER JOIN municipios m ON m.id = d.municipio_id
                 WHERE d.protocolo_ano = :ano
                   AND d.excluido_em IS NULL
                 ORDER BY d.data_desastre ASC, d.criado_em ASC, d.id ASC
                 FOR UPDATE'
            );
            $stmt->execute(['ano' => $ano]);
            $registros = $stmt->fetchAll();

            foreach ($registros as $indice => $registro) {
                $stmt = $pdo->prepare(
                    'UPDATE desastres
                     SET protocolo_dgd = :protocolo, protocolo_sequencial = :sequencial
                     WHERE id = :id'
                );
                $stmt->execute([
                    'id' => (int) $registro['id'],
                    'protocolo' => '__TMP_DGD_' . (int) $registro['id'],
                    'sequencial' => 4000000000 + $indice,
                ]);
            }

            foreach ($registros as $indice => $registro) {
                $sequencial = $indice + 1;
                $protocolo = $this->montarProtocolo(
                    $ano,
                    $sequencial,
                    (string) $registro['data_desastre'],
                    (string) $registro['municipio']
                );
                $stmt = $pdo->prepare(
                    'UPDATE desastres
                     SET protocolo_dgd = :protocolo, protocolo_sequencial = :sequencial
                     WHERE id = :id'
                );
                $stmt->execute([
                    'id' => (int) $registro['id'],
                    'protocolo' => $protocolo,
                    'sequencial' => $sequencial,
                ]);

                if ($protocolo !== $registro['protocolo_dgd']) {
                    $alteracoes[] = [
                        'id' => (int) $registro['id'],
                        'protocolo_anterior' => (string) $registro['protocolo_dgd'],
                        'protocolo_novo' => $protocolo,
                    ];
                }
            }

            $stmt = $pdo->prepare(
                'INSERT INTO sequencias_protocolos (ano, ultimo_sequencial)
                 VALUES (:ano, :sequencial)
                 ON DUPLICATE KEY UPDATE ultimo_sequencial = VALUES(ultimo_sequencial)'
            );
            $stmt->execute(['ano' => $ano, 'sequencial' => count($registros)]);
        }

        return $alteracoes;
    }

    private function proximoSequencial(int $ano): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT ultimo_sequencial FROM sequencias_protocolos WHERE ano = :ano FOR UPDATE');
        $stmt->execute(['ano' => $ano]);
        $current = $stmt->fetchColumn();

        if ($current === false) {
            $stmt = $pdo->prepare('INSERT INTO sequencias_protocolos (ano, ultimo_sequencial) VALUES (:ano, 1)');
            $stmt->execute(['ano' => $ano]);
            return 1;
        }

        $next = (int) $current + 1;
        $stmt = $pdo->prepare('UPDATE sequencias_protocolos SET ultimo_sequencial = :sequencial WHERE ano = :ano');
        $stmt->execute(['ano' => $ano, 'sequencial' => $next]);

        return $next;
    }

    /** @param list<int> $anos */
    private function bloquearAnos(array $anos): void
    {
        $pdo = Database::connection();

        foreach ($anos as $ano) {
            $stmt = $pdo->prepare(
                'INSERT INTO sequencias_protocolos (ano, ultimo_sequencial)
                 VALUES (:ano, 0)
                 ON DUPLICATE KEY UPDATE ano = VALUES(ano)'
            );
            $stmt->execute(['ano' => $ano]);

            $stmt = $pdo->prepare('SELECT ano FROM sequencias_protocolos WHERE ano = :ano FOR UPDATE');
            $stmt->execute(['ano' => $ano]);
        }
    }

    private function montarProtocolo(int $ano, int $sequencial, string $dataDesastre, string $municipioNome): string
    {
        return sprintf(
            'DGD-%d-%06d-%s-%s',
            $ano,
            $sequencial,
            date('Ymd', strtotime($dataDesastre)),
            $this->normalizarMunicipio($municipioNome)
        );
    }

    private function normalizarMunicipio(string $municipio): string
    {
        $normalized = $this->corrigirEncoding($municipio);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        $normalized = strtoupper((string) $normalized);
        $normalized = preg_replace('/[\'`^~"]+/', '', $normalized);
        $normalized = preg_replace('/[^A-Z0-9]+/', '_', $normalized);
        $normalized = trim((string) $normalized, '_');

        return $normalized !== '' ? $normalized : 'MUNICIPIO';
    }

    private function corrigirEncoding(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (str_contains($value, "\xC3\x83") || str_contains($value, "\xC3\x82")) {
            $repaired = mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');

            if (mb_check_encoding($repaired, 'UTF-8')) {
                return $repaired;
            }
        }

        return mb_check_encoding($value, 'UTF-8') ? $value : mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    }
}
