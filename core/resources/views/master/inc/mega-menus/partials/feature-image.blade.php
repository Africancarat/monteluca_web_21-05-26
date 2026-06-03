@php
    $megaFeatureSrc = $src ?? url('/core/public/storage/images/crowning-mens-wedding-rings.jpg');
    $megaFeatureHref = $href ?? route('front.catalog');
    $megaFeatureAlt = $alt ?? __('Featured jewelry');
@endphp
<div class="col-lg-3 luxury-mega-col luxury-mega-col--media d-none d-lg-block">
    <a href="{{ $megaFeatureHref }}" class="luxury-mega-feature" tabindex="-1" aria-hidden="true">
        <img
            src="{{ $megaFeatureSrc }}"
            alt="{{ $megaFeatureAlt }}"
            class="luxury-mega-feature__img"
            loading="lazy"
            width="420"
            height="500">
    </a>
</div>
