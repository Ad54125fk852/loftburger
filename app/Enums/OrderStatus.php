<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case ReadyForPickup = 'ready_for_pickup';
    case Served = 'served';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public static function getDescription($value): string
{
    switch ($value) {
        case self::New:
            return 'Nuevo';

        case self::Processing:
            return 'Preparando';

        case self::ReadyForPickup:
            return 'Completado';

        case self::Served:
            return 'Servido';

        case self::Closed:
            return 'Completado';

        case self::Cancelled:
            return 'Cancelado';

        default:
            return '';
    }
}

}
