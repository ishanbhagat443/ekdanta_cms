{{--
    Ekdanta · Legal In Short (Privacy l-short, Terms t-short). Four cards
    dealt from a stack into place as you scroll (scrub tracks per card);
    two by two on phones. Drawn dealt. Card numbers take the approved
    small-text shades (#6A736D on light cards, #8A968F on dark; drawn #7B867F).
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $icons = \App\Services\SectionTypes\Ekdanta\LegalShortSection::ICONS;
    $light = strtoupper((string) $d['fg']) !== '#FFFFFF';
@endphp
<div class="ek ek-legal-short" id="{{ $d['anchor'] ?: 'l-short' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $d['bg'] }}; color: {{ $d['fg'] }}">
                <div class="ek-art">
                    <div data-m="ls-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([120, 96])) }}; width: {{ ek_u($v([640, 342])) }}">
                        <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: {{ $d['accent'] }}">{{ $t['eyebrow'] }}</p>
                        <h2 style="margin: {{ ek_u(8) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([64, 44])) }}; line-height: 1; letter-spacing: -0.045em; color: inherit">{{ $t['title'] }}</h2>
                        <p style="margin: {{ ek_u(12) }} 0 0; font-size: max({{ ek_u($v([20, 16])) }}, 0.8125rem); line-height: 1.5; color: {{ $d['muted'] }}">{{ $t['text'] }}</p>
                    </div>
                    @foreach (array_slice((array) $d['cards'], 0, 4) as $k => $card)
                        <div data-m="ls-card-{{ $k }}" style="position: absolute; left: {{ ek_u($mob ? 24 + ($k % 2) * 177 : 96 + $k * 317) }}; top: {{ ek_u($mob ? 236 + intdiv($k, 2) * 208 : 360) }}; width: {{ ek_u($v([296, 165])) }}; height: {{ ek_u($v([330, 196])) }}; box-sizing: border-box; padding: {{ ek_u($v([26, 16])) }}; border-radius: {{ ek_u($v([26, 18])) }}; background: {{ $d['card_bg'] }}; display: flex; flex-direction: column; gap: {{ ek_u($v([12, 8])) }}; box-shadow: 0 {{ ek_u(30) }} {{ ek_u(50) }} {{ ek_u(-36) }} rgba(13,20,16,0.6)">
                            <span style="display: flex; justify-content: space-between; align-items: center">
                                <span style="width: {{ ek_u($v([56, 40])) }}; height: {{ ek_u($v([56, 40])) }}; border-radius: 999px; background: var(--tk-green); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="var(--tk-ink)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($v([28, 22])) }}; height: {{ ek_u($v([28, 22])) }}">{!! $icons[$card['icon'] ?? 'form'] ?? $icons['form'] !!}</svg></span>
                                <span aria-hidden="true" style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); color: {{ $light ? '#6A736D' : '#8A968F' }}">{{ str_pad((string) ($k + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            </span>
                            <h3 style="margin: auto 0 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([23, 16])) }}, 0.8125rem); line-height: 1.15; letter-spacing: -0.02em; color: {{ $d['fg'] }}">{{ $card['title'] ?? '' }}</h3>
                            <p style="margin: 0; font-size: max({{ ek_u($v([16, 13])) }}, 0.8125rem); line-height: 1.45; color: {{ $d['muted'] }}">{{ $card['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
