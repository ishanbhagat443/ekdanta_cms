{{--
    Ekdanta · Big Brand Finale: the closing line, the contact card and the
    footer line with the visitor's "Pause animations" button. The band and
    footer colours are the approved contrast fixes (--tk-finale, --tk-finale-foot).
    Phones, email and address: Settings → Business.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $groups = collect(business_phones())->groupBy('label');
    $email = (string) setting('business.email', '');
    $address = business_offices()[0]['address'] ?? '';
    $buttons = array_values(array_filter((array) ($d['buttons'] ?? []), fn ($b) => ($b['label'] ?? '') !== ''));
    $year = fn ($text) => str_replace('{year}', now()->format('Y'), (string) $text);
@endphp
<div class="ek ek-finale" id="{{ $d['anchor'] ?: 'contact' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $label = 'font-family: \'Poppins\', sans-serif; font-weight: 600; font-size: '.($mob ? 'max('.ek_u(11).', 0.75rem)' : 'max('.ek_u(12).', 0.75rem)').'; letter-spacing: 0.1em; text-transform: uppercase; color: var(--tk-muted)';
            $value = 'font-family: \'Poppins\', sans-serif; font-weight: 700; font-size: max('.ek_u($v([22, 18])).', 0.8125rem); text-decoration: none';
            $group = 'display: flex; flex-direction: column; gap: '.ek_u($v([4, 2]));
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-finale)">
                <div class="ek-art">
                    <div style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([150, 88])) }}; width: {{ ek_u($v([760, 342])) }}">
                        <p data-m="end-1" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([34, 20])) }}, 0.8125rem); letter-spacing: -0.02em">{{ $t['line_1'] }}</p>
                        <p data-m="end-2" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([120, 64])) }}; line-height: {{ $v([0.95, 0.98]) }}; letter-spacing: -0.055em">{{ $t['line_2'] }}</p>
                        <p data-m="end-big" style="margin: {{ $mob ? ek_u(4).' 0 0' : 0 }}; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([200, 88])) }}; line-height: 0.9; letter-spacing: -0.06em; color: #FFFFFF; transform-origin: left center; transform: rotate(-3deg)">{!! nl2br(e($t['big'])) !!}</p>
                    </div>

                    <address data-m="end-card" style="position: absolute; {{ $mob ? 'left: '.ek_u(16).'; right: '.ek_u(16).'; top: '.ek_u(376).'; padding: '.ek_u(18).' '.ek_u(20).' '.ek_u(20) : 'left: '.ek_u(880).'; top: '.ek_u(130).'; width: '.ek_u(464).'; padding: '.ek_u(32) }}; margin: 0; font-style: normal; box-sizing: border-box; border-radius: {{ ek_u($v([28, 24])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u($v([40, 30])) }} {{ ek_u($v([80, 60])) }} {{ ek_u($v([-40, -30])) }} rgba(13,20,16,0.5); display: flex; flex-direction: column; gap: {{ ek_u($v([18, 11])) }}">
                        <img src="{{ media_ref($t['logo'] ?? null)?->url }}" alt="{{ $d['logo_alt'] }}" style="height: {{ ek_u($v([40, 30])) }}; width: auto; align-self: flex-start">
                        {{-- Mobile: the first group on its own, the rest and the email side by side. --}}
                        @foreach ($groups as $groupLabel => $numbers)
                            @if ($mob && $loop->index === 1) <div style="display: flex; flex-wrap: wrap; gap: 0 {{ ek_u(20) }}"> @endif
                            <div style="{{ $group }}">
                                <span style="{{ $label }}">{{ $groupLabel }}</span>
                                @if ($mob) <span style="display: flex; flex-wrap: wrap; gap: 0 {{ ek_u(14) }}"> @endif
                                    @foreach ($numbers as $phone)
                                        <a href="{{ $phone['tel'] }}" style="{{ $value }}">{{ $phone['number'] }}</a>
                                    @endforeach
                                @if ($mob) </span> @endif
                            </div>
                        @endforeach
                        @if ($email !== '')
                            <div style="{{ $group }}"><span style="{{ $label }}">{{ $t['email_label'] }}</span><a href="mailto:{{ $email }}" style="{{ $value }}; font-size: max({{ ek_u($v([20, 18])) }}, 0.8125rem)">{{ $email }}</a></div>
                        @endif
                        @if ($mob && $groups->count() > 1) </div> @endif
                        @if ($address !== '')
                            <div style="{{ $group }}"><span style="{{ $label }}">{{ $t['address_label'] }}</span><span style="font-size: max({{ ek_u($v([17, 14])) }}, 0.8125rem); line-height: {{ $v([1.5, 1.42]) }}">{{ $address }}</span></div>
                        @endif
                        <div style="display: flex; flex-wrap: wrap; gap: {{ ek_u($v([10, 6])) }}">
                            @foreach ($buttons as $i => $button)
                                @php $external = \App\Support\Link::external($button['link'] ?? ''); @endphp
                                <a href="{{ link_href($button['link'] ?? '') }}" @if ($external) target="_blank" rel="noopener" @endif style="height: {{ ek_u($v([48, 40])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([18, 12])) }}; display: inline-flex; align-items: center; border-radius: 999px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([15, 12.5])) }}, {{ $mob ? '0.78125rem' : '0.8125rem' }}); text-decoration: none; white-space: nowrap; {{ $i === 0 ? 'background: var(--tk-ink); color: #FFFFFF' : 'box-shadow: inset 0 0 0 '.ek_u(1.5).' var(--tk-ink)' }}">{{ $button['label'] }}</a>
                            @endforeach
                        </div>
                    </address>

                    <footer data-m="end-foot" style="position: absolute; left: {{ ek_u($v([96, 24])) }}; right: {{ ek_u($v([96, 24])) }}; bottom: {{ ek_u($v([34, 16])) }}; padding-top: {{ ek_u($v([16, 10])) }}; border-top: {{ ek_u(1) }} solid rgba(13,20,16,0.2); display: flex; {{ $mob ? 'flex-direction: column; gap: '.ek_u(2) : 'justify-content: space-between; align-items: center; gap: '.ek_u(16) }}; font-size: max({{ ek_u($v([15, 12])) }}, {{ $mob ? '0.75rem' : '0.8125rem' }}); color: var(--tk-finale-foot)">
                        <span>{{ $year($t['footer_left']) }}</span>
                        {{-- The button keeps its 44px target; the negative margin keeps the line its drawn height. --}}
                        <span style="display: flex; {{ $mob ? 'flex-direction: column; align-items: flex-start; gap: '.ek_u(2) : 'align-items: center; gap: '.ek_u(16) }}">
                            <span>{{ $t['footer_right'] }}</span>
                            @if ($t['pause_label'])
                                <button type="button" class="ek-pause-toggle" data-ek-pause aria-pressed="false" style="margin: -11px -0.25rem">{{ $t['pause_label'] }}</button>
                            @endif
                        </span>
                    </footer>
                </div>
            </div>
        </div>
    @endforeach
</div>
