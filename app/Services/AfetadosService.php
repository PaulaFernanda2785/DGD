<?php

declare(strict_types=1);

namespace App\Services;

class AfetadosService
{
    public function calcular(array $data): int
    {
        $fields = [
            'numero_obitos',
            'numero_feridos',
            'numero_enfermos',
            'numero_desabrigados',
            'numero_desalojados',
            'numero_outros_afetados',
        ];

        $total = 0;

        foreach ($fields as $field) {
            $total += max((int) ($data[$field] ?? 0), 0);
        }

        return $total;
    }
}
