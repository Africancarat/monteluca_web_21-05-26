@if (isset($complete_the_look_items) && $complete_the_look_items->isNotEmpty())
    <div class="complete-the-look relatedproduct-section container padding-bottom-3x mb-1 s-pt-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2 class="h3 luxury-headline">{{ __('Complete the look') }}</h2>
                    <p class="small text-muted max-width-legend">{{ __('Hand-picked pairing ideas — coordinating wedding bands, studs, or pendants for the same celebration.') }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="complete-the-look-slider owl-carousel">
                    @foreach ($complete_the_look_items as $citem)
                        <div class="slider-item">
                            <div class="product-card">
                                @if (! $citem->is_stock())
                                    <div class="product-badge bg-secondary border-default text-body">{{ __('out of stock') }}</div>
                                @endif
                                <div class="product-thumb">
                                    <img class="lazy"
                                        data-src="{{ \App\Helpers\ImageHelper::storageImageUrl($citem->thumbnail ?: $citem->photo) }}"
                                        alt="{{ $citem->name }}">
                                    <div class="product-button-group">
                                        <a class="product-button wishlist_store"
                                            href="{{ route('user.wishlist.store', $citem->id) }}"
                                            title="{{ __('Wishlist') }}"><i class="icon-heart"></i></a>
                                        @include('includes.item_footer', ['sitem' => $citem])
                                    </div>
                                </div>
                                <div class="product-card-body">
                                    @if ($citem->category && $citem->category->slug)
                                        <div class="product-category">
                                            <a href="{{ route('front.catalog') . '?category=' . $citem->category->slug }}">{{ $citem->category->name }}</a>
                                        </div>
                                    @endif
                                    <h3 class="product-title">
                                        <a href="{{ route('front.product', $citem->slug) }}">{{ Str::limit($citem->name, 35) }}</a>
                                    </h3>
                                    <h4 class="product-price">
                                        @if ($citem->previous_price != 0)
                                            <del>{{ PriceHelper::setPreviousPrice($citem->previous_price) }}</del>
                                        @endif
                                        {{ PriceHelper::grandCurrencyPrice($citem) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
