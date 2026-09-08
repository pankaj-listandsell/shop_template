@if (is_plugin_active('ecommerce'))
    @php
        $products = martfury_digital_home_best_sellers(8);
    @endphp

    @if ($products->isNotEmpty())
        <section class="dh-section dh-section--tint dh-bestsellers">
            <div class="dh-container">
                <header class="dh-section__head dh-section__head--split">
                    <div>
                        <h2 class="dh-section__title">{{ __('Best sellers') }}</h2>
                        <p class="dh-section__sub">{{ __('What other builders are buying right now') }}</p>
                    </div>
                    <a class="dh-section__more" href="{{ route('public.products') }}">{{ __('View all') }}</a>
                </header>

                <div class="dh-grid">
                    @foreach ($products as $product)
                        {!! Theme::partial('home.product-card', ['product' => $product, 'showSales' => true]) !!}
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endif
