@php
    $shapes = [
        ['Round', 'Round'], ['Princess', 'Princess'], ['Oval', 'Oval'], ['Cushion', 'Cushion'],
        ['Radiant', 'Radiant'], ['Pear', 'Pear'], ['Emerald', 'Emerald'], ['Asscher', 'Asscher'],
        ['Marquise', 'Marquise'], ['Heart', 'Heart'],
    ];
    $reqShape = request('shape');
    $currentShape = is_array($reqShape) ? (string) (($reqShape[0] ?? '') ?: '') : (string) ($reqShape ?: '');
@endphp

<div class="filter-group diamond-shape-group">
    <label class="filter-label">{{ __('Shape') }}</label>
    <div class="diamond-shapes-scroll" role="listbox" aria-label="{{ __('Diamond shape') }}">
        @foreach($shapes as [$label, $key])
            <button type="button"
                    data-shape="{{ $key }}"
                    class="diamond-shape-btn {{ $currentShape === $key ? 'is-active' : '' }}"
                    role="option"
                    aria-selected="{{ $currentShape === $key ? 'true' : 'false' }}"
                    title="{{ __($label) }}"
                    onclick="selectDiamondShape(event, '{{ $key }}')">
                @include('front.diamonds.partials.shape-icon', ['key' => $key])
                <span class="diamond-shape-btn__label">{{ __($label) }}</span>
            </button>
        @endforeach
        <button type="button"
                data-shape=""
                class="diamond-shape-btn diamond-shape-btn--all {{ $currentShape === '' ? 'is-active' : '' }}"
                role="option"
                aria-selected="{{ $currentShape === '' ? 'true' : 'false' }}"
                onclick="selectDiamondShape(event, '')">
            <span class="diamond-shape-btn__label">{{ __('All shapes') }}</span>
        </button>
    </div>
    <input type="hidden" id="diamondShapeInput" value="{{ $currentShape }}" @if($currentShape !== '') name="shape" @endif>
</div>
