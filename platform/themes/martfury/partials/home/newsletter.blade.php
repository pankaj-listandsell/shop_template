@if (is_plugin_active('newsletter'))
    <section class="dh-section dh-newsletter">
        <div class="dh-container">
            <div class="dh-newsletter__box">
                <div class="dh-newsletter__copy">
                    <span class="dh-newsletter__eyebrow">{{ __('Release notes') }}</span>
                    <h2 class="dh-newsletter__title">{{ __('New releases, in your inbox') }}</h2>
                    <p class="dh-newsletter__sub">{{ __('Get told first when a new theme or plugin drops — plus the occasional discount code.') }}</p>
                </div>

                <div class="dh-newsletter__side">
                    {{-- Same endpoint and extra-fields hook as partials/short-codes/newsletter-form.blade.php --}}
                    <form class="dh-newsletter__form ps-form--newsletter" method="post" action="{{ route('public.newsletter.subscribe') }}">
                        @csrf
                        <label class="dh-newsletter__label" for="dh-newsletter-email">{{ __('Email address') }}</label>
                        <div class="dh-newsletter__row">
                            <input id="dh-newsletter-email" class="form-control" name="email" type="email" required placeholder="{{ __('you@example.com') }}">
                            <button class="dh-newsletter__btn" type="submit">{{ __('Subscribe') }}</button>
                        </div>

                        {!! apply_filters('form_extra_fields_render', null, \Botble\Newsletter\Forms\Fronts\NewsletterForm::class) !!}
                    </form>

                    <ul class="dh-newsletter__points">
                        <li>{{ __('Only when something ships') }}</li>
                        <li>{{ __('No spam, ever') }}</li>
                        <li>{{ __('Unsubscribe in one click') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endif
