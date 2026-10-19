{{--
    The reading rail's contents and progress (Article Body, Legal Body; run
    by article.js inside [data-article]). $toc: list of [id, label, num?],
    $tocLabel, $minutes, $leftLabel ("{minutes} min left"), $readLabel
    ("{percent}% read"). Phones and tablets: the progress hides; the slim
    bar under the header is partials/reading-now, in the text column.
--}}
@php $ring = round(2 * M_PI * 22, 2); @endphp
@if ($toc !== [])
    <span class="ek-ab__label">{{ $tocLabel }}</span>
    <nav class="ek-ab__toc" aria-label="{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($tocLabel)) }}" data-toc>
        <span class="ek-ab__bar" aria-hidden="true"></span>
        @foreach ($toc as $entry)
            <a href="#{{ $entry['id'] }}">@if (isset($entry['num']))<span class="ek-ab__num">{{ $entry['num'] }}</span>@endif{{ $entry['label'] }}</a>
        @endforeach
    </nav>
@endif
<div class="ek-ab__progress">
    <svg viewBox="0 0 56 56" aria-hidden="true"><circle cx="28" cy="28" r="22" fill="none" stroke="#DDE2DA" stroke-width="5"></circle><circle data-ring cx="28" cy="28" r="22" fill="none" stroke="#2EB45D" stroke-width="5" stroke-linecap="round" stroke-dasharray="{{ $ring }}" stroke-dashoffset="{{ $ring }}" transform="rotate(-90 28 28)"></circle></svg>
    <span><span class="ek-ab__left" data-left>{{ str_replace('{minutes}', $minutes, $leftLabel) }}</span><span data-read>{{ str_replace('{percent}', '0', $readLabel) }}</span></span>
</div>
