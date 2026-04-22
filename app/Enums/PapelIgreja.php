<?php

namespace App\Enums;

use InvalidArgumentException;

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

    public static function fromValue(self|string $papel): self
    {
        if ($papel instanceof self) {
            return $papel;
        }

        $papelNormalizado = trim($papel);
        $papelEnum = self::tryFrom($papelNormalizado);

        if ($papelEnum instanceof self) {
            return $papelEnum;
        }

        throw new InvalidArgumentException('Papel de igreja invalido: ' . $papel);
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN_LOCAL => 'Admin local',
            self::COORDENADOR => 'Coordenador',
            self::MUSICO => 'Musico',
        };
    }
}
