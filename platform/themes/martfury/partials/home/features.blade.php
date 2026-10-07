@php
    /**
     * Alternating image/content rows. The second row flips via `reverse`, which only
     * swaps the grid column order - the markup stays in reading order so screen readers
     * and mobile both get image, then text.
     *
     * Copy lives here rather than in theme options: it makes specific promises the store
     * actually keeps (child themes, one-site licence, six months support, 14-day refund),
     * so it needs to change alongside the Terms and Refund pages, not independently.
     */
    $rows = [
        [
            'image' => 'general/feature-extend.jpg',
            'eyebrow' => __('Made to be edited'),
            'title' => __('Built to be extended, not fought with'),
            'body' => __('Every theme ships with a child theme, documented hooks and the Sass sources — not just the compiled CSS. Your changes survive updates because updates only ever replace the parent.'),
            'points' => [
                __('Child theme in every download'),
                __('Documented hooks and filters'),
                __('Sass sources, not only built CSS'),
                __('Documentation you can read offline'),
            ],
            'link' => route('public.products'),
            'linkText' => __('Browse the catalogue'),
            'reverse' => false,
        ],
        [
            'image' => 'general/feature-licence.jpg',
            'eyebrow' => __('Buy once'),
            'title' => __('One licence, updates for life'),
            'body' => __('Your licence key arrives the moment payment clears. Support runs for six months; updates never expire and there is no renewal to forget about.'),
            'points' => [
                __('One production site per licence'),
                __('Unlimited local and staging copies'),
                __('Six months of support included'),
                __('14-day refund if something is genuinely wrong'),
            ],
            'link' => url('/terms-conditions'),
            'linkText' => __('Read the licence terms'),
            'reverse' => true,
        ],
    ];
@endphp

<section class="dh-section dh-features">
    <div class="dh-container">
        @foreach ($rows as $row)
            <div @class(['dh-feature', 'dh-feature--reverse' => $row['reverse']])>
                <div class="dh-feature__media">
                    {!! RvMedia::image($row['image'], $row['title'], 'medium') !!}
                </div>

                <div class="dh-feature__body">
                    <span class="dh-feature__eyebrow">{{ $row['eyebrow'] }}</span>
                    <h2 class="dh-feature__title">{{ $row['title'] }}</h2>
                    <p class="dh-feature__text">{{ $row['body'] }}</p>

                    <ul class="dh-feature__points">
                        @foreach ($row['points'] as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>

                    <a class="dh-feature__link" href="{{ $row['link'] }}">
                        {{ $row['linkText'] }}
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
