@if ($product && is_object($product))
    @php
        /**
         * Marketplace product tile. Every caller wraps this in <div class="ps-product">,
         * which is neutralised in digital-home.scss so this card supplies the chrome.
         *
         * The class names changed from ps-product__* to dh-card__*, but the JS contract did
         * not: behaviour comes from the data-bb-toggle attributes that
         * EcommerceHelper::jsAttributes() emits (EcommerceHelper.php:1886), plus data-url /
         * data-id. Those are reproduced verbatim below - do not "tidy" them away.
         */
        // Badge only earns its place when the catalogue is actually mixed - see
        // martfury_catalogue_is_mixed() in functions/digital-home.php.
        $isDigital = function_exists('martfury_show_digital_badge')
            && martfury_show_digital_badge($product);
        $demoUrl = function_exists('martfury_product_meta') ? martfury_product_meta($product, 'demo_url') : '';
        $version = function_exists('martfury_product_meta') ? martfury_product_meta($product, 'version') : '';
    @endphp

    <div class="dh-card dh-card--tile">
        <a class="dh-card__thumb" href="{{ $product->url }}" title="{{ $product->name }}">
            {!! RvMedia::image($product->image, $product->name, 'medium', lazy: $lazy ?? true) !!}

            @if ($isDigital)
                <span class="dh-card__type">{{ __('Digital') }}</span>
            @endif

            @if ($product->isOutOfStock())
                <span class="dh-card__labels">
                    <span class="dh-card__label dh-card__label--out">{{ __('Out Of Stock') }}</span>
                </span>
            @elseif ($product->productLabels->isNotEmpty())
                <span class="dh-card__labels">
                    @foreach ($product->productLabels as $label)
                        <span class="dh-card__label" {!! $label->css_styles !!}>{{ $label->name }}</span>
                    @endforeach
                </span>
            @elseif ($product->front_sale_price !== $product->price)
                <span class="dh-card__labels">
                    <span class="dh-card__label dh-card__label--sale">{{ get_sale_percentage($product->price, $product->front_sale_price) }}</span>
                </span>
            @endif

            @if ($demoUrl)
                <span class="dh-card__preview" role="button" tabindex="0"
                      data-dh-preview="{{ $demoUrl }}"
                      title="{{ __('Open live preview in a new tab') }}">{{ __('Live preview') }}</span>
            @endif
        </a>

        <ul class="dh-card__actions">
            @if (EcommerceHelper::isCartEnabled())
                @if ($product->has_variation)
                    <li><a href="#" data-bb-toggle="quick-shop" data-url="{{ route('public.ajax.quick-shop', $product->slug) }}" {!! EcommerceHelper::jsAttributes('quick-shop', $product) !!} title="{{ __('Select Options') }}"><i class="icon-bag2"></i></a></li>
                @else
                    <li><a class="add-to-cart-button" data-id="{{ $product->id }}" href="#" data-url="{{ route('public.cart.add-to-cart') }}" {!! EcommerceHelper::jsAttributes('add-to-cart', $product, additional: ['data-bb-toggle' => 'none']) !!} title="{{ __('Add To Cart') }}"><i class="icon-bag2"></i></a></li>
                @endif
            @endif
            <li><a class="js-quick-view-button" href="#" data-url="{{ route('public.ajax.quick-view', $product->id) }}" title="{{ __('Quick View') }}"><i class="icon-eye"></i></a></li>
            @if (EcommerceHelper::isWishlistEnabled())
                <li><a class="js-add-to-wishlist-button" href="#" data-url="{{ route('public.wishlist.add', $product->id) }}" title="{{ __('Add to Wishlist') }}"><i class="icon-heart"></i></a></li>
            @endif
            @if (EcommerceHelper::isCompareEnabled())
                <li><a class="js-add-to-compare-button" href="#" data-url="{{ route('public.compare.add', $product->id) }}" title="{{ __('Compare') }}"><i class="icon-chart-bars"></i></a></li>
            @endif
        </ul>

        <div class="dh-card__body">
            @if (is_plugin_active('marketplace') && $product->store->id)
                <a class="dh-card__vendor" href="{{ $product->store->url }}" title="{{ __('Visit store') }}: {{ $product->store->name }}">{{ $product->store->name }} {!! $product->store->badge !!}</a>
            @elseif ($category = $product->categories->first())
                <a class="dh-card__cat" href="{{ $category->url }}">{{ $category->name }}</a>
            @endif

            <a class="dh-card__title" href="{{ $product->url }}" title="{{ $product->name }}">{!! BaseHelper::clean($product->name) !!}</a>

            @if (EcommerceHelper::isReviewEnabled())
                <div class="dh-card__meta">
                    <span class="dh-card__rating" title="{{ $product->reviews_avg }} / 5">
                        <span class="dh-card__stars">
                            <span class="dh-card__stars-on" style="width: {{ $product->reviews_avg * 20 }}%"></span>
                        </span>
                        <span class="dh-card__reviews">({{ $product->reviews_count }})</span>
                    </span>
                </div>
            @endif

            @if ($version || $product->updated_at)
                <div class="dh-card__spec">
                    @if ($version)
                        <span class="dh-card__version">v{{ $version }}</span>
                    @endif
                    @if ($product->updated_at && function_exists('martfury_product_updated_label'))
                        <span class="dh-card__updated">{{ __('Updated') }} {{ martfury_product_updated_label($product) }}</span>
                    @endif
                </div>
            @endif

            @php
                $priceHtml = Theme::partial('product-price', [
                    'product' => $product,
                    'priceWrapperTag' => 'p',
                    'priceWrapperClass' => 'dh-card__price',
                ]);
            @endphp

            @if ($priceHtml)
                <div class="dh-card__footer">
                    {!! apply_filters('ecommerce_before_product_price_in_listing', null, $product) !!}
                    {!! $priceHtml !!}
                    {!! apply_filters('ecommerce_after_product_price_in_listing', null, $product) !!}
                </div>
            @endif
        </div>
    </div>
@endif
