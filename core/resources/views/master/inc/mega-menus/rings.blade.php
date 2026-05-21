{{-- Wedding / fashion rings mega + icons --}}
<div class="row luxury-mega-row g-0">
    <div class="col-lg-4 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by style') }}</p>
        @foreach ([
            ['icon' => 'ring-women', 'label' => __('All rings'), 'href' => url('/catalog?category=Wedding-Rings')],
            ['icon' => 'ring-eternity', 'label' => __('Eternity rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Eternity-Rings']))],
            ['icon' => 'ring-women', 'label' => __('Curved rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Curved-Rings']))],
            ['icon' => 'ring-anniversary', 'label' => __('Anniversary rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Anniversary-Rings']))],
            ['icon' => 'ring-men', 'label' => __("Men's wedding rings"), 'href' => url('/catalog?' . http_build_query(['subcategory' => "Men-s-wedding-Rings"]))],
        ] as $row)
            <a href="{{ $row['href'] }}" class="luxury-mega-link luxury-mega-link--with-icon">
                @include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])
                <span>{{ $row['label'] }}</span>
            </a>
        @endforeach
        <div class="luxury-mega-divider"></div>
        <a href="{{ url('/wedding-rings') }}" class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'star-premier'])<span>{{ __('Best-selling wedding rings') }}</span></a>
    </div>
    <div class="col-lg-4 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Women') }}</p>
        @foreach ([['icon' => 'diamond', 'label' => __('Diamond wedding rings')], ['icon' => 'ring-women', 'label' => __('Classic wedding rings')], ['icon' => 'ring-women', 'label' => __('Curved wedding rings')], ['icon' => 'sparkle', 'label' => __("Fashion wedding rings")], ['icon' => 'ring-women', 'label' => __("Women's wedding rings")]] as $row)
            <a href="{{ route('front.catalog', ['search' => $row['label']]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])<span>{{ $row['label'] }}</span></a>
        @endforeach
        <p class="luxury-mega-subkicker mt-3">{{ __('Men') }}</p>
        @foreach ([['icon' => 'ring-men', 'label' => __('Diamond bands')], ['icon' => 'ring-men', 'label' => __('Classic bands')], ['icon' => 'ring-men', 'label' => __('Carved bands')], ['icon' => 'ring-men', 'label' => __('Alternative metals')], ['icon' => 'ring-men', 'label' => __("Men's wedding rings")]] as $row)
            <a href="{{ route('front.catalog', ['search' => $row['label']]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])<span>{{ $row['label'] }}</span></a>
        @endforeach
    </div>
    <div class="col-lg-4 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by metal') }}</p>
        @foreach ([['label' => __('White gold'), 'q' => 'white gold ring'], ['label' => __('Yellow gold'), 'q' => 'yellow gold ring'], ['label' => __('Rose gold'), 'q' => 'rose gold ring'], ['label' => __('Platinum'), 'q' => 'platinum ring'], ['label' => __('Titanium'), 'q' => 'titanium ring']] as $m)
            <a href="{{ route('front.catalog', ['search' => $m['q']]) }}" class="luxury-mega-link luxury-mega-link--swatch">
                <span class="luxury-mega-swatch luxury-mega-swatch--{{ \Illuminate\Support\Str::slug($m['q'], '-') }}" aria-hidden="true"></span>
                {{ $m['label'] }}
            </a>
        @endforeach
        <p class="luxury-mega-subkicker mt-3">{{ __('Diamond classics') }}</p>
        <a href="{{ route('front.catalog', ['search' => 'eternity ring']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-eternity'])<span>{{ __('Eternity rings') }}</span></a>
        <a href="{{ route('front.catalog', ['search' => 'anniversary ring']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-anniversary'])<span>{{ __('Anniversary rings') }}</span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Education') }}</p>
        <a href="{{ route('education.guides.show', ['slug' => 'wedding-bands-guide']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'book'])<span>{{ __('Wedding ring style') }}</span></a>
        <a href="{{ route('education.guides.show', ['slug' => 'metal-types']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-men'])<span>{{ __('Alternative metals') }}</span></a>
        <a href="{{ route('education.guides.show', ['slug' => 'ring-settings']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-setting'])<span>{{ __('Ring guide') }}</span></a>
    </div>
</div>
