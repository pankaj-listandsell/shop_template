@php
    /** Injected into the product detail page via the ecommerce_after_product_description filter. */
    $demoUrl = martfury_product_meta($product, 'demo_url');
    $version = martfury_product_meta($product, 'version');
    $compatibility = martfury_product_compatibility($product);
    $included = martfury_product_included($product);
    $updated = martfury_product_updated_label($product);
@endphp

@if ($demoUrl || $version || $compatibility || $included || $updated)
    <div class="dh-pmeta">
        @if ($demoUrl)
            <a class="dh-pmeta__preview" href="{{ $demoUrl }}" target="_blank" rel="noopener noreferrer">
                {{ __('Live preview') }}
                <span aria-hidden="true">&#8599;</span>
            </a>
        @endif

        <dl class="dh-pmeta__specs">
            @if ($version)
                <div class="dh-pmeta__row">
                    <dt>{{ __('Version') }}</dt>
                    <dd>{{ $version }}</dd>
                </div>
            @endif

            @if ($updated)
                <div class="dh-pmeta__row">
                    <dt>{{ __('Last updated') }}</dt>
                    <dd>{{ $updated }}</dd>
                </div>
            @endif

            @if ($compatibility)
                <div class="dh-pmeta__row">
                    <dt>{{ __('Compatible with') }}</dt>
                    <dd>
                        <span class="dh-pmeta__tags">
                            @foreach ($compatibility as $item)
                                <span class="dh-pmeta__tag">{{ $item }}</span>
                            @endforeach
                        </span>
                    </dd>
                </div>
            @endif
        </dl>

        @if ($included)
            <div class="dh-pmeta__included">
                <h4 class="dh-pmeta__included-title">{{ __("What's included") }}</h4>
                <ul class="dh-pmeta__included-list">
                    @foreach ($included as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
