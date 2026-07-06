<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AuditoriaRepository
{
    public function create(array $data): void
    {
        $sql = 'INSERT INTO auditoria_logs (
            usuario_id, perfil_codigo, modulo, acao, entidade, entidade_id,
            valor_anterior, valor_novo, justificativa, ip, user_agent
        ) VALUES (
            :usuario_id, :perfil_codigo, :modulo, :acao, :entidade, :entidade_id,
            :valor_anterior, :valor_novo, :justificativa, :ip, :user_agent
        )';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'perfil_codigo' => $data['perfil_codigo'] ?? null,
            'modulo' => $data['modulo'],
            'acao' => $data['acao'],
            'entidade' => $data['entidade'] ?? null,
            'entidade_id' => $data['entidade_id'] ?? null,
            'valor_anterior' => isset($data['valor_anterior']) ? json_encode($data['valor_anterior'], JSON_UNESCAPED_UNICODE) : null,
            'valor_novo' => isset($data['valor_novo']) ? json_encode($data['valor_novo'], JSON_UNESCAPED_UNICODE) : null,
            'justificativa' => $data['justificativa'] ?? null,
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }
}
