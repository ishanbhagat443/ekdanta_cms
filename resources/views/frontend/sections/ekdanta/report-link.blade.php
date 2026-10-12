{{--
    Ekdanta · Report Broken Link. Scroll tracks bring the chain in, close
    its gap and cross-fade every "broken" layer (data-m="nf-bad") into its
    "working" one (nf-ok); loops jitter the halves and spark the break.
    Drawn fixed. The WhatsApp message can carry the address that failed.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $href = link_href($d['button_link'] ?? '');
    if (($d['add_address'] ?? true) && str_starts_with($href, 'https://wa.me/')) {
        $href .= (str_contains($href, '?text=') ? rawurlencode(' ') : '?text=').rawurlencode(url()->current());
    }
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
    $ring = fn (string $stroke, int $w, int $h) => '<svg viewBox="0 0 200 104" aria-hidden="true" style="width: '.ek_u($w).'; height: '.ek_u($h).'"><rect x="11" y="11" width="178" height="82" rx="41" fill="none" stroke="'.$stroke.'" stroke-width="20"></rect></svg>';
@endphp
<div class="ek ek-report-link" id="{{ $d['anchor'] ?: 'n-report' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            [$rw, $rh] = $v([[200, 104], [140, 73]]);
            $button = '<a class="ek-press" href="'.e($href).'" target="_blank" rel="noopener" style="'.($mob ? 'position: absolute; left: '.ek_u(24).'; right: '.ek_u(24).'; top: '.ek_u(760).'; height: '.ek_u(54).'; display: flex; justify-content: center;' : 'margin-top: '.ek_u(30).'; height: '.ek_u(56).'; padding: 0 '.ek_u(24).'; display: inline-flex;').' box-sizing: border-box; align-items: center; gap: '.ek_u(10).'; background: var(--tk-green); color: var(--tk-ink); border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: max('.ek_u($v([16, 15])).', 0.8125rem); text-decoration: none"'.($mob ? ' data-m="nf-report-button"' : '').'><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: '.ek_u($v([18, 17])).'; height: '.ek_u($v([18, 17])).'">'.$chat.'</svg>'.e($t['button_label']).'</a>';
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-ink); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="nf-report-head" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([230, 84])) }}; width: {{ ek_u($v([720, 342])) }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([10, 6])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([68, 38])) }}, 0.8125rem); line-height: 1.02; letter-spacing: -0.05em">{{ $t['heading'] }}<br><span style="color: var(--tk-green)">{{ $t['heading_2'] }}</span></h2>
                        <p style="margin: {{ ek_u($v([20, 10])) }} 0 0; {{ $mob ? '' : 'width: '.ek_u(520).';' }} font-size: max({{ ek_u($v([20, 15])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: #BFD2C6">{{ $t['text'] }}</p>
                        @unless ($mob){!! $button !!}@endunless
                    </div>

                    <div role="img" aria-label="{{ $d['picture_description'] }}" data-m="nf-chain" style="position: absolute; left: {{ ek_u($v([790, 0])) }}; top: {{ ek_u($v([170, 330])) }}; width: {{ ek_u($v([560, 390])) }}; height: {{ ek_u($v([560, 400])) }}">
                        <div style="position: absolute; left: {{ ek_u($v([50, 24])) }}; top: 0; width: {{ ek_u($v([460, 342])) }}; height: {{ ek_u($v([60, 50])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([10, 6])) }} 0 {{ ek_u($v([20, 16])) }}; border-radius: 999px; background: rgba(255,255,255,0.08); box-shadow: inset 0 0 0 {{ ek_u(1) }} rgba(255,255,255,0.14); display: flex; align-items: center; gap: {{ ek_u($v([12, 10])) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#8A968F" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" style="width: {{ ek_u($v([16, 14])) }}; height: {{ ek_u($v([16, 14])) }}; flex: none"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path></svg>
                            <span aria-hidden="true" style="position: relative; flex: 1; height: {{ ek_u($v([24, 22])) }}; font-size: max({{ ek_u($v([17, 15])) }}, 0.8125rem); color: var(--tk-line-2); white-space: nowrap">
                                <span data-m="nf-bad" style="position: absolute; left: 0; top: 0; opacity: 0">{{ $d['address_start'] }}<span style="color: #FFFFFF">{{ $d['address_broken'] }}</span></span>
                                <span data-m="nf-ok" style="position: absolute; left: 0; top: 0">{{ $d['address_start'] }}<span style="color: #FFFFFF">{{ $d['address_fixed'] }}</span></span>
                            </span>
                            <span aria-hidden="true" style="position: relative; flex: none; width: {{ ek_u($v([116, 98])) }}; height: {{ ek_u($v([38, 36])) }}; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([13, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }})">
                                <span data-m="nf-bad" style="position: absolute; inset: 0; border-radius: 999px; background: rgba(229,72,77,0.18); color: #FF8A8E; display: flex; align-items: center; justify-content: center; opacity: 0">{{ $d['broken_label'] }}</span>
                                <span data-m="nf-ok" style="position: absolute; inset: 0; border-radius: 999px; background: var(--tk-green); color: var(--tk-ink); display: flex; align-items: center; justify-content: center">{{ $d['fixed_label'] }}</span>
                            </span>
                        </div>

                        <div aria-hidden="true" style="position: absolute; left: {{ ek_u($v([280, 195])) }}; top: {{ ek_u($v([320, 210])) }}; width: 0; height: 0; transform: rotate(-35deg)">
                            @foreach ([['nf-gap-l', $v([-180, -126]), '#56605A', '#2EB45D'], ['nf-gap-r', $v([-20, -14]), '#8A968F', '#8FE3AE']] as [$part, $x, $broken, $fixed])
                                <span data-m="{{ $part }}" style="position: absolute; left: {{ ek_u($x) }}; top: {{ ek_u($v([-52, -36])) }}"><span data-m="nf-jit" style="position: relative; display: block; width: {{ ek_u($rw) }}; height: {{ ek_u($rh) }}">
                                    <span data-m="nf-bad" style="position: absolute; inset: 0; opacity: 0">{!! $ring($broken, $rw, $rh) !!}</span><span data-m="nf-ok" style="position: absolute; inset: 0">{!! $ring($fixed, $rw, $rh) !!}</span>
                                </span></span>
                            @endforeach
                        </div>
                        <svg aria-hidden="true" data-m="nf-spark-out" viewBox="0 0 140 140" style="width: {{ ek_u($v([140, 100])) }}; height: {{ ek_u($v([140, 100])) }}; position: absolute; left: {{ ek_u($v([210, 145])) }}; top: {{ ek_u($v([250, 160])) }}; opacity: 0">
                            <g data-m="nf-spark" style="transform-box: fill-box; transform-origin: center"><path d="M70 22v20 M70 98v20 M22 70h20 M98 70h20 M36 36l14 14 M90 90l14 14 M104 36L90 50 M50 90L36 104" stroke="#F2A230" stroke-width="5" stroke-linecap="round"></path></g>
                        </svg>
                        <span aria-hidden="true" data-m="nf-check" style="position: absolute; left: {{ ek_u($v([240, 168])) }}; top: {{ ek_u($v([410, 272])) }}; width: {{ ek_u($v([80, 56])) }}; height: {{ ek_u($v([80, 56])) }}; border-radius: 999px; background: var(--tk-green); display: flex; align-items: center; justify-content: center; box-shadow: 0 {{ ek_u($v([16, 14])) }} {{ ek_u($v([34, 28])) }} {{ ek_u($v([-14, -12])) }} rgba(46,180,93,0.8)"><svg viewBox="0 0 24 24" fill="none" stroke="#0D1410" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u($v([40, 28])) }}; height: {{ ek_u($v([40, 28])) }}"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg></span>
                    </div>

                    @if ($mob){!! $button !!}@endif
                </div>
            </div>
        </div>
    @endforeach
</div>
