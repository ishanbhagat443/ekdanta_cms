{{--
    Ekdanta · FAQ Deck. The cards are the steps; each card's place in the
    deck (thrown away, in front, or further back) follows the steps' index
    in CSS (_faq-deck.scss). Every question and answer stays readable to
    screen readers; the arrows jump to the previous / next card.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $faqs = \App\Models\CollectionItem::query()->of('faqs')->published()->get()->filter(fn ($f) => $f->get('group') === ($d['group'] ?: 'contact'))->values();
    $count = max(1, $faqs->count());
    $styles = [['#FFFFFF', '#0D1410', '#4A564F', '#1E8E47'], ['#E6F4EA', '#0D1410', '#2F4A38', '#1E8E47'], ['#0A5A30', '#FFFFFF', '#DCEFE2', '#8FE3AE']];
    $counter = fn (string $text) => str_replace(['{n}', '{total}'], ['<span data-ek-step-num="2">01</span>', e(sprintf('%02d', $count))], e($text));
    $up = '<path d="M12 19V5M5 12l7-7 7 7"></path>';
    $down = '<path d="M12 5v14M5 12l7 7 7-7"></path>';
@endphp
<div class="ek ek-faq-deck" id="{{ $d['anchor'] ?: 'c-faq' }}" style="--count: {{ $count }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $arrow = 'width: '.ek_u($v([50, 44])).'; height: '.ek_u($v([50, 44])).'; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer';
            $more = '<p style="margin: '.($mob ? '0' : ek_u(6).' 0 0').'; font-size: max('.ek_u($v([17, 15])).', 0.8125rem); line-height: 1.5; color: #4A564F">'.e($t['more_before']).' <a href="'.e(link_href($d['more_link'] ?? 'wa:')).'"'.(\App\Support\Link::external($d['more_link'] ?? 'wa:') ? ' target="_blank" rel="noopener"' : '').' style="font-weight: 600; color: var(--tk-green-deep)">'.e($t['more_link_label']).'</a>'.e($t['more_after']).'</p>';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}" style="--line: {{ $v([120, 64]) }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div data-m="faq-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([140, 82])) }}; width: {{ ek_u($v([430, 342])) }}; {{ $mob ? '' : 'display: flex; flex-direction: column; gap: '.ek_u(16).';' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ $mob ? ek_u(4).' 0 0' : '0' }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([56, 31])) }}; line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.045em">{!! nl2br(e($t['heading'])) !!}</h2>
                        <div style="margin-top: {{ ek_u($v([22, 12])) }}; display: flex; align-items: {{ $mob ? 'center' : 'flex-end' }}; gap: {{ ek_u($v([14, 12])) }}">
                            <div aria-hidden="true" style="flex-shrink: 0; height: {{ ek_u($v([120, 64])) }}; overflow: hidden; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([120, 64])) }}; line-height: {{ ek_u($v([120, 64])) }}; letter-spacing: -0.05em; color: var(--tk-green-deep)">
                                <span class="ek-faq-roll" style="display: flex; flex-direction: column">
                                    @for ($i = 1; $i <= $count; $i++)<span>{{ sprintf('%02d', $i) }}</span>@endfor
                                </span>
                            </div>
                            @if ($mob)
                                <div style="display: flex; flex-direction: column; gap: {{ ek_u(8) }}">
                            @endif
                            <span aria-live="polite" style="{{ $mob ? '' : 'padding-bottom: '.ek_u(18).';' }} font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([18, 13.5])) }}, 0.8125rem); color: var(--tk-muted)">{!! $counter($t['counter']) !!}</span>
                            @if ($mob)
                                    <div aria-hidden="true" style="width: {{ ek_u(160) }}; height: {{ ek_u(4) }}; border-radius: {{ ek_u(2) }}; background: #DCE3DA"><div class="ek-faq-bar" style="height: 100%; border-radius: {{ ek_u(2) }}; background: var(--tk-green); transform-origin: 0 50%"></div></div>
                                </div>
                                <div style="margin-left: auto; display: flex; gap: {{ ek_u(8) }}">
                                    <button type="button" class="ek-press" data-ek-step="-1" aria-label="{{ $d['previous_label'] }}" style="{{ $arrow }}; border: {{ ek_u(1.5) }} solid var(--tk-ink); background: transparent"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $up !!}</svg></button>
                                    <button type="button" class="ek-press" data-ek-step="+1" aria-label="{{ $d['next_label'] }}" style="{{ $arrow }}; border: 0; background: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $down !!}</svg></button>
                                </div>
                            @endif
                        </div>
                        @unless ($mob)
                            <div aria-hidden="true" style="width: {{ ek_u(360) }}; height: {{ ek_u(4) }}; border-radius: {{ ek_u(2) }}; background: #DCE3DA"><div class="ek-faq-bar" style="height: 100%; border-radius: {{ ek_u(2) }}; background: var(--tk-green); transform-origin: 0 50%"></div></div>
                            <div style="margin-top: {{ ek_u(10) }}; display: flex; gap: {{ ek_u(10) }}">
                                <button type="button" class="ek-press" data-ek-step="-1" aria-label="{{ $d['previous_label'] }}" style="{{ $arrow }}; border: {{ ek_u(1.5) }} solid var(--tk-ink); background: transparent"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(20) }}; height: {{ ek_u(20) }}">{!! $up !!}</svg></button>
                                <button type="button" class="ek-press" data-ek-step="+1" aria-label="{{ $d['next_label'] }}" style="{{ $arrow }}; border: 0; background: var(--tk-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(20) }}; height: {{ ek_u(20) }}">{!! $down !!}</svg></button>
                            </div>
                            {!! $more !!}
                        @endunless
                    </div>

                    <div data-m="faq-deck" style="position: absolute; left: {{ ek_u($v([600, 16])) }}; top: {{ ek_u($v([200, 268])) }}; width: {{ ek_u($v([744, 358])) }}; height: {{ ek_u($v([440, 380])) }}">
                        <div data-m="faq-cards">
                            @foreach ($faqs as $i => $faq)
                                @php [$bg, $fg, $answer, $num] = $styles[$i % 3]; @endphp
                                <article class="ek-faq-card {{ $i === 0 ? 'is-active' : '' }}" style="--i: {{ $i }}; --s: {{ $i % 2 ? 1 : -1 }}; position: absolute; left: 0; top: 0; width: {{ ek_u($v([744, 358])) }}; height: {{ ek_u($v([420, 344])) }}; box-sizing: border-box; padding: {{ ek_u($v([44, 22])) }}; border-radius: {{ ek_u($v([32, 26])) }}; background: {{ $bg }}; color: {{ $fg }}; box-shadow: 0 {{ ek_u($v([30, 24])) }} {{ ek_u($v([60, 50])) }} {{ ek_u($v([-36, -30])) }} rgba(13,20,16,0.45), inset 0 0 0 {{ ek_u(1) }} rgba(13,20,16,0.06); display: flex; flex-direction: column; gap: {{ ek_u($v([16, 12])) }}; transform-origin: 50% 100%">
                                    <span aria-hidden="true" style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([64, 44])) }}; line-height: 1; letter-spacing: -0.04em; color: {{ $num }}">{{ sprintf('%02d', $i + 1) }}</span>
                                    <h3 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([32, 22])) }}, 0.8125rem); line-height: {{ $v([1.18, 1.2]) }}; letter-spacing: -0.03em">{{ $faq->title }}</h3>
                                    <p style="margin: 0; font-size: max({{ ek_u($v([20, 16])) }}, 0.8125rem); line-height: {{ $v([1.6, 1.55]) }}; color: {{ $answer }}">{{ $faq->get('answer') }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    @if ($mob)
                        <div data-m="faq-more" style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(690) }}">{!! $more !!}</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
