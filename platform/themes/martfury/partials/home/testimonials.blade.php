@if (is_plugin_active('testimonial'))
    @php
        $testimonials = \Botble\Testimonial\Models\Testimonial::query()
            ->wherePublished()
            ->latest()
            ->limit(3)
            ->get();
    @endphp

    @if ($testimonials->isNotEmpty())
        <section class="dh-section dh-section--tint dh-testimonials">
            <div class="dh-container">
                <header class="dh-section__head">
                    <h2 class="dh-section__title">{{ __('Loved by developers') }}</h2>
                    <p class="dh-section__sub">{{ __('What customers say after shipping with our products') }}</p>
                </header>

                <div class="dh-testimonials__grid">
                    @foreach ($testimonials as $testimonial)
                        <figure class="dh-testimonial">
                            <blockquote class="dh-testimonial__quote">
                                {!! BaseHelper::clean($testimonial->content) !!}
                            </blockquote>
                            <figcaption class="dh-testimonial__author">
                                <span class="dh-testimonial__avatar">
                                    {!! RvMedia::image($testimonial->image, $testimonial->name, 'thumb') !!}
                                </span>
                                <span class="dh-testimonial__who">
                                    <strong>{{ $testimonial->name }}</strong>
                                    @if ($testimonial->company)
                                        <small>{{ $testimonial->company }}</small>
                                    @endif
                                </span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endif
