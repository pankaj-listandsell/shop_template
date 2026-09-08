@if (is_plugin_active('faq'))
    @php
        // `faqs` has no `order` column - only faq_categories does. Same ordering the theme's
        // own [faq] shortcode uses (functions/shortcodes.php:510).
        $faqCategories = \Botble\Faq\Models\FaqCategory::query()
            ->wherePublished()
            ->with(['faqs' => fn ($query) => $query->wherePublished()])
            ->orderBy('order')
            ->get()
            ->filter(fn ($category) => $category->faqs->isNotEmpty());

        // Keep the category each question came from so the accordion reads as grouped
        // without splitting into separate lists. Taking a slice per category rather than
        // the first eight overall, so every group is represented instead of the first two
        // filling the whole list.
        $perCategory = (int) ceil(8 / max($faqCategories->count(), 1));

        $faqs = $faqCategories
            ->flatMap(fn ($category) => $category->faqs
                ->take($perCategory)
                ->map(fn ($faq) => [$faq, $category->name]))
            ->take(8);

        $contactPage = \Botble\Page\Models\Page::query()->where('name', 'Contact')->first();
    @endphp

    @if ($faqs->isNotEmpty())
        <section class="dh-section dh-faq">
            <div class="dh-container">
                <div class="dh-faq__layout">
                    <aside class="dh-faq__aside">
                        <h2 class="dh-section__title">{{ __('Questions before you buy') }}</h2>
                        <p class="dh-section__sub">{{ __('Licensing, updates and support — answered.') }}</p>

                        <span class="dh-faq__art">
                            {!! RvMedia::image('general/faq-support.jpg', __('Questions and answers'), 'medium') !!}
                        </span>

                        <div class="dh-faq__cta">
                            <p class="dh-faq__cta-text">{{ __('Still not sure? Ask before you buy — we would rather answer than refund.') }}</p>
                            <a class="dh-faq__cta-link" href="{{ $contactPage ? $contactPage->url : url('/contact') }}">
                                {{ __('Contact support') }}
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </aside>

                    {{-- Native <details> keeps the accordion working without extra JS. --}}
                    <div class="dh-faq__list">
                        @foreach ($faqs as [$faq, $categoryName])
                            <details class="dh-faq__item" @if ($loop->first) open @endif>
                                <summary class="dh-faq__q">
                                    <span class="dh-faq__q-text">
                                        <span class="dh-faq__tag">{{ $categoryName }}</span>
                                        {{ $faq->question }}
                                    </span>
                                </summary>
                                <div class="dh-faq__a">{!! BaseHelper::clean($faq->answer) !!}</div>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
@endif
