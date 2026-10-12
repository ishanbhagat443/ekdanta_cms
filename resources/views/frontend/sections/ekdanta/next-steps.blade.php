{{--
    Ekdanta · Next Steps Track. The steps are the engine's steps; the track
    fill, the marker and each step's "reached" look follow --ek-f in CSS
    (_next-steps.scss); the current step (is-active) gets the white card and
    the pulse. Scroll tracks bring the head, track and cards in.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $steps = array_values(array_filter((array) ($d['steps'] ?? []), fn ($s) => ($s['title'] ?? '') !== ''));
    $total = count($steps);
    $icons = [
        'mail' => '<g data-m="nx-icon-mail"><path d="M3 6h18v12H3z"></path><path d="M3 7l9 6 9-6"></path></g>',
        'phone' => '<path data-m="nx-icon-phone" style="transform-box: fill-box; transform-origin: center" d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path>',
        'search' => '<g data-m="nx-icon-search"><circle cx="11" cy="11" r="6"></circle><path d="M20 20l-4.5-4.5"></path></g>',
        'doc' => '<path d="M7 3h7l4 4v14H7z M14 3v4h4"></path><path data-m="nx-icon-doc" style="transform-origin: 0 50%" d="M10 12h5 M10 16h5"></path>',
    ];
    $mail = '<path d="M3 6h18v12H3z"></path><path d="M3 7l9 6 9-6"></path>';
@endphp
<div class="ek ek-next-steps" id="{{ $d['anchor'] ?: 't-next' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $gap = $v([322.67, 160]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}" style="--gap: {{ $gap }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    @if ($mob)
                        <div data-m="nx-head" style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(86) }}; width: {{ ek_u(342) }}">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                            <h2 style="margin: {{ ek_u(4) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(32) }}; line-height: 1.02; letter-spacing: -0.05em; white-space: nowrap">{{ $t['heading'] }}</h2>
                            <p data-m="nx-marker-in" aria-live="polite" style="margin: {{ ek_u(6) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(13) }}, 0.8125rem); color: var(--tk-green-deep)"><span class="ek-nx-a">{!! str_replace(['{n}', '{total}'], ['<span data-ek-step-num="1">1</span>', $total], e($t['progress'])) !!}</span><span class="ek-nx-b">{{ $t['progress_done'] }}</span></p>
                        </div>
                    @else
                        <div data-m="nx-head" style="position: absolute; left: {{ ek_u(96) }}; top: {{ ek_u(118) }}; width: {{ ek_u(1248) }}; display: flex; align-items: flex-end; justify-content: space-between">
                            <div><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(14) }}, 0.8125rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                                <h2 style="margin: {{ ek_u(8) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u(64) }}; line-height: 1.02; letter-spacing: -0.05em">{{ $t['heading'] }}</h2></div>
                            <p style="margin: 0 0 {{ ek_u(8) }}; width: {{ ek_u(400) }}; font-size: max({{ ek_u(19) }}, 0.8125rem); line-height: 1.55; color: #3B4640">{{ $t['text'] }}</p>
                        </div>
                    @endif

                    <div style="position: absolute; {{ $mob ? 'left: 0; top: 0; width: '.ek_u(390).'; height: '.ek_u(844) : 'left: '.ek_u(96).'; top: '.ek_u(326).'; width: '.ek_u(1248).'; height: '.ek_u(480) }}">
                        <span aria-hidden="true" data-m="nx-track" class="ek-nx-track" style="position: absolute; {{ $mob ? 'left: '.ek_u(38).'; top: '.ek_u(232).'; width: '.ek_u(4).'; height: '.ek_u($gap * 3).'; transform-origin: 50% 0' : 'left: '.ek_u(140).'; top: '.ek_u(92).'; width: '.ek_u($gap * 3).'; height: '.ek_u(4).'; transform-origin: 0 50%' }}; border-radius: {{ ek_u(4) }}; background: var(--tk-line-2); overflow: hidden">
                            <span class="ek-nx-fill" style="position: absolute; inset: 0; background: var(--tk-green); transform-origin: {{ $mob ? '50% 0' : '0 50%' }}"></span>
                        </span>
                        @unless ($mob)
                            <span aria-hidden="true" data-m="nx-marker-in" style="position: absolute; left: {{ ek_u(140 - 82) }}; top: 0; width: {{ ek_u(164) }}">
                                <span class="ek-nx-marker" style="display: flex; flex-direction: column; align-items: center">
                                    <span style="height: {{ ek_u(40) }}; box-sizing: border-box; padding: 0 {{ ek_u(14) }}; display: inline-flex; align-items: center; gap: {{ ek_u(8) }}; border-radius: 999px; background: var(--tk-ink); color: #FFFFFF; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.06em; white-space: nowrap"><svg viewBox="0 0 24 24" fill="none" stroke="#8FE3AE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $mail !!}</svg><span class="ek-nx-a">{{ $t['marker'] }}</span><span class="ek-nx-b">{{ $t['marker_done'] }}</span></span>
                                    <span style="width: 0; height: 0; border-left: {{ ek_u(7) }} solid transparent; border-right: {{ ek_u(7) }} solid transparent; border-top: {{ ek_u(8) }} solid var(--tk-ink)"></span>
                                </span>
                            </span>
                        @endunless

                        <ol data-m="nx-steps" aria-label="{{ $t['eyebrow'] }}" style="list-style: none; margin: 0; padding: 0; position: absolute; inset: 0">
                            @foreach ($steps as $i => $step)
                                @php
                                    $dx = $mob ? 18 : 140 + $i * $gap - 28;
                                    $dy = $mob ? 232 + $i * $gap - 22 : 66;
                                    $dot = $v([56, 44]);
                                @endphp
                                <li class="ek-nx-step {{ $i === 0 ? 'is-active' : '' }}" style="--i: {{ $i }}; position: absolute; left: 0; top: 0">
                                    <span aria-hidden="true" style="position: absolute; left: {{ ek_u($dx) }}; top: {{ ek_u($dy) }}; width: {{ ek_u($dot) }}; height: {{ ek_u($dot) }}">
                                        <span class="ek-nx-pulse" style="position: absolute; inset: 0"><span data-m="nx-pulse" style="position: absolute; inset: 0; border-radius: 50%; background: var(--tk-green); opacity: 0"></span></span>
                                        <span class="ek-nx-dot" style="position: absolute; inset: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u($v([24, 20])) }}; height: {{ ek_u($v([24, 20])) }}; overflow: visible">{!! $icons[$step['icon'] ?? 'mail'] ?? $icons['mail'] !!}</svg></span>
                                    </span>
                                    <span data-m="nx-card" style="position: absolute; {{ $mob ? 'left: '.ek_u(76).'; top: '.ek_u($dy).'; width: '.ek_u(298) : 'left: '.ek_u(140 + $i * $gap - 140).'; top: '.ek_u(160).'; width: '.ek_u(280).'; height: '.ek_u(290) }}">
                                        <span class="ek-nx-card" style="height: 100%; box-sizing: border-box; padding: {{ $mob ? ek_u(12).' '.ek_u(14) : ek_u(26).' '.ek_u(24) }}; border-radius: {{ ek_u($v([24, 18])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([10, 4])) }}">
                                            <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? ek_u(10.5) : 'max('.ek_u(12).', 0.75rem)' }}; letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $step['kicker'] ?? '' }}</span>
                                            <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([23, 18])) }}, 0.8125rem); line-height: 1.15; letter-spacing: -0.03em; color: var(--tk-ink)">{{ $step['title'] }}</span>
                                            <span style="font-size: max({{ ek_u($v([16.5, 13.5])) }}, 0.8125rem); line-height: {{ $v([1.5, 1.42]) }}; color: #3B4640">{{ $step['text'] ?? '' }}</span>
                                            <span class="ek-nx-chip" style="margin-top: {{ $mob ? ek_u(4) : 'auto' }}; align-self: flex-start; padding: {{ ek_u($v([6, 4])) }} {{ ek_u($v([12, 10])) }}; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? ek_u(11.5) : 'max('.ek_u(13).', 0.8125rem)' }}">{{ $step['chip'] ?? '' }}</span>
                                        </span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                        @if ($mob)
                            <span aria-hidden="true" data-m="nx-marker-in" style="position: absolute; left: {{ ek_u(18) }}; top: {{ ek_u(232 - 22) }}; z-index: 3"><span class="ek-nx-marker" style="width: {{ ek_u(44) }}; height: {{ ek_u(44) }}; border-radius: 50%; background: var(--tk-ink); box-shadow: 0 {{ ek_u(10) }} {{ ek_u(24) }} {{ ek_u(-10) }} rgba(13,20,16,0.6); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="#8FE3AE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $mail !!}</svg></span></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
