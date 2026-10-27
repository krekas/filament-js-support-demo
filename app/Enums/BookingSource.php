<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingSource: string implements HasColor, HasLabel
{
    case Online = 'online';
    case BoxOffice = 'box_office';
    case Comp = 'comp';

    public function getLabel(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::BoxOffice => 'Box office',
            self::Comp => 'Comp',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Online => 'info',
            self::BoxOffice => 'success',
            self::Comp => 'warning',
        };
    }
}
