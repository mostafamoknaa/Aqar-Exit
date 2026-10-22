<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager'; // Case manager (مسؤول حالة)
    case User = 'user';       // Seller and/or buyer in the mobile app

    public function isStaff(): bool
    {
        return in_array($this, [self::Admin, self::Manager], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Manager => 'Case Manager',
            self::User => 'User (Seller/Buyer)',
        };
    }
}
