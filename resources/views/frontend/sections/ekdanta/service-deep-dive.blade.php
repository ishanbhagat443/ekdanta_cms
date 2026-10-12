{{--
    Ekdanta · Service Deep Dive. One panel per service group, stacked; the
    scene (service-deep-dive.js) wipes each open and builds its tiles
    (tiles/<artwork>.blade.php, filled by service-tiles.js). Drawn finished,
    first group showing. The steps are the panels, so the tabs (and the
    Expertise Cards) jump to a group.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $groups = \App\Models\CollectionItem::pick('service-groups', $d['groups'] ?? null)->values();
    $services = \App\Models\CollectionItem::pick('services')->filter(fn ($s) => $s->get('in_grid'));
    $total = $groups->count();
@endphp
<div class="ek ek-service-deep-dive" id="{{ $d['anchor'] ?: 's-deep' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $wipe = $v([[1078, 440], [195, 420]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $groups->first()?->get('colour') ?: 'var(--tk-green)' }}">
                <div class="ek-art">
                    <div data-m="deep-groups" style="position: absolute; inset: 0">
                        @foreach ($groups as $gi => $group)
                            @php
                                $items = $services->filter(fn ($s) => $s->get('group') === $group->slug)->values();
                                $ink = $group->get('text_colour') ?: '#0D1410';
                                $dark = strtoupper($ink) === '#FFFFFF';
                                $bg = $group->get('colour') ?: '#2EB45D';
                                $badge = $group->get('badge') ?: '#2EB45D';
                                $capBadge = strtoupper($badge) === '#0D1410' ? '#2EB45D' : $badge; // tile numbers stay light on the tiles
                                $tileBg = $dark ? (strtoupper($bg) === '#0D1410' ? '#18231E' : 'var(--tk-ink)') : '#FFFFFF';
                                $tileShadow = $dark
                                    ? (strtoupper($bg) === '#0D1410' ? 'inset 0 0 0 '.ek_u(1).' rgba(255,255,255,0.07)' : '0 '.ek_u(22).' '.ek_u(40).' '.ek_u(-26).' rgba(0,0,0,0.6)')
                                    : '0 '.ek_u(22).' '.ek_u(40).' '.ek_u(-26).' rgba(13,20,16,'.(strtoupper($bg) === '#F4F6F1' ? '0.35' : '0.55').')';
                                $line = $group->get('deep_line') ?: $ink;
                                if ($mob && strtoupper($line) === '#1E8E47') { $line = '#1B8141'; } // small text: the approved shade
                            @endphp
                            <section data-group="{{ $gi }}" data-badge="{{ $badge }}" data-badge-text="{{ $group->get('badge_text') ?: '#FFFFFF' }}" data-ink="{{ $ink }}" aria-labelledby="{{ $section->id }}-{{ $comp[0] }}-{{ $group->slug }}"
                                     style="position: absolute; inset: 0; overflow: hidden; background: {{ $bg }}; color: {{ $ink }}; {{ $gi > 0 ? 'clip-path: circle(0px at '.ek_u($wipe[0]).' '.ek_u($wipe[1]).');' : '' }}">
                                <span aria-hidden="true" data-deep="word" style="position: absolute; right: {{ ek_u($v([-40, -20])) }}; bottom: {{ ek_u($v([-70, -30])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([300, 120])) }}; line-height: 1; letter-spacing: -0.06em; color: transparent; -webkit-text-stroke: {{ ek_u($v([2, 1.5])) }} {{ $dark ? 'rgba(255,255,255,0.10)' : 'rgba(13,20,16,0.14)' }}; white-space: nowrap; opacity: 0.5">{{ $group->get('word') }}</span>
                                <div data-deep="head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([128, 82])) }}; width: {{ ek_u($v([660, 342])) }}">
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: {{ $group->get('deep_kicker') ?: $ink }}">{{ str_replace(['{n}', '{total}', '{word}'], [sprintf('%02d', $gi + 1), sprintf('%02d', $total), $group->get('word')], $t['kicker']) }}</span>
                                    <h2 id="{{ $section->id }}-{{ $comp[0] }}-{{ $group->slug }}" style="margin: {{ ek_u($v([6, 2])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([80, 44])) }}; line-height: 1; letter-spacing: -0.05em">{{ $group->title }}</h2>
                                </div>
                                <div data-deep="text" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([262, 152])) }}; width: {{ ek_u($v([640, 342])) }}">
                                    <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([30, 17])) }}, 0.8125rem); letter-spacing: {{ $v([-0.03, -0.02]) }}em; color: {{ $line }}">{{ $group->get('hinglish') }}</p>
                                    <p style="margin: {{ ek_u($v([10, 4])) }} 0 0; {{ $mob ? '' : 'width: '.ek_u(600).';' }} font-size: max({{ ek_u($v([19, 13.5])) }}, 0.8125rem); line-height: {{ $v([1.5, 1.42]) }}; color: {{ $group->get('deep_text') ?: $ink }}">{{ $group->get('description') }}</p>
                                </div>

                                @unless ($mob)
                                    <ol style="position: absolute; left: {{ ek_u(96) }}; top: {{ ek_u(420) }}; width: {{ ek_u(660) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: {{ ek_u(22) }} {{ ek_u(28) }}">
                                        @foreach ($items as $k => $item)
                                            <li data-deep="item" style="display: flex; gap: {{ ek_u(12) }}"><span aria-hidden="true" style="width: {{ ek_u(30) }}; height: {{ ek_u(30) }}; flex-shrink: 0; border-radius: 50%; box-shadow: inset 0 0 0 {{ ek_u(1.5) }} {{ $ink }}; background: {{ $badge }}; color: {{ $group->get('badge_text') ?: '#FFFFFF' }}; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(12.5) }}, 0.78125rem); display: flex; align-items: center; justify-content: center">{{ sprintf('%02d', $k + 1) }}</span><span style="display: flex; flex-direction: column; gap: {{ ek_u(4) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(18) }}, 0.8125rem); line-height: 1.25; letter-spacing: -0.02em">{{ $item->title }}</span><span style="font-size: max({{ ek_u(15) }}, 0.8125rem); line-height: 1.42; color: {{ $group->get('deep_text') ?: $ink }}">{{ $item->get('one_liner') }}</span></span></li>
                                        @endforeach
                                    </ol>
                                @endunless

                                @foreach ($items as $k => $item)
                                    @php [$x, $y] = $mob ? [24 + ($k % 2) * 177, 252 + intdiv($k, 2) * 140] : [812 + ($k % 2) * 274, 130 + intdiv($k, 2) * 212]; @endphp
                                    <div aria-hidden="true" data-deep="tile" data-tile="{{ $item->get('tile') }}" style="position: absolute; left: {{ ek_u($x) }}; top: {{ ek_u($y) }}; width: {{ ek_u($v([258, 165])) }}; height: {{ ek_u($v([196, 128])) }}; border-radius: {{ ek_u($v([20, 16])) }}; overflow: hidden; background: {{ $tileBg }}; box-shadow: {{ $tileShadow }}">
                                        <div style="position: absolute; left: 0; top: 0; width: {{ ek_u(258) }}; height: {{ ek_u(150) }}; {{ $mob ? 'transform-origin: 0 0; transform: scale(0.6395);' : '' }}">
                                            @includeIf('frontend.sections.ekdanta.tiles.'.$item->get('tile'))
                                        </div>
                                        <div style="position: absolute; left: {{ ek_u($v([16, 10])) }}; right: {{ ek_u($v([12, 8])) }}; bottom: {{ ek_u($v([14, 9])) }}; display: flex; align-items: center; gap: {{ ek_u($v([8, 6])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u($v([13.5, 11])) }}; {{ $mob ? 'line-height: 1.15;' : 'letter-spacing: -0.01em;' }} color: {{ $dark ? 'var(--tk-line-3)' : 'var(--tk-ink)' }}"><span style="width: {{ ek_u($v([22, 17])) }}; height: {{ ek_u($v([22, 17])) }}; flex-shrink: 0; border-radius: 50%; background: {{ $capBadge }}; color: var(--tk-ink); font-size: {{ ek_u($v([10.5, 8.5])) }}; font-weight: 700; display: flex; align-items: center; justify-content: center">{{ sprintf('%02d', $k + 1) }}</span>{{ $item->get('short_name') ?: $item->title }}</div>
                                    </div>
                                @endforeach

                                @if ($mob)
                                    <div aria-hidden="true" style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(674) }}; display: flex; gap: {{ ek_u(4) }}">
                                        @foreach ($items as $k => $item)
                                            <span data-deep="dot" style="width: {{ ek_u($k === $items->count() - 1 ? 18 : 6) }}; height: {{ ek_u(4) }}; border-radius: {{ ek_u(2) }}; background: {{ $badge }}; transition: width 250ms ease-out"></span>
                                        @endforeach
                                    </div>
                                    <ol style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(690) }}; width: {{ ek_u(342) }}; height: {{ ek_u(90) }}; margin: 0; padding: 0; list-style: none">
                                        @foreach ($items as $k => $item)
                                            <li data-deep="caption" style="position: absolute; left: 0; top: 0; width: {{ ek_u(342) }}; display: flex; gap: {{ ek_u(10) }}; {{ $k === $items->count() - 1 ? '' : 'opacity: 0;' }}"><span aria-hidden="true" style="width: {{ ek_u(26) }}; height: {{ ek_u(26) }}; flex-shrink: 0; border-radius: 50%; background: {{ $badge }}; color: {{ $group->get('badge_text') ?: '#FFFFFF' }}; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u(11) }}; display: flex; align-items: center; justify-content: center">{{ sprintf('%02d', $k + 1) }}</span><span style="display: flex; flex-direction: column; gap: {{ ek_u(2) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(16) }}, 0.8125rem); line-height: 1.2; letter-spacing: -0.02em">{{ $item->title }}</span><span style="font-size: max({{ ek_u(13.5) }}, 0.8125rem); line-height: 1.4; color: {{ $group->get('deep_text') ?: $ink }}">{{ $item->get('one_liner') }}</span></span></li>
                                        @endforeach
                                    </ol>
                                @endif
                            </section>
                        @endforeach
                    </div>

                    <nav aria-label="{{ $d['nav_label'] }}" class="ek-deep-nav" style="position: absolute; {{ $mob ? 'left: '.ek_u(12).'; right: '.ek_u(12).'; top: '.ek_u(784).'; gap: '.ek_u(2).'; padding: '.ek_u(4).'; box-shadow: 0 '.ek_u(14).' '.ek_u(26).' '.ek_u(-16).' rgba(0,0,0,0.7); overflow: hidden' : 'left: '.ek_u(96).'; top: '.ek_u(808).'; gap: '.ek_u(4).'; padding: '.ek_u(5).'; box-shadow: 0 '.ek_u(16).' '.ek_u(30).' '.ek_u(-18).' rgba(0,0,0,0.7)' }}; display: flex; align-items: center; border-radius: 999px; background: rgba(13,20,16,0.9)">
                        @foreach ($groups as $gi => $group)
                            <button type="button" class="ek-press ek-deep-tab {{ $gi === 0 ? 'is-current' : '' }}" data-deep="tab" data-ek-step="{{ $gi }}" @if ($gi === 0) aria-current="true" @endif style="{{ $mob ? 'flex: 1; padding: 0;' : 'padding: 0 '.ek_u(18).';' }} height: {{ ek_u(38) }}; min-height: 38px; border: 0; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 13])) }}, 0.8125rem); cursor: pointer">{{ $group->title }}</button>
                        @endforeach
                        <span aria-hidden="true" style="{{ $mob ? 'position: absolute; left: '.ek_u(18).'; right: '.ek_u(18).'; bottom: '.ek_u(1).'; height: '.ek_u(2) : 'width: '.ek_u(120).'; height: '.ek_u(4).'; margin: 0 '.ek_u(14).' 0 '.ek_u(12) }}; border-radius: {{ ek_u(2) }}; background: rgba(255,255,255,{{ $v([0.18, 0.12]) }})"><span data-deep="bar" style="display: block; height: 100%; border-radius: {{ ek_u(2) }}; background: var(--tk-orange); transform-origin: 0 50%; transform: scaleX(0)"></span></span>
                    </nav>
                </div>
            </div>
        </div>
    @endforeach
</div>
