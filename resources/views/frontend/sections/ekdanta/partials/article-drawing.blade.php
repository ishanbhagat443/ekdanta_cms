{{--
    The Article Hero's drawing: a brochure that tells, a phone reel that
    shows, and the enquiries it brings. Drawn at the desktop size; phones
    scale it ($k) and leave the messages out. Loops and load tracks run its
    parts (data-m); scroll moves its layers at different speeds.
--}}
@php
    $lines = fn ($v) => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $v))));
@endphp
<div data-m="ah-dots" aria-hidden="true" style="position: absolute; left: 0; right: 0; top: calc(-80 * var(--u)); bottom: calc(-80 * var(--u)); background-image: radial-gradient(rgba(143,227,174,0.2) calc(1.6 * var(--u)), transparent calc(1.6 * var(--u))); background-size: calc(30 * var(--u)) calc(30 * var(--u))"></div>
<div aria-hidden="true" style="position: absolute; inset: 0; background: radial-gradient(ellipse 50% 70% at 58% 45%, rgba(46,180,93,0.38), transparent 70%)"></div>
<div aria-hidden="true" style="position: absolute; left: calc(36 * var(--u)); bottom: calc(-34 * var(--u)); font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max(calc(150 * var(--u)), 0.8125rem); line-height: 1; letter-spacing: -0.06em; color: transparent; -webkit-text-stroke: calc(1.5 * var(--u)) rgba(143,227,174,0.22); white-space: nowrap">{{ $d['drawing_outline'] }}</div>

<div style="position: absolute; left: calc(200 * var(--u)); top: calc(70 * var(--u))" data-m="ah-bro-y">
<div data-m="ah-bro" role="img" aria-label="{{ $d['brochure_alt'] }}" style="position: relative; width: calc(320 * var(--u)); height: calc(410 * var(--u)); border-radius: calc(8 * var(--u)); background: #FFFFFF; box-shadow: 0 calc(40 * var(--u)) calc(70 * var(--u)) calc(-30 * var(--u)) rgba(0,0,0,0.6); transform: rotate(-8deg); overflow: hidden">
<div style="height: calc(128 * var(--u)); background: var(--tk-line-2); display: flex; align-items: flex-end; padding: 0 calc(24 * var(--u)) calc(18 * var(--u))">
<svg viewBox="0 0 120 64" aria-hidden="true" style="width: calc(120 * var(--u)); height: calc(64 * var(--u))"><path d="M0 64V30l22 12V30l22 12V30l22 12V8h14v-8h10v8h14v56z" fill="#B9C2BB"></path><rect x="96" y="38" width="10" height="10" fill="#DDE2DA"></rect></svg>
</div>
<div style="padding: calc(20 * var(--u)) calc(24 * var(--u)); display: flex; flex-direction: column; gap: calc(10 * var(--u))">
<span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: calc(11 * var(--u)); letter-spacing: 0.14em; color: #8A968F">{{ $d['brochure_kicker'] }}</span>
<span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max(calc(25 * var(--u)), 0.8125rem); line-height: 1.1; letter-spacing: -0.03em; color: var(--tk-muted)">{!! nl2br(e($d['brochure_title'])) !!}</span>
<span style="display: flex; flex-direction: column; gap: calc(6 * var(--u)); font-size: max(calc(14.5 * var(--u)), 0.8125rem); color: #8A968F">
@foreach ($lines($d['brochure_points']) as $point)<span>• {{ $point }}</span>@endforeach
</span>
</div>
<span style="position: absolute; left: calc(24 * var(--u)); bottom: calc(14 * var(--u)); font-size: max(calc(12 * var(--u)), 0.75rem); color: #A7B0AA">{{ $d['brochure_page'] }}</span>
<span data-m="ah-tells" style="position: absolute; right: calc(16 * var(--u)); top: calc(16 * var(--u)); padding: calc(7 * var(--u)) calc(14 * var(--u)); border-radius: calc(999 * var(--u)); background: var(--tk-ink); color: #FFFFFF; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max(calc(14 * var(--u)), 0.8125rem); letter-spacing: 0.08em">{{ $d['tells'] }}</span>
</div>
</div>

<svg aria-hidden="true" viewBox="0 0 150 120" style="width: calc(150 * var(--u)); height: calc(120 * var(--u)); position: absolute; left: calc(520 * var(--u)); top: calc(120 * var(--u))"><path data-m="ah-arrow" pathLength="1" stroke-dasharray="1" style="stroke-dashoffset: 0" d="M8 100 C 40 40, 90 20, 136 34" fill="none" stroke="#F2A230" stroke-width="5" stroke-linecap="round"></path><path data-m="ah-head-stk" d="M122 20 L140 35 L118 46" fill="none" stroke="#F2A230" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" style="transform-box: fill-box; transform-origin: center"></path></svg>

<div style="position: absolute; left: calc(660 * var(--u)); top: calc(26 * var(--u))" data-m="ah-ph-y">
<div data-m="ah-ph" role="img" aria-label="{{ $d['phone_alt'] }}" style="position: relative; width: calc(250 * var(--u)); height: calc(480 * var(--u)); box-sizing: border-box; border-radius: calc(40 * var(--u)); border: calc(9 * var(--u)) solid #121A15; background: #121A15; box-shadow: 0 calc(50 * var(--u)) calc(90 * var(--u)) calc(-30 * var(--u)) rgba(0,0,0,0.75); transform: rotate(5deg)">
<div style="position: relative; width: calc(232 * var(--u)); height: calc(462 * var(--u)); border-radius: calc(31 * var(--u)); overflow: hidden; background: linear-gradient(180deg, #23382C 0%, var(--tk-ink) 72%)">
<div style="position: absolute; left: calc(12 * var(--u)); right: calc(12 * var(--u)); top: calc(12 * var(--u)); display: flex; gap: calc(4 * var(--u))">
<span style="flex: 1; height: calc(3 * var(--u)); border-radius: calc(2 * var(--u)); background: rgba(255,255,255,0.28); overflow: hidden"><span data-m="ah-seg0" style="display: block; height: calc(3 * var(--u)); width: 100%; background: #FFFFFF"></span></span>
<span style="flex: 1; height: calc(3 * var(--u)); border-radius: calc(2 * var(--u)); background: rgba(255,255,255,0.28); overflow: hidden"><span data-m="ah-seg1" style="display: block; height: calc(3 * var(--u)); width: 0; background: #FFFFFF"></span></span>
<span style="flex: 1; height: calc(3 * var(--u)); border-radius: calc(2 * var(--u)); background: rgba(255,255,255,0.28); overflow: hidden"><span data-m="ah-seg2" style="display: block; height: calc(3 * var(--u)); width: 0; background: #FFFFFF"></span></span>
</div>
<div style="position: absolute; left: calc(12 * var(--u)); right: calc(12 * var(--u)); top: calc(26 * var(--u)); display: flex; align-items: center; gap: calc(8 * var(--u))">
<span style="width: calc(28 * var(--u)); height: calc(28 * var(--u)); border-radius: calc(999 * var(--u)); background: var(--tk-orange); display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" aria-hidden="true" style="width: calc(16 * var(--u)); height: calc(16 * var(--u))"><path d="M2 22V11l6 3.5V11l6 3.5V4h4v4h4v14z" fill="#0D1410"></path></svg></span>
<span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max(calc(13 * var(--u)), 0.8125rem); color: #FFFFFF">{{ $d['handle'] }}</span>
<span style="margin-left: calc(2 * var(--u)); padding: calc(3 * var(--u)) calc(9 * var(--u)); border-radius: calc(8 * var(--u)); box-shadow: inset 0 0 0 calc(1 * var(--u)) rgba(255,255,255,0.6); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: calc(11 * var(--u)); color: #FFFFFF">{{ $d['follow'] }}</span>
</div>
<svg aria-hidden="true" viewBox="0 0 232 240" style="width: calc(232 * var(--u)); height: calc(240 * var(--u)); position: absolute; left: 0; top: calc(66 * var(--u))">
<path data-m="ah-spin" style="transform-box: fill-box; transform-origin: center" d="M113.5 98.2 L123.8 99.7 L123.8 108.3 L113.5 109.8 L111.7 116.7 L119.8 123.1 L115.5 130.6 L105.9 126.8 L100.8 131.9 L104.6 141.5 L97.1 145.8 L90.7 137.7 L83.8 139.5 L82.3 149.8 L73.7 149.8 L72.2 139.5 L65.3 137.7 L58.9 145.8 L51.4 141.5 L55.2 131.9 L50.1 126.8 L40.5 130.6 L36.2 123.1 L44.3 116.7 L42.5 109.8 L32.2 108.3 L32.2 99.7 L42.5 98.2 L44.3 91.3 L36.2 84.9 L40.5 77.4 L50.1 81.2 L55.2 76.1 L51.4 66.5 L58.9 62.2 L65.3 70.3 L72.2 68.5 L73.7 58.2 L82.3 58.2 L83.8 68.5 L90.7 70.3 L97.1 62.2 L104.6 66.5 L100.8 76.1 L105.9 81.2 L115.5 77.4 L119.8 84.9 L111.7 91.3Z" fill="#8FE3AE"></path><circle cx="78" cy="104" r="13" fill="#1B2A22"></circle>
<path data-m="ah-spin-r" style="transform-box: fill-box; transform-origin: center" d="M166.5 59.5 L173.8 60.5 L173.8 67.5 L166.5 68.5 L164.6 73.7 L169.5 79.2 L165.0 84.5 L158.8 80.6 L154.0 83.4 L154.3 90.7 L147.4 92.0 L145.1 85.0 L139.7 84.0 L135.1 89.8 L129.1 86.3 L131.8 79.5 L128.3 75.3 L121.1 76.8 L118.7 70.2 L125.2 66.8 L125.2 61.2 L118.7 57.8 L121.1 51.2 L128.3 52.7 L131.8 48.5 L129.1 41.7 L135.1 38.2 L139.7 44.0 L145.1 43.0 L147.4 36.0 L154.3 37.3 L154.0 44.6 L158.8 47.4 L165.0 43.5 L169.5 48.8 L164.6 54.3Z" fill="#2EB45D"></path><circle cx="146" cy="64" r="8" fill="#1B2A22"></circle>
<line data-m="ah-scan" x1="160" y1="120" x2="160" y2="186" stroke="#8FE3AE" stroke-width="2" stroke-dasharray="4 4"></line>
<g data-m="ah-qc" style="transform-box: fill-box; transform-origin: center"><circle cx="160" cy="112" r="13" fill="#2EB45D"></circle><path d="M154 112l4 4 8-8" fill="none" stroke="#0D1410" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"></path></g>
<rect data-m="ah-box" x="-30" y="164" width="24" height="24" rx="4" fill="#F2A230"></rect>
<rect data-m="ah-box" x="-30" y="164" width="24" height="24" rx="4" fill="#F2A230"></rect>
<rect data-m="ah-box" x="-30" y="164" width="24" height="24" rx="4" fill="#F2A230"></rect>
<rect x="8" y="188" width="216" height="16" rx="8" fill="#33463B"></rect>
<line data-m="ah-belt" x1="16" y1="196" x2="216" y2="196" stroke="#5E7266" stroke-width="3" stroke-dasharray="6 8"></line>
<circle cx="16" cy="196" r="5" fill="#8A968F"></circle><circle cx="216" cy="196" r="5" fill="#8A968F"></circle>
</svg>
<div style="position: absolute; right: calc(10 * var(--u)); bottom: calc(60 * var(--u)); display: flex; flex-direction: column; align-items: center; gap: calc(12 * var(--u)); color: #FFFFFF; font-size: calc(11 * var(--u)); font-family: 'Poppins', sans-serif; font-weight: 600">
<span style="display: flex; flex-direction: column; align-items: center; gap: calc(2 * var(--u))"><svg viewBox="0 0 24 24" aria-hidden="true" style="width: calc(24 * var(--u)); height: calc(24 * var(--u))"><path data-m="ah-heart" style="transform-box: fill-box; transform-origin: center" d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" fill="#FF5A6E"></path></svg>{{ $d['likes'] }}</span>
<span style="display: flex; flex-direction: column; align-items: center; gap: calc(2 * var(--u))"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linejoin="round" aria-hidden="true" style="width: calc(22 * var(--u)); height: calc(22 * var(--u))"><path d="M20 12a8 8 0 0 1-11.6 7.1L4 20l1-4.2A8 8 0 1 1 20 12z"></path></svg>{{ $d['comments'] }}</span>
<svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linejoin="round" aria-hidden="true" style="width: calc(22 * var(--u)); height: calc(22 * var(--u))"><path d="M21 3L3 10l7 3 3 7z"></path></svg>
</div>
<div style="position: absolute; left: calc(12 * var(--u)); right: calc(50 * var(--u)); bottom: calc(22 * var(--u)); display: flex; flex-direction: column; gap: calc(6 * var(--u)); color: #FFFFFF">
<span style="font-size: max(calc(13 * var(--u)), 0.8125rem); line-height: 1.35"><b style="font-family: 'Poppins', sans-serif; font-weight: 600">{{ $d['handle'] }}</b> {{ $d['caption'] }}</span>
<span style="display: flex; align-items: center; gap: calc(6 * var(--u)); font-size: calc(11.5 * var(--u)); color: #BFD2C6"><svg viewBox="0 0 24 24" fill="#BFD2C6" aria-hidden="true" style="width: calc(12 * var(--u)); height: calc(12 * var(--u))"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>{{ $d['audio'] }}</span>
</div>
</div>
<span data-m="ah-shows" style="position: absolute; left: calc(-50 * var(--u)); top: calc(300 * var(--u)); padding: calc(7 * var(--u)) calc(14 * var(--u)); border-radius: calc(999 * var(--u)); background: var(--tk-green); color: var(--tk-ink); font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max(calc(14 * var(--u)), 0.8125rem); letter-spacing: 0.08em">{{ $d['shows'] }}</span>
</div>
</div>

@unless ($mob ?? false)
<div style="position: absolute; left: calc(950 * var(--u)); top: calc(110 * var(--u)); width: calc(262 * var(--u)); display: flex; flex-direction: column; gap: calc(14 * var(--u))" data-m="ah-bub-y">
@foreach (array_slice((array) ($d['bubbles'] ?? []), 0, 3) as $i => $bubble)<div data-m="ah-bub" style="padding: calc(12 * var(--u)) calc(14 * var(--u)) calc(13 * var(--u)); border-radius: calc(18 * var(--u)) calc(18 * var(--u)) calc(18 * var(--u)) calc(4 * var(--u)); background: #FFFFFF; box-shadow: 0 calc(20 * var(--u)) calc(40 * var(--u)) calc(-20 * var(--u)) rgba(0,0,0,0.6); display: flex; flex-direction: column; gap: calc(6 * var(--u))">
<span style="display: flex; align-items: center; gap: calc(8 * var(--u)); font-size: max(calc(12 * var(--u)), 0.75rem); color: var(--tk-muted)"><span style="width: calc(22 * var(--u)); height: calc(22 * var(--u)); border-radius: calc(999 * var(--u)); background: {{ ['var(--tk-orange)', 'var(--tk-mint)', 'var(--tk-line-2)'][$i % 3] }}; display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: calc(11 * var(--u)); color: var(--tk-ink)">{{ $bubble['initial'] ?? '' }}</span>{{ $d['bubble_label'] }}<span style="margin-left: auto">{{ $d['bubble_time'] }}</span></span>
<span style="font-size: max(calc(15 * var(--u)), 0.8125rem); line-height: 1.35; color: var(--tk-ink)">{{ $bubble['text'] ?? '' }}</span>
</div>@endforeach
</div>
@endunless
