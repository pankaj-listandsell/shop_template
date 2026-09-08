@php
    $stats = martfury_digital_home_stats();

    /**
     * A stats bar exists to show scale. With a dozen items and no sales there is no scale
     * to show, and filling the slots with slogans dressed as numbers reads worse than
     * showing nothing - so the whole section stays hidden until the figures are worth it.
     *
     * It returns on its own once the store has traded; nothing needs re-enabling.
     */
    $minSales = 50;
    $minCustomers = 50;

    $worthShowing = $stats['sales'] >= $minSales && $stats['customers'] >= $minCustomers;

    $tiles = $worthShowing
        ? [
            ['value' => $stats['items'], 'label' => __('Items available')],
            ['value' => $stats['sales'], 'label' => __('Downloads sold')],
            ['value' => $stats['customers'], 'label' => __('Happy customers')],
            ['value' => $stats['categories'], 'label' => __('Categories')],
        ]
        : [];
@endphp

@if ($tiles)
    <section class="dh-stats">
        <div class="dh-container">
            <ul class="dh-stats__grid">
                @foreach ($tiles as $tile)
                    <li class="dh-stats__item">
                        <span class="dh-stats__value">{{ martfury_digital_home_format_count((int) $tile['value']) }}</span>
                        <span class="dh-stats__label">{{ $tile['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
