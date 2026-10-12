{{-- Home Hero headline block: $t (texts), $chips, $ghost (the faded decorative copy), $mobile. --}}
<div @if ($ghost) aria-hidden="true" @endif style="position: absolute; left: {{ ek_u($mobile ? 24 : 96) }}; top: {{ ek_u($mobile ? 100 : 150) }}; width: {{ ek_u($mobile ? 342 : 900) }}; color: {{ $ghost ? 'rgba(255,255,255,0.13)' : 'var(--tk-ink)' }}">
    <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($mobile ? 21 : 44) }}, 0.8125rem); letter-spacing: -0.02em">{{ $t['intro'] }}</p>
    @if ($ghost)
        <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($mobile ? 78 : 176) }}; line-height: 1.2">{{ $t['heading'] }}</p>
    @else
        <h1 style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($mobile ? 78 : 176) }}; line-height: 1.2">{{ $t['heading'] }}</h1>
    @endif
    <p style="margin: {{ ek_u($mobile ? 10 : 18) }} 0 0; @unless ($mobile) width: {{ ek_u(640) }}; @endunless font-size: max({{ ek_u($mobile ? 17 : 23) }}, 0.8125rem); line-height: 1.5">{{ $t['text'] }}</p>
    @if ($chips !== [])
        <div style="margin-top: {{ ek_u($mobile ? 16 : 26) }}; display: flex; {{ $mobile ? 'flex-wrap: wrap;' : '' }} gap: {{ ek_u($mobile ? 8 : 10) }}">
            @foreach ($chips as $chip)
                <span style="padding: {{ ek_u($mobile ? 6 : 9) }} {{ ek_u($mobile ? 12 : 16) }}; border-radius: 999px; box-shadow: inset 0 0 0 {{ ek_u(1.5) }} {{ $ghost ? 'rgba(255,255,255,0.13)' : 'var(--tk-ink)' }}; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($mobile ? 13 : 15) }}, 0.8125rem)">{{ $chip }}</span>
            @endforeach
        </div>
    @endif
</div>
