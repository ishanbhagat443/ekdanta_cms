{{--
    Ekdanta · How It Works. The steps are the engine's steps: the track fill,
    each node's "reached" look and the lit card follow --ek-f in CSS
    (_how-it-works.scss); scroll tracks bring each step in. Phones (no
    design): a vertical track with the cards beside it.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $service = $templateItem ?? null;
    $service = $service instanceof \App\Models\CollectionItem ? $service : (\App\Models\CollectionItem::pick('services', $d['service'] ?? null)->first());
    $steps = $service ? array_slice(array_values(array_filter((array) $service->get('steps', []), fn ($r) => ($r['title'] ?? '') !== '')), 0, 4) : [];
    $n = count($steps);
@endphp
@if ($steps)
<div class="ek ek-how-it-works" id="{{ $d['anchor'] ?: 'v-steps' }}" style="--last: {{ max(1, $n - 1) }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $gap = $v([316, 136]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="hw-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([116, 84])) }}; {{ $mob ? 'width: '.$u(300) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([56, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    <span aria-hidden="true" style="position: absolute; right: {{ $u($v([70, 16])) }}; top: {{ $u($v([90, 724])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([260, 120])) }}; line-height: 1; letter-spacing: -0.06em; color: transparent; -webkit-text-stroke: {{ $u(2) }} rgba(255,255,255,0.12)"><span data-ek-step-num="2">01</span></span>
                    <div aria-hidden="true" data-m="hw-track" style="position: absolute; {{ $mob ? 'left: '.$u(40).'; top: '.$u(250).'; width: '.$u(4).'; height: '.$u($gap * ($n - 1)) : 'left: '.$u(246).'; top: '.$u(420).'; width: '.$u($gap * ($n - 1)).'; height: '.$u(4) }}; border-radius: {{ $u(2) }}; background: rgba(255,255,255,0.14); overflow: hidden"><div class="ek-hw-fill" style="width: 100%; height: 100%; background: var(--tk-green); transform-origin: {{ $mob ? '50% 0' : '0 50%' }}"></div></div>
                    <ol data-m="hw-steps" style="list-style: none; margin: 0; padding: 0; position: absolute; inset: 0">
                        @foreach ($steps as $i => $step)
                            <li class="ek-hw-step {{ $i === 0 ? 'is-active' : '' }}" style="--i: {{ $i }}; position: absolute; left: 0; top: 0">
                                <div data-m="hw-in">
                                    <span aria-hidden="true" class="ek-hw-node" style="position: absolute; {{ $mob ? 'left: '.$u(24).'; top: '.$u(250 + $i * $gap - 16) : 'left: '.$u(246 + $i * $gap - 18).'; top: '.$u(404) }}; width: {{ $u($v([36, 32])) }}; height: {{ $u($v([36, 32])) }}; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u(14) }}, 0.8125rem); display: flex; align-items: center; justify-content: center">{{ $i + 1 }}</span>
                                    <div class="ek-hw-card" style="position: absolute; {{ $mob ? 'left: '.$u(76).'; top: '.$u(250 + $i * $gap - 30).'; width: '.$u(292) : 'left: '.$u(246 + $i * $gap - 150).'; top: '.$u(476).'; width: '.$u(300).'; min-height: '.$u(200) }}; box-sizing: border-box; padding: {{ $u($v([24, 14])) }}; border-radius: {{ $u($v([22, 18])) }}; transform-origin: 50% 0">
                                        <span style="display: block; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u(13) }}, 0.8125rem); letter-spacing: 0.08em" class="ek-hw-label">{{ str_replace('{n}', (string) ($i + 1), $t['step_label']) }}</span>
                                        <h3 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([23, 18])) }}, 0.8125rem); line-height: 1.15; letter-spacing: -0.03em">{{ $step['title'] }}</h3>
                                        <p style="margin: {{ $u($v([8, 4])) }} 0 0; font-size: max({{ $u($v([16, 14])) }}, 0.8125rem); line-height: 1.45; opacity: 0.85">{{ $step['text'] ?? '' }}</p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
