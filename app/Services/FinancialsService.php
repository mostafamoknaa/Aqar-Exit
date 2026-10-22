<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Listing;
use App\Models\User;

/**
 * Reported vs verified financials. Formulas (all bcmath, decimal strings):
 *  remaining            = total_price - paid_amount            (derived)
 *  buyer_cash_required  = paid_amount (+ outstanding_fees if configured)
 *  buyer_commission     = total_price * buyer_commission_rate  (on completion only)
 *  est. total cost      = cash + remaining + outstanding_fees + commission
 * Verified figures are recorded separately; a mismatch flags `discrepancy`
 * and never silently overwrites the owner's reported figures.
 */
class FinancialsService
{
    public function verify(Listing $listing, string $verifiedTotal, string $verifiedPaid, User $verifier, ?string $notes = null): Listing
    {
        $matches = bccomp($verifiedTotal, (string) $listing->total_price, 2) === 0
            && bccomp($verifiedPaid, (string) $listing->paid_amount, 2) === 0;

        $listing->update([
            'verified_total_price' => $verifiedTotal,
            'verified_paid_amount' => $verifiedPaid,
            'financial_status' => $matches ? 'verified' : 'discrepancy',
            'financial_notes' => $notes,
            'financial_verified_by' => $verifier->id,
            'financial_verified_at' => now(),
        ]);
        app(AuditLogger::class)->log('listing.financials_verified', $listing, null, [
            'financial_status' => $listing->financial_status,
            'verified_total_price' => $verifiedTotal,
            'verified_paid_amount' => $verifiedPaid,
        ], $verifier);

        return $listing;
    }
}
