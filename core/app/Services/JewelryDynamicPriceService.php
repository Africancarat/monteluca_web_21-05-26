<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\Http;

/**
 * Server-side mirror of the PDP jewelry dynamic price request
 * (see resources/views/front/catalog/partials/pdp-jewelry-extras.blade.php).
 * Resolves unit base price in the same units as items.discount_price / #pdp_line_base_price.
 */
class JewelryDynamicPriceService
{
    public static function dynamicPricingApiUrl(): string
    {
        return rtrim((string) env('JEWELRY_DYNAMIC_PRICE_URL', 'http://localhost/jewelry-api/dynamic-price.php'), '/');
    }

    public static function itemUsesDynamicApi(Item $item): bool
    {
        $karats = Item::normalizeJewelryOptionList($item->gold_karat);
        if (is_array($karats) && $karats !== []) {
            return true;
        }

        $item->loadMissing('diamondAttribute');
        $da = $item->diamondAttribute;
        if (! $da) {
            return false;
        }

        $colors = Item::normalizeJewelryOptionList($da->color_grade);
        $clars = Item::normalizeJewelryOptionList($da->clarity_grade);
        $shapes = Item::normalizeJewelryOptionList($da->shape);
        $hasCarat = $da->carat_weight !== null && (float) $da->carat_weight > 0;

        return (is_array($colors) && $colors !== [])
            || (is_array($clars) && $clars !== [])
            || (is_array($shapes) && $shapes !== [])
            || $hasCarat;
    }

    /**
     * @return float|null Positive unit price in base currency, or null if unavailable.
     */
    public static function fetchUnitBasePrice(
        int $productId,
        ?string $karat,
        ?string $color,
        ?string $clarity,
        ?string $caratWeight = null,
        ?string $shape = null
    ): ?float {
        $url = self::dynamicPricingApiUrl();
        if ($url === '') {
            return null;
        }

        $payload = [
            'product_id' => $productId,
            'karat' => $karat !== null ? trim((string) $karat) : '',
            'quality' => $clarity !== null ? trim((string) $clarity) : '',
            'color' => $color !== null ? trim((string) $color) : '',
            'shape' => $shape !== null ? trim((string) $shape) : '',
            'carat_weight' => $caratWeight !== null ? trim((string) $caratWeight) : '',
        ];

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $price = self::extractNumericPrice($response->json());
        if ($price === null || $price <= 0 || $price > 99999999.99) {
            return null;
        }

        return round($price, 2);
    }

    /**
     * @param  mixed  $json
     */
    private static function extractNumericPrice($json): ?float
    {
        $data = $json;
        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            $inner = $data['data'];
            if (
                array_key_exists('final_price', $inner)
                || array_key_exists('price', $inner)
                || array_key_exists('discount_price', $inner)
                || array_key_exists('sale_price', $inner)
            ) {
                $data = $inner;
            }
        }
        if (! is_array($data)) {
            return null;
        }

        $price = $data['final_price'] ?? $data['price'] ?? $data['discount_price'] ?? $data['sale_price'] ?? $data['amount'] ?? null;
        if ($price === null) {
            return null;
        }
        if (is_string($price)) {
            $price = str_replace(',', '.', trim($price));
        }
        if (! is_numeric($price)) {
            return null;
        }

        return (float) $price;
    }
}
