{{-- Home "first banner": 5 square category tiles (luxury strip). Expects $banner_first array. --}}
{{-- Same image URL pattern as genius-banner tiles: url('/core/public/storage/images/'.$file) — do not split url() then concat filenames (slashes get dropped). --}}
<div class="luxury-first-banner-strip bannner-section mt-60">
    <div class="container">
        <h2 class="luxury-first-banner-strip__title">{{ __('Shop by collection') }}</h2>
        <div class="luxury-first-banner-strip__grid">
            @for ($i = 1; $i <= 5; $i++)
                @php
                    $img = trim((string) ($banner_first['img' . $i] ?? ''));
                    $href = trim((string) ($banner_first['firsturl' . $i] ?? ''));
                    if ($href === '') {
                        $href = '#';
                    }
                @endphp
                <div class="luxury-first-banner-strip__cell">
                    <a href="{{ $href }}" class="luxury-category-tile">
                        <div class="luxury-category-tile__media">
                            @if ($img !== '')
                                <img src="{{ url('/core/public/storage/images/' . ltrim($img, '/')) }}"
                                    alt="{{ strip_tags((string) ($banner_first['title' . $i] ?? '')) }}">
                            @endif
                        </div>
                        <div class="luxury-category-tile__label">
                            @if (!empty($banner_first['subtitle' . $i]))
                                <p class="luxury-category-tile__subtitle">{{ $banner_first['subtitle' . $i] }}</p>
                            @endif
                            @if (!empty($banner_first['title' . $i]))
                                <h4 class="luxury-category-tile__title">{{ $banner_first['title' . $i] }}</h4>
                            @endif
                        </div>
                    </a>
                </div>
            @endfor
        </div>
    </div>
</div>
