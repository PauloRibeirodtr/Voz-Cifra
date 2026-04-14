<?php

namespace App\Enums;

enum PapelIgreja: string
{
    case ADMIN_LOCAL = 'admin_local';
    case COORDENADOR = 'coordenador';
    case MUSICO = 'musico';

    public static function values(): array
    {
        return array_map(
            static fn (self $papel): string => $papel->value,
            self::cases()
        );
    }
}
