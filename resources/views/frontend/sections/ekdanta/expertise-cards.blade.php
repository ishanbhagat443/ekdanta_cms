{{--
    Ekdanta · Expertise Cards. One card per service group, drawn dealt and
    face up (the final state, by left/top); scroll tracks move each card by
    offsets from there: raise the deck, fan it, deal it and flip it.
    Card label at 80 % opacity (drawn 70 %): approved 10 Oct 2026, 5:1 on green. A card is a link to its group in the Service Deep
    Dive (the motion engine scrolls to that group's step).
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $groups = \App\Models\CollectionItem::pick('service-groups', $d['groups'] ?? null);
    $services = \App\Models\CollectionItem::pick('services')->filter(fn ($s) => $s->get('in_grid'));
    $mark = media_ref($d['mark'] ?? null);
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
<div class="ek ek-expertise-cards" id="{{ $d['anchor'] ?: 's-menu' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$w, $h] = $v([[294, 440], [165, 300]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="menu-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([116, 84])) }}; {{ $mob ? 'width: '.$u(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([64, 32])) }}; line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    @unless ($mob)
                        <p data-m="menu-text" style="position: absolute; right: {{ $u(96) }}; top: {{ $u(138) }}; width: {{ $u(400) }}; margin: 0; font-size: max({{ $u(19) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ $t['text'] }}</p>
                    @endunless

                    @foreach ($groups->values() as $i => $group)
                        @php
                            $items = $services->filter(fn ($s) => $s->get('group') === $group->slug)->values();
                            $bg = $group->get('card_colour') ?: $group->get('colour');
                            $fg = $group->get('text_colour') ?: '#0D1410';
                            [$x, $y] = $mob ? [24 + ($i % 2) * 177, 196 + intdiv($i, 2) * 312] : [96 + $i * 316, 290];
                            $shadow = strtoupper((string) $bg) === '#FFFFFF'
                                ? '0 '.$u(30).' '.$u(60).' '.$u(-30).' rgba(13,20,16,0.35), inset 0 0 0 '.$u(1).' rgba(13,20,16,0.06)'
                                : '0 '.$u($v([30, 24])).' '.$u($v([60, 50])).' '.$u($v([-30, -28])).' rgba(13,20,16,0.55)';
                        @endphp
                        <div data-m="card-{{ $i }}" class="ek-pcard" style="position: absolute; left: {{ $u($x) }}; top: {{ $u($y) }}; width: {{ $u($w) }}; height: {{ $u($h) }}; transform-origin: {{ $u($w / 2) }} {{ $u($v([640, 440])) }}; z-index: {{ 10 + $i }}; perspective: {{ $u($v([1400, 1000])) }}">
                            <a href="{{ $d['jump_to'] ?: '#s-deep' }}" data-ek-step="{{ $i }}" data-ek-target="{{ $d['jump_to'] ?: '#s-deep' }}" aria-label="{{ str_replace('{group}', $group->title, $d['card_label']) }}" data-m="flip-{{ $i }}" style="position: relative; display: block; width: {{ $u($w) }}; height: {{ $u($h) }}; text-decoration: none; transform-style: preserve-3d">
                                <span style="position: absolute; inset: 0; box-sizing: border-box; padding: {{ $u($v([26, 14])) }}; border-radius: {{ $u($v([24, 20])) }}; background: {{ $bg }}; color: {{ $fg }}; box-shadow: {{ $shadow }}; backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column">
                                    <span style="display: flex; justify-content: space-between; align-items: center; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? $u(10.5) : 'max('.$u(14).', 0.8125rem)' }}; letter-spacing: {{ $v([0.08, 0.06]) }}em"><span>{{ sprintf('%02d', $i + 1) }}</span><span style="opacity: 0.8">{{ str_replace('{count}', (string) $items->count(), $t['kicker']) }}</span></span>
                                    <span style="margin-top: {{ $u($v([20, 10])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([36, 24])) }}; line-height: 1; letter-spacing: -0.04em">{{ $group->title }}</span>
                                    <span style="margin-top: {{ $u($v([12, 6])) }}; font-size: max({{ $u($v([16, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); line-height: {{ $v([1.45, 1.35]) }}; opacity: 0.85">{{ $group->get('short_description') }}</span>
                                    <span style="margin-top: auto; {{ $mob ? '' : 'padding-top: '.$u(14).';' }} display: flex; flex-direction: column; gap: {{ $u($v([9, 5])) }}">
                                        @foreach ($items as $item)
                                            <span data-m="items-{{ $i }}" style="display: flex; align-items: center; gap: {{ $u($v([10, 7])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $mob ? $u(11.5).'; line-height: 1.2' : 'max('.$u(15).', 0.8125rem)' }}"><span style="width: {{ $u($v([7, 5])) }}; height: {{ $u($v([7, 5])) }}; border-radius: 50%; background: {{ $group->get('card_dot') ?: $group->get('accent') }}; flex-shrink: 0"></span>{{ $item->get('short_name') ?: $item->title }}</span>
                                        @endforeach
                                    </span>
                                    <span style="margin-top: {{ $u($v([18, 10])) }}; padding-top: {{ $u($v([14, 8])) }}; border-top: {{ $u(1) }} solid {{ $group->get('rule') ?: 'currentColor' }}; display: flex; justify-content: space-between; align-items: center; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $mob ? $u(11.5) : 'max('.$u(14).', 0.8125rem)' }}">{{ $t['link_label'] }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u($v([18, 14])) }}; height: {{ $u($v([18, 14])) }}">{!! $arrow !!}</svg></span>
                                </span>
                                <span aria-hidden="true" style="position: absolute; inset: 0; border-radius: {{ $u($v([24, 20])) }}; background: var(--tk-green-deep); transform: rotateY(180deg); backface-visibility: hidden; -webkit-backface-visibility: hidden; box-shadow: 0 {{ $u($v([30, 24])) }} {{ $u($v([60, 50])) }} {{ $u($v([-30, -28])) }} rgba(13,20,16,0.6); display: flex; align-items: center; justify-content: center">
                                    <span style="position: absolute; inset: {{ $u($v([14, 10])) }}; border-radius: {{ $u($v([16, 14])) }}; border: {{ $u(1.5) }} dashed rgba(143,227,174,0.35)"></span>
                                    <span style="width: {{ $u($v([116, 76])) }}; height: {{ $u($v([116, 76])) }}; border-radius: 50%; background: #FFFFFF; display: flex; align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark->url }}" alt="" style="width: {{ $u($v([70, 46])) }}; height: auto; display: block">@endif</span>
                                </span>
                            </a>
                        </div>
                    @endforeach

                    <p data-m="menu-hint" style="position: absolute; left: 0; right: 0; top: {{ $u($v([780, 816])) }}; margin: 0; text-align: center; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: max({{ $u($v([15, 12])) }}, {{ $mob ? '0.75rem' : '0.8125rem' }}); color: var(--tk-muted)">{{ $t['hint'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
