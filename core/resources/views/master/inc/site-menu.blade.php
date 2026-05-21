
@php
  
    $links = json_decode($menus->menus, true);
 
@endphp

<nav class="site-menu luxury-nav-wrap">
    <ul class="luxury-nav">
      
        @foreach ($links as $link)
            @php
             $href = Helper::getHref($link); 
             $megaKey = \App\Support\LuxuryMegaMenu::keyForMenuLink($link);
             $finalHref = $link['href'] == null ? $href : $link['href'];
             $linkTarget = $link['target'] ?? '_self';
            @endphp

            @if ($megaKey && view()->exists('master.inc.mega-menus.' . $megaKey))
                <li class="t-h-dropdown luxury-nav__item luxury-nav__item--has-mega @if($finalHref == URL::current()) active @endif">
                    <a class="main-link luxury-nav__link" href="{{ $finalHref }}" target="{{ $linkTarget }}">{{ $link['text'] }}<i class="icon-chevron-down"></i></a>

                    <div class="luxury-mega-panel" role="navigation" aria-label="{{ $link['text'] }}">
                        <div class="luxury-mega-panel__scroll">
                            <div class="container luxury-mega-panel__inner py-4 py-lg-5">
                                @include('master.inc.mega-menus.' . $megaKey)
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (!array_key_exists("children",$link))
                <li class="luxury-nav__item @if($href == URL::current() ) active  @endif">
                    <a class="luxury-nav__link" href="{{ $link["href"] == null ? $href : $link["href"] }}" target="{{ $linkTarget }}">{{$link["text"]}}</a>
                </li>
            @else
                <li class="t-h-dropdown luxury-nav__item">
                    <a class="main-link luxury-nav__link" href="{{$href}}" target="{{ $linkTarget }}">{{$link["text"]}}<i class="icon-chevron-down"></i></a>

                    <div class="t-h-dropdown-menu luxury-megamenu">
                        @foreach ($link["children"] as $level2)

                        @php
                            $l2Href = Helper::getHref($level2);
                            
                        @endphp
                        
                        <a class="@if($l2Href == URL::current() ) active  @endif" href="{{$l2Href}}" target="{{ $level2['target'] ?? '_self' }}">
                            <i class="icon-chevron-right pr-2"></i>
                            {{$level2["text"]}}
                        </a>
                        @endforeach
                    </div>

                </li>
            @endif

        @endforeach
    </ul>
</nav>
