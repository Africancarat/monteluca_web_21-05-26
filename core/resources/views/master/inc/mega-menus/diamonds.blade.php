{{-- Diamonds mega: loose search + design paths + jewelry + icons --}}
<div class="row luxury-mega-row g-0">
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Design your own engagement ring') }}</p>
        <a href="{{ route('diamonds.index') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond'])<span>{{ __('Start with a diamond') }}</span></a>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-lab'])<span>{{ __('Start with a lab-grown diamond') }}</span></a>
        <a href="{{ route('diamonds.index') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-fancy'])<span>{{ __('Start with a fancy colour diamond') }}</span></a>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-fancy'])<span>{{ __('Start with a fancy lab-grown diamond') }}</span></a>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'ring-setting'])<span>{{ __('Start with a setting') }}</span></a>
        <a href="{{ route('education.guides.show', ['slug' => 'ring-settings']) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'sparkle'])<span>{{ __('The ring studio') }}</span></a>
        <div class="luxury-mega-divider"></div>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--accent luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-lab'])<span>{{ __('Lab-grown diamonds — featured') }}</span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Premier diamond collection') }}</p>
        <a href="{{ route('diamonds.index') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'star-premier'])<span>{{ __('True hearts & ideal cuts') }}</span></a>
        <a href="{{ route('diamonds.index') }}" class="luxury-mega-link luxury-mega-link--footer luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond'])<span>{{ __('Shop all diamonds') }}</span></a>
    </div>
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Loose diamonds') }}</p>
        <div class="luxury-mega-grid luxury-mega-grid--shapes">
            @foreach (['Round', 'Princess', 'Cushion', 'Emerald', 'Pear', 'Oval', 'Radiant', 'Asscher', 'Marquise', 'Heart'] as $shape)
                <a href="{{ route('diamonds.index', ['shape' => $shape]) }}" class="luxury-mega-pill luxury-mega-pill--with-icon luxury-mega-pill--shape">
                    @include('front.diamonds.partials.shape-svg', ['key' => $shape, 'svgClass' => 'luxury-mega-ico luxury-mega-ico--shape'])
                    <span>{{ $shape }}</span>
                </a>
            @endforeach
            <a href="{{ route('diamonds.index', ['shape' => 'Octagon']) }}" class="luxury-mega-pill luxury-mega-pill--with-icon luxury-mega-pill--shape">
                @include('front.diamonds.partials.shape-svg', ['key' => 'Octagon', 'svgClass' => 'luxury-mega-ico luxury-mega-ico--shape'])
                <span>{{ __('Octagon') }} <span class="luxury-mega-badge">New</span></span>
            </a>
        </div>
        <p class="luxury-mega-subkicker mt-3">{{ __('Fancy colour diamonds') }}</p>
        <div class="luxury-mega-grid">
            @foreach ([['code' => 'yellow', 'label' => __('Yellow')], ['code' => 'pink', 'label' => __('Pink')], ['code' => 'purple', 'label' => __('Purple')], ['code' => 'blue', 'label' => __('Blue')], ['code' => 'green', 'label' => __('Green')], ['code' => 'orange', 'label' => __('Orange')], ['code' => 'brown', 'label' => __('Brown')], ['code' => 'black', 'label' => __('Black')]] as $fc)
                <a href="{{ route('front.catalog', ['search' => $fc['label'].' '.__('diamond')]) }}" class="luxury-mega-pill luxury-mega-pill--with-icon luxury-mega-pill--fancy luxury-mega-pill--fancy-{{ $fc['code'] }}">
                    @include('master.inc.mega-menus.partials.icon', ['name' => 'diamond', 'class' => 'luxury-mega-ico luxury-mega-pill__ico'])
                    <span>{{ $fc['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="col-lg-3 luxury-mega-col">
        <p class="luxury-mega-kicker">{{ __('Design your own jewelry') }}</p>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'earrings'])<span>{{ __('Earrings') }}</span></a>
        <a href="{{ route('diamonds.compare.index') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'hearts-pair'])<span>{{ __('Natural diamond pairs') }}</span></a>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'hearts-pair-lab'])<span>{{ __('Lab-grown diamond pairs') }}</span></a>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'pendant'])<span>{{ __('Pendants') }}</span></a>
        <p class="luxury-mega-subkicker mt-3">{{ __('Diamond jewelry') }}</p>
        @foreach ([['icon' => 'ring-eternity', 'label' => __('Eternity rings')], ['icon' => 'ring-anniversary', 'label' => __('Anniversary rings')], ['icon' => 'studs', 'label' => __('Diamond studs')], ['icon' => 'pendant', 'label' => __('Diamond pendants')], ['icon' => 'tennis', 'label' => __('Tennis bracelets')]] as $j)
            <a href="{{ route('front.catalog', ['search' => $j['label']]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => $j['icon']])<span>{{ $j['label'] }}</span></a>
        @endforeach
        <div class="luxury-mega-divider"></div>
        <a href="{{ route('front.catalog') }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'necklace-arc'])<span>{{ __('All diamond jewelry') }}</span></a>
        <a href="{{ route('diamonds.index', ['lab_grown' => 1]) }}" class="luxury-mega-link luxury-mega-link--with-icon">@include('master.inc.mega-menus.partials.icon', ['name' => 'diamond-lab'])<span>{{ __('All lab-grown diamond jewelry') }}</span></a>
    </div>
    @include('master.inc.mega-menus.partials.feature-image', [
        'src' => url('/core/public/storage/images/crowning-gemstone-jewelry.jpg'),
        'href' => route('diamonds.index'),
        'alt' => __('Diamonds'),
    ])
</div>
