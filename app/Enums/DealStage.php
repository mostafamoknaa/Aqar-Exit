<?php

namespace App\Enums;

enum DealStage: string
{
    case NewLead = 'new'; // Buyer interest received
    case Contacted = 'contacted';
    case Negotiation = 'negotiation';
    case Reserved = 'reserved';
    case Documents = 'documents';
    case DeveloperApproval = 'developer_approval';
    case Signing = 'signing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NewLead => 'Buyer Interest Received',
            self::Contacted => 'Contacted',
            self::Negotiation => 'Negotiation in Progress',
            self::Reserved => 'Reserved',
            self::Documents => 'Documents',
            self::DeveloperApproval => 'Developer Approval',
            self::Signing => 'Assignment Process / Signing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Ordered pipeline stages shown as progress in the mobile app. */
    public static function pipeline(): array
    {
        return [self::NewLead, self::Contacted, self::Negotiation, self::Reserved, self::Documents, self::DeveloperApproval, self::Signing, self::Completed];
    }
}
