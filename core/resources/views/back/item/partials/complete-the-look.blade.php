@php
    $excludeItemId = isset($item) ? (int) $item->id : 0;
    $selected = $completeTheLookSelected ?? collect();
@endphp

<div class="card mt-3">
    <div class="card-body">
        <div class="form-group mb-0">
            <label for="complete_the_look_ids">{{ __('Complete The Look') }}</label>
            <select name="complete_the_look_ids[]" id="complete_the_look_ids" class="form-control" multiple>
                @foreach ($selected as $lookItem)
                    <option value="{{ $lookItem->id }}" selected>
                        {{ $lookItem->name }}@if ($lookItem->sku) ({{ $lookItem->sku }})@endif
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">
                {{ __('Choose coordinating products to show on the product page. Search by name or SKU.') }}
            </small>
        </div>
    </div>
</div>
