<?php

namespace App\Enums;

enum UnitType: string
{
    case Apartment = 'apartment';
    case Villa = 'villa';
    case Townhouse = 'townhouse';
    case TwinHouse = 'twin_house';
    case Duplex = 'duplex';
    case Penthouse = 'penthouse';
    case Chalet = 'chalet';
    case Office = 'office';
    case Retail = 'retail';

    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }
}
