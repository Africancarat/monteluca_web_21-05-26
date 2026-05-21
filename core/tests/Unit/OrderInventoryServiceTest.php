<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\OrderInventoryService;
use Tests\TestCase;

class OrderInventoryServiceTest extends TestCase
{
    public function test_should_restock_when_cancelled_after_paid(): void
    {
        $order = new Order([
            'payment_method' => 'Stripe',
            'payment_status' => 'Paid',
        ]);

        $this->assertTrue(app(OrderInventoryService::class)->shouldRestockOrder($order, 'Paid'));
    }

    public function test_should_restock_when_cancelled_unpaid_cod(): void
    {
        $order = new Order([
            'payment_method' => 'Cash On Delivery',
            'payment_status' => 'Unpaid',
        ]);

        $this->assertTrue(app(OrderInventoryService::class)->shouldRestockOrder($order, 'Unpaid'));
    }

    public function test_should_not_restock_when_cancelled_unpaid_stripe(): void
    {
        $order = new Order([
            'payment_method' => 'Stripe',
            'payment_status' => 'Unpaid',
        ]);

        $this->assertFalse(app(OrderInventoryService::class)->shouldRestockOrder($order, 'Unpaid'));
    }
}
