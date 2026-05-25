{{-- James Allen–style engagement mega (column layout + deep links + icons). --}}
<div class="row luxury-mega-row g-0">
    <div class="col-lg-4 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by style') }}</p>
        @foreach ([
            ['icon' => 'ring-women', 'label' => __('All rings'), 'href' => url('/catalog?category=Engagement-Rings')],
            ['icon' => 'ring-setting', 'label' => __('Minimalist rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Minimalist-Rings']))],
            ['icon' => 'ring-setting', 'label' => __('Solitaire rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Solitaire-Rings']))],
            ['icon' => 'sparkle', 'label' => __('Halo rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Halo-Rings']))],
            ['icon' => 'ring-setting', 'label' => __('Bezel rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Bezel-Rings']))],
            ['icon' => 'ring-setting', 'label' => __('Toi et Moi rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Toi-et-mo-Rings']))],
            ['icon' => 'ring-eternity', 'label' => __('Eternity band'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Eternity-Rings']))],
            ['icon' => 'ring-eternity', 'label' => __('Stacking rings'), 'href' => url('/catalog?' . http_build_query(['subcategory' => 'Stacking-Rings']))],
            ['icon' => 'ring-anniversary', 'label' => __('Bridal sets'), 'href' => route('front.catalog', ['subcategory' => 'bridal-sets'])],
        ] as $row)
            <a href="{{ $row['href'] }}" class="luxury-mega-link luxury-mega-link--with-icon">
                @include('master.inc.mega-menus.partials.icon', ['name' => $row['icon']])
                <span>{{ $row['label'] }}</span>
            </a>
        @endforeach
        <div class="luxury-mega-divider"></div>

        <p class="luxury-mega-kicker">{{ __('Design your own engagement ring') }}</p>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-setting'])<span>{{ __('Start with a setting') }}</span></a>
        <a href="{{ route('diamonds.index') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond'])<span>{{ __('Start with a diamond') }}</span></a>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-lab'])<span>{{ __('Start with a lab-grown diamond') }}</span></a>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'gemstone'])<span>{{ __('Start with a gemstone') }}</span></a>
        <div class="luxury-mega-divider"></div>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--emphasis luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'sparkle'])<span>{{ __('Ready-to-ship engagement rings') }} <span class="luxury-mega-badge">New</span></span></a>
        <a href="{{ url('/engagement-rings') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-women'])<span>{{ __("Explore men's engagement rings") }}</span></a>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'star-premier'])<span>{{ __('Top engagement rings') }}</span></a>
        <a href="{{ url('/engagement-rings') }}" class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-setting'])<span>{{ __('Shop all engagement') }}</span></a>
    </div>
    <div class="col-lg-4 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Customize your engagement ring') }}</p>
        <a href="{{ route('education.guides.show', ['slug' => 'ring-settings']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'sparkle'])<span>{{ __('The ring studio') }}</span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Engagement ring styles') }}</p>
        <div class="luxury-mega-grid">
            @foreach ([['key' => 'solitaire', 'label' => __('Solitaire')], ['key' => 'bezel', 'label' => __('Bezel')], ['key' => 'pave', 'label' => __('Pavé')], ['key' => 'halo', 'label' => __('Halo')], ['key' => 'channel-set', 'label' => __('Channel-set')], ['key' => 'hidden-halo', 'label' => __('Hidden halo')], ['key' => 'side-stone', 'label' => __('Side-stone')], ['key' => 'three-stone', 'label' => __('Three-stone')]] as $row)
                <a href="{{ route('front.catalog', ['search' => $row['label']]) }}" class="luxury-mega-pill luxury-mega-pill--with-icon">
                    @include('master.inc.mega-menus.partials.style-icon', ['slug' => $row['key']])
                    <span>{{ $row['label'] }}</span>
                </a>
            @endforeach
        </div>
        <p class="luxury-mega-subkicker mt-3">{{ __('More styles') }}</p>
        <div class="luxury-mega-grid">
            @foreach ([['key' => 'tension', 'label' => __('Tension')], ['key' => 'unique', 'label' => __('Unique')], ['key' => 'floral', 'label' => __('Floral')], ['key' => 'cathedral', 'label' => __('Cathedral')], ['key' => 'tiara', 'label' => __('Tiara')], ['key' => 'cluster', 'label' => __('Cluster')], ['key' => 'vintage', 'label' => __('Vintage')]] as $row)
                <a href="{{ route('front.catalog', ['search' => $row['label']]) }}" class="luxury-mega-pill luxury-mega-pill--with-icon">
                    @include('master.inc.mega-menus.partials.style-icon', ['slug' => $row['key']])
                    <span>{{ $row['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="col-lg-4 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Shop by metal') }}</p>
        @foreach ([['label' => __('Rose gold'), 'q' => 'rose gold'], ['label' => __('White gold'), 'q' => 'white gold'], ['label' => __('Yellow gold'), 'q' => 'yellow gold'], ['label' => __('Platinum'), 'q' => 'platinum']] as $m)
            <a href="{{ route('front.catalog', ['search' => $m['q']]) }}" class="luxury-mega-link luxury-mega-link--swatch">
                <span class="luxury-mega-swatch luxury-mega-swatch--{{ \Illuminate\Support\Str::slug($m['q'], '-') }}" aria-hidden="true"></span>
                {{ $m['label'] }}
            </a>
        @endforeach
        <p class="luxury-mega-subkicker mt-3">{{ __('Collections') }}</p>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'sparkle'])<span>{{ __('Featured collections') }} <span class="luxury-mega-badge">New</span></span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Education') }}</p>
        <a href="{{ route('education.guides.show', ['slug' => 'engagement-ring-guide']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'book'])<span>{{ __('Engagement guide') }}</span></a>
    </div>
</div>
