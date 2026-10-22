<?php

declare(strict_types=1);

namespace App\Services;

/**
 * The confirmed 12-step assignment case, implemented as two enforced
 * sub-workflows (listing review 1-6, deal pipeline 7-12). Keeping them
 * separate lets a listing be reviewed once while many buyers compete,
 * and each step still maps 1:1 to this list.
 *
 * @return list<array{step:int, key:string, phase:string, source:string}>
 */
class CaseWorkflow
{
    public const STEPS = [
        ['step' => 1, 'key' => 'draft', 'phase' => 'listing', 'source' => 'listings.status=draft'],
        ['step' => 2, 'key' => 'submitted', 'phase' => 'listing', 'source' => 'listings.status=pending_review'],
        ['step' => 3, 'key' => 'under_review', 'phase' => 'listing', 'source' => 'listings.status=under_review'],
        ['step' => 4, 'key' => 'additional_info_requested', 'phase' => 'listing', 'source' => 'listings.status=changes_requested'],
        ['step' => 5, 'key' => 'documents_resubmitted', 'phase' => 'listing', 'source' => 'listings.status=documents_resubmitted'],
        ['step' => 6, 'key' => 'approved_for_listing', 'phase' => 'listing', 'source' => 'listings.status=published'],
        ['step' => 7, 'key' => 'buyer_interest_received', 'phase' => 'deal', 'source' => 'deals.stage=new'],
        ['step' => 8, 'key' => 'negotiation_in_progress', 'phase' => 'deal', 'source' => 'deals.stage=negotiation'],
        ['step' => 9, 'key' => 'assignment_process_in_progress', 'phase' => 'deal', 'source' => 'deals.stage=documents|developer_approval|signing'],
        ['step' => 10, 'key' => 'completed', 'phase' => 'deal', 'source' => 'deals.stage=completed (platform workflow; legal transfer is completed at the developer)'],
        ['step' => 11, 'key' => 'rejected', 'phase' => 'listing', 'source' => 'listings.status=rejected'],
        ['step' => 12, 'key' => 'cancelled', 'phase' => 'deal', 'source' => 'deals.stage=cancelled'],
    ];

    /** Resolve the current 1-12 step for a listing and (optionally) its active deal. */
    public static function currentStep(string $listingStatus, ?string $dealStage): int
    {
        if ($dealStage) {
            return match ($dealStage) {
                'new', 'contacted' => 7,
                'negotiation' => 8,
                'reserved', 'documents', 'developer_approval', 'signing' => 9,
                'completed' => 10,
                'cancelled' => 12,
                default => 6,
            };
        }

        return match ($listingStatus) {
            'draft' => 1,
            'pending_review' => 2,
            'under_review' => 3,
            'changes_requested' => 4,
            'documents_resubmitted' => 5,
            'published', 'reserved' => 6,
            'completed' => 10,
            'rejected' => 11,
            default => 1,
        };
    }
}
