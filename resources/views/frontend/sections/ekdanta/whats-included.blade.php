{{--
    Ekdanta · What's Included. Each card sits at its place in the grid and
    is dealt there from a shared stack by its own scroll track (wi-card-N);
    the ticks draw in turn. Phones (no design): two columns.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $service = $templateItem ?? null;
    $service = $service instanceof \App\Models\CollectionItem ? $service : (\App\Models\CollectionItem::pick('services', $d['service'] ?? null)->first());
    $items = $service ? array_slice(array_values(array_filter((array) $service->get('included', []), fn ($r) => ($r['title'] ?? '') !== '')), 0, 6) : [];
@endphp
@if ($items)
<div class="ek ek-whats-included" id="{{ $d['anchor'] ?: 'v-incl' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$cols, $dx, $dy, $cw, $ch] = $v([[3, 424, 200, 400, 176], [2, 177, 180, 165, 168]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="wi-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([116, 84])) }}; {{ $mob ? 'width: '.$u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([56, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    <ul data-m="wi-stack" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([290, 220])) }}; width: {{ $u($v([1248, 342])) }}; height: {{ $u($v([400, 528])) }}; margin: 0; padding: 0; list-style: none">
                        @foreach ($items as $i => $item)
                            <li data-m="wi-card-{{ $i + 1 }}" style="position: absolute; left: {{ $u(($i % $cols) * $dx) }}; top: {{ $u(intdiv($i, $cols) * $dy) }}; width: {{ $u($cw) }}; height: {{ $u($ch) }}; box-sizing: border-box; padding: {{ $u($v([24, 14])) }} {{ $u($v([26, 14])) }}; border-radius: {{ $u($v([22, 18])) }}; background: #FFFFFF; box-shadow: 0 {{ $u(20) }} {{ $u(40) }} {{ $u(-28) }} rgba(13,20,16,0.45); z-index: {{ 10 - $i }}">
                                <span style="display: flex; justify-content: space-between; align-items: center"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u(13) }}, 0.8125rem); letter-spacing: 0.08em; color: var(--tk-green-text-sm)">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span><svg viewBox="0 0 30 30" aria-hidden="true" style="width: {{ $u($v([30, 26])) }}; height: {{ $u($v([30, 26])) }}"><circle cx="15" cy="15" r="14" fill="#D9F2E1"></circle><path data-m="wi-tick" pathLength="1" d="M9 15.5l4 4 8-9" fill="none" stroke="#1E8E47" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="1 1" stroke-dashoffset="0"></path></svg></span>
                                <h3 style="margin: {{ $u($v([12, 6])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([21, 16])) }}, 0.8125rem); line-height: 1.2; letter-spacing: -0.02em">{{ $item['title'] }}</h3>
                                <p style="margin: {{ $u($v([6, 4])) }} 0 0; font-size: max({{ $u($v([16, 13])) }}, 0.8125rem); line-height: {{ $v([1.45, 1.35]) }}; color: var(--tk-muted)">{{ $item['text'] ?? '' }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
