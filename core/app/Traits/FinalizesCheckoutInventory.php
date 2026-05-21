<?php

namespace App\Traits;

use App\Helpers\PriceHelper;
use App\Services\OrderInventoryService;

trait FinalizesCheckoutInventory
{
    /**
     * Complete reservations and decrement stock (all payment gateways should use this).
     */
    protected function finalizeCheckoutInventory(): void
    {
        app(OrderInventoryService::class)->finalizeSuccessfulCheckout();
    }

    /**
     * @param  array<string, mixed>|null  $cart
     */
    protected function finalizeCheckoutLicenses(?array $cart = null): void
    {
        $cart = $cart ?? \Illuminate\Support\Facades\Session::get('cart', []);
        if (is_iterable($cart)) {
            PriceHelper::LicenseQtyDecrese($cart);
        }
    }
}
