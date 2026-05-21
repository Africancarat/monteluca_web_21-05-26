<?php

namespace App\Http\Controllers\Back;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChieldCategory;
use App\Models\DiamondAttribute;
use App\Models\Item;
use App\Models\Order;
use App\Models\Subcategory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class CsvProductController extends Controller
{
    protected function csvCell($value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        // Arrays/objects (e.g. casted JSON columns like metal_type) must be serialized.
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);
        return $encoded === false ? '' : $encoded;
    }

    protected function normalizeCsvRow(array $row): array
    {
        $out = [];
        foreach ($row as $k => $v) {
            $out[$k] = $this->csvCell($v);
        }
        return $out;
    }

    /**
     * Diamond attribute columns that exist on the current database table.
     *
     * @return list<string>
     */
    protected function diamondAttributeDbColumns(): array
    {
        static $columns = null;
        if ($columns !== null) {
            return $columns;
        }

        if (! Schema::hasTable('diamond_attributes')) {
            $columns = [];

            return $columns;
        }

        $skip = ['id', 'item_id', 'created_at', 'updated_at'];
        $columns = array_values(array_filter(
            Schema::getColumnListing('diamond_attributes'),
            fn (string $col) => ! in_array($col, $skip, true)
        ));

        return $columns;
    }

    /**
     * Diamond attribute columns included in product CSV export / sample / import.
     *
     * @return list<string>
     */
    protected function diamondAttributeCsvFields(): array
    {
        $preferred = [
            'carat_weight',
            'cut_grade',
            'color_grade',
            'clarity_grade',
            'shape',
            'table_pct',
            'depth_pct',
            'length_mm',
            'width_mm',
            'depth_mm',
            'lab',
            'certificate_number',
            'certificate_url',
            'certificate_report_image',
            'certificate_report_pdf',
            'is_lab_grown',
            'fluorescence',
            'polish',
            'symmetry',
            'video_360_url',
            'images_360',
        ];

        $existing = array_flip($this->diamondAttributeDbColumns());

        return array_values(array_filter(
            $preferred,
            fn (string $field) => isset($existing[$field])
        ));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function filterDiamondAttributesForDb(array $attributes): array
    {
        $allowed = array_flip($this->diamondAttributeDbColumns());

        return array_intersect_key($attributes, $allowed);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function filterItemImportRow(array $row): array
    {
        static $allowed = null;
        if ($allowed === null) {
            $fillable = (new Item)->getFillable();
            $cols = Schema::hasTable('items') ? Schema::getColumnListing('items') : [];
            $allowed = array_flip(array_intersect($fillable, $cols));
        }

        return array_intersect_key($row, $allowed);
    }

    /**
     * @return list<string>
     */
    protected function productCsvHeaders(): array
    {
        $cols = Schema::getColumnListing('items');
        $remove = ['category_id', 'subcategory_id', 'childcategory_id', 'brand_id'];
        $cols = array_values(array_filter($cols, function ($c) use ($remove) {
            return ! in_array($c, $remove, true);
        }));

        foreach (['category', 'subcategory', 'childcategory', 'brand'] as $extra) {
            if (! in_array($extra, $cols, true)) {
                $cols[] = $extra;
            }
        }

        foreach (array_merge(['has_diamond'], $this->diamondAttributeCsvFields()) as $diamondCol) {
            if (! in_array($diamondCol, $cols, true)) {
                $cols[] = $diamondCol;
            }
        }

        return $cols;
    }

    /**
     * @param  array<string, mixed>  $list  Item row (from toArray or similar)
     * @return array<string, mixed>
     */
    protected function appendDiamondColumnsToExportRow(array $list, ?DiamondAttribute $diamond): array
    {
        $list['has_diamond'] = $diamond ? '1' : '0';

        foreach ($this->diamondAttributeCsvFields() as $field) {
            $list[$field] = $diamond ? $diamond->getAttribute($field) : '';
        }

        return $list;
    }

    /**
     * Pull diamond columns off the CSV row before Item::fill().
     *
     * @param  array<string, mixed>  $row
     * @return array{has_diamond: bool, attributes: array<string, mixed>}
     */
    protected function extractDiamondImportFromRow(array &$row): array
    {
        $hasDiamond = null;
        if (array_key_exists('has_diamond', $row)) {
            $hasDiamond = $this->csvTruthy($row['has_diamond']);
            unset($row['has_diamond']);
        }

        $attributes = [];
        foreach ($this->diamondAttributeCsvFields() as $field) {
            if (! array_key_exists($field, $row)) {
                continue;
            }
            $attributes[$field] = $row[$field];
            unset($row[$field]);
        }

        if ($hasDiamond === null) {
            $hasDiamond = $this->diamondRowHasMeaningfulData($attributes);
        }

        return [
            'has_diamond' => $hasDiamond,
            'attributes' => $attributes,
        ];
    }

    protected function csvTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $s = strtolower(trim((string) $value));

        return in_array($s, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function diamondRowHasMeaningfulData(array $attributes): bool
    {
        foreach ($attributes as $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function parseDiamondAttributesForImport(array $attributes, bool $downloadRemote = false): array
    {
        $out = [];

        foreach (['color_grade', 'clarity_grade', 'shape', 'images_360'] as $jsonField) {
            if (! array_key_exists($jsonField, $attributes)) {
                continue;
            }
            $raw = $attributes[$jsonField];
            if ($raw === null || $raw === '') {
                $out[$jsonField] = null;
                continue;
            }
            $out[$jsonField] = Item::normalizeJewelryOptionList($raw);
        }

        $scalarFields = array_diff(
            $this->diamondAttributeCsvFields(),
            ['color_grade', 'clarity_grade', 'shape', 'images_360', 'is_lab_grown']
        );

        foreach ($scalarFields as $field) {
            if (! array_key_exists($field, $attributes)) {
                continue;
            }
            $raw = $attributes[$field];
            if ($raw === null || trim((string) $raw) === '') {
                $out[$field] = null;
                continue;
            }

            if (in_array($field, ['certificate_report_image', 'certificate_report_pdf'], true)) {
                $cell = trim((string) $raw);
                $saved = $this->importPhotoCellToImages($cell, $downloadRemote);
                $out[$field] = $saved ?: $cell;
                continue;
            }

            if (in_array($field, ['carat_weight', 'table_pct', 'depth_pct', 'length_mm', 'width_mm', 'depth_mm'], true)) {
                $out[$field] = is_numeric($raw) ? $raw : null;
                continue;
            }

            $out[$field] = trim((string) $raw);
        }

        if (array_key_exists('is_lab_grown', $attributes)) {
            $raw = $attributes['is_lab_grown'];
            $out['is_lab_grown'] = ($raw === null || trim((string) $raw) === '')
                ? false
                : $this->csvTruthy($raw);
        }

        return $out;
    }

    protected function syncDiamondAttributeFromImport(Item $item, bool $hasDiamond, array $attributes): void
    {
        if (! $hasDiamond) {
            DiamondAttribute::where('item_id', $item->id)->delete();

            return;
        }

        DiamondAttribute::updateOrCreate(
            ['item_id' => $item->id],
            $this->filterDiamondAttributesForDb($attributes)
        );
    }

    protected function streamCsv(array $rows, string $filename)
    {
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Expires' => '0',
            'Pragma' => 'public',
        ];

        $callback = function () use ($rows) {
            $FH = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($FH, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                $row = is_array($row) ? $this->normalizeCsvRow($row) : [(string) $row];
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index()
    {
        return view('back.item.bulk-upload');
    }
    public function export()
    {
        $lists = Item::with('diamondAttribute')
            ->where('item_type', '!=', 'affilite')
            ->get();
        if ($lists->isEmpty()) {
            // Avoid fatal "Undefined array key 0" / empty header build.
            return $this->streamCsv([['message'], ['No products found']], 'products_csv_export.csv');
        }

        $cat = Category::whereIn('id', $lists->pluck('category_id')->filter()->unique()->values())
            ->pluck('name', 'id');
        $sub = Subcategory::whereIn('id', $lists->pluck('subcategory_id')->filter()->unique()->values())
            ->pluck('name', 'id');
        $child = ChieldCategory::whereIn('id', $lists->pluck('childcategory_id')->filter()->unique()->values())
            ->pluck('name', 'id');
        $brand = Brand::whereIn('id', $lists->pluck('brand_id')->filter()->unique()->values())
            ->pluck('name', 'id');

        $headers = $this->productCsvHeaders();
        $rows = [];
        foreach ($lists as $item) {
            $list = $item->toArray();
            $list['photo'] = ImageHelper::storageImageUrl($list['photo']);
            $list['category'] = (string) ($cat[$list['category_id']] ?? '');
            $list['subcategory'] = $list['subcategory_id'] ? (string) ($sub[$list['subcategory_id']] ?? '') : '';
            $list['childcategory'] = $list['childcategory_id'] ? (string) ($child[$list['childcategory_id']] ?? '') : '';
            $list['brand'] = $list['brand_id'] ? (string) ($brand[$list['brand_id']] ?? '') : '';
            unset($list['category_id'], $list['subcategory_id'], $list['childcategory_id'], $list['brand_id']);
            $list = $this->appendDiamondColumnsToExportRow($list, $item->diamondAttribute);

            if (isset($list['pdp_shape_variants']) && is_array($list['pdp_shape_variants'])) {
                $shapeRows = [];
                foreach ($item->pdpShapeVariantRows() as $shapeRow) {
                    $shapeRows[] = [
                        'shape' => $shapeRow['shape'],
                        'metal' => $shapeRow['metal'],
                        'image' => $shapeRow['image'],
                        'images' => $shapeRow['images'],
                    ];
                }
                $list['pdp_shape_variants'] = $shapeRows;
            }

            if (isset($list['pdp_metal_variants']) && is_array($list['pdp_metal_variants'])) {
                $list['pdp_metal_variants'] = Item::normalizePdpMetalVariantRows($list['pdp_metal_variants']);
            }

            $ordered = [];
            foreach ($headers as $key) {
                $ordered[$key] = $list[$key] ?? '';
            }
            $rows[] = $ordered;
        }

        array_unshift($rows, $headers);

        return $this->streamCsv($rows, 'products_csv_export.csv');
    }

    /**
     * Download a "sample" CSV matching the current export format headers.
     * Useful for bulk imports: user fills rows under these headers.
     */
    public function sample()
    {
        $cols = $this->productCsvHeaders();

        $example = [];
        foreach ($cols as $col) {
            $example[] = $this->sampleCsvCellExample($col);
        }

        return $this->streamCsv([$cols, $example], 'products_csv_sample.csv');
    }

    /**
     * Example cell values for the sample CSV (column name => value).
     */
    protected function sampleCsvCellExample(string $column): string
    {
        $shapeVariantsExample = [
            [
                'shape' => 'ROUND',
                'metal' => 'YELLOW GOLD',
                'image' => 'round-yellow-1.png',
                'images' => ['round-yellow-1.png', 'round-yellow-2.png'],
            ],
            [
                'shape' => 'PEAR',
                'metal' => 'ROSE GOLD',
                'image' => 'pear-rose-1.png',
                'images' => ['pear-rose-1.png'],
            ],
        ];

        $metalVariantsExample = [
            [
                'key' => 'YELLOW GOLD',
                'label' => 'YELLOW GOLD',
                'image' => 'ring-yellow.png',
                'images' => ['ring-yellow.png'],
            ],
            [
                'key' => 'ROSE GOLD',
                'label' => 'ROSE GOLD',
                'image' => 'ring-rose.png',
                'images' => ['ring-rose.png', 'ring-rose-2.png'],
            ],
        ];

        return match ($column) {
            'name' => 'Sample Engagement Ring',
            'slug' => 'sample-engagement-ring',
            'sku' => 'SKU-SAMPLE-001',
            'status' => '1',
            'item_type' => 'normal',
            'is_type' => 'undefine',
            'discount_price' => '999',
            'previous_price' => '1299',
            'stock' => '10',
            'has_diamond' => '1',
            'metal_type' => json_encode(['YELLOW GOLD', 'ROSE GOLD', 'WHITE GOLD'], JSON_UNESCAPED_UNICODE),
            'gold_karat' => json_encode(['14KT', '18KT'], JSON_UNESCAPED_UNICODE),
            'shape' => json_encode(['Round', 'Pear'], JSON_UNESCAPED_UNICODE),
            'pdp_shape_variants' => json_encode($shapeVariantsExample, JSON_UNESCAPED_UNICODE),
            'pdp_metal_variants' => json_encode($metalVariantsExample, JSON_UNESCAPED_UNICODE),
            'carat_weight' => '1.25',
            'cut_grade' => 'Excellent',
            'color_grade' => json_encode(['F', 'G'], JSON_UNESCAPED_UNICODE),
            'clarity_grade' => json_encode(['VS1', 'VS2'], JSON_UNESCAPED_UNICODE),
            default => '',
        };
    }

    /**
     * @return list<array{shape: string, metal: string, image: string, images: list<string>}>|null
     */
    protected function normalizePdpShapeVariantsForCsvImport(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return null;
            }
            $value = $decoded;
        }

        if (! is_array($value)) {
            return null;
        }

        $rows = [];
        foreach ($value as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $shape = Item::normalizePdpVariantToken($entry['shape'] ?? '');
            $metal = Item::normalizePdpVariantToken($entry['metal'] ?? '');
            if ($shape === '' || $metal === '') {
                continue;
            }

            $images = [];
            if (isset($entry['images']) && is_array($entry['images'])) {
                foreach ($entry['images'] as $img) {
                    $img = Item::normalizePdpVariantImageFilename((string) $img);
                    if (Item::isValidPdpGalleryImageFilename($img)) {
                        $images[] = $img;
                    }
                }
            }
            $primary = Item::normalizePdpVariantImageFilename($entry['image'] ?? $entry['photo'] ?? '');
            if (Item::isValidPdpGalleryImageFilename($primary) && ! in_array($primary, $images, true)) {
                array_unshift($images, $primary);
            }
            $images = Item::normalizePdpVariantFilenames($images);
            if ($images === []) {
                continue;
            }

            $rows[] = [
                'shape' => $shape,
                'metal' => $metal,
                'image' => $images[0],
                'images' => $images,
            ];
        }

        return $rows === [] ? null : $rows;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    protected function normalizePdpMetalVariantsForCsvImport(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return null;
            }
            $value = $decoded;
        }

        if (! is_array($value)) {
            return null;
        }

        $normalized = Item::normalizePdpMetalVariantRows($value);

        return $normalized === [] ? null : $normalized;
    }

    public function transactionExport()
    {
        $lists = Transaction::all()->toArray();
        if (empty($lists)) {
            return $this->streamCsv([['message'], ['No transactions found']], 'transaction_export.csv');
        }

        array_unshift($lists, array_keys($lists[0]));
        return $this->streamCsv($lists, 'transaction_export.csv');
    }

    public function orderExport()
    {
        $lists = Order::all()->toArray();
        if (empty($lists)) {
            return $this->streamCsv([['message'], ['No orders found']], 'order_csv_export.csv');
        }

        array_unshift($lists, array_keys($lists[0]));
        return $this->streamCsv($lists, 'order_csv_export.csv');
    }

    //*** POST Request
    public function import(Request $request)
    {

        try {
            // Bulk imports can take longer, especially on shared hosting.
            @set_time_limit(0);
            $filename = '';
            if ($file = $request->file('csv')) {
                $ext = strtolower((string) $file->getClientOriginalExtension());
                if ($ext !== 'csv') {
                    return back()->withError(__('Please upload a .csv file (not ') . $ext . ').');
                }
                $filename = time() . "." . $file->getClientOriginalExtension();
                $file->move('assets/temp_files', $filename);
            }

            $file = fopen('assets/temp_files/' . $filename, "r");

            $header = fgetcsv($file);
            if (! is_array($header) || $header === []) {
                fclose($file);
                return back()->withError(__('CSV header row missing.'));
            }

            // Normalize header keys (trim + lowercase)
            $keys = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $header);

            // Cache lookups for speed (avoid N queries per row)
            $catMap = Category::pluck('id', 'name')->toArray();
            $subMap = Subcategory::pluck('id', 'name')->toArray();
            $childMap = ChieldCategory::pluck('id', 'name')->toArray();
            $brandMap = Brand::pluck('id', 'name')->toArray();

            $count = 0;
            while (($line = fgetcsv($file)) !== false) {
                if (! is_array($line) || $line === []) {
                    continue;
                }

                $row = [];
                foreach ($keys as $idx => $k) {
                    if ($k === '') continue;
                    $row[$k] = $line[$idx] ?? null;
                }

                // Skip completely empty/blank rows (common with Excel exports).
                $hasAnyValue = false;
                foreach ($row as $v) {
                    if (is_string($v)) {
                        if (trim($v) !== '') {
                            $hasAnyValue = true;
                            break;
                        }
                    } elseif ($v !== null && $v !== '') {
                        $hasAnyValue = true;
                        break;
                    }
                }
                if (! $hasAnyValue) {
                    continue;
                }

                // If there is no product name, treat as non-row.
                $rowName = trim((string) ($row['name'] ?? ''));
                if ($rowName === '') {
                    continue;
                }

                // Resolve category/subcategory/childcategory/brand by NAME columns (matching export/sample).
                $categoryName = trim((string) ($row['category'] ?? ''));
                $subcategoryName = trim((string) ($row['subcategory'] ?? ''));
                $childcategoryName = trim((string) ($row['childcategory'] ?? ''));
                $brandName = trim((string) ($row['brand'] ?? ''));

                $row['category_id'] = $categoryName !== '' && isset($catMap[$categoryName]) ? (int) $catMap[$categoryName] : 0;
                $row['subcategory_id'] = $subcategoryName !== '' && isset($subMap[$subcategoryName]) ? (int) $subMap[$subcategoryName] : 0;
                $row['childcategory_id'] = $childcategoryName !== '' && isset($childMap[$childcategoryName]) ? (int) $childMap[$childcategoryName] : 0;
                $row['brand_id'] = $brandName !== '' && isset($brandMap[$brandName]) ? (int) $brandMap[$brandName] : 0;

                unset($row['category'], $row['subcategory'], $row['childcategory'], $row['brand']);

                $downloadRemoteImages = (string) $request->query('download_images') === '1';
                $diamondImport = $this->extractDiamondImportFromRow($row);
                if ($diamondImport['has_diamond']) {
                    $diamondImport['attributes'] = $this->parseDiamondAttributesForImport(
                        $diamondImport['attributes'],
                        $downloadRemoteImages
                    );
                } else {
                    $diamondImport['attributes'] = [];
                }

                foreach (['id', 'created_at', 'updated_at'] as $stripCol) {
                    unset($row[$stripCol]);
                }

                if (array_key_exists('complete_the_look_ids', $row)) {
                    $row['complete_the_look_ids'] = Item::normalizeCompleteTheLookIds($row['complete_the_look_ids']);
                }

                // Normalize JSON-array jewelry fields if provided as CSV/JSON in a cell.
                foreach (['metal_type', 'gold_karat'] as $jsonCol) {
                    if (! array_key_exists($jsonCol, $row)) {
                        continue;
                    }
                    $raw = $row[$jsonCol];
                    if ($raw === null || $raw === '') {
                        $row[$jsonCol] = null;
                        continue;
                    }
                    if (is_string($raw)) {
                        $decoded = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $row[$jsonCol] = $decoded;
                        }
                    }
                    if ($jsonCol === 'metal_type' || $jsonCol === 'gold_karat') {
                        $row[$jsonCol] = Item::normalizeJewelryOptionList($row[$jsonCol]);
                    }
                }

                if (array_key_exists('pdp_shape_variants', $row)) {
                    $row['pdp_shape_variants'] = $this->normalizePdpShapeVariantsForCsvImport($row['pdp_shape_variants']);
                }

                if (array_key_exists('pdp_metal_variants', $row)) {
                    $row['pdp_metal_variants'] = $this->normalizePdpMetalVariantsForCsvImport($row['pdp_metal_variants']);
                }

                // Photo: allow either a filename or a full URL (like export()).
                $photoCell = trim((string) ($row['photo'] ?? ''));
                if ($photoCell !== '') {
                    // Default: do not download remote images (can be slow/time out).
                    // If needed, pass ?download_images=1 in the URL to enable downloading.
                    $saved = $this->importPhotoCellToImages($photoCell, (string) $request->query('download_images') === '1');
                    if ($saved) {
                        $row['photo'] = $saved;
                        // keep thumbnail same (legacy behavior)
                        $row['thumbnail'] = $saved;
                    }
                }

                // Ensure slug exists to avoid admin "View" route errors.
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '') {
                    $slug = Str::slug((string) ($row['name'] ?? 'item')) ?: ('item-' . Str::random(6));
                }
                // Keep slug unique
                $base = $slug;
                $n = 1;
                while (Item::where('slug', $slug)->exists()) {
                    $n++;
                    $slug = $base . '-' . $n;
                    if ($n > 50) {
                        $slug = $base . '-' . Str::random(6);
                        break;
                    }
                }
                $row['slug'] = $slug;

                // Always set default is_type if missing
                if (empty($row['is_type'])) {
                    $row['is_type'] = 'undefine';
                }

                DB::transaction(function () use ($row, $diamondImport, &$count) {
                    $data = new Item();
                    $data->fill($this->filterItemImportRow($row))->save();
                    $this->syncDiamondAttributeFromImport(
                        $data,
                        $diamondImport['has_diamond'],
                        $diamondImport['attributes']
                    );
                    $count++;
                });
            }
            fclose($file);

            $removefiles = glob('assets/temp_files/*');

            // Deleting all the files in the list
            foreach ($removefiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            return back()->withSuccess(__('Bulk Product File Imported Successfully.') . ' (' . $count . ')');
        } catch (\Throwable $th) {
            Log::error('CSV import failed', ['exception' => $th]);

            $message = __('CSV import failed. Check that category/brand names exist and the CSV matches your database.');
            if ($th instanceof \Illuminate\Database\QueryException
                && preg_match("/Unknown column '([^']+)'/", $th->getMessage(), $matches)) {
                $message = __('CSV import failed: column ":col" is not in your database. Run php artisan migrate or remove that column from the CSV.', [
                    'col' => $matches[1],
                ]);
            }

            return back()->withError($message);
        }
    }

    protected function importPhotoCellToImages(string $value, bool $downloadRemote = false): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\\/\\//i', $value)) {
            $path = (string) parse_url($value, PHP_URL_PATH);
            $isLocalStorage = $path !== '' && stripos($path, '/storage/images/') !== false;

            // Optional: download third-party URLs and store a local filename (?download_images=1).
            if ($downloadRemote && ! $isLocalStorage) {
                try {
                    $ctx = stream_context_create([
                        'http' => ['timeout' => 6],
                        'https' => ['timeout' => 6],
                    ]);
                    $contents = @file_get_contents($value, false, $ctx);
                    if ($contents !== false && $contents !== '') {
                        $ext = 'jpg';
                        if (preg_match('/\\.(png|webp|jpg|jpeg)(\\?|#|$)/i', $value, $m)) {
                            $ext = strtolower($m[1] === 'jpeg' ? 'jpg' : $m[1]);
                        }
                        $name = ImageHelper::resolveAvailableFilename(
                            'images',
                            ImageHelper::filenameFromUrlOrPath($value)
                        );
                        Storage::put('images/' . $name, $contents);

                        return $name;
                    }
                } catch (\Throwable $e) {
                    // fall through to storing the original URL
                }
            }

            // Store full URL (matches bulk export and user CSV imports).
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return url($value);
        }

        return $value;
    }


    public function uploadImage($file, $path, $delete = null)
    {
        return ImageHelper::ItemhandleUploadedImage($file, $path, $delete);
    }
}
