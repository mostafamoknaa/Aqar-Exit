<?php

namespace App\Enums;

enum DealStage: string
{
    case NewLead = 'new';
    case Contacted = 'contacted';
    case Reserved = 'reserved';
    case Documents = 'documents';
    case DeveloperApproval = 'developer_approval';
    case Signing = 'signing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NewLead => 'New',
            self::Contacted => 'Contacted',
            self::Reserved => 'Reserved',
            self::Documents => 'Documents',
            self::DeveloperApproval => 'Developer Approval',
            self::Signing => 'Signing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Ordered pipeline stages shown as progress in the mobile app. */
    public static function pipeline(): array
    {
        return [self::NewLead, self::Contacted, self::Reserved, self::Documents, self::DeveloperApproval, self::Signing, self::Completed];
    }
}
