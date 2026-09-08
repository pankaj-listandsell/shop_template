/*!
 * Digital marketplace helpers.
 *
 * "Live preview" badges sit inside the card's thumbnail link, so they cannot be anchors
 * themselves - nesting <a> is invalid. They are spans carrying data-dh-preview, opened
 * here, and the click must not bubble up to the product link wrapping them.
 *
 * Hand-written, not built: it is registered directly in the theme's config.php next to
 * css/digital-home.css. Keep the copy in platform/themes/martfury/public/js/ in sync -
 * that is what `artisan cms:theme:assets:publish` copies over.
 */
(function () {
    'use strict';

    function open(trigger) {
        var url = trigger.getAttribute('data-dh-preview');

        if (url) {
            window.open(url, '_blank', 'noopener');
        }
    }

    function closest(target) {
        return target && target.closest ? target.closest('[data-dh-preview]') : null;
    }

    document.addEventListener('click', function (event) {
        var trigger = closest(event.target);

        if (!trigger) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        open(trigger);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        var trigger = closest(event.target);

        if (!trigger) {
            return;
        }

        event.preventDefault();
        open(trigger);
    });
})();
