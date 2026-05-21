<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiamondAttribute extends Model
{
    protected $fillable = [
        'item_id',
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

    protected $casts = [
        'is_lab_grown' => 'boolean',
        'carat_weight' => 'decimal:3',
        'color_grade' => 'array',
        'clarity_grade' => 'array',
        'shape' => 'array',
        'images_360' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Shape values as a flat list (supports JSON array cast or legacy single string).
     *
     * @return list<string>
     */
    public function shapeLabels(): array
    {
        $raw = $this->getAttribute('shape');

        if (is_array($raw)) {
            $out = [];
            foreach ($raw as $value) {
                $s = trim((string) $value);
                if ($s !== '') {
                    $out[] = $s;
                }
            }

            return array_values(array_unique($out));
        }

        return Item::normalizeJewelryOptionList($raw) ?? [];
    }

    public function shapeDisplay(): string
    {
        $labels = $this->shapeLabels();

        return $labels === [] ? '' : implode(', ', $labels);
    }
}
