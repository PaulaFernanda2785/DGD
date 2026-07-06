<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class PainelService
{
    public function resumo(): array
    {
        try {
            $stmt = Database::connection()->query('SELECT * FROM vw_painel_resumo LIMIT 1');
            $resumo = $stmt->fetch();
        } catch (\Throwable) {
            $resumo = null;
        }

        return $resumo ?: [
            'total_desastres' => 0,
            'total_decretos_municipais' => 0,
            'homologacoes_solicitadas' => 0,
            'homologados' => 0,
            'enviados_pge' => 0,
            'pendentes_pge' => 0,
            'total_afetados' => 0,
        ];
    }

    public function recentes(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT id, protocolo_dgd, municipio, data_desastre, homologacao, reconhecimento
                 FROM vw_decretos_listagem
                 WHERE ativo = 1
                 ORDER BY criado_em DESC
                 LIMIT 5'
            );

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }
}
