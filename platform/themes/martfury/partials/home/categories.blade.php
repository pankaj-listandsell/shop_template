@if (is_plugin_active('ecommerce'))
    @php
        $categories = get_featured_product_categories();
    @endphp

    @if ($categories->isNotEmpty())
        <section class="dh-section dh-categories">
            <div class="dh-container">
                <header class="dh-section__head dh-section__head--split">
                    <div>
                        <h2 class="dh-section__title">{{ __('Browse by category') }}</h2>
                        <p class="dh-section__sub">{{ __('Find the right starting point for your project') }}</p>
                    </div>
                    <a class="dh-section__more" href="{{ route('public.products') }}">{{ __('All items') }}</a>
                </header>

                <ul class="dh-categories__grid">
                    @foreach ($categories as $category)
                        @php
                            $count = $category->products_count ?? $category->products()->count();
                        @endphp
                        <li class="dh-categories__item">
                            <a href="{{ $category->url }}">
                                {{-- Illustration carries the card; the icon is the fallback. --}}
                                <span class="dh-categories__art">
                                    @if ($category->image)
                                        {!! RvMedia::image($category->image, $category->name, 'small') !!}
                                    @else
                                        <i class="{{ $category->icon ?: 'icon-layers' }}"></i>
                                    @endif
                                </span>
                                <span class="dh-categories__body">
                                    <span class="dh-categories__name">{{ $category->name }}</span>
                                    <span class="dh-categories__count">{{ trans_choice('{0}No items|{1}:count item|[2,*]:count items', $count, ['count' => $count]) }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endif
