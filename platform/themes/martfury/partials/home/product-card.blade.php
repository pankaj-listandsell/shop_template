@php
    /**
     * Card used by the best-sellers and trending sections.
     *
     * $product      - Botble\Ecommerce\Models\Product
     * $showSales    - bool, print the "N sales" line (best-sellers only)
     */
    $showSales = $showSales ?? false;
    // Badge only earns its place when the catalogue is actually mixed - see
    // martfury_catalogue_is_mixed() in functions/digital-home.php.
    $isDigital = martfury_show_digital_badge($product);
    $demoUrl = martfury_product_meta($product, 'demo_url');
    $version = martfury_product_meta($product, 'version');
@endphp

<div class="dh-card">
    <a class="dh-card__thumb" href="{{ $product->url }}" title="{{ $product->name }}">
        {!! RvMedia::image($product->image, $product->name, 'medium') !!}

        @if ($isDigital)
            <span class="dh-card__type">{{ __('Digital') }}</span>
        @endif

        @if ($demoUrl)
            {{-- Sits inside the thumb link, so it must stop the click bubbling to the product page. --}}
            <span class="dh-card__preview" role="button" tabindex="0"
                  data-dh-preview="{{ $demoUrl }}"
                  title="{{ __('Open live preview in a new tab') }}">{{ __('Live preview') }}</span>
        @endif

        @if ($product->productLabels->isNotEmpty())
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
    </a>

    <div class="dh-card__body">
        @if ($category = $product->categories->first())
            <a class="dh-card__cat" href="{{ $category->url }}">{{ $category->name }}</a>
        @endif

        <a class="dh-card__title" href="{{ $product->url }}" title="{{ $product->name }}">{!! BaseHelper::clean($product->name) !!}</a>

        <div class="dh-card__meta">
            @if (EcommerceHelper::isReviewEnabled())
                <span class="dh-card__rating" title="{{ $product->reviews_avg }} / 5">
                    <span class="dh-card__stars">
                        <span class="dh-card__stars-on" style="width: {{ $product->reviews_avg * 20 }}%"></span>
                    </span>
                    <span class="dh-card__reviews">({{ $product->reviews_count }})</span>
                </span>
            @endif

            @if ($showSales && $product->sales_count)
                <span class="dh-card__sales">{{ martfury_digital_home_format_count((int) $product->sales_count) }} {{ __('sales') }}</span>
            @endif
        </div>

        @if ($version || $product->updated_at)
            <div class="dh-card__spec">
                @if ($version)
                    <span class="dh-card__version">v{{ $version }}</span>
                @endif
                @if ($product->updated_at)
                    <span class="dh-card__updated">{{ __('Updated') }} {{ martfury_product_updated_label($product) }}</span>
                @endif
            </div>
        @endif

        <div class="dh-card__footer">
            {!! Theme::partial('product-price', [
                'product' => $product,
                'priceWrapperTag' => 'p',
                'priceWrapperClass' => 'dh-card__price',
            ]) !!}

            @if (EcommerceHelper::isCartEnabled())
                @if ($product->has_variation)
                    <a class="dh-card__buy" href="#" data-bb-toggle="quick-shop" data-url="{{ route('public.ajax.quick-shop', $product->slug) }}" {!! EcommerceHelper::jsAttributes('quick-shop', $product) !!} title="{{ __('Select Options') }}">{{ __('Options') }}</a>
                @else
                    <a class="dh-card__buy add-to-cart-button" href="#" data-id="{{ $product->id }}" data-url="{{ route('public.cart.add-to-cart') }}" {!! EcommerceHelper::jsAttributes('add-to-cart', $product, additional: ['data-bb-toggle' => 'none']) !!} title="{{ __('Add To Cart') }}">{{ __('Add to cart') }}</a>
                @endif
            @endif
        </div>
    </div>
</div>
