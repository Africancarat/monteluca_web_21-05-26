@php
    use App\Models\Item;

    $item = $item ?? null;
    $shapeCatalog = [
        'ROUND', 'PEAR', 'OVAL', 'MARQUISE', 'PRINCESS', 'CUSHION', 'EMERALD',
        'RADIANT', 'ASSCHER', 'HEART', 'EMERALD CUT',
    ];

    $shapeSelected = $shapeSelected ?? [];
    if (! is_array($shapeSelected)) {
        $shapeSelected = Item::normalizeJewelryOptionList($shapeSelected) ?? [];
    }
    foreach ($shapeSelected as $s) {
        $s = strtoupper(trim((string) $s));
        if ($s !== '' && ! in_array($s, $shapeCatalog, true)) {
            $shapeCatalog[] = $s;
        }
    }

    $existingByCombo = [];
    if ($item) {
        foreach ($item->pdpShapeVariantRows() as $row) {
            $formShape = str_replace(' ', '_', $row['shape']);
            $metalKey = Item::pdpMetalLabelToFormKey($row['metal']);
            $existingByCombo[$formShape][$metalKey] = $row['images'];
        }
    }

    $metalOptions = Item::PDP_METAL_IMAGE_OPTIONS;
@endphp

<div class="card" id="pdp_shape_metal_images_card">
    <div class="card-body">
        {{-- Signals repository to process shape×metal uploads on save (see ItemRepository::persistPdpShapeVariants). --}}
        <input type="hidden" name="pdp_shape_matrix" value="1">
        {{--
            Upload field names (multiple files per cell):
              pdp_shape_metal_images[ROUND][YELLOW_GOLD][]
              pdp_shape_metal_images[PEAR][ROSE_GOLD][]
            Keep existing filenames:
              pdp_shape_keep[ROUND][YELLOW_GOLD][]
        --}}
        <h6 class="mb-2"><b>{{ __('PDP gallery images (shape + metal)') }}</b></h6>
        <p class="small text-muted mb-3">
            {{ __('Upload one or more images for each diamond shape and metal finish. On the product page, the gallery updates when the customer changes shape or metal. If a cell is empty, the storefront uses the Featured image + Gallery images (Yellow Gold) or previously saved metal galleries.') }}
        </p>

        @foreach ($shapeCatalog as $shapeLabel)
            @php
                $shapeLabel = (string) $shapeLabel;
                $formShape = str_replace(' ', '_', strtoupper($shapeLabel));
            @endphp
            <div class="border rounded p-3 mb-3">
                <h6 class="mb-3 text-uppercase"><b>{{ $shapeLabel }}</b></h6>
                <div class="row">
                    @foreach ($metalOptions as $metalLabel)
                        @php
                            $metalKey = Item::pdpMetalLabelToFormKey($metalLabel);
                            $existing = $existingByCombo[$formShape][$metalKey] ?? [];
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                            <label class="d-block font-weight-bold small mb-2">{{ $metalLabel }}</label>
                            @if ($existing !== [])
                                <div class="d-flex flex-wrap mb-2">
                                    @foreach ($existing as $fn)
                                        @php $fn = (string) $fn; @endphp
                                        @if ($fn === '')
                                            @continue
                                        @endif
                                        <div class="mr-2 mb-2 text-center" style="max-width:90px;">
                                            <img src="{{ url('/core/public/storage/images/'.$fn) }}"
                                                alt=""
                                                class="img-thumbnail d-block mb-1"
                                                style="max-width:84px;max-height:84px;object-fit:cover;">
                                            <input type="hidden"
                                                name="pdp_shape_keep[{{ $formShape }}][{{ $metalKey }}][]"
                                                value="{{ $fn }}">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <input type="file"
                                name="pdp_shape_metal_images[{{ $formShape }}][{{ $metalKey }}][]"
                                accept="image/*"
                                class="form-control form-control-sm"
                                multiple>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
