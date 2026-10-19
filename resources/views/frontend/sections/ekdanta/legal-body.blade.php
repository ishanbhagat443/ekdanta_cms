{{--
    Ekdanta · Legal Body + Reading Rail (Privacy l-full, Terms t-full). The
    Article Body's layout, rail and script (article.js) on the section's own
    blocks: each heading numbered (01, 02 …) and listed under "On this page";
    paragraphs, lists, a highlighted note, label/value rows. Drawn once for
    every screen size (styles: _article.scss, .ek-ab--legal). Blocks rise as
    they arrive; the note's highlight sweeps in (--hl).
--}}
@php
    $d = $section->viewData('desktop');
    $anchor = $d['anchor'] ?: 'l-full';
    $blocks = array_values(array_filter((array) ($d['blocks'] ?? []), fn ($b) => trim((string) ($b['text'] ?? '')) !== ''));
    $minutes = \App\Support\LegalContent::minutes($blocks);
    $lines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));
    $toc = [];
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'heading') {
            $n = count($toc) + 1;
            $toc[] = ['id' => $anchor.'-'.$n, 'label' => trim($block['text']), 'num' => str_pad((string) $n, 2, '0', STR_PAD_LEFT)];
        }
    }
    $tick = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>';
    $part = 0;
@endphp
<div class="ek ek-legal-body" id="{{ $anchor }}" data-article data-minutes="{{ $minutes }}" data-left="{{ $d['left_label'] }}" data-done="{{ $d['done_label'] }}" data-read="{{ $d['read_label'] }}">
    <div class="ek-ab ek-ab--legal">
        <aside class="ek-ab__rail" aria-label="{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($d['toc_label'])) }}" data-m="lb-rail">
            @include('frontend.sections.ekdanta.partials.reading-rail', ['toc' => $toc, 'tocLabel' => $d['toc_label'], 'minutes' => $minutes, 'leftLabel' => $d['left_label'], 'readLabel' => $d['read_label']])
            @if ($d['ask_label'])
                <p class="ek-lb-ask">{{ $d['ask_before'] }} <a href="{{ link_href($d['ask_link'] ?? '') }}">{{ $d['ask_label'] }}</a></p>
            @endif
        </aside>
        <article class="ek-ab__main">
            @if ($toc !== [])
                @include('frontend.sections.ekdanta.partials.reading-now', ['first' => $toc[0], 'left' => str_replace('{minutes}', $minutes, $d['left_label'])])
            @endif
            <div class="ek-lb-text" data-article-text>
                @foreach ($blocks as $block)
                    @switch($block['type'] ?? 'paragraph')
                        @case('heading')
                            @php $entry = $toc[$part++]; @endphp
                            <h2 id="{{ $entry['id'] }}" tabindex="-1" data-m="lb-rise"><span class="ek-lb-num">{{ $entry['num'] }}</span>{{ $entry['label'] }}</h2>
                            @break
                        @case('list')
                            <ul class="ek-lb-list">
                                @foreach ($lines($block['text']) as $item)
                                    <li data-m="lb-rise"><span class="ek-lb-tick">{!! $tick !!}</span><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                            @break
                        @case('note')
                            <p class="ek-lb-note" data-m="lb-rise"><span class="ek-lb-note__hl" data-m="lb-note" aria-hidden="true"></span><span>{{ $block['text'] }}</span></p>
                            @break
                        @case('labels')
                            <dl class="ek-lb-kv">
                                @foreach ($lines($block['text']) as $row)
                                    @php [$label, $value] = array_pad(array_map('trim', explode('|', $row, 2)), 2, ''); @endphp
                                    <div data-m="lb-rise"><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                                @endforeach
                            </dl>
                            @break
                        @default
                            <p data-m="lb-rise">{!! nl2br(e($block['text'])) !!}</p>
                    @endswitch
                @endforeach
            </div>
            @if ($d['end_note'])
                <p class="ek-lb-end">{{ $d['end_note'] }}</p>
            @endif
        </article>
    </div>
</div>
