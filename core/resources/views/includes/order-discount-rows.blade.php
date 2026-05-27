@php
    $orderDiscount = json_decode($order->discount, true) ?: [];
    $totalDiscount = (float) ($orderDiscount['discount'] ?? 0);
    $couponCode = data_get($orderDiscount, 'code.code_name');
    $referralCode = data_get($orderDiscount, 'referral.referral_code');
    $referralBalanceAmount = (float) data_get($orderDiscount, 'referral_balance.amount', 0);
    $codeDiscount = max(0, $totalDiscount - $referralBalanceAmount);
@endphp

@if ($referralBalanceAmount > 0)
    @php $formatted = round($referralBalanceAmount * $order->currency_value, 2); @endphp
    <tr>
        <td class="px-0 border-top border-top-2">
            <span class="text-muted">{{ __('Referral balance') }}</span>
        </td>
        <td class="px-0 text-right border-top border-top-2" colspan="5">
            <span class="text-danger">
                @if ($setting->currency_direction == 1)
                    -{{ $order->currency_sign }}{{ $formatted }}
                @else
                    -{{ $formatted }}{{ $order->currency_sign }}
                @endif
            </span>
        </td>
    </tr>
@endif

@if ($codeDiscount > 0 && ($couponCode || $referralCode))
    @php
        if ($couponCode && $referralCode) {
            $discountLabel = __('Discount') . ' (' . $couponCode . ', ' . $referralCode . ')';
        } elseif ($couponCode) {
            $discountLabel = __('Coupon discount') . ' (' . $couponCode . ')';
        } else {
            $discountLabel = __('Referral discount') . ' (' . $referralCode . ')';
        }
        $formatted = round($codeDiscount * $order->currency_value, 2);
    @endphp
    <tr>
        <td class="px-0 border-top border-top-2">
            <span class="text-muted">{{ $discountLabel }}</span>
        </td>
        <td class="px-0 text-right border-top border-top-2" colspan="5">
            <span class="text-danger">
                @if ($setting->currency_direction == 1)
                    -{{ $order->currency_sign }}{{ $formatted }}
                @else
                    -{{ $formatted }}{{ $order->currency_sign }}
                @endif
            </span>
        </td>
    </tr>
@elseif ($totalDiscount > 0 && $referralBalanceAmount <= 0)
    @php
        if ($couponCode && $referralCode) {
            $discountLabel = __('Discount') . ' (' . $couponCode . ', ' . $referralCode . ')';
        } elseif ($couponCode) {
            $discountLabel = __('Coupon discount') . ' (' . $couponCode . ')';
        } elseif ($referralCode) {
            $discountLabel = __('Referral discount') . ' (' . $referralCode . ')';
        } else {
            $discountLabel = __('Discount');
        }
        $formatted = round($totalDiscount * $order->currency_value, 2);
    @endphp
    <tr>
        <td class="px-0 border-top border-top-2">
            <span class="text-muted">{{ $discountLabel }}</span>
        </td>
        <td class="px-0 text-right border-top border-top-2" colspan="5">
            <span class="text-danger">
                @if ($setting->currency_direction == 1)
                    -{{ $order->currency_sign }}{{ $formatted }}
                @else
                    -{{ $formatted }}{{ $order->currency_sign }}
                @endif
            </span>
        </td>
    </tr>
@endif
