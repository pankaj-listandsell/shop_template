@if (is_plugin_active('ecommerce'))
    @php
        $products = get_trending_products(['take' => 8]);
    @endphp

    @if ($products && $products->isNotEmpty())
        {{--
            Deliberately not another card grid. Best sellers already renders eight of those
            immediately above, and a second identical block gave the page no rhythm. A
            ranked list also matches what "trending" means better than a grid does.
        --}}
        <section class="dh-section dh-trending">
            <div class="dh-container">
                <header class="dh-section__head dh-section__head--split">
                    <div>
                        <h2 class="dh-section__title">{{ __('Trending this week') }}</h2>
                        <p class="dh-section__sub">{{ __('Most viewed themes and plugins right now') }}</p>
                    </div>
                    <a class="dh-section__more" href="{{ route('public.products') }}">{{ __('View all') }}</a>
                </header>

                <ol class="dh-rank">
                    @foreach ($products as $product)
                        <li class="dh-rank__item">
                            <span class="dh-rank__no">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>

                            <a class="dh-rank__thumb" href="{{ $product->url }}" title="{{ $product->name }}">
                                {!! RvMedia::image($product->image, $product->name, 'thumb') !!}
                            </a>

                            <span class="dh-rank__body">
                                @if ($category = $product->categories->first())
                                    <a class="dh-rank__cat" href="{{ $category->url }}">{{ $category->name }}</a>
                                @endif
                                <a class="dh-rank__title" href="{{ $product->url }}">{!! BaseHelper::clean($product->name) !!}</a>
                            </span>

                            <span class="dh-rank__price">
                                {!! Theme::partial('product-price', [
                                    'product' => $product,
                                    'priceWrapperTag' => 'span',
                                    'priceWrapperClass' => 'dh-rank__price-value',
                                ]) !!}
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif
@endif
