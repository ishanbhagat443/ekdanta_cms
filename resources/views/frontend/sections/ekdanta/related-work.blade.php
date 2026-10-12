{{--
    Ekdanta · Related Work. The service's case studies (its "related case
    studies" field) or, until it has some, its placeholder cards. Scroll
    tracks bring the cards in from the sides. Phones (no design): compact
    cards, one under another.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $service = $templateItem ?? null;
    $service = $service instanceof \App\Models\CollectionItem ? $service : (\App\Models\CollectionItem::pick('services', $d['service'] ?? null)->first());
    $cards = collect();
    if ($service) {
        $cases = trim((string) $service->get('proof_cases')) !== '' ? \App\Models\CollectionItem::pick('case-studies', $service->get('proof_cases'))->take(3) : collect();
        $cards = $cases->isNotEmpty()
            ? $cases->map(fn ($c) => ['case' => $c])
            : collect((array) $service->get('proof_placeholders', []))->filter(fn ($p) => ($p['title'] ?? '') !== '')->take(3)->map(fn ($p) => ['placeholder' => $p]);
        $cards = $cards->values();
    }
    $photo = '<rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><path d="M21 16l-5-5-8 8"></path>';
@endphp
@if ($cards->isNotEmpty())
<div class="ek ek-related-work" id="{{ $d['anchor'] ?: 'v-proof' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div data-m="rw-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([116, 84])) }}; width: {{ $u($v([900, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([56, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $service->get('proof_title') }}</h2>
                    </div>
                    <ul style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([280, 200])) }}; width: {{ $u($v([1248, 342])) }}; margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: {{ $mob ? '1fr' : 'repeat(3, minmax(0, 1fr))' }}; gap: {{ $u($v([24, 14])) }}">
                        @foreach ($cards as $i => $card)
                            @php
                                $case = $card['case'] ?? null;
                                $hex = $case ? ltrim((string) $case->get('colour'), '#') : '';
                            @endphp
                            <li data-m="rw-card-{{ $i + 1 }}" style="height: {{ $u($v([500, 170])) }}; border-radius: {{ $u($v([24, 20])) }}; overflow: hidden; display: flex; flex-direction: {{ $mob ? 'row' : 'column' }}; background: {{ $case ? (strlen($hex) === 6 ? '#'.$hex : 'var(--tk-ink)') : 'var(--tk-paper)' }}; {{ $case ? 'box-shadow: 0 '.$u(40).' '.$u(70).' '.$u(-40).' rgba(13,20,16,0.6)' : '' }}">
                                @if ($case)
                                    <div style="{{ $mob ? 'width: '.$u(124).'; flex: none' : 'height: '.$u(300) }}; overflow: hidden; background: #FFFFFF">@if ($img = media_ref($case->get('image'))?->url)<img src="{{ $img }}" alt="{{ str_replace('{name}', $case->title, (string) $d['image_alt']) }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: top; display: block">@endif</div>
                                    <div style="padding: {{ $u($v([20, 14])) }} {{ $u($v([22, 16])) }}; display: flex; flex-direction: column; gap: {{ $u($v([10, 6])) }}; color: #FFFFFF; min-width: 0">
                                        <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([24, 17])) }}, 0.8125rem); letter-spacing: -0.03em">{{ $case->title }}</h3>
                                        <p style="margin: 0; font-size: max({{ $u($v([15, 13])) }}, 0.8125rem); line-height: 1.35; color: rgba(255,255,255,0.8)">{{ implode(' · ', array_filter(array_map('trim', preg_split('/\R/', (string) $case->get('made'))))) }}</p>
                                        @if ($url = $case->url())<a href="{{ $url }}" style="margin-top: {{ $mob ? 'auto' : $u(4) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([15, 13])) }}, 0.8125rem); color: var(--tk-mint)">{{ $t['link_label'] }}<span class="visually-hidden">: {{ $case->title }}</span></a>@endif
                                    </div>
                                @else
                                    <div style="flex: 1; margin: {{ $u($v([18, 12])) }}; border-radius: {{ $u($v([18, 14])) }}; border: {{ $u(2) }} dashed #C5CCC7; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: {{ $u($v([10, 4])) }}; text-align: center; padding: {{ $u($v([24, 12])) }}; color: var(--tk-muted)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#9AA39E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ $u($v([40, 28])) }}; height: {{ $u($v([40, 28])) }}">{!! $photo !!}</svg>
                                        <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([18, 15])) }}, 0.8125rem); color: #3B4640">{{ $card['placeholder']['title'] }}</h3>
                                        <p style="margin: 0; font-size: max({{ $u($v([15, 13])) }}, 0.8125rem); line-height: 1.45">{{ $card['placeholder']['text'] ?? '' }}</p>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
