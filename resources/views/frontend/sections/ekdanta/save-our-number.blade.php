{{--
    Ekdanta · Save Our Number. The numbers (Settings → Business) are tap-to-
    call links; the button downloads /contact.vcf. Scroll tracks raise the
    phone, cross-fade the unknown caller into Ekdanta and drop the "saved"
    toast in; loops ring and vibrate the phone. Drawn saved.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $phones = business_phones();
    $first = $phones[0] ?? null;
    // The first number as an unknown caller sees it: +91 90994 17987.
    $code = preg_replace('/\D/', '', (string) setting('business.country_code', ''));
    $shown = ! $first ? '' : (str_starts_with($first['number'], '+') || $code === '' ? $first['number'] : '+'.$code.' '.$first['number']);
    $mark = media_ref($d['mark'] ?? null);
    $handset = '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path>';
@endphp
<div class="ek ek-save-our-number" id="{{ $d['anchor'] ?: 't-save' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $button = '<a class="ek-press" href="'.e(route('front.vcard')).'" download="Ekdanta.vcf" style="'.($mob ? 'position: absolute; left: '.ek_u(24).'; right: '.ek_u(24).'; top: '.ek_u(762).'; height: '.ek_u(52).'; justify-content: center; display: flex;' : 'margin-top: '.ek_u(30).'; height: '.ek_u(56).'; padding: 0 '.ek_u(24).'; display: inline-flex;').' box-sizing: border-box; align-items: center; gap: '.ek_u(10).'; background: var(--tk-green); color: var(--tk-ink); border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 15])).', 0.8125rem); text-decoration: none"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: '.ek_u(18).'; height: '.ek_u(18).'"><circle cx="9" cy="8" r="4"></circle><path d="M2 20c0-3.5 3-6 7-6s7 2.5 7 6"></path><path d="M19 8v6M16 11h6"></path></svg>'.e($t['button_label']).'</a>';
            [$pw, $ph, $pb, $pr] = $v([[310, 640, 11, 48], [166, 312, 7, 30]]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div data-ek-fit="S" aria-hidden="true" style="position: absolute; inset: 0; background: radial-gradient(ellipse {{ $v(['40% 60% at 72% 50%', '60% 35% at 70% 52%']) }}, rgba(46,180,93,0.22), transparent 70%)"></div>

                    <div style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([150, 84])) }}; width: {{ ek_u($v([640, 342])) }}">
                        <div data-m="sv-head">
                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                            <h2 style="margin: {{ ek_u($v([10, 6])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([66, 36])) }}; line-height: 1.02; letter-spacing: -0.05em">{{ $t['heading'] }}<br><span style="color: var(--tk-green)">{{ $t['heading_2'] }}</span></h2>
                            <p style="margin: {{ ek_u($v([20, 10])) }} 0 0; {{ $mob ? '' : 'width: '.ek_u(560).';' }} font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: #BFD2C6">{{ $t['text'] }}</p>
                        </div>
                        @unless ($mob)
                            <ul style="margin: {{ ek_u(30) }} 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: {{ ek_u(4) }}">
                                @foreach ($phones as $phone)
                                    <li data-m="sv-number"><a href="{{ $phone['tel'] }}" style="display: flex; align-items: baseline; gap: {{ ek_u(18) }}; padding: {{ ek_u(10) }} 0; border-bottom: {{ ek_u(1) }} solid rgba(255,255,255,0.12); text-decoration: none; color: #FFFFFF"><span style="width: {{ ek_u(120) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(12) }}, 0.75rem); letter-spacing: 0.1em; text-transform: uppercase; color: #8A968F">{{ $phone['label'] }}</span><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u(28) }}; letter-spacing: -0.01em">{{ $phone['number'] }}</span></a></li>
                                @endforeach
                            </ul>
                            <div data-m="sv-button">{!! $button !!}</div>
                        @endunless
                    </div>

                    <div data-m="sv-phone" style="position: absolute; left: {{ ek_u($v([905, 112])) }}; top: {{ ek_u($v([120, 288])) }}">
                        <div data-m="sv-vibrate" role="img" aria-label="{{ $d['phone_description'] }}" style="position: relative; width: {{ ek_u($pw) }}; height: {{ ek_u($ph) }}; box-sizing: border-box; border-radius: {{ ek_u($pr) }}; border: {{ ek_u($pb) }} solid #26302B; background: #26302B; box-shadow: 0 {{ ek_u($v([60, 40])) }} {{ ek_u($v([100, 70])) }} {{ ek_u($v([-40, -30])) }} rgba(0,0,0,0.85)">
                            <div style="position: relative; width: {{ ek_u($pw - 2 * $pb) }}; height: {{ ek_u($ph - 2 * $pb) }}; border-radius: {{ ek_u($pr - 10 + ($mob ? 3 : 0)) }}; overflow: hidden; background: linear-gradient(180deg, #1E3A2A 0%, #0F1D16 60%, #0B130F 100%)">
                                <div data-m="sv-toast" style="position: absolute; left: 0; right: 0; top: {{ ek_u($v([30, 14])) }}; display: flex; justify-content: center">
                                    <span style="height: {{ ek_u($v([40, 26])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([16, 10])) }}; display: inline-flex; align-items: center; gap: {{ ek_u($v([8, 5])) }}; border-radius: 999px; background: #FFFFFF; color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u($v([13, 10])) }}; box-shadow: 0 {{ ek_u(10) }} {{ ek_u(24) }} {{ ek_u(-10) }} rgba(0,0,0,0.6)"><svg viewBox="0 0 24 24" fill="none" stroke="#1E8E47" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u($v([16, 11])) }}; height: {{ ek_u($v([16, 11])) }}"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>{{ $d['toast'] }}</span>
                                </div>
                                <span style="position: absolute; left: 0; right: 0; top: {{ ek_u($v([104, 54])) }}; text-align: center; font-size: {{ ek_u($v([15, 11])) }}; color: #BFD2C6">{{ $d['incoming'] }}</span>
                                <div style="position: absolute; left: {{ ek_u($v([84, 44])) }}; top: {{ ek_u($v([144, 76])) }}; width: {{ ek_u($v([120, 64])) }}; height: {{ ek_u($v([120, 64])) }}">
                                    @for ($i = 0; $i < 2; $i++)
                                        <span data-m="sv-ring" style="position: absolute; inset: 0; border-radius: 50%; background: var(--tk-green); opacity: 0"></span>
                                    @endfor
                                    <span data-m="sv-unknown" style="position: absolute; inset: 0; border-radius: 50%; background: var(--tk-muted); display: flex; align-items: center; justify-content: center; opacity: 0"><svg viewBox="0 0 24 24" fill="#C5CCC7" style="width: {{ ek_u($v([60, 32])) }}; height: {{ ek_u($v([60, 32])) }}"><circle cx="12" cy="8.5" r="4.2"></circle><path d="M4 21c0-4.4 3.6-7.5 8-7.5s8 3.1 8 7.5z"></path></svg></span>
                                    <span data-m="sv-saved" style="position: absolute; inset: 0; border-radius: 50%; background: #FFFFFF; display: flex; align-items: center; justify-content: center">@if ($mark)<img loading="lazy" decoding="async" src="{{ $mark->web_url }}" alt="" style="width: {{ ek_u($v([66, 36])) }}; height: auto">@endif</span>
                                </div>
                                <div style="position: absolute; left: 0; right: 0; top: {{ ek_u($v([292, 154])) }}; height: {{ ek_u($v([80, 44])) }}; text-align: center">
                                    <span data-m="sv-unknown" style="position: absolute; left: 0; right: 0; top: 0; display: flex; flex-direction: column; gap: {{ ek_u($v([4, 2])) }}; opacity: 0"><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ ek_u($v([26, 14])) }}; letter-spacing: -0.01em">{{ $shown }}</span><span style="font-size: {{ ek_u($v([15, 10.5])) }}; color: var(--tk-orange)">{{ $d['unknown'] }}</span></span>
                                    <span data-m="sv-saved" style="position: absolute; left: 0; right: 0; top: 0; display: flex; flex-direction: column; gap: {{ ek_u($v([4, 2])) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([32, 18])) }}; letter-spacing: -0.02em">{{ $d['caller'] }}</span><span style="font-size: {{ ek_u($v([15, 10.5])) }}; color: var(--tk-mint)">{{ $d['caller_line'] }}</span></span>
                                </div>
                                <div style="position: absolute; left: 0; right: 0; bottom: {{ ek_u($v([60, 26])) }}; display: flex; justify-content: space-around; padding: 0 {{ ek_u($v([30, 16])) }}">
                                    @foreach ([[$d['decline'], '#E5484D', true], [$d['accept'], 'var(--tk-green)', false]] as [$word, $bg, $down])
                                        <span style="display: flex; flex-direction: column; align-items: center; gap: {{ ek_u(8) }}; font-size: {{ ek_u(13) }}; color: #BFD2C6"><span @unless ($down) data-m="sv-accept" @endunless style="width: {{ ek_u($v([66, 38])) }}; height: {{ ek_u($v([66, 38])) }}; border-radius: 50%; background: {{ $bg }}; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="#FFFFFF" style="width: {{ ek_u($v([28, 16])) }}; height: {{ ek_u($v([28, 16])) }}; {{ $down ? 'transform: rotate(135deg)' : '' }}">{!! $handset !!}</svg></span>@unless ($mob){{ $word }}@endunless</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($mob)
                        <ul style="position: absolute; left: {{ ek_u(24) }}; top: {{ ek_u(620) }}; width: {{ ek_u(342) }}; margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column">
                            @foreach ($phones as $phone)
                                <li data-m="sv-number"><a href="{{ $phone['tel'] }}" style="display: flex; align-items: baseline; justify-content: space-between; padding: {{ ek_u(8) }} 0; border-bottom: {{ ek_u(1) }} solid rgba(255,255,255,0.12); text-decoration: none; color: #FFFFFF"><span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u(11) }}, 0.75rem); letter-spacing: 0.1em; text-transform: uppercase; color: #8A968F">{{ $phone['label'] }}</span><span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u(20) }}, 0.8125rem)">{{ $phone['number'] }}</span></a></li>
                            @endforeach
                        </ul>
                        <div data-m="sv-button">{!! $button !!}</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
