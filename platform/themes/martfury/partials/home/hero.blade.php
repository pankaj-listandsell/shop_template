@php
    $stats = martfury_digital_home_stats();
    $pills = is_plugin_active('ecommerce') ? get_featured_product_categories()->take(6) : collect();
@endphp

<section class="dh-hero">
    <div class="dh-container">
        <div class="dh-hero__inner">
            <p class="dh-hero__eyebrow">{{ __('Themes & plugins for every stack') }}</p>

            <h1 class="dh-hero__title">
                {{ __('Build your next site') }}<br>
                <span class="dh-hero__title-accent">{{ __('without starting from scratch') }}</span>
            </h1>

            <p class="dh-hero__lead">
                {{ __('Production-ready themes and plugins, downloadable the moment you buy. Lifetime updates and a license key with every purchase.') }}
            </p>

            {{-- Same action + data-ajax-url as partials/header.blade.php:37 so the live suggestion dropdown keeps working. --}}
            <form class="dh-hero__search ps-form--quick-search" action="{{ route('public.products') }}" data-ajax-url="{{ route('public.ajax.search-products') }}" method="get">
                <label class="dh-hero__search-label" for="dh-hero-q">{{ __('Search') }}</label>
                <input
                    id="dh-hero-q"
                    class="dh-hero__search-input form-control"
                    name="q"
                    type="text"
                    autocomplete="off"
                    value="{{ request()->query('q') }}"
                    placeholder="{{ __('Search :count+ themes and plugins…', ['count' => martfury_digital_home_format_count($stats['items'])]) }}"
                >
                <button class="dh-hero__search-btn" type="submit">{{ __('Search') }}</button>
                <div class="ps-panel--search-result"></div>
            </form>

            @if ($pills->isNotEmpty())
                <ul class="dh-hero__pills">
                    <li class="dh-hero__pills-label">{{ __('Popular:') }}</li>
                    @foreach ($pills as $category)
                        <li><a href="{{ $category->url }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
