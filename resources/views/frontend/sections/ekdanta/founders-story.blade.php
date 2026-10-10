{{--
    Ekdanta · Founders Story. The story is real text; the word-highlight
    track splits it into words and lights them as the visitor scrolls (the
    whole sentence stays readable to screen readers). Drawn fully lit.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $photos = array_values((array) ($d['photos'] ?? []));
    $stats = array_values(array_filter((array) ($d['stats'] ?? []), fn ($s) => ($s['value'] ?? '') !== ''));
@endphp
<div class="ek ek-founders-story" id="{{ $d['anchor'] ?: 'j-about' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <div style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([120, 84])) }}; width: {{ $u($v([740, 342])) }}">
                        <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([38, 21])) }}, 0.8125rem); letter-spacing: -0.02em">{{ $t['intro'] }}</p>
                        <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([104, 54])) }}; line-height: 1.2; color: var(--tk-green-text)">{{ $t['heading'] }}</h2>
                        @unless ($mob)
                            <p data-m="story" style="margin: {{ $u(20) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ $u(27) }}; line-height: 1.42; letter-spacing: -0.02em">{{ $t['story'] }}</p>
                            <p data-m="signature" style="margin: {{ $u(22) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u(18) }}, 0.8125rem); color: var(--tk-green-text-sm)">{{ $t['signature'] }}</p>
                        @endunless
                    </div>

                    <div style="position: absolute; left: {{ $u($v([900, 24])) }}; top: {{ $u($v([120, 196])) }}; width: {{ $u($v([444, 342])) }}; display: flex; gap: {{ $u($v([20, 12])) }}">
                        @foreach ($photos as $photo)
                            @php $media = media_ref($photo['image'] ?? null); @endphp
                            <div style="flex: 1; height: {{ $u($v([300, 150])) }}; border-radius: {{ $u($v([120, 82])) }} {{ $u($v([120, 82])) }} {{ $u($v([18, 14])) }} {{ $u($v([18, 14])) }}; overflow: hidden; background: var(--tk-line-2); display: flex; align-items: flex-end; justify-content: center; @unless ($media) {{ $mob ? 'padding: 0 '.$u(8).' '.$u(12).'; text-align: center;' : 'padding-bottom: '.$u(18).';' }} @endunless box-sizing: border-box; font-size: max({{ $u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); color: var(--tk-muted)">
                                @if ($media)
                                    <img src="{{ $media->url }}" alt="{{ trim(str_replace('Photo:', '', (string) ($photo['name'] ?? '')), '[] ') }}" style="width: 100%; height: 100%; object-fit: cover; display: block">
                                @else
                                    {{ $photo['name'] ?? '' }}
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($mob)
                        <p data-m="story" style="position: absolute; left: {{ $u(24) }}; top: {{ $u(366) }}; width: {{ $u(342) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u(17.5) }}, 0.8125rem); line-height: 1.42; letter-spacing: -0.02em">{{ $t['story'] }}</p>
                        <p data-m="signature" style="position: absolute; left: {{ $u(24) }}; top: {{ $u(608) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u(14) }}, 0.8125rem); color: var(--tk-green-text-sm)">{{ $t['signature'] }}</p>
                    @endif

                    <dl style="position: absolute; left: {{ $u($v([900, 24])) }}; top: {{ $u($v([470, 650])) }}; width: {{ $u($v([444, 342])) }}; margin: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: {{ $mob ? $u(16).' '.$u(18) : $u(28).' '.$u(24) }}">
                        @foreach ($stats as $stat)
                            <div style="display: flex; flex-direction: column; gap: {{ $u(2) }}">
                                <dt style="order: 2; font-weight: 400; font-size: max({{ $u($v([16, 13])) }}, 0.8125rem); {{ $mob ? 'line-height: 1.3;' : '' }} color: var(--tk-muted)">{{ $stat['label'] }}</dt>
                                <dd @if (! empty($stat['count'])) data-m="founder-count" @endif style="order: 1; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([54, 32])) }}; line-height: 1; letter-spacing: -0.04em">{{ $stat['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    @endforeach
</div>
