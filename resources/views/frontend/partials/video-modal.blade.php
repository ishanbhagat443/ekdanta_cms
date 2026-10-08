{{-- Reusable lightbox video modal: normalizes YouTube/Vimeo URLs to
     embeddable form, falls back to a raw <video> tag for direct MP4 links.
     Instagram Reels/posts can't be third-party-iframed (Instagram blocks
     it) — those render via Instagram's own official oEmbed widget script
     instead, showing their native player linking back to the real post.
     The iframe/video src loads only while the modal is open (bandwidth-safe).
     Usage: @include('frontend.partials.video-modal', ['videoUrl' => $url, 'modalId' => $id, 'autoplay' => true]) --}}
@php
    $embed = null;
    $instagramUrl = null;
    if (!empty($videoUrl)) {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([\w-]+)~', $videoUrl, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('~vimeo\.com/(\d+)~', $videoUrl, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        } elseif (preg_match('~instagram\.com/(?:reel|reels|p)/[\w-]+~', $videoUrl, $m)) {
            $instagramUrl = $m[0];
        }
    }
    $autoplay = $autoplay ?? true;
@endphp

@if (!empty($videoUrl))
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1">
        <div class="modal-dialog {{ $instagramUrl ? 'modal-sm' : 'modal-xl' }} modal-dialog-centered">
            <div class="modal-content bg-dark border-0">
                @if ($instagramUrl)
                    {{-- Instagram's official embed markup — instagram.com/embed.js
                         renders the real post/reel with its native player. --}}
                    <div class="modal-body p-2 gsm-instagram-embed-wrap">
                        <blockquote class="instagram-media" data-instgrm-permalink="https://{{ $instagramUrl }}"
                                    data-instgrm-version="14" style="width:100%; margin:0;"></blockquote>
                    </div>
                @else
                    <div class="modal-body p-0" style="aspect-ratio: 16/9;">
                        @if ($embed)
                            <iframe data-src="{{ $embed }}{{ $autoplay ? '?autoplay=1' : '' }}" src="about:blank"
                                    class="w-100 h-100 gsm-video-frame" frameborder="0" allow="autoplay; fullscreen"></iframe>
                        @else
                            <video controls class="w-100 h-100 gsm-video-frame" data-src="{{ $videoUrl }}"></video>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @push('scripts')
        @if ($instagramUrl)
            <script async src="//www.instagram.com/embed.js"></script>
            <script>
                // Instagram's widget only scans the DOM once on script load —
                // re-process the embed each time this modal opens, since it
                // may not exist in the DOM yet on first page load.
                (function () {
                    var modal = document.getElementById('{{ $modalId }}');
                    modal.addEventListener('shown.bs.modal', function () {
                        if (window.instgrm && window.instgrm.Embeds) {
                            window.instgrm.Embeds.process();
                        }
                    });
                })();
            </script>
        @else
            <script>
                // Load/unload the video only while the modal is open.
                (function () {
                    var modal = document.getElementById('{{ $modalId }}');
                    modal.addEventListener('shown.bs.modal', function () {
                        var frame = modal.querySelector('.gsm-video-frame');
                        frame.src = frame.dataset.src;
                    });
                    modal.addEventListener('hidden.bs.modal', function () {
                        var frame = modal.querySelector('.gsm-video-frame');
                        frame.src = 'about:blank';
                    });
                })();
            </script>
        @endif
    @endpush
@endif
