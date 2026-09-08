<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Payment\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Data helpers for the "Digital Marketplace Home" page template
|--------------------------------------------------------------------------
|
| Auto-loaded by Helper::autoload() (platform/core/base/src/Supports/Helper.php).
|
| Only the numbers that ec_products does not already store live here. Anything the
| ecommerce plugin already exposes - get_featured_products(), get_trending_products(),
| get_featured_product_categories() - is used directly by the partials instead.
|
*/

if (! function_exists('martfury_digital_home_cache_minutes')) {
    function martfury_digital_home_cache_minutes(): int
    {
        return 60;
    }
}

if (! function_exists('martfury_digital_home_sales_query')) {
    /**
     * Base query joining products to the order lines of orders that actually completed.
     *
     * There is no `sold` column on ec_products, so sales have to be aggregated from
     * ec_order_product. Mirrors the query the admin report already relies on:
     * platform/plugins/ecommerce/src/Tables/Reports/TopSellingProductsTable.php:60
     */
    function martfury_digital_home_sales_query(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Product::query()
            ->join('ec_order_product', 'ec_products.id', '=', 'ec_order_product.product_id')
            ->join('ec_orders', 'ec_orders.id', '=', 'ec_order_product.order_id')
            ->where('ec_orders.is_finished', true);

        if (is_plugin_active('payment')) {
            $query->leftJoin('payments', 'payments.order_id', '=', 'ec_orders.id')
                ->where(function ($query): void {
                    $query
                        ->where('payments.status', PaymentStatusEnum::COMPLETED)
                        ->orWhereNull('ec_orders.payment_id');
                });
        }

        return $query;
    }
}

if (! function_exists('martfury_digital_home_best_sellers')) {
    /**
     * Published products ordered by units sold, each carrying a `sales_count` attribute.
     *
     * @return Collection<int, Product>
     */
    function martfury_digital_home_best_sellers(int $limit = 8): Collection
    {
        if (! is_plugin_active('ecommerce')) {
            return new Collection();
        }

        $ids = Cache::remember(
            sprintf('martfury_digital_home_best_sellers_%d', $limit),
            martfury_digital_home_cache_minutes() * 60,
            function () use ($limit): array {
                return martfury_digital_home_sales_query()
                    // Qualified on purpose: ec_orders and payments both carry a `status`
                    // column, so the unqualified wherePublished() scope is ambiguous here.
                    ->where('ec_products.status', BaseStatusEnum::PUBLISHED)
                    ->where('ec_products.is_variation', false)
                    ->selectRaw('ec_products.id as id, SUM(ec_order_product.qty) as sales_count')
                    ->groupBy('ec_products.id')
                    ->orderByDesc('sales_count')
                    ->limit($limit)
                    ->pluck('sales_count', 'id')
                    ->all();
            }
        );

        if (! $ids) {
            // Nothing has sold yet - fall back to featured products so the section is never blank.
            return get_featured_products(['take' => $limit]);
        }

        $products = Product::query()
            ->wherePublished()
            ->whereIn('ec_products.id', array_keys($ids))
            ->with(['slugable', 'variations', 'productCollections', 'productLabels', 'categories'])
            ->get()
            ->each(function (Product $product) use ($ids): void {
                $product->sales_count = (int) ($ids[$product->getKey()] ?? 0);
            });

        // Keep the "most sold first" order that the aggregate produced.
        return $products->sortByDesc('sales_count')->values();
    }
}

if (! function_exists('martfury_digital_home_stats')) {
    /**
     * Headline counters for the stats bar.
     *
     * @return array{items: int, sales: int, customers: int, categories: int}
     */
    function martfury_digital_home_stats(): array
    {
        if (! is_plugin_active('ecommerce')) {
            return ['items' => 0, 'sales' => 0, 'customers' => 0, 'categories' => 0];
        }

        return Cache::remember(
            'martfury_digital_home_stats',
            martfury_digital_home_cache_minutes() * 60,
            function (): array {
                return [
                    'items' => Product::query()
                        ->wherePublished()
                        ->where('is_variation', false)
                        ->count(),
                    'sales' => (int) martfury_digital_home_sales_query()->sum('ec_order_product.qty'),
                    'customers' => (int) DB::table('ec_customers')->count(),
                    'categories' => ProductCategory::query()->wherePublished()->count(),
                ];
            }
        );
    }
}

if (! function_exists('martfury_digital_home_format_count')) {
    /**
     * 1234 -> "1.2K", 1500000 -> "1.5M". Keeps the stats bar from wrapping on mobile.
     */
    function martfury_digital_home_format_count(int $number): string
    {
        if ($number >= 1000000) {
            return rtrim(rtrim(number_format($number / 1000000, 1), '0'), '.') . 'M';
        }

        if ($number >= 1000) {
            return rtrim(rtrim(number_format($number / 1000, 1), '0'), '.') . 'K';
        }

        return (string) $number;
    }
}

if (! function_exists('martfury_digital_home_is_digital')) {
    /**
     * Product::isTypeDigital() only exists when the ecommerce plugin ships digital support,
     * so probe defensively before the partials badge a card.
     */
    function martfury_digital_home_is_digital(Product $product): bool
    {
        if (method_exists($product, 'isTypeDigital')) {
            return $product->isTypeDigital();
        }

        return class_exists(ProductTypeEnum::class)
            && $product->product_type == ProductTypeEnum::DIGITAL;
    }
}

if (! function_exists('martfury_catalogue_is_mixed')) {
    /**
     * True only when the published catalogue holds both digital and physical products.
     *
     * A "Digital" badge on a store where *everything* is digital distinguishes nothing - it
     * is noise on every card. Gating it on this means the badge disappears for a pure
     * digital catalogue and reappears by itself the day a physical product is added.
     */
    function martfury_catalogue_is_mixed(): bool
    {
        if (! is_plugin_active('ecommerce')) {
            return false;
        }

        return (bool) Cache::remember(
            'martfury_catalogue_is_mixed',
            martfury_digital_home_cache_minutes() * 60,
            function (): bool {
                $base = Product::query()->wherePublished()->where('is_variation', false);

                return (clone $base)->where('product_type', ProductTypeEnum::DIGITAL)->exists()
                    && (clone $base)->where('product_type', ProductTypeEnum::PHYSICAL)->exists();
            }
        );
    }
}

if (! function_exists('martfury_show_digital_badge')) {
    /**
     * Whether a given product should carry the "Digital" badge on its card.
     */
    function martfury_show_digital_badge(Product $product): bool
    {
        return martfury_catalogue_is_mixed() && martfury_digital_home_is_digital($product);
    }
}

if (! function_exists('martfury_digital_home_clear_cache')) {
    /**
     * Call after seeding or bulk-importing products so the homepage picks the new numbers up
     * without waiting for the hour to lapse.
     */
    function martfury_digital_home_clear_cache(int $maxLimit = 24): void
    {
        Cache::forget('martfury_digital_home_stats');
        Cache::forget('martfury_catalogue_is_mixed');

        for ($limit = 1; $limit <= $maxLimit; $limit++) {
            Cache::forget(sprintf('martfury_digital_home_best_sellers_%d', $limit));
        }
    }
}
