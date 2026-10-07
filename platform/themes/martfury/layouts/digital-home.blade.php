{!! Theme::partial('header') !!}

{{--
    Digital marketplace homepage.

    Unlike layouts/homepage.blade.php this does not render Theme::content() - the sections
    below replace the page body entirely. The page's stored shortcode content is left
    untouched, so switching the template back to "Homepage" restores the old front page.
--}}
<main id="digital-home" class="digital-home">
    {!! Theme::partial('home.hero') !!}
    {!! Theme::partial('home.stats') !!}
    {!! Theme::partial('home.categories') !!}
    {!! Theme::partial('home.best-sellers') !!}
    {!! Theme::partial('home.trending') !!}
    {{-- Products first, then why buy them here. --}}
    {!! Theme::partial('home.features') !!}
    {!! Theme::partial('home.testimonials') !!}
    {!! Theme::partial('home.faq') !!}
    {!! Theme::partial('home.newsletter') !!}
</main>

{!! Theme::partial('footer') !!}
