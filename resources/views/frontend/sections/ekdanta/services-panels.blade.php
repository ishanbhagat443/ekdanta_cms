{{--
    Ekdanta · Services Panels. One full-screen panel per service group,
    side by side; the steps' index (--ek-f) slides the row, drifts each
    outline word and fills the bar, all in CSS. Groups: Collections →
    Service groups; each lists its services (Collections → Services).
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $groups = \App\Models\CollectionItem::pick('service-groups', $d['groups'] ?? null);
    $services = \App\Models\CollectionItem::pick('services')->filter(fn ($s) => $s->get('in_grid'));
    $n = max(1, $groups->count());
@endphp
<div class="ek ek-services-panels" id="{{ $d['anchor'] ?: 'j-services' }}" style="--panels: {{ $n }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $w = $v([1440, 390]);
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: {{ $groups->first()?->get('colour') ?: 'var(--tk-green)' }}">
                <div class="ek-art">
                    <ol data-m="svc-panels" class="ek-sp-track" style="--w: {{ $w }}; position: absolute; top: 0; left: 0; width: {{ $u($w * $n) }}; height: {{ $u($v([900, 844])) }}; margin: 0; padding: 0; list-style: none; display: flex">
                        @foreach ($groups as $i => $group)
                            @php $items = $services->filter(fn ($s) => $s->get('group') === $group->slug); @endphp
                            <li style="position: relative; width: {{ $u($w) }}; height: {{ $u($v([900, 844])) }}; flex-shrink: 0; overflow: hidden; background: {{ $group->get('colour') }}; color: {{ $group->get('text_colour') }}">
                                <span style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([130, 100])) }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([14, 12])) }}, {{ $mob ? '0.75rem' : '0.8125rem' }}); letter-spacing: 0.12em; opacity: 0.8; white-space: pre">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}  /  {{ $t['label'] }}</span>
                                <span aria-hidden="true" class="ek-sp-word" style="--i: {{ $i }}; position: absolute; left: {{ $u($v([80, 18])) }}; top: {{ $u($v([170, 122])) }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([230, 96])) }}; line-height: 1; letter-spacing: -0.06em; color: transparent; -webkit-text-stroke: {{ $u($v([2, 1.5])) }} {{ $group->get('stroke') ?: 'currentColor' }}; white-space: nowrap">{{ $group->get('word') }}</span>
                                <h2 style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([430, 248])) }}; margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u($v([60, 40])) }}; letter-spacing: -0.045em">{{ $group->title }}</h2>
                                <ul style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([530, 316])) }}; width: {{ $u($v([700, 342])) }}; margin: 0; padding: 0; list-style: none; {{ $mob ? 'display: flex; flex-direction: column; gap: '.$u(10) : 'display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: '.$u(12).' '.$u(24) }}">
                                    @foreach ($items as $item)
                                        <li style="display: flex; align-items: center; gap: {{ $u($v([12, 10])) }}; padding-bottom: {{ $u($v([12, 10])) }}; border-bottom: {{ $u(1) }} solid {{ $group->get('rule') ?: 'currentColor' }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([20, 16.5])) }}, 0.8125rem)"><span aria-hidden="true" style="width: {{ $u($v([10, 8])) }}; height: {{ $u($v([10, 8])) }}; border-radius: 50%; background: {{ $group->get('accent') }}; flex-shrink: 0"></span>{{ $item->title }}</li>
                                    @endforeach
                                </ul>
                                <p style="position: absolute; left: {{ $u($v([900, 24])) }}; top: {{ $u($v([540, 610])) }}; width: {{ $u($v([440, 342])) }}; margin: 0; font-size: max({{ $u($v([22, 17])) }}, 0.8125rem); line-height: 1.55; opacity: 0.9">{{ $group->get('description') }}</p>
                            </li>
                        @endforeach
                    </ol>
                    <div aria-hidden="true" style="position: absolute; left: {{ $u($v([96, 24])) }}; right: {{ $u($v([96, 24])) }}; bottom: {{ $u($v([50, 40])) }}; height: {{ $u(4) }}; border-radius: {{ $u(2) }}; background: rgba(128,128,128,0.3); overflow: hidden"><div class="ek-sp-progress" style="height: 100%; border-radius: {{ $u(2) }}; background: var(--tk-orange); transform-origin: 0 50%"></div></div>
                </div>
            </div>
        </div>
    @endforeach
</div>
