<?php

namespace App\Services;

use App\Models\AttributeOption;
use App\Models\InventorySoftLock;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class InventoryReservationService
{
    public const SESSION_LOCK_MAP = 'inventory_cart_lock_map';

    public function reservationTtlMinutes(): int
    {
        return max(1, (int) config('inventory.reservation_ttl_minutes', 30));
    }

    public function sessionId(): string
    {
        return (string) Session::getId();
    }

    /**
     * @param  list<int>  $optionIds
     */
    public function buildVariantKeys(int $itemId, array $optionIds): array
    {
        $keys = [InventorySoftLock::variantKeyForItem()];
        foreach ($this->normalizeOptionIds($optionIds) as $optionId) {
            $keys[] = InventorySoftLock::variantKeyForOption($optionId);
        }

        return $keys;
    }

    /**
     * @param  list<int>  $optionIds
     */
    public function canReserve(int $itemId, array $optionIds, int $qty, ?string $cartLineKey = null): bool
    {
        if ($qty < 1) {
            return false;
        }

        $item = Item::find($itemId);
        if (! $item || $item->item_type !== 'normal') {
            return true;
        }

        $sessionId = $this->sessionId();

        foreach ($this->buildVariantKeys($itemId, $optionIds) as $variantKey) {
            $available = $this->availableQtyForVariant($item, $variantKey, $sessionId, $cartLineKey);
            if ($qty > $available) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reserve inventory for a cart line (item + each selected option).
     *
     * @param  list<int>  $optionIds
     * @return list<int> Lock IDs
     */
    public function reserveCartLine(int $itemId, array $optionIds, int $qty, string $cartLineKey): array
    {
        $item = Item::find($itemId);
        if (! $item || $item->item_type !== 'normal') {
            return [];
        }

        if ($qty < 1) {
            return [];
        }

        $lockName = 'reserve-item-' . $itemId;
        $lockSeconds = max(1, (int) config('inventory.reserve_lock_seconds', 10));

        $lock = Cache::lock($lockName, $lockSeconds);

        return $lock->block($lockSeconds, function () use ($itemId, $optionIds, $qty, $cartLineKey, $item) {
            return DB::transaction(function () use ($itemId, $optionIds, $qty, $cartLineKey, $item) {
                Item::query()->whereKey($itemId)->lockForUpdate()->first();

                foreach ($this->normalizeOptionIds($optionIds) as $optionId) {
                    AttributeOption::query()->whereKey($optionId)->lockForUpdate()->first();
                }

                $this->releaseCartLineKey($cartLineKey, false);

                if (! $this->canReserve($itemId, $optionIds, $qty, $cartLineKey)) {
                    return [];
                }

                $expiresAt = now()->addMinutes($this->reservationTtlMinutes());
                $sessionId = $this->sessionId();
                $userId = Auth::id();
                $lockIds = [];

                foreach ($this->buildVariantKeys($itemId, $optionIds) as $variantKey) {
                    $lock = InventorySoftLock::create([
                        'item_id' => $itemId,
                        'variant_key' => $variantKey,
                        'user_id' => $userId,
                        'session_id' => $sessionId,
                        'qty' => $qty,
                        'status' => InventorySoftLock::STATUS_RESERVED,
                        'expires_at' => $expiresAt,
                    ]);
                    $lockIds[] = $lock->id;
                }

                $this->rememberCartLineLocks($cartLineKey, $lockIds);

                return $lockIds;
            });
        }) ?? [];
    }

    public function releaseCartLineKey(string $cartLineKey, bool $removeFromMap = true): void
    {
        $map = Session::get(self::SESSION_LOCK_MAP, []);
        $ids = $map[$cartLineKey] ?? [];

        if ($ids !== []) {
            InventorySoftLock::query()
                ->whereIn('id', $ids)
                ->where('status', InventorySoftLock::STATUS_RESERVED)
                ->update(['status' => InventorySoftLock::STATUS_RELEASED]);
        }

        if ($removeFromMap) {
            unset($map[$cartLineKey]);
            Session::put(self::SESSION_LOCK_MAP, $map);
        }
    }

    public function releaseSession(?string $sessionId = null): void
    {
        $sessionId = $sessionId ?? $this->sessionId();

        InventorySoftLock::query()
            ->where('session_id', $sessionId)
            ->where('status', InventorySoftLock::STATUS_RESERVED)
            ->update(['status' => InventorySoftLock::STATUS_RELEASED]);

        Session::forget(self::SESSION_LOCK_MAP);
    }

    public function completeSession(?string $sessionId = null): void
    {
        $sessionId = $sessionId ?? $this->sessionId();

        InventorySoftLock::query()
            ->where('session_id', $sessionId)
            ->where('status', InventorySoftLock::STATUS_RESERVED)
            ->update(['status' => InventorySoftLock::STATUS_COMPLETED]);

        Session::forget(self::SESSION_LOCK_MAP);
    }

    public function cancelSession(?string $sessionId = null): void
    {
        $sessionId = $sessionId ?? $this->sessionId();

        InventorySoftLock::query()
            ->where('session_id', $sessionId)
            ->whereIn('status', [
                InventorySoftLock::STATUS_RESERVED,
                InventorySoftLock::STATUS_COMPLETED,
            ])
            ->update(['status' => InventorySoftLock::STATUS_CANCELLED]);

        Session::forget(self::SESSION_LOCK_MAP);
    }

    public function expireDueReservations(): int
    {
        return DB::transaction(function () {
            return InventorySoftLock::query()
                ->where('status', InventorySoftLock::STATUS_RESERVED)
                ->where('expires_at', '<', now())
                ->update(['status' => InventorySoftLock::STATUS_EXPIRED]);
        });
    }

    /**
     * Available units for sale (physical / variant).
     */
    public function availableQtyForVariant(Item $item, string $variantKey, ?string $sessionId = null, ?string $cartLineKey = null): int
    {
        if ($item->item_type !== 'normal') {
            return PHP_INT_MAX;
        }

        $physical = $this->physicalQtyForVariant($item, $variantKey);
        if ($physical === PHP_INT_MAX) {
            return PHP_INT_MAX;
        }

        $reservedOthers = $this->activeReservedQty($item->id, $variantKey, $sessionId, $cartLineKey);

        return max(0, $physical - $reservedOthers);
    }

    public function availableItemQty(Item $item, ?string $sessionId = null, ?string $cartLineKey = null): int
    {
        return $this->availableQtyForVariant($item, InventorySoftLock::variantKeyForItem(), $sessionId, $cartLineKey);
    }

    public function availableOptionQty(AttributeOption $option, int $itemId, ?string $sessionId = null, ?string $cartLineKey = null): int
    {
        if ($option->stock === 'unlimited') {
            return PHP_INT_MAX;
        }

        $item = Item::find($itemId);

        return $item
            ? $this->availableQtyForVariant($item, InventorySoftLock::variantKeyForOption((int) $option->id), $sessionId, $cartLineKey)
            : max(0, (int) $option->stock);
    }

    protected function physicalQtyForVariant(Item $item, string $variantKey): int
    {
        if ($variantKey === InventorySoftLock::variantKeyForItem()) {
            return max(0, (int) $item->stock);
        }

        if (str_starts_with($variantKey, 'option:')) {
            $optionId = (int) substr($variantKey, 7);
            $option = AttributeOption::find($optionId);
            if (! $option) {
                return 0;
            }
            if ($option->stock === 'unlimited') {
                return PHP_INT_MAX;
            }

            return max(0, (int) $option->stock);
        }

        return 0;
    }

    protected function activeReservedQty(int $itemId, string $variantKey, ?string $sessionId = null, ?string $cartLineKey = null): int
    {
        $query = InventorySoftLock::query()
            ->activeReserved()
            ->where('item_id', $itemId)
            ->where('variant_key', $variantKey);

        if ($sessionId !== null && $cartLineKey !== null) {
            $ownIds = Session::get(self::SESSION_LOCK_MAP, [])[$cartLineKey] ?? [];
            if ($ownIds !== []) {
                $query->where(function ($q) use ($sessionId, $ownIds) {
                    $q->where('session_id', '!=', $sessionId)
                        ->orWhereNotIn('id', $ownIds);
                });
            } else {
                $query->where('session_id', '!=', $sessionId);
            }
        }

        return (int) $query->sum('qty');
    }

    /**
     * @param  list<int>  $lockIds
     */
    protected function rememberCartLineLocks(string $cartLineKey, array $lockIds): void
    {
        $map = Session::get(self::SESSION_LOCK_MAP, []);
        $map[$cartLineKey] = $lockIds;
        Session::put(self::SESSION_LOCK_MAP, $map);
    }

    /**
     * @param  list<int|string>  $optionIds
     * @return list<int>
     */
    protected function normalizeOptionIds(array $optionIds): array
    {
        $ids = [];
        foreach ($optionIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}
