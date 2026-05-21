<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Soft reservation TTL (minutes)
    |--------------------------------------------------------------------------
    */
    'reservation_ttl_minutes' => (int) env('INVENTORY_RESERVATION_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Cache lock seconds when reserving (add-to-cart)
    |--------------------------------------------------------------------------
    */
    'reserve_lock_seconds' => (int) env('INVENTORY_RESERVE_LOCK_SECONDS', 10),

    'variant_key_item' => 'item',

];
