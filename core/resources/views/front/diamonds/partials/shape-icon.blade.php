@php
    $shapeKey = (string) ($key ?? $shape ?? '');
    $iconUrl = \App\Helpers\ImageHelper::diamondShapeIconUrl($shapeKey);
    $svgClass = trim(($svgClass ?? '') !== '' ? $svgClass : 'diamond-shape-btn__img');
@endphp
<span class="diamond-shape-btn__icon" aria-hidden="true">
    @if ($iconUrl)
        <img src="{{ $iconUrl }}" alt="" class="{{ $svgClass }}" loading="lazy" decoding="async" width="38" height="38">
    @else
        @include('front.diamonds.partials.shape-svg', [
            'key' => $shapeKey,
            'svgClass' => 'shape-svg ' . $svgClass,
        ])
    @endif
</span>
