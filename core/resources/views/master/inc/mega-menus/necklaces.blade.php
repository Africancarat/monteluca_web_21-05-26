{{-- Necklaces mega + icons --}}
<div class="row luxury-mega-row g-0">
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop all necklaces') }}</p>
        {{-- Top subcategories (match screenshot) --}}
        @foreach ([
            ['icon' => 'necklace-arc', 'label' => __('All Necklaces'), 'href' => url('/catalog?' . http_build_query(['search' => 'Necklace']))],
            ['icon' => 'pendant', 'label' => __('Pendant'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Pendant']))],
            ['icon' => 'chain', 'label' => __('Chain Necklaces'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Chain-Necklace']))],
            ['icon' => 'pendant', 'label' => __('Pendant Necklaces'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Pendant-Necklace']))],
            ['icon' => 'necklace-arc', 'label' => __('Layering Necklaces'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Layering-Necklace']))],
            ['icon' => 'necklace-arc', 'label' => __('Choker Necklaces'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Choker-Necklace']))],
        ] as $row)
            <a href="{{ $row['href'] }}" class="luxury-mega-link luxury-mega-link--with-icon">
                @include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])
                <span>{{ $row['label'] }}</span>
            </a>
        @endforeach

        {{-- Existing links remain after the top list --}}
        @foreach ([
            ['icon' => 'tennis', 'label' => __('Diamond necklaces')],
            ['icon' => 'chain', 'label' => __('Gold necklaces')],
            ['icon' => 'pendant', 'label' => __('Cross necklaces')],
            ['icon' => 'necklace-arc', 'label' => __("Men's necklaces")],
            ['icon' => 'chain', 'label' => __('Cuban chains')],
            ['icon' => 'necklace-arc', 'label' => __('Pearl necklaces')],
            ['icon' => 'gemstone', 'label' => __('Birthstone necklaces')],
            ['icon' => 'gemstone', 'label' => __('Gemstone necklaces')],
        ] as $row)
            <a href="{{ route('front.catalog', ['search' => $row['label']]) }}" class="luxury-mega-link luxury-mega-link--with-icon">
                @include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])
                <span>{{ $row['label'] }}</span>
            </a>
        @endforeach
        <div class="luxury-mega-divider"></div>
        <a href="{{ route('front.catalog', ['search' => 'necklace']) }}" class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'star-premier'])<span>{{ __('Best-selling necklaces') }}</span></a>
    </div>
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by metal') }}</p>
        @foreach ([['label' => __('White gold necklaces'), 'q' => 'white gold necklace'], ['label' => __('Yellow gold necklaces'), 'q' => 'yellow gold necklace'], ['label' => __('Rose gold necklaces'), 'q' => 'rose gold necklace'], ['label' => __('Platinum necklaces'), 'q' => 'platinum necklace']] as $m)
            <a href="{{ route('front.catalog', ['search' => $m['q']]) }}" class="luxury-mega-link luxury-mega-link--swatch">
                <span class="luxury-mega-swatch luxury-mega-swatch--{{ \Illuminate\Support\Str::slug($m['q'], '-') }}" aria-hidden="true"></span>
                {{ $m['label'] }}
            </a>
        @endforeach
        <p class="luxury-mega-subkicker mt-3">{{ __('Design your own') }}</p>
        <a href="{{ route('front.catalog', ['search' => 'pendant']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'pendant'])<span>{{ __('Pendants') }}</span></a>
        <a href="{{ route('diamonds.index') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond'])<span>{{ __('Natural diamond') }}</span></a>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-lab'])<span>{{ __('Lab-grown diamond') }}</span></a>
    </div>
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Diamond classics') }}</p>
        <a href="{{ route('front.catalog', ['search' => 'tennis necklace']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'tennis'])<span>{{ __('Tennis necklaces') }}</span></a>
        <a href="{{ route('front.catalog', ['search' => 'diamond pendant']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'pendant'])<span>{{ __('Diamond pendants') }}</span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Collections') }}</p>
        <a href="{{ route('front.catalog', ['search' => 'charms']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'sparkle'])<span>{{ __('Charms collection') }}</span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Education') }}</p>
        <a href="{{ route('education.guides.show', ['slug' => 'necklaces-and-pendants']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'book'])<span>{{ __('Pendants guide') }}</span></a>
        <div class="luxury-mega-divider"></div>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'necklace-arc'])<span>{{ __('View all jewelry') }} &rsaquo;</span></a>
    </div>
    @include('master.inc.mega-menus.partials.feature-image', [
        'src' => url('/core/public/storage/images/crowning-gemstone-jewelry.jpg'),
        'href' => route('front.catalog', ['search' => 'necklace']),
        'alt' => __('Necklaces'),
    ])
</div>
