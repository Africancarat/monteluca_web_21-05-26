{{-- Earrings mega + subcategories (luxury modal) --}}
<div class="row luxury-mega-row g-0">
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by earing') }}</p>

        {{-- Top subcategories (match screenshot) --}}
        @foreach ([
            {{-- "category" expects the category SLUG (CatalogController uses Category::whereSlug). --}}
            ['icon' => 'earrings', 'label' => __('All Earrings'), 'href' => url('/catalog?' . http_build_query(['category' => 'Earrings']))],
            ['icon' => 'earrings', 'label' => __('Stud Earrings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Stud-Earrings']))],
            ['icon' => 'earrings', 'label' => __('Huggies & Hoops'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Huggies-Hoops']))],
            ['icon' => 'earrings', 'label' => __('Bridal Earrings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Bridal-Earrings']))],
            ['icon' => 'earrings', 'label' => __('Dangle Earrings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Dangle-Earrings']))],
            ['icon' => 'diamond', 'label' => __('Diamond Earrings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Diamond-Earrings']))],
        ] as $row)
            <a href="{{ $row['href'] }}" class="luxury-mega-link luxury-mega-link--with-icon">
                @include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])
                <span>{{ $row['label'] }}</span>
            </a>
        @endforeach

        <div class="luxury-mega-divider"></div>
        <a href="{{ url('/catalog?' . http_build_query(['subcategory' => 'Earrings'])) }}"
            class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">
            @include('master.inc.mega-menus.partials.icon', ['name' => 'star-premier'])
            <span>{{ __('Best-selling earrings') }}</span>
        </a>
    </div>

    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by metal') }}</p>
        @foreach ([
            ['label' => __('White gold'), 'q' => 'white gold earrings'],
            ['label' => __('Yellow gold'), 'q' => 'yellow gold earrings'],
            ['label' => __('Rose gold'), 'q' => 'rose gold earrings'],
            ['label' => __('Platinum'), 'q' => 'platinum earrings'],
        ] as $m)
            <a href="{{ url('/catalog?' . http_build_query(['search' => $m['q']])) }}" class="luxury-mega-link luxury-mega-link--swatch">
                <span class="luxury-mega-swatch luxury-mega-swatch--{{ \Illuminate\Support\Str::slug($m['q'], '-') }}" aria-hidden="true"></span>
                {{ $m['label'] }}
            </a>
        @endforeach
    </div>

    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Diamond classics') }}</p>
        <a href="{{ url('/catalog?' . http_build_query(['search' => 'diamond studs'])) }}"
            class="luxury-mega-link luxury-mega-link--with-icon">
            @include('master.inc.mega-menus.partials.icon', ['name' => 'diamond'])
            <span>{{ __('Diamond studs') }}</span>
        </a>
        <a href="{{ url('/catalog?' . http_build_query(['search' => 'diamond hoops'])) }}"
            class="luxury-mega-link luxury-mega-link--with-icon">
            @include('master.inc.mega-menus.partials.icon', ['name' => 'diamond'])
            <span>{{ __('Diamond hoops') }}</span>
        </a>
        <a href="{{ url('/catalog?' . http_build_query(['search' => 'drop earrings'])) }}"
            class="luxury-mega-link luxury-mega-link--with-icon">
            @include('master.inc.mega-menus.partials.icon', ['name' => 'earrings'])
            <span>{{ __('Drop earrings') }}</span>
        </a>
        <div class="luxury-mega-divider"></div>
        <a href="{{ route('front.catalog') }}"
            class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">
            @include('master.inc.mega-menus.partials.icon', ['name' => 'earrings'])
            <span>{{ __('View all jewelry') }} &rsaquo;</span>
        </a>
    </div>
    @include('master.inc.mega-menus.partials.feature-image', [
        'src' => url('/core/public/storage/images/crowning-diamond-studs.jpg'),
        'href' => url('/catalog?' . http_build_query(['category' => 'Earrings'])),
        'alt' => __('Earrings'),
    ])
</div>

