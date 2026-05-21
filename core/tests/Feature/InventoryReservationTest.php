<?php

namespace Tests\Feature;

use App\Models\InventorySoftLock;
use App\Models\Item;
use App\Services\InventoryReservationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class InventoryReservationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('inventory.reservation_ttl_minutes', 30);
        Cache::flush();

        if (! Schema::hasTable('inventory_soft_locks')) {
            $this->markTestSkipped('inventory_soft_locks table not migrated.');
        }
    }

    protected function startSessionWithId(string $id): void
    {
        $session = app('session');
        $session->setId($id);
        $session->start();
    }

    protected function createNormalItem(int $stock): Item
    {
        $item = new Item([
            'category_id' => 0,
            'name' => 'Reservation test item',
            'slug' => 'res-test-' . uniqid(),
            'details' => 'Test',
            'sort_details' => 'Test',
            'discount_price' => 10,
            'previous_price' => 0,
            'stock' => $stock,
            'status' => 1,
            'item_type' => 'normal',
            'is_type' => 'undefine',
            'tax_id' => 0,
            'photo' => 'test.jpg',
        ]);
        $item->save();

        return $item;
    }

    public function test_second_session_cannot_reserve_last_unit(): void
    {
        $item = $this->createNormalItem(1);
        $service = app(InventoryReservationService::class);

        $this->startSessionWithId('session-a');
        $locksA = $service->reserveCartLine($item->id, [], 1, $item->id . '-line-a');
        $this->assertNotEmpty($locksA);

        $this->startSessionWithId('session-b');
        $this->assertFalse($service->canReserve($item->id, [], 1, $item->id . '-line-b'));
    }

    public function test_expired_reservations_are_marked_expired(): void
    {
        $item = $this->createNormalItem(5);

        $lock = InventorySoftLock::create([
            'item_id' => $item->id,
            'variant_key' => InventorySoftLock::variantKeyForItem(),
            'session_id' => 'expired-session',
            'qty' => 2,
            'status' => InventorySoftLock::STATUS_RESERVED,
            'expires_at' => now()->subMinute(),
        ]);

        $count = app(InventoryReservationService::class)->expireDueReservations();
        $this->assertGreaterThanOrEqual(1, $count);

        $lock->refresh();
        $this->assertSame(InventorySoftLock::STATUS_EXPIRED, $lock->status);
    }

    public function test_available_stock_excludes_active_reservations(): void
    {
        $item = $this->createNormalItem(10);

        InventorySoftLock::create([
            'item_id' => $item->id,
            'variant_key' => InventorySoftLock::variantKeyForItem(),
            'session_id' => 'other-session',
            'qty' => 4,
            'status' => InventorySoftLock::STATUS_RESERVED,
            'expires_at' => now()->addMinutes(30),
        ]);

        $available = app(InventoryReservationService::class)->availableItemQty($item);
        $this->assertSame(6, $available);
    }
}
