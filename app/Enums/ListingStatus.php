<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case ChangesRequested = 'changes_requested';
    case Published = 'published';
    case Reserved = 'reserved';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::ChangesRequested => 'Changes Requested',
            self::Published => 'Published',
            self::Reserved => 'Reserved',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
            self::Hidden => 'Hidden',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-700',
            self::PendingReview => 'bg-amber-100 text-amber-800',
            self::ChangesRequested => 'bg-orange-100 text-orange-800',
            self::Published => 'bg-emerald-100 text-emerald-800',
            self::Reserved => 'bg-sky-100 text-sky-800',
            self::Completed => 'bg-teal-100 text-teal-800',
            self::Rejected => 'bg-red-100 text-red-800',
            self::Hidden => 'bg-gray-200 text-gray-600',
        };
    }
}
