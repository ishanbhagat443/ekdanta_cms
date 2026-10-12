{{--
    Ekdanta · Service Pricing + FAQ. Scroll tracks bring the factors and the
    questions in. The accordion is native <details> (works without JS; the
    first question starts open on desktop). Phones (no design): pricing
    first, then the questions, all closed.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $service = $templateItem ?? null;
    $service = $service instanceof \App\Models\CollectionItem ? $service : (\App\Models\CollectionItem::pick('services', $d['service'] ?? null)->first());
@endphp
@if ($service)
@php
    $lower = $service->get('lower_name') ?: mb_strtolower($service->title);
    $factors = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $service->get('price_factors')))));
    $faqs = \App\Models\CollectionItem::query()->of('faqs')->published()->get()->filter(fn ($f) => $f->get('group') === $service->slug)->values();
    $wa = str_replace('{service}', $lower, (string) ($d['button_link'] ?? ''));
    $plus = '<path d="M12 5v14M5 12h14"></path>';
@endphp
<div class="ek ek-service-pricing-faq" id="{{ $d['anchor'] ?: 'v-price' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="pf-head" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([120, 84])) }}; width: {{ $u($v([520, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([56, 32])) }}, 0.8125rem); line-height: 1.04; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        <p style="margin: {{ $u($v([14, 8])) }} 0 0; font-size: max({{ $u($v([19, 15])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-muted)">{{ str_replace('{service}', $lower, $t['text']) }}</p>
                    </div>
                    <div style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([320, 238])) }}; width: {{ $u($v([520, 342])) }}; display: flex; flex-direction: column; gap: {{ $u($v([12, 8])) }}">
                        <ol style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ $u($v([12, 8])) }}">
                            @foreach ($factors as $k => $factor)
                                <li data-m="pf-factor" style="display: flex; align-items: center; gap: {{ $u($v([14, 10])) }}; padding: {{ $u($v([14, 10])) }} {{ $u($v([18, 12])) }}; border-radius: {{ $u($v([16, 14])) }}; background: #FFFFFF; box-shadow: 0 {{ $u(10) }} {{ $u(20) }} {{ $u(-18) }} rgba(13,20,16,0.5)"><span class="ek-pf-num" aria-hidden="true" style="width: {{ $u($v([30, 26])) }}; height: {{ $u($v([30, 26])) }}; flex-shrink: 0; border-radius: 999px; background: #D9F2E1; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u(13) }}, 0.8125rem); display: flex; align-items: center; justify-content: center">{{ $k + 1 }}</span><span style="font-size: max({{ $u($v([17, 14])) }}, 0.8125rem); line-height: 1.35">{{ $factor }}</span></li>
                            @endforeach
                        </ol>
                        <a data-m="pf-button" class="ek-press" href="{{ link_href($wa) }}" @if (\App\Support\Link::external($wa)) target="_blank" rel="noopener" @endif style="align-self: flex-start; margin-top: {{ $u($v([10, 4])) }}; height: {{ $u($v([52, 44])) }}; box-sizing: border-box; padding: 0 {{ $u($v([22, 18])) }}; display: inline-flex; align-items: center; gap: {{ $u(8) }}; background: var(--tk-ink); color: #FFFFFF; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([15, 14])) }}, 0.8125rem); text-decoration: none">{{ str_replace('{service}', $lower, $t['button_label']) }}</a>
                    </div>

                    @if ($faqs->isNotEmpty())
                        <div style="position: absolute; left: {{ $u($v([720, 24])) }}; top: {{ $u($v([120, 524])) }}; width: {{ $u($v([624, 342])) }}">
                            <span data-m="pf-faq-head" style="display: block; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['faq_label'] }}</span>
                            <div style="margin-top: {{ $u($v([16, 10])) }}; display: flex; flex-direction: column; gap: {{ $u($v([12, 8])) }}">
                                @foreach ($faqs as $k => $faq)
                                    <details data-m="pf-faq" class="ek-pf-faq" @if ($k === 0 && ! $mob) open @endif style="border-radius: {{ $u($v([20, 16])) }}; background: #FFFFFF; box-shadow: 0 {{ $u(14) }} {{ $u(28) }} {{ $u(-24) }} rgba(13,20,16,0.5); overflow: hidden">
                                        <summary style="display: flex; align-items: center; justify-content: space-between; gap: {{ $u(16) }}; padding: {{ $u($v([20, 14])) }} {{ $u($v([24, 16])) }}; cursor: pointer; list-style: none; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ $u($v([19, 15])) }}, 0.8125rem); letter-spacing: -0.02em; color: var(--tk-ink)">{{ $faq->title }}<span aria-hidden="true" class="ek-pf-chev" style="width: {{ $u($v([34, 28])) }}; height: {{ $u($v([34, 28])) }}; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width: {{ $u(16) }}; height: {{ $u(16) }}">{!! $plus !!}</svg></span></summary>
                                        <p style="margin: 0; padding: 0 {{ $u($v([24, 16])) }} {{ $u($v([22, 14])) }}; font-size: max({{ $u($v([17, 14])) }}, 0.8125rem); line-height: 1.55; color: #3B4640">{{ $faq->get('answer') }}</p>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
