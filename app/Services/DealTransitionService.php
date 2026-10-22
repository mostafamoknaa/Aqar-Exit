<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DealStage;
use App\Enums\ListingStatus;
use App\Models\Deal;
use App\Models\User;
use App\Notifications\DealStageChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Assignment-case workflow. Valid transitions are enforced server-side only;
 * mobile clients can never set a stage directly.
 *
 * Platform "completed" != legal completion of transfer — the legal step is
 * the developer assignment, tracked by reaching the signing/completed stages.
 */
class DealTransitionService
{
    /** @var array<string, list<DealStage>> */
    public const TRANSITIONS = [
        'new' => [DealStage::Contacted, DealStage::Negotiation, DealStage::Cancelled],
        'contacted' => [DealStage::Negotiation, DealStage::Reserved, DealStage::Cancelled],
        'negotiation' => [DealStage::Reserved, DealStage::Contacted, DealStage::Cancelled],
        'reserved' => [DealStage::Documents, DealStage::Cancelled],
        'documents' => [DealStage::DeveloperApproval, DealStage::Contacted, DealStage::Cancelled],
        'developer_approval' => [DealStage::Signing, DealStage::Documents, DealStage::Cancelled],
        'signing' => [DealStage::Completed, DealStage::Cancelled],
        'completed' => [],
        'cancelled' => [],
    ];

    public function canTransition(Deal $deal, DealStage $to): bool
    {
        return in_array($to, self::TRANSITIONS[$deal->stage->value] ?? [], true);
    }

    public function transition(Deal $deal, DealStage $to, User $actor, ?string $reason = null): Deal
    {
        if (! $this->canTransition($deal, $to)) {
            throw ValidationException::withMessages([
                'stage' => ["Cannot move a case from {$deal->stage->label()} to {$to->label()}."],
            ]);
        }

        return DB::transaction(function () use ($deal, $to, $actor, $reason) {
            $from = $deal->stage;
            $deal->update(['stage' => $to]);
            $deal->events()->create([
                'actor_id' => $actor->id, 'from_stage' => $from->value, 'to_stage' => $to->value, 'reason' => $reason,
            ]);

            if ($to === DealStage::Completed) {
                $deal->listing()->update(['status' => ListingStatus::Completed]);
            }
            if ($to === DealStage::Cancelled && $deal->listing->status === ListingStatus::Reserved) {
                $deal->listing()->update(['status' => ListingStatus::Published]);
            }

            // No sensitive financial/contract data in notification payloads beyond ids + stage.
            $deal->buyer->notify(new DealStageChanged($deal->id, $to->value));
            $deal->seller->notify(new DealStageChanged($deal->id, $to->value));
            app(AuditLogger::class)->log('deal.stage_changed', $deal, ['stage' => $from->value], ['stage' => $to->value], $actor);

            return $deal->fresh();
        });
    }
}
