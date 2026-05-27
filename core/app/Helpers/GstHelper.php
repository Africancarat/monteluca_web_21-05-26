<?php

namespace App\Helpers;

use App\Models\Tax;

class GstHelper
{
    /**
     * @return array{gst_percent: float, cgst_percent: float, sgst_percent: float, cgst_amount: float, sgst_amount: float, total_tax: float}
     */
    public static function splitForSubtotal(float $subtotal): array
    {
        $gstPercent = (float) (Tax::query()->where('status', 1)->value('value') ?? 0);
        $gstPercent = max(0, $gstPercent);

        $half = $gstPercent / 2;
        $cgstAmount = round(($subtotal * $half) / 100, 2);
        $sgstAmount = round(($subtotal * $half) / 100, 2);

        return [
            'gst_percent' => $gstPercent,
            'cgst_percent' => $half,
            'sgst_percent' => $half,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'total_tax' => round($cgstAmount + $sgstAmount, 2),
        ];
    }
}

