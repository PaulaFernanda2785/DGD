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
