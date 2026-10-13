{{--
    Ekdanta · Client Logo Wall. Scroll tracks cascade the logos in; the
    industry filter (client-wall.js) moves each logo to its new place
    (CSS transitions, _client-logo-wall.scss). Without JS the filter stays
    hidden and every logo shows. Phones (no design): five logos a row.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $clients = \App\Models\CollectionItem::pick('clients', $d['clients'] ?? null)->values();
    $total = $clients->count();
    $counts = $clients->countBy(fn ($c) => $c->get('industry'));
    $filters = [['', $d['all_label'], $total], ...collect(\App\Collections\Clients::INDUSTRIES)->map(fn ($label, $key) => [$key, $label, $counts[$key] ?? 0])->filter(fn ($f) => $f[2] > 0)->values()->all()];
@endphp
<div class="ek ek-client-logo-wall" id="{{ $d['anchor'] ?: 'w-clients' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$cols, $dx, $dy, $tw, $th] = $v([[8, 157, 76, 145, 64], [5, 69, 42, 63, 36]]);
            $showing = str_replace(['{n}', '{total}'], ['<b data-wall-shown style="color: var(--tk-ink)">'.$total.'</b>', $total], e($t['showing']));
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art" data-wall data-cols="{{ $cols }}" data-dx="{{ $dx }}" data-dy="{{ $dy }}">
                    <div data-m="cl-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([108, 84])) }}; {{ $mob ? 'width: '.ek_u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([56, 34])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>

                    <div role="group" aria-label="{{ $d['filter_label'] }}" data-m="cl-filters" data-wall-filters hidden style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([250, 192])) }}; {{ $mob ? 'width: '.ek_u(342).'; flex-wrap: wrap;' : '' }} display: flex; gap: {{ ek_u($v([8, 6])) }}">
                        @foreach ($filters as $i => [$key, $label, $count])
                            <button type="button" class="ek-wall-chip ek-press" data-wall-filter="{{ $key }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" style="height: {{ ek_u($v([42, 36])) }}; padding: 0 {{ ek_u($v([16, 12])) }}; border: 0; border-radius: 999px; box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(13,20,16,0.14); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 13])) }}, 0.8125rem); cursor: pointer">{{ $label }} <span class="ek-wall-chip-n">{{ $count }}</span></button>
                        @endforeach
                    </div>
                    <p data-m="cl-showing" aria-live="polite" style="position: absolute; {{ $mob ? 'left: '.ek_u(24).'; top: '.ek_u(324) : 'right: '.ek_u(96).'; top: '.ek_u(260) }}; margin: 0; font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem); color: var(--tk-muted)">{!! $showing !!}</p>

                    <ul style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([320, 354])) }}; width: {{ ek_u($v([1248, 342])) }}; height: {{ ek_u($v([450, 372])) }}; margin: 0; padding: 0; list-style: none">
                        @foreach ($clients as $i => $client)
                            <li class="ek-wall-tile" data-wall-tile="{{ $client->get('industry') }}" style="--x: {{ $i % $cols }}; --y: {{ intdiv($i, $cols) }}; --dx: {{ $dx }}; --dy: {{ $dy }}; position: absolute; left: 0; top: 0; width: {{ ek_u($tw) }}; height: {{ ek_u($th) }}">
                                <div data-m="cl-tile-in" style="width: 100%; height: 100%; border-radius: {{ ek_u($v([14, 10])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(1) }} 0 rgba(13,20,16,0.05), 0 {{ ek_u(10) }} {{ ek_u(18) }} {{ ek_u(-14) }} rgba(13,20,16,0.45); display: flex; align-items: center; justify-content: center">
                                    @if ($logo = media_ref($client->get('logo'))?->web_url)<img loading="lazy" decoding="async" src="{{ $logo }}" alt="{{ $client->title }}" loading="lazy" style="width: {{ ek_u($tw - 10) }}; height: {{ ek_u($th - 16) }}; object-fit: contain; display: block">@else<span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([12, 9])) }}, 0.75rem); text-align: center">{{ $client->title }}</span>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <p data-m="cl-footer" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([800, 752])) }}; margin: 0; font-size: max({{ ek_u($v([16, 14])) }}, 0.8125rem); color: var(--tk-muted)">{{ $t['footer'] }} <a href="{{ link_href($d['footer_link'] ?? '') }}" @if (\App\Support\Link::external($d['footer_link'] ?? '')) target="_blank" rel="noopener" @endif style="font-family: 'Poppins', sans-serif; font-weight: 600; color: var(--tk-green-deep)">{{ $t['footer_link_label'] }}</a></p>
                </div>
            </div>
        </div>
    @endforeach
</div>
