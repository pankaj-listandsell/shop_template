<div class="ps-product-list mt-40 mb-40">
    <div class="ps-container">
        <div class="ps-section__header">
            <h3>{!! BaseHelper::clean($title) !!}</h3>
            <ul class="ps-section__links">
                <li><a href="{{ route('public.products') }}">{{ __('View All') }}</a></li>
            </ul>
        </div>
        <div class="ps-section__content">
            <div class="ps-carousel--responsive owl-slider"
                 data-owl-auto="true"
                 data-owl-loop="false"
                 data-owl-speed="10000"
                 data-owl-gap="20"
                 data-owl-nav="false"
                 data-owl-dots="true"
                 data-owl-item="4"
                 data-owl-item-xs="1"
                 data-owl-item-sm="2"
                 data-owl-item-md="2"
                 data-owl-item-lg="3"
                 data-owl-item-xl="4"
                 data-owl-duration="1000"
                 data-owl-mousedrag="on"
            >
                @foreach($products as $product)
                    <div class="ps-product">
                        {!! Theme::partial('product-item', compact('product')) !!}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
