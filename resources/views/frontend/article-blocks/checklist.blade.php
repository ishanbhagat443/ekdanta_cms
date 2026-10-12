<ul class="ek-b-check" aria-label="Checklist before you post">
@foreach (['Shoot vertical, on a phone. A recent phone is enough.', 'Use daylight, or switch on every light on the floor.', 'Keep each reel under 30 seconds.', 'Add captions. Most people watch with the sound off.', 'Put your phone number or WhatsApp link in the caption.'] as $item)
<li><span class="ek-b-check__tick" data-m="ab-ck" aria-hidden="true"><span></span><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5" pathLength="1"></path></svg></span><span>{{ $item }}</span></li>
@endforeach
</ul>
