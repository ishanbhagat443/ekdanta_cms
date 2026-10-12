{{--
    Ekdanta · Works Well With. The related services are links to their own
    pages, each on its hero colours. Scroll tracks raise and straighten
    them. Phones (no design): compact cards, one under another.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $service = $templateItem ?? null;
    $service = $service instanceof \App\Models\CollectionItem ? $service : (\App\Models\CollectionItem::pick('services', $d['service'] ?? null)->first());
    $slugs = $service ? trim((string) $service->get('related')) : '';
    $related = $slugs !== '' ? \App\Models\CollectionItem::pick('services', $slugs)->filter(fn ($s) => $s->url() !== null)->take(3)->values() : collect();
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
@if ($related->isNotEmpty())
<div class="ek ek-works-well-with" id="{{ $d['anchor'] ?: 'v-related' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-green-deep); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="ww-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([116, 84])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([56, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    <ul style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([290, 200])) }}; width: {{ ek_u($v([1248, 342])) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: {{ $mob ? '1fr' : 'repeat(3, minmax(0, 1fr))' }}; gap: {{ ek_u($v([24, 14])) }}">
                        @foreach ($related as $i => $other)
                            @php $lower = $other->get('lower_name') ?: mb_strtolower($other->title); @endphp
                            <li data-m="ww-card-{{ $i + 1 }}">
                                <a class="ek-press" href="{{ $other->url() }}" style="height: {{ ek_u($v([380, 176])) }}; box-sizing: border-box; padding: {{ ek_u($v([30, 18])) }}; border-radius: {{ ek_u($v([26, 20])) }}; background: {{ $other->get('hero_bg') ?: 'var(--tk-ink)' }}; color: {{ $other->get('hero_fg') ?: '#FFFFFF' }}; text-decoration: none; display: flex; flex-direction: column; box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(255,255,255,0.16), 0 {{ ek_u(40) }} {{ ek_u(70) }} {{ ek_u(-40) }} rgba(0,0,0,0.6)">
                                    <span class="ek-ww-group" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.1em">{{ $other->get('group_label') }}</span>
                                    <span style="margin-top: {{ ek_u($v([14, 6])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([34, 22])) }}, 0.8125rem); line-height: 1.05; letter-spacing: -0.04em">{{ $other->title }}</span>
                                    @unless ($mob)<span style="margin-top: {{ ek_u(14) }}; font-size: max({{ ek_u(17) }}, 0.8125rem); line-height: 1.5; opacity: 0.85">{{ $other->get('promise') }}</span>@endunless
                                    <span style="margin-top: auto; display: inline-flex; align-items: center; gap: {{ ek_u(10) }}; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem)">{{ str_replace('{service}', $lower, $t['link_label']) }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $arrow !!}</svg></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
