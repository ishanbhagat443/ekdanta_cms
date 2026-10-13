{{--
    With JavaScript off, a section whose items take turns on one stage shows
    only its first item; this lists every item below it instead. $heading
    (optional, when the stage's own heading needs the script), $items: list
    of [title, text]. Nothing of it renders while scripts run.
--}}
<noscript>
    <div class="ek-noscript">
        @if (! empty($heading))<h2>{{ $heading }}</h2>@endif
        <ul>
            @foreach ($items as [$title, $text])
                @if (trim($title.$text) !== '')
                    <li>@if ($title !== '')<strong>{{ $title }}</strong>@endif @if ($text !== '')<span>{{ $text }}</span>@endif</li>
                @endif
            @endforeach
        </ul>
    </div>
</noscript>
