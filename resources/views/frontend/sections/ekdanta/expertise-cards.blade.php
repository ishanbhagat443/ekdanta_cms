{{--
    Ekdanta · Expertise Cards. One card per service group, drawn dealt and
    face up (the final state, by left/top); scroll tracks move each card by
    offsets from there: raise the deck, fan it, deal it and flip it.
    Card label at 80 % opacity (drawn 70 %): approved 10 Oct 2026, 5:1 on green. A card is a link to its group in the Service Deep
    Dive (the motion engine scrolls to that group's step).
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $groups = \App\Models\CollectionItem::pick('service-groups', $d['groups'] ?? null);
    $services = \App\Models\CollectionItem::pick('services')->filter(fn ($s) => $s->get('in_grid'));
    $mark = media_ref($d['mark'] ?? null);
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
<div class="ek ek-expertise-cards" id="{{ $d['anchor'] ?: 's-menu' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$w, $h] = $v([[294, 440], [165, 300]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="menu-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([116, 84])) }}; {{ $mob ? 'width: '.ek_u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([64, 32])) }}; line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    @unless ($mob)
                        <p data-m="menu-text" style="position: absolute; right: {{ ek_u(96) }}; top: {{ ek_u(138) }}; width: {{ ek_u(400) }}; margin: 0; font-size: max({{ ek_u(19) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $t['text'] }}</p>
                    @endunless

                    @foreach ($groups->values() as $i => $group)
                        @php
                            $items = $services->filter(fn ($s) => $s->get('group') === $group->slug)->values();
                            $bg = $group->get('card_colour') ?: $group->get('colour');
                            $fg = $group->get('text_colour') ?: '#0D1410';
                            [$x, $y] = $mob ? [24 + ($i % 2) * 177, 196 + intdiv($i, 2) * 312] : [96 + $i * 316, 290];
                            $shadow = strtoupper((string) $bg) === '#FFFFFF'
                                ? '0 '.ek_u(30).' '.ek_u(60).' '.ek_u(-30).' rgba(13,20,16,0.35), inset 0 0 0 '.ek_u(1).' rgba(13,20,16,0.06)'
                                : '0 '.ek_u($v([30, 24])).' '.ek_u($v([60, 50])).' '.ek_u($v([-30, -28])).' rgba(13,20,16,0.55)';
                        @endphp
                        <div data-m="card-{{ $i }}" class="ek-pcard" style="position: absolute; left: {{ ek_u($x) }}; top: {{ ek_u($y) }}; width: {{ ek_u($w) }}; height: {{ ek_u($h) }}; transform-origin: {{ ek_u($w / 2) }} {{ ek_u($v([640, 440])) }}; z-index: {{ 10 + $i }}; perspective: {{ ek_u($v([1400, 1000])) }}">
                            <a href="{{ $d['jump_to'] ?: '#s-deep' }}" data-ek-step="{{ $i }}" data-ek-target="{{ $d['jump_to'] ?: '#s-deep' }}" aria-label="{{ str_replace('{group}', $group->title, $d['card_label']) }}" data-m="flip-{{ $i }}" style="position: relative; display: block; width: {{ ek_u($w) }}; height: {{ ek_u($h) }}; text-decoration: none; transform-style: preserve-3d">
                                <span style="position: absolute; inset: 0; box-sizing: border-box; padding: {{ ek_u($v([26, 14])) }}; border-radius: {{ ek_u($v([24, 20])) }}; background: {{ $bg }}; color: {{ $fg }}; box-shadow: {{ $shadow }}; backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column">
                                    <span style="display: flex; justify-content: space-between; align-items: center; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? ek_u(10.5) : 'max('.ek_u(14).', 0.8125rem)' }}; letter-spacing: {{ $v([0.08, 0.06]) }}em"><span>{{ sprintf('%02d', $i + 1) }}</span><span style="opacity: 0.8">{{ str_replace('{count}', (string) $items->count(), $t['kicker']) }}</span></span>
                                    <span style="margin-top: {{ ek_u($v([20, 10])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([36, 24])) }}; line-height: 1; letter-spacing: -0.04em">{{ $group->title }}</span>
                                    <span style="margin-top: {{ ek_u($v([12, 6])) }}; font-size: max({{ ek_u($v([16, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); line-height: {{ $v([1.45, 1.35]) }}; opacity: 0.85">{{ $group->get('short_description') }}</span>
                                    <span style="margin-top: auto; {{ $mob ? '' : 'padding-top: '.ek_u(14).';' }} display: flex; flex-direction: column; gap: {{ ek_u($v([9, 5])) }}">
                                        @foreach ($items as $item)
                                            <span data-m="items-{{ $i }}" style="display: flex; align-items: center; gap: {{ ek_u($v([10, 7])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? ek_u(11.5).'; line-height: 1.2' : 'max('.ek_u(15).', 0.8125rem)' }}"><span style="width: {{ ek_u($v([7, 5])) }}; height: {{ ek_u($v([7, 5])) }}; border-radius: 50%; background: {{ $group->get('card_dot') ?: $group->get('accent') }}; flex-shrink: 0"></span>{{ $item->get('short_name') ?: $item->title }}</span>
                                        @endforeach
                                    </span>
                                    <span style="margin-top: {{ ek_u($v([18, 10])) }}; padding-top: {{ ek_u($v([14, 8])) }}; border-top: {{ ek_u(1) }} solid {{ $group->get('rule') ?: 'currentColor' }}; display: flex; justify-content: space-between; align-items: center; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? ek_u(11.5) : 'max('.ek_u(14).', 0.8125rem)' }}">{{ $t['link_label'] }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($v([18, 14])) }}; height: {{ ek_u($v([18, 14])) }}">{!! $arrow !!}</svg></span>
                                </span>
                                <span aria-hidden="true" style="position: absolute; inset: 0; border-radius: {{ ek_u($v([24, 20])) }}; background: var(--tk-green-deep); transform: rotateY(180deg); backface-visibility: hidden; -webkit-backface-visibility: hidden; box-shadow: 0 {{ ek_u($v([30, 24])) }} {{ ek_u($v([60, 50])) }} {{ ek_u($v([-30, -28])) }} rgba(13,20,16,0.6); display: flex; align-items: center; justify-content: center">
                                    <span style="position: absolute; inset: {{ ek_u($v([14, 10])) }}; border-radius: {{ ek_u($v([16, 14])) }}; border: {{ ek_u(1.5) }} dashed rgba(143,227,174,0.35)"></span>
                                    <span style="width: {{ ek_u($v([116, 76])) }}; height: {{ ek_u($v([116, 76])) }}; border-radius: 50%; background: #FFFFFF; display: flex; align-items: center; justify-content: center">@if ($mark)<img loading="lazy" decoding="async" src="{{ $mark->web_url }}" alt="" style="width: {{ ek_u($v([70, 46])) }}; height: auto; display: block">@endif</span>
                                </span>
                            </a>
                        </div>
                    @endforeach

                    <p data-m="menu-hint" style="position: absolute; left: 0; right: 0; top: {{ ek_u($v([780, 816])) }}; margin: 0; text-align: center; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ ek_u($v([15, 12])) }}, {{ $mob ? '0.75rem' : '0.8125rem' }}); color: var(--tk-muted)">{{ $t['hint'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
