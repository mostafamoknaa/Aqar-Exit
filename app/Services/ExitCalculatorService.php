<?php

namespace App\Services;

class ExitCalculatorService
{
    /**
     * Free exit calculator: returning the unit to the developer vs. Aqar Exit.
     *
     * @return array{cancellation_penalty:int, refund_if_cancelled:int, refund_with_aqar_exit:int, loss_if_cancelled:int, loss_percent_of_paid:float, remaining_amount:int}
     */
    public function calculate(int $totalPrice, int $paidAmount, ?float $cancellationPercent = null): array
    {
        $percent = $cancellationPercent ?? (float) config('aqar.default_cancellation_percent');
        $penalty = (int) round($totalPrice * $percent / 100);
        $refundIfCancelled = max(0, $paidAmount - $penalty);

        return [
            'cancellation_penalty' => $penalty,
            'refund_if_cancelled' => $refundIfCancelled,
            'refund_with_aqar_exit' => $paidAmount, // seller recovers 100% of what he paid, cash
            'loss_if_cancelled' => $paidAmount - $refundIfCancelled,
            'loss_percent_of_paid' => $paidAmount > 0 ? round(($paidAmount - $refundIfCancelled) / $paidAmount * 100, 1) : 0.0,
            'remaining_amount' => max(0, $totalPrice - $paidAmount),
        ];
    }
}
