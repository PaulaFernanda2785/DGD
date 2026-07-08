<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class DecretoHistoricoRepository
{
    public function create(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO desastre_historico_status (
                desastre_id, campo, valor_anterior, valor_novo, usuario_id, justificativa
             ) VALUES (
                :desastre_id, :campo, :valor_anterior, :valor_novo, :usuario_id, :justificativa
             )'
        );

        $stmt->execute([
            'desastre_id' => $data['desastre_id'],
            'campo' => $this->limit((string) $data['campo'], 80),
            'valor_anterior' => $this->nullableLimit($data['valor_anterior'] ?? null),
            'valor_novo' => $this->nullableLimit($data['valor_novo'] ?? null),
            'usuario_id' => $data['usuario_id'] ?? null,
            'justificativa' => $data['justificativa'] ?? null,
        ]);
    }

    public function byDesastre(int $desastreId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT h.*, u.nome AS usuario_nome, u.email AS usuario_email
             FROM desastre_historico_status h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.desastre_id = :desastre_id
             ORDER BY h.criado_em DESC, h.id DESC'
        );
        $stmt->execute(['desastre_id' => $desastreId]);

        return $stmt->fetchAll();
    }

    private function nullableLimit(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $this->limit($value, 255);
    }

    private function limit(string $value, int $length): string
    {
        return mb_substr($value, 0, $length);
    }
}
