<?php

/**
 * Nested permalinks for the Page module.
 *
 * Botble builds every slug with Str::slug(), which drops "/", so a page can only
 * ever live on a single URL segment. This lets a Page sit at any depth — e.g.
 * "demo/create/test" — while every other module keeps Botble's default behaviour.
 *
 * Three pieces are needed:
 *   1. FILTER_SLUG_STRING       keeps the slashes when the permalink field's ajax
 *                               preview rewrites what the user typed
 *   2. Created/UpdatedContentEvent  writes the slashed key on save
 *   3. ThemeRoutingAfterEvent   adds a route that matches more than one segment
 *
 * Nothing here touches platform/packages, so a Botble core update won't wipe it.
 * Delete this file to get stock behaviour back.
 */

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Page\Models\Page;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Events\ThemeRoutingAfterEvent;
use Botble\Theme\Http\Controllers\PublicController;
use Illuminate\Support\Str;

if (! function_exists('martfury_nested_permalink_slugify')) {
    /**
     * Slugify each segment on its own so the separators survive.
     * "Demo/Create Me/Test" becomes "demo/create-me/test".
     *
     * Empty segments are deliberately kept, which kicks in while the user is
     * still typing — see the note in the FILTER_SLUG_STRING hook below.
     */
    function martfury_nested_permalink_slugify(string $raw): string
    {
        return implode('/', array_map(fn ($segment) => Str::slug($segment), explode('/', $raw)));
    }
}

if (! function_exists('martfury_nested_permalink_key')) {
    /**
     * The stored form: slugified, empty segments dropped, and unique.
     */
    function martfury_nested_permalink_key(string $raw, ?string $prefix, int|string|null $ignoreSlugId = null): string
    {
        $key = collect(explode('/', $raw))
            ->map(fn ($segment) => Str::slug($segment))
            ->filter()
            ->implode('/');

        if (! $key) {
            return '';
        }

        // Core already ran its uniqueness loop, but against the slash-stripped
        // value — so the slashed key has to be checked again here.
        $base = $key;
        $index = 1;

        while (
            Slug::query()
                ->where('key', $key)
                ->where('prefix', $prefix)
                ->when($ignoreSlugId, fn ($query) => $query->where('id', '!=', $ignoreSlugId))
                ->exists()
        ) {
            $key = $base . '-' . $index++;
        }

        return $key;
    }
}

/**
 * 1. The permalink field posts to ajax/slug/create on every keystroke and drops
 *    the response straight back into the input, so without this the slashes are
 *    stripped again the moment they're typed. Scoped to that one endpoint; the
 *    save path is handled by the listener below.
 */
add_filter(FILTER_SLUG_STRING, function (string $slug, ?string $model = null) {
    if (! request()->routeIs('slug.create') || ($model ?: request()->input('model')) !== Page::class) {
        return $slug;
    }

    $raw = (string) request()->input('value');

    if (! str_contains($raw, '/')) {
        return $slug;
    }

    // Keep the separators exactly as they were typed, empty ones included.
    // slug.js writes this response straight back into the input, so trimming a
    // trailing "/" here would delete the separator out from under the cursor the
    // moment the user paused — capping them at however many segments they could
    // type inside the 700ms debounce.
    $live = martfury_nested_permalink_slugify($raw);

    // Only de-duplicate once the value looks finished. Running it against a
    // half-typed path would append "-1" to a segment still being written.
    if (! Str::endsWith($live, '/') && ! Str::contains($live, '//')) {
        $live = martfury_nested_permalink_key(
            $raw,
            SlugHelper::getPrefix(Page::class, '', false),
            request()->input('slug_id')
        );
    }

    return $live ?: $slug;
}, 120, 2);

app()->booted(function (): void {
    /**
     * 2. Botble's own slug listeners have already written the stripped key by the
     *    time this runs (registered later = runs later), so correct it here where
     *    the page and its slug id are both known.
     */
    app('events')->listen(
        [CreatedContentEvent::class, UpdatedContentEvent::class],
        function ($event): void {
            if (! $event->data instanceof Page) {
                return;
            }

            $raw = (string) $event->request->input('slug');

            // No slash means the user wants a plain permalink — leave core alone.
            if (! str_contains($raw, '/')) {
                return;
            }

            $slug = Slug::query()
                ->where([
                    'reference_type' => Page::class,
                    'reference_id' => $event->data->getKey(),
                ])
                ->first();

            if (! $slug) {
                return;
            }

            $key = martfury_nested_permalink_key($raw, $slug->prefix, $slug->getKey());

            if ($key && $slug->key !== $key) {
                $slug->key = $key;
                $slug->save();
            }
        }
    );
});

/**
 * 3. Core registers "{slug?}" (one segment) and "{prefix}/{slug?}" (a registered
 *    module prefix). Neither matches "demo/create/test", so add a multi-segment
 *    route. ThemeRoutingAfterEvent fires at the end of the public route group,
 *    which puts this after every other route — including the whole admin area —
 *    so it can only ever catch what nothing else claimed.
 */
app('events')->listen(ThemeRoutingAfterEvent::class, function (ThemeRoutingAfterEvent $event): void {
    $adminDir = preg_quote(trim((string) config('core.base.general.admin_dir', 'admin'), '/'), '#');

    $event->router
        ->get('{slug}', function (string $slug) {
            $prefix = SlugHelper::getPrefix(Page::class, '', false);

            $key = $prefix && Str::startsWith($slug, $prefix . '/')
                ? Str::after($slug, $prefix . '/')
                : $slug;

            // Only Pages opt into nested permalinks; anything else keeps 404ing
            // exactly as it did before this file existed.
            abort_unless(
                Slug::query()
                    ->where([
                        'key' => $key,
                        'prefix' => $prefix,
                        'reference_type' => Page::class,
                    ])
                    ->exists(),
                404
            );

            return app(PublicController::class)->getView($key, $prefix);
        })
        // At least two segments, and never the admin area.
        ->where('slug', '(?!' . $adminDir . '(?:/|$))[^/]+(?:/[^/]+)+')
        ->name('public.nested-page');
});
