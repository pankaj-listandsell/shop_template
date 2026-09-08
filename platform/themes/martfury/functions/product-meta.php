<?php

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Models\Product;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Digital product metadata (demo URL, version, compatibility)
|--------------------------------------------------------------------------
|
| A theme/plugin listing needs three things ec_products has no column for: a live
| preview link, a version number and what the item is compatible with. Rather than
| migrating the products table, these are stored as meta boxes - the same mechanism
| the ecommerce plugin itself uses for product FAQs
| (platform/plugins/ecommerce/src/Listeners/SaveProductFaqListener.php:29).
|
| "Last updated" deliberately has no field: the product's own updated_at is always
| accurate and cannot drift out of date.
|
*/

if (! function_exists('martfury_product_meta_fields')) {
    /**
     * @return array<string, array{label: string, placeholder: string, help: string}>
     */
    function martfury_product_meta_fields(): array
    {
        return [
            'demo_url' => [
                'label' => __('Live preview URL'),
                'placeholder' => 'https://demo.example.com/theme',
                'help' => __('Shown as a "Live preview" button on product cards and the product page.'),
            ],
            'version' => [
                'label' => __('Version'),
                'placeholder' => '1.4.2',
                'help' => __('Current release of this item.'),
            ],
            'compatible_with' => [
                'label' => __('Compatible with'),
                'placeholder' => 'WordPress 6.5+, PHP 8.2+, WooCommerce 9',
                'help' => __('Comma-separated. Rendered as tags on the product page.'),
            ],
            'included' => [
                'label' => __("What's included"),
                'placeholder' => 'Theme files, Child theme, Documentation, 6 months support',
                'help' => __('Comma-separated. Listed on the product page so buyers know what they get.'),
            ],
        ];
    }
}

if (! function_exists('martfury_product_meta')) {
    /**
     * Read one meta value for a product. Returns '' when unset.
     */
    function martfury_product_meta(Product $product, string $key): string
    {
        if (! $product->getKey()) {
            return '';
        }

        $value = MetaBox::getMetaData($product, 'martfury_' . $key, true);

        return is_string($value) ? trim($value) : '';
    }
}

if (! function_exists('martfury_product_meta_list')) {
    /**
     * A comma-separated meta value split into trimmed, non-empty items.
     *
     * @return array<int, string>
     */
    function martfury_product_meta_list(Product $product, string $key): array
    {
        $raw = martfury_product_meta($product, $key);

        if (! $raw) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}

if (! function_exists('martfury_product_compatibility')) {
    /**
     * "Compatible with" split into individual tags.
     *
     * @return array<int, string>
     */
    function martfury_product_compatibility(Product $product): array
    {
        return martfury_product_meta_list($product, 'compatible_with');
    }
}

if (! function_exists('martfury_product_included')) {
    /**
     * "What's included" split into individual bullet items.
     *
     * @return array<int, string>
     */
    function martfury_product_included(Product $product): array
    {
        return martfury_product_meta_list($product, 'included');
    }
}

if (! function_exists('martfury_product_updated_label')) {
    /**
     * Human "Updated 3 days ago" style label from the product's own timestamp.
     */
    function martfury_product_updated_label(Product $product): string
    {
        if (! $product->updated_at instanceof Carbon) {
            return '';
        }

        return $product->updated_at->diffForHumans();
    }
}

app()->booted(function (): void {
    if (! is_plugin_active('ecommerce')) {
        return;
    }

    // ---------------------------------------------------------------- admin form

    add_action(BASE_ACTION_META_BOXES, function (string $context, $object): void {
        if ($context !== 'advanced' || ! $object instanceof Product) {
            return;
        }

        MetaBox::addMetaBox(
            'martfury_digital_meta_wrapper',
            __('Digital item details'),
            function () {
                $args = func_get_args();
                $product = $args[0] ?? null;

                $html = '<p class="text-muted mb-3">'
                    . e(__('Used by the marketplace theme. Leave blank to hide a row on the front end.'))
                    . '</p>';

                foreach (martfury_product_meta_fields() as $key => $field) {
                    $value = $product instanceof Product ? martfury_product_meta($product, $key) : '';
                    $id = 'martfury_' . $key;

                    $html .= '<div class="form-group mb-3">'
                        . '<label class="form-label" for="' . $id . '">' . e($field['label']) . '</label>'
                        . '<input type="text" class="form-control" id="' . $id . '"'
                        . ' name="' . $id . '"'
                        . ' value="' . e($value) . '"'
                        . ' placeholder="' . e($field['placeholder']) . '">'
                        . '<div class="form-text">' . e($field['help']) . '</div>'
                        . '</div>';
                }

                return $html;
            },
            $object::class,
            $context
        );
    }, 40, 2);

    // ---------------------------------------------------------------- saving

    $save = function ($screen, $request, $object): void {
        if (! $object instanceof Product || ! $object->getKey()) {
            return;
        }

        foreach (array_keys(martfury_product_meta_fields()) as $key) {
            $input = $request->input('martfury_' . $key);

            if (is_string($input) && trim($input) !== '') {
                MetaBox::saveMetaBoxData($object, 'martfury_' . $key, trim($input));
            } else {
                MetaBox::deleteMetaData($object, 'martfury_' . $key);
            }
        }

        // The homepage caches best-sellers and stats for an hour.
        if (function_exists('martfury_digital_home_clear_cache')) {
            martfury_digital_home_clear_cache();
        }

        Cache::forget('martfury_product_meta_' . $object->getKey());
    };

    add_action(BASE_ACTION_AFTER_CREATE_CONTENT, $save, 40, 3);
    add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, $save, 40, 3);

    // ---------------------------------------------------------------- product page
    //
    // Injected through the filter the theme's product view already exposes
    // (views/ecommerce/product.blade.php:57), so the view itself stays untouched.

    add_filter('ecommerce_after_product_description', function ($html, $product) {
        if (! $product instanceof Product) {
            return $html;
        }

        return $html . Theme::partial('product-meta', compact('product'));
    }, 40, 2);
});
