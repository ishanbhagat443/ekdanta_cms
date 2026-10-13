{{--
    Ekdanta · Brand Shelves. The scatter-to-slots scene pops the logos up and
    flies them onto their industry shelf; shelf counts and bars follow.
    Drawn with every logo on its shelf. Logos: Collections → Clients,
    grouped by industry in the order of the Clients industry list.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $clients = \App\Models\CollectionItem::pick('clients', $d['clients'] ?? null);
    $shelves = collect(array_keys(\App\Collections\Clients::INDUSTRIES))
        ->map(fn ($industry) => ['name' => $industry, 'items' => $clients->filter(fn ($c) => $c->get('industry') === $industry)->values()])
        ->filter(fn ($shelf) => $shelf['items']->isNotEmpty())
        ->values();
    $stats = array_values(array_filter((array) ($d['stats'] ?? []), fn ($s) => ($s['value'] ?? '') !== ''));
@endphp
<div class="ek ek-brand-shelves" id="{{ $d['anchor'] ?: 'j-brands' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            // Shelf label positions: desktop in a row; phones as the design places them.
            $shelfAt = $mob ? [[24, 236], [206, 236], [24, 506], [206, 392]] : [[96, 236], [418, 236], [740, 236], [1062, 236]];
            $slot = $mob ? [80, 29, 26] : [147, 58, 64];
            $scale = $mob ? 0.548 : 1;
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="brand-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 86])) }}; width: {{ ek_u($v([760, 342])) }}">
                        <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([64, 34])) }}; line-height: {{ $v([1, 1.02]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ ek_u($v([12, 8])) }} 0 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); line-height: {{ $v([1.5, 1.45]) }}; color: var(--tk-muted)">{{ $t['text'] }}</p>
                        @if ($mob)
                            <a href="{{ link_href($t['link']) }}" target="_blank" rel="noopener" style="display: inline-block; margin-top: {{ ek_u(6) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(13) }}, 0.8125rem); color: var(--tk-green-deep)">{{ $t['link_label'] }}</a>
                        @endif
                    </div>
                    @unless ($mob)
                        <a href="{{ link_href($t['link']) }}" target="_blank" rel="noopener" data-m="brand-head" style="position: absolute; right: {{ ek_u(96) }}; top: {{ ek_u(128) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(16) }}, 0.8125rem); color: var(--tk-green-deep)">{{ $t['link_label'] }}</a>
                    @endunless

                    @foreach ($shelves as $s => $shelf)
                        @php [$sx, $sy] = $shelfAt[$s % count($shelfAt)]; @endphp
                        <div data-m="shelf" style="position: absolute; left: {{ ek_u($sx) }}; top: {{ ek_u($sy) }}; width: {{ ek_u($v([282, 160])) }}">
                            <div style="display: flex; justify-content: space-between; align-items: baseline">
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([18, 13])) }}, 0.8125rem)">{{ $shelf['name'] }}</span>
                                <span aria-hidden="true" data-m="shelf-count" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([34, 20])) }}, 0.8125rem); letter-spacing: -0.03em; color: var(--tk-green-text)">{{ $shelf['items']->count() }}</span>
                            </div>
                            <div aria-hidden="true" style="margin-top: {{ ek_u($v([8, 4])) }}; height: {{ ek_u($v([4, 3])) }}; border-radius: {{ ek_u(2) }}; background: #DCE3DA; overflow: hidden"><div data-m="shelf-fill" style="height: 100%; border-radius: {{ ek_u(2) }}; background: var(--tk-green); transform-origin: 0 50%"></div></div>
                        </div>
                    @endforeach

                    <ul data-m="tiles" style="margin: 0; padding: 0; list-style: none">
                        @foreach ($shelves as $s => $shelf)
                            @php [$sx, $sy] = $shelfAt[$s % count($shelfAt)]; @endphp
                            @foreach ($shelf['items'] as $j => $client)
                                <li data-shelf="{{ $s }}" style="position: absolute; left: 0; top: 0; width: {{ ek_u(135) }}; height: {{ ek_u(48) }}; border-radius: {{ ek_u(10) }}; overflow: hidden; background: #FFFFFF; {{ $mob ? 'transform-origin: 0 0;' : '' }} transform: translate({{ ek_u($sx + ($j % 2) * $slot[0]) }}, {{ ek_u($sy + $slot[2] + intdiv($j, 2) * $slot[1]) }}) scale({{ $scale }})">
                                    <img loading="lazy" decoding="async" src="{{ media_ref($client->get('logo'))?->web_url }}" alt="{{ $client->title }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; display: block">
                                </li>
                            @endforeach
                        @endforeach
                    </ul>

                    <dl data-m="brand-stats" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; right: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([784, 676])) }}; margin: 0; padding-top: {{ ek_u($v([16, 12])) }}; border-top: {{ ek_u(1) }} solid #DCE3DA; display: grid; {{ $mob ? 'grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: repeat(2, auto); grid-auto-flow: column; gap: '.ek_u(10).' '.ek_u(16) : 'grid-template-columns: repeat(4, minmax(0, 1fr)); gap: '.ek_u(40) }}">
                        @foreach ($stats as $stat)
                            <div style="display: flex; align-items: baseline; gap: {{ ek_u($v([12, 8])) }}">
                                <dt style="order: 2; font-weight: 400; font-size: max({{ ek_u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); line-height: {{ $v([1.3, 1.25]) }}; color: var(--tk-muted)">{{ $mob && ($stat['mobile_label'] ?? '') !== '' ? $stat['mobile_label'] : $stat['label'] }}</dt>
                                <dd @if (! empty($stat['count'])) data-m="brand-count" @endif style="order: 1; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([40, 24])) }}; line-height: 1; letter-spacing: -0.04em">{{ $stat['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    @endforeach
</div>
