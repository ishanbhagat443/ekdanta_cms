{{--
    The cookie bar (Settings → Cookies), shown until the visitor chooses;
    "Cookie settings" ([data-cookie-settings]) brings it back. Not a dialog:
    it doesn't take or trap focus. consent.js keeps the choice 180 days.
    data-reload: "Allow all" reloads the page when this page has tags that
    only the server adds (Analytics, Tag Manager, Conversion tags).
--}}
@if ($bar = cookie_bar())
    @php $reload = setting('seo.google_analytics') || setting('seo.google_tag_manager') || View::hasSection('conversion_tags'); @endphp
    <div class="gsm-cookie-bar" role="region" aria-label="Cookie choices" data-cookie-bar data-reload="{{ $reload ? 1 : 0 }}" @if (in_array(request()->cookie('cookie_consent'), ['all', 'necessary'], true)) hidden @endif>
        <p><b>{{ $bar['text_bold'] }}</b> {{ $bar['text'] }}@if ($bar['link_text']) <a href="{{ link_href($bar['link_url']) }}">{{ $bar['link_text'] }}</a>@endif</p>
        <div class="gsm-cookie-bar__actions">
            <button type="button" class="gsm-cookie-bar__all" data-cookie-choice="all">{{ $bar['accept'] }}</button>
            <button type="button" class="gsm-cookie-bar__necessary" data-cookie-choice="necessary">{{ $bar['reject'] }}</button>
        </div>
    </div>
@endif
