 <footer class="ps-footer">
        <div class="ps-container">
            <div class="ps-footer__widgets">
                @if (theme_option('hotline') || theme_option('address') || theme_option('email') || theme_option('social-name-1'))
                    {{--
                        The footer's first column: who this is, then how to reach us.
                        Everything sold here is a download, so there is no phone line to
                        staff and no counter to visit - the hotline and address only render
                        when actually filled in, replacing the theme's hardcoded
                        "Call us 24/7" above a demo number.
                    --}}
                    <aside class="widget widget_footer widget_contact-us footer-brand">
                        @php
                            // The header logo sets "Template" in white so it reads on the
                            // yellow bar - on this white footer that half disappears. Use the
                            // light-background variant when it exists, else fall back to the
                            // theme logo so an uploaded logo still shows.
                            $footerLogo = file_exists(public_path('storage/general/logo-on-light.png'))
                                ? 'general/logo-on-light.png'
                                : null;
                        @endphp
                        <a class="footer-brand__logo" href="{{ BaseHelper::getHomepageUrl() }}">
                            @if ($footerLogo)
                                {{--
                                    Botble's settings store keeps values HTML-encoded, so the
                                    site title arrives as "Themes &amp;amp; plugins". Blade
                                    would escape it a second time inside the attribute; decode
                                    once so the alt text reads correctly.
                                --}}
                                <img src="{{ RvMedia::getImageUrl($footerLogo) }}" alt="{{ html_entity_decode(theme_option('site_title') ?: config('app.name'), ENT_QUOTES, 'UTF-8') }}" style="max-height: 34px">
                            @else
                                {!! Theme::getLogoImage(['style' => 'max-height: 34px']) !!}
                            @endif
                        </a>

                        <p class="footer-brand__blurb">
                            {{ __('Themes and plugins built in-house, delivered the moment you buy, and updated for life.') }}
                        </p>

                        @if (theme_option('email'))
                            <div class="footer-support">
                                <span class="footer-support__label">{{ __('Support') }}</span>
                                <a class="footer-support__email" href="mailto:{{ theme_option('email') }}">{{ theme_option('email') }}</a>
                                <span class="footer-support__note">{{ __('We reply within one working day') }}</span>
                            </div>
                        @endif

                        @if (theme_option('hotline'))
                            <p class="footer-support__extra">{{ theme_option('hotline') }}</p>
                        @endif

                        @if (theme_option('address'))
                            <p class="footer-support__extra">{{ theme_option('address') }}</p>
                        @endif

                        {!! Theme::partial('social-links') !!}
                    </aside>
                @endif
                {!! dynamic_sidebar('footer_sidebar') !!}
            </div>
            @if (Widget::group('bottom_footer_sidebar')->getWidgets())
                <div class="ps-footer__links" id="footer-links">
                    {!! dynamic_sidebar('bottom_footer_sidebar') !!}
                </div>
            @endif
            <div class="ps-footer__copyright">
                <p class="site-copyright">{!! Theme::getSiteCopyright() !!}</p>
                @php $paymentMethods = array_filter(json_decode(theme_option('payment_methods', []), true)); @endphp
                @if ($paymentMethods)
                    <div class="footer-payments">
                        <span class="payment-method-title">{{ __('We Using Safe Payment For') }}:</span>
                        <p class="d-sm-inline-block d-block">
                            @if (theme_option('payment_methods_link'))
                                <a href="{{ url(theme_option('payment_methods_link')) }}" target="_blank">
                            @endif
                            @foreach($paymentMethods as $method)
                                @if (!empty($method))
                                    <span>
                                        {!! RvMedia::image($method, __('Payment methods')) !!}
                                    </span>
                                @endif
                            @endforeach
                            @if (theme_option('payment_methods_link'))
                                </a>
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </footer>

    @if (is_plugin_active('newsletter') && theme_option('enable_newsletter_popup', 'yes') === 'yes')
        <div data-session-domain="{{ config('session.domain') ?? request()->getHost() }}"></div>
        <div class="ps-popup" id="subscribe" data-time="{{ (int)theme_option('newsletter_show_after_seconds', 10) * 1000 }}">
            <div class="ps-popup__content bg--cover" data-background="{{ RvMedia::getImageUrl(theme_option('newsletter_image')) }}" style="background-size: cover!important;"><a class="ps-popup__close" title="{{ __('Close') }}" href="#"><i class="icon-cross"></i></a>
                <form method="post" action="{{ route('public.newsletter.subscribe') }}" class="ps-form--subscribe-popup newsletter-form">
                    @csrf
                    <div class="ps-form__content">
                        <h4>{{ theme_option('newsletter_popup_title') ?: __('Get 25% Discount') }}</h4>
                        <p>{{ theme_option('newsletter_popup_description') ?: __('Subscribe to the mailing list to receive updates on new arrivals, special offers and our promotions.') }}</p>
                        <div class="mb-3">
                            <input class="form-control" name="email" type="email" placeholder="{{ __('Email Address') }}" required>
                        </div>

                        {!! apply_filters('form_extra_fields_render', null, \Botble\Newsletter\Forms\Fronts\NewsletterForm::class) !!}

                        <div class="mb-3">
                            <button class="ps-btn" type="submit" >{{ __('Subscribe') }}</button>
                        </div>
                        <div class="ps-checkbox">
                            <input class="form-control" type="checkbox" id="dont_show_again" name="dont_show_again">
                            <label for="dont_show_again">{{ __("Don't show this popup again") }}</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {!! Theme::get('bottomFooter') !!}

    @if (theme_option('show_back_to_top_button', 'yes') === 'yes')
        <div id="back2top"><i class="icon icon-arrow-up" title="Scroll Up"></i></div>
    @endif
    <div class="ps-site-overlay"></div>
    @if (is_plugin_active('ecommerce'))
        <div class="ps-search" id="site-search"><a class="ps-btn--close" href="#"></a>
            <div class="ps-search__content">
                <form class="ps-form--primary-search" action="{{ route('public.products') }}" data-ajax-url="{{ route('public.ajax.search-products') }}" method="get">
                    <input class="form-control input-search-product" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" type="text" autocomplete="off" placeholder="{{ __('Search themes, plugins, UI kits…') }}">
                    <div class="spinner-icon">
                        <i class="fa fa-spin fa-spinner"></i>
                    </div>
                    <button title="{{ __('Search') }}"><i class="aroma-magnifying-glass"></i></button>
                    <div class="ps-panel--search-result"></div>
                </form>
            </div>
        </div>
    @endif
    <div class="modal fade" id="product-quickview" tabindex="-1" role="dialog" aria-labelledby="product-quickview" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"><span class="modal-close" data-bs-dismiss="modal"><i class="icon-cross2"></i></span>
                <article class="ps-product--detail ps-product--fullwidth ps-product--quickview">
                </article>
            </div>
        </div>
    </div>

    @include(Theme::getThemeNamespace('views.ecommerce.includes.quick-shop-modal'))

    <script>
        window.trans = {
            "View All": "{{ __('View All') }}",
            "No reviews!": "{{ __('No reviews!') }}",
        };
        window.siteConfig = window.siteConfig || {};
        window.siteConfig.ajaxCartUrl = "{{ route('public.ajax.cart') }}";
    </script>

    {!! Theme::footer() !!}

    </body>
</html>
