{{--
    Ekdanta · Who We Work With. Scroll tracks count the heading's numbers
    and each industry's clients ($count), slide the three logo rows past
    each other, bring the cards up and fill their bars. Phones (no design):
    smaller tiles, cards in a 2 × 2 grid.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $clients = \App\Models\CollectionItem::pick('clients', $d['clients'] ?? null);
    $rows = $clients->isEmpty() ? [] : $clients->chunk((int) ceil($clients->count() / 3))->values();
    $industries = array_values(array_filter((array) ($d['industries'] ?? []), fn ($r) => ($r['industry'] ?? '') !== ''));
    $counts = $clients->countBy(fn ($c) => $c->get('industry'));
    // Rows start where the design draws them and travel this far (design px).
    $travel = [[0, -1000], [-1300, 1000], [-100, -900]];
@endphp
<div class="ek ek-who-we-work-with" id="{{ $d['anchor'] ?: 'a-brands' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$tw, $th] = $v([[170, 72], [140, 60]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="br-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([112, 84])) }}; width: {{ ek_u($v([1100, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([60, 34])) }}, 0.8125rem); line-height: 1.02; letter-spacing: -0.045em"><span data-m="br-count" style="color: var(--tk-green-text)">{{ $d['brands_count'] }}</span> {{ $t['brands_label'] }} <span data-m="br-count" style="color: var(--tk-green-text)">{{ $d['industries_count'] }}</span> {{ $t['industries_label'] }} <span data-m="br-count" style="color: var(--tk-green-text)">{{ $d['cities_count'] }}</span> {{ $t['cities_label'] }}</h2>
                        <p style="margin: {{ ek_u($v([12, 8])) }} 0 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $t['text'] }}</p>
                    </div>

                    <div data-m="br-rows">
                        @foreach ($rows as $r => $row)
                            <ul data-m="br-row-{{ $r + 1 }}" style="position: absolute; left: {{ ek_u($travel[$r][0]) }}; top: {{ ek_u($v([340 + $r * 86, 296 + $r * 72])) }}; margin: 0; padding: 0; list-style: none; display: flex; gap: {{ ek_u($v([14, 12])) }}">
                                @foreach ($row as $client)
                                    <li style="flex-shrink: 0; width: {{ ek_u($tw) }}; height: {{ ek_u($th) }}; border-radius: {{ ek_u($v([16, 14])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(1) }} 0 rgba(13,20,16,0.05), 0 {{ ek_u(10) }} {{ ek_u(20) }} {{ ek_u(-16) }} rgba(13,20,16,0.45); display: flex; align-items: center; justify-content: center">
                                        @if ($logo = media_ref($client->get('logo'))?->web_url)<img loading="lazy" decoding="async" src="{{ $logo }}" alt="{{ $client->title }}" loading="lazy" style="width: {{ ek_u($tw - 35) }}; height: {{ ek_u($th - 24) }}; object-fit: contain; display: block">@else<span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(13) }}, 0.75rem); text-align: center">{{ $client->title }}</span>@endif
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>

                    <ul style="position: absolute; left: {{ ek_u($v([96, 24])) }}; right: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([640, 524])) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat({{ $v([4, 2]) }}, minmax(0, 1fr)); gap: {{ ek_u($v([24, 12])) }}">
                        @foreach ($industries as $row)
                            <li data-m="br-card" style="box-sizing: border-box; padding: {{ ek_u($v([22, 14])) }} {{ ek_u($v([24, 16])) }}; border-radius: {{ ek_u($v([20, 16])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u(16) }} {{ ek_u(30) }} {{ ek_u(-24) }} rgba(13,20,16,0.4)">
                                <span style="display: flex; justify-content: space-between; align-items: baseline; gap: {{ ek_u(8) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([18, 14])) }}, 0.8125rem)">{{ $row['industry'] }}</span><span data-m="br-card-count" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([40, 28])) }}, 0.8125rem); letter-spacing: -0.04em; color: var(--tk-green-text)">{{ $counts[$row['industry']] ?? 0 }}</span></span>
                                <span aria-hidden="true" style="display: block; margin-top: {{ ek_u($v([12, 8])) }}; height: {{ ek_u(6) }}; border-radius: {{ ek_u(3) }}; background: var(--tk-line-1); overflow: hidden"><span data-m="br-card-fill" style="display: block; height: 100%; border-radius: {{ ek_u(3) }}; background: var(--tk-green); transform-origin: 0 50%"></span></span>
                                <span style="display: block; margin-top: {{ ek_u($v([10, 8])) }}; font-size: max({{ ek_u($v([14.5, 13])) }}, 0.8125rem); line-height: 1.35; color: var(--tk-muted)">{{ $row['examples'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
