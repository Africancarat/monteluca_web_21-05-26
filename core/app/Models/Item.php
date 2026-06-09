<?php

namespace App\Models;

use App\Helpers\ImageHelper;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;
use App\Models\ItemPrice;

class Item extends Model
{
    public function itemPrice()
    {
        return $this->belongsTo(ItemPrice::class, 'item_price_id');
    }
    protected static function booted(): void
    {
        static::saving(function (Item $item) {
            foreach (['photo', 'thumbnail'] as $attribute) {
                $value = $item->getAttribute($attribute);
                if (! is_string($value) || trim($value) === '') {
                    continue;
                }
                $fixed = ImageHelper::storageImageBasename($value);
                if ($fixed !== $value) {
                    $item->setAttribute($attribute, $fixed);
                }
            }
        });
    }

    protected $fillable = ['category_id','subcategory_id','childcategory_id','brand_id','name','slug','sku','tags','video','sort_details','specification_name','specification_description','is_specification','details','photo','thumbnail','discount_price','previous_price','stock','meta_keywords','meta_description','status','is_type','tax_id','date','item_type','file','link','file_type','license_name','license_key','affiliate_link',"seller_id","complete_the_look_ids","pdp_metal_variants","pdp_shape_variants","pdp_ar_model_url","metal_type","gold_karat","gold_weight","labour_per_gram","igi_per_carat","margin_type","margin_value"];

    protected $casts = [
        'pdp_metal_variants' => 'array',
        'pdp_shape_variants' => 'array',
        'metal_type' => 'array',
        'gold_karat' => 'array',
    ];

    /** PDP admin / API metal labels for shape+metal image matrix. */
    public const PDP_METAL_IMAGE_OPTIONS = [
        'YELLOW GOLD',
        'ROSE GOLD',
        'WHITE GOLD',
        'PLATINUM',
    ];

    /** Form field segment => stored metal label. */
    public const PDP_METAL_FORM_KEYS = [
        'YELLOW_GOLD' => 'YELLOW GOLD',
        'ROSE_GOLD' => 'ROSE GOLD',
        'WHITE_GOLD' => 'WHITE GOLD',
        'PLATINUM' => 'PLATINUM',
    ];

    /**
     * Normalize admin / import input to a flat list for JSON array columns (metal_type, gold_karat).
     * Accepts JSON array strings, comma-separated CSV, or a PHP array (e.g. multi-select).
     */
    public static function normalizeJewelryOptionList($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $v) {
                $s = is_string($v) || is_numeric($v) ? trim((string) $v) : '';
                if ($s !== '') {
                    $out[] = $s;
                }
            }

            return $out === [] ? null : array_values($out);
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        $decoded = json_decode($str, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $out = [];
            foreach ($decoded as $v) {
                $s = trim((string) $v);
                if ($s !== '') {
                    $out[] = $s;
                }
            }

            return $out === [] ? null : array_values($out);
        }

        $out = [];
        foreach (explode(',', $str) as $piece) {
            $piece = trim((string) $piece);
            if ($piece !== '') {
                $out[] = $piece;
            }
        }

        return $out === [] ? null : array_values($out);
    }

    /**
     * @return list<int>
     */
    public static function parseCompleteTheLookIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $ids = [];
        foreach ($parts as $part) {
            $id = (int) $part;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Normalize admin / CSV input to comma-separated item IDs (or null).
     */
    public static function normalizeCompleteTheLookIds(mixed $value, ?int $excludeItemId = null): ?string
    {
        $ids = self::parseCompleteTheLookIds($value);

        if ($excludeItemId !== null && $excludeItemId > 0) {
            $ids = array_values(array_filter($ids, fn (int $id) => $id !== (int) $excludeItemId));
        }

        if ($ids === []) {
            return null;
        }

        $existing = self::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $existingSet = array_flip($existing);

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($existingSet[$id])) {
                $ordered[] = $id;
            }
        }

        return $ordered === [] ? null : implode(',', $ordered);
    }

    public static function normalizePdpVariantToken(?string $value): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    public static function pdpMetalFormKeyToLabel(string $formKey): string
    {
        $key = strtoupper(trim($formKey));

        return self::PDP_METAL_FORM_KEYS[$key] ?? self::normalizePdpVariantToken($formKey);
    }

    public static function pdpMetalLabelToFormKey(string $label): string
    {
        $norm = self::normalizePdpVariantToken($label);
        foreach (self::PDP_METAL_FORM_KEYS as $formKey => $metalLabel) {
            if ($norm === self::normalizePdpVariantToken($metalLabel)) {
                return $formKey;
            }
        }

        return str_replace(' ', '_', $norm);
    }

    /**
     * Store only the image filename (never a full URL) in pdp_*_variants JSON.
     */
    public static function normalizePdpVariantImageFilename(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            $path = parse_url($value, PHP_URL_PATH) ?? '';
            $value = $path !== '' ? basename($path) : $value;
        }

        $value = ImageHelper::normalizeStorageImagePath($value);

        return ltrim(str_replace('\\', '/', basename($value)), '/');
    }

    /**
     * PDP product galleries use photos (jpg/png/webp), not diamond-shape icon SVGs.
     */
    public static function isValidPdpGalleryImageFilename(?string $filename): bool
    {
        $filename = self::normalizePdpVariantImageFilename($filename);
        if ($filename === '') {
            return false;
        }

        return ! str_ends_with(strtolower($filename), '.svg');
    }

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    public static function normalizePdpVariantFilenames(array $names): array
    {
        $out = [];
        foreach ($names as $name) {
            $name = self::normalizePdpVariantImageFilename($name);
            if (self::isValidPdpGalleryImageFilename($name)) {
                $out[] = $name;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function normalizePdpMetalVariantRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $images = self::normalizePdpVariantFilenames(
                is_array($row['images'] ?? null) ? $row['images'] : []
            );
            if ($images === []) {
                continue;
            }
            $key = self::normalizePdpVariantToken($row['key'] ?? $row['label'] ?? $row['slug'] ?? '');
            $label = (string) ($row['label'] ?? $row['name'] ?? $key);
            $out[] = [
                'key' => $key !== '' ? $key : $label,
                'label' => $label,
                'image' => $images[0],
                'images' => $images,
            ];
        }

        return $out;
    }

    /**
     * Resolve PDP gallery URLs: shape+metal matrix first, then metal-only, then defaults.
     *
     * @param  list<string>  $defaultUrls  Featured + gallery URLs
     * @return list<string>
     */
    public function resolvePdpGalleryUrls(?string $shape, ?string $metal, array $defaultUrls = []): array
    {
        $shapeTok = self::normalizePdpVariantToken($shape);
        $metalTok = self::normalizePdpVariantToken($metal);

        foreach ($this->pdpShapeVariantRows() as $row) {
            if ($shapeTok === '' || $metalTok === '') {
                continue;
            }
            if (! self::pdpTokensMatch($row['shape'] ?? '', $shapeTok) || ! self::pdpTokensMatch($row['metal'] ?? '', $metalTok)) {
                continue;
            }
            $urls = self::resolveVariantImageUrls($row);
            if ($urls !== []) {
                return $urls;
            }
        }

        foreach ($this->pdpMetalVariantRows() as $row) {
            if ($metalTok === '') {
                continue;
            }
            $rowMetal = self::normalizePdpVariantToken($row['key'] ?? $row['label'] ?? $row['slug'] ?? '');
            if (! self::pdpTokensMatch($rowMetal, $metalTok)) {
                continue;
            }
            $urls = self::resolveVariantImageUrls($row);
            if ($urls !== []) {
                return $urls;
            }
        }

        return array_values(array_unique(array_filter($defaultUrls)));
    }

    /**
     * @return list<array{shape: string, metal: string, image: string, images: list<string>}>
     */
    public function pdpShapeVariantRows(): array
    {
        $raw = $this->pdp_shape_variants;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $shape = self::normalizePdpVariantToken($row['shape'] ?? '');
            $metal = self::normalizePdpVariantToken($row['metal'] ?? '');
            if ($shape === '' || $metal === '') {
                continue;
            }
            $images = [];
            if (isset($row['images']) && is_array($row['images'])) {
                foreach ($row['images'] as $img) {
                    $img = self::normalizePdpVariantImageFilename((string) $img);
                    if (self::isValidPdpGalleryImageFilename($img)) {
                        $images[] = $img;
                    }
                }
            }
            $primary = self::normalizePdpVariantImageFilename($row['image'] ?? $row['photo'] ?? '');
            if (self::isValidPdpGalleryImageFilename($primary) && ! in_array($primary, $images, true)) {
                array_unshift($images, $primary);
            }
            $images = self::normalizePdpVariantFilenames($images);
            if ($images === []) {
                continue;
            }
            $out[] = [
                'shape' => $shape,
                'metal' => $metal,
                'image' => $images[0],
                'images' => $images,
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pdpMetalVariantRows(): array
    {
        $raw = $this->pdp_metal_variants;

        return is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    public static function resolveVariantImageUrls(array $row): array
    {
        $urls = [];
        $imgs = $row['images'] ?? $row['gallery'] ?? null;
        if (is_array($imgs)) {
            foreach ($imgs as $img) {
                $resolved = ImageHelper::storageImageUrl((string) $img, '');
                if ($resolved !== '') {
                    $urls[] = $resolved;
                }
            }
        }
        $single = trim((string) ($row['image'] ?? $row['photo'] ?? ''));
        if ($single !== '') {
            $resolved = ImageHelper::storageImageUrl($single, '');
            if ($resolved !== '' && ! in_array($resolved, $urls, true)) {
                array_unshift($urls, $resolved);
            }
        }

        return array_values(array_unique($urls));
    }

    protected static function pdpTokensMatch(string $a, string $b): bool
    {
        $a = self::normalizePdpVariantToken($a);
        $b = self::normalizePdpVariantToken($b);
        if ($a === '' || $b === '') {
            return false;
        }

        return $a === $b;
    }

    /**
     * Published products linked for "Complete the look" (preserves CSV/admin order).
     */
    public function inventorySoftLocks()
    {
        return $this->hasMany(InventorySoftLock::class);
    }

    public function availableStock(?string $sessionId = null, ?string $cartLineKey = null): int
    {
        return app(\App\Services\InventoryReservationService::class)
            ->availableItemQty($this, $sessionId, $cartLineKey);
    }

    public function completeTheLookItems()
    {
        $ids = self::parseCompleteTheLookIds($this->complete_the_look_ids);
        if ($ids === []) {
            return collect();
        }

        return self::query()
            ->with(['category', 'brand'])
            ->where('status', 1)
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(function ($item) use ($ids) {
                $pos = array_search((int) $item->id, $ids, true);

                return $pos === false ? 9999 : $pos;
            })
            ->values();
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category')->withDefault();
    }

    public function subcategory()
    {
        return $this->belongsTo('App\Models\Subcategory')->withDefault();
    }

    public function childcategory()
    {
        return $this->belongsTo('App\Models\ChieldCategory')->withDefault();
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand')->withDefault();
    }

    public function campaigns()
    {
        return $this->hasMany('App\Models\CampaignItem');
    }

    public function tax()
    {
        return $this->belongsTo('App\Models\Tax')->withDefault();
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\Attribute');
    }

    public function galleries()
    {
        return $this->hasMany('App\Models\Gallery');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review');
    }

    public function diamondAttribute()
    {
        return $this->hasOne(DiamondAttribute::class);
    }

    public static function taxCalculate($item)
    {
        if($item->tax){
            $price = $item->discount_price;
            $percentage = $item->tax->value;
            $tax = ($price * $percentage) / 100;
            return $tax;
        }else{
            return 0;
        }
        
    }




    public function getWishlistItemId()
    {
        return Wishlist::whereItemId($this->id)->first()->id;
    }


    public function user()
    {
    	return $this->belongsTo('App\Models\User','vendor_id')->withDefault();
    }


    public function is_stock()
    {
        $item = $this;
        // license product stock check------------
        if($item->item_type == 'license'){
            if($item->license_key){
                $lisense_key = json_decode($item->license_key,true);
                if(count($lisense_key) > 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        // digital product stock check-------------

        if($item->item_type == 'digital'){
            return true;
        }
        if($item->item_type == 'affiliate'){
            return true;
        }

        // physical product stock check

        if($item->item_type == 'normal'){
            if($item->stock){
                if($item->stock != 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
          
        }
     
    }

}
