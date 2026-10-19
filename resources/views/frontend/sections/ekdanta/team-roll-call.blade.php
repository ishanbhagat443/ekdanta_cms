{{--
    Ekdanta · Team Roll Call. Scroll tracks slide the roles in from
    alternate sides; a roll-call track moves --roll down the list and the
    role it reaches lights up (_team-roll-call.scss). Phones (no design):
    each row stacks the role over its description.
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $roles = \App\Models\CollectionItem::pick('team-roles', $d['roles'] ?? null)->take(7)->values();
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
@endphp
<div class="ek ek-team-roll-call" id="{{ $d['anchor'] ?: 'a-team' }}">
    @foreach ($section->comps($d, $m) as $comp => $t)
        @php
            $mob = $comp !== 'desktop';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-green-deep); color: #FFFFFF">
                <div class="ek-art">
                    <div data-m="tm-head" style="position: absolute; left: {{ ek_x($v([96, 24])) }}; top: {{ ek_u($v([112, 84])) }}; {{ $mob ? 'width: '.ek_x(342) : '' }}">
                        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, {{ $v(['0.8125rem', '0.75rem']) }}); letter-spacing: 0.12em; color: var(--tk-mint)">{{ $t['eyebrow'] }}</span>
                        <h2 style="margin: {{ ek_u($v([8, 4])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u($v([60, 36])) }}, 0.8125rem); line-height: 1.02; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                    </div>
                    <p data-m="tm-text" style="position: absolute; {{ $mob ? 'left: '.ek_x(24).'; top: '.ek_u(198) : 'right: '.ek_x(96).'; top: '.ek_u(140) }}; width: {{ ek_x($v([420, 342])) }}; margin: 0; font-size: max({{ ek_u($v([19, 15])) }}, 0.8125rem); line-height: 1.5; color: var(--tk-mint-2)">{{ $t['text'] }}</p>

                    <ol data-m="tm-bands" style="list-style: none; margin: 0; padding: 0">
                        @foreach ($roles as $i => $role)
                            <li class="ek-team-band" data-m="tm-band-{{ $i % 2 ? 'odd' : 'even' }}" style="--i: {{ $i }}; position: absolute; left: {{ ek_x($v([96, 24])) }}; top: {{ ek_u($v([270 + $i * 78, 276 + $i * 72])) }}; width: {{ ek_x($v([1248, 342])) }}; height: {{ ek_u($v([64, 62])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([12, 8])) }} 0 {{ ek_u($v([28, 18])) }}; border-radius: 999px; display: flex; align-items: center; gap: {{ ek_u($v([18, 12])) }}">
                                <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.06em; opacity: 0.85">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                @if ($mob)
                                    <span style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: {{ ek_u(1) }}">
                                        <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u(17) }}, 0.8125rem); line-height: 1.15; letter-spacing: -0.03em">{{ $role->title }}</span>
                                        <span style="font-size: max({{ ek_u(12.5) }}, 0.8125rem); line-height: 1.2; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $role->get('description') }}</span>
                                    </span>
                                @else
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ ek_u(30) }}, 0.8125rem); letter-spacing: -0.035em">{{ $role->title }}</span>
                                    <span style="margin-left: auto; font-size: max({{ ek_u(17) }}, 0.8125rem); opacity: 0.85">{{ $role->get('description') }}</span>
                                @endif
                                <span aria-hidden="true" class="ek-team-arrow" style="width: {{ ek_u($v([44, 36])) }}; height: {{ ek_u($v([44, 36])) }}; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width: {{ ek_u($v([18, 16])) }}; height: {{ ek_u($v([18, 16])) }}">{!! $arrow !!}</svg></span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    @endforeach
</div>
