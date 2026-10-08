@php
    $bannerUrl = $page->bannerMedia?->url;
    $title = $page->effectiveBannerTitle();
@endphp

{{-- Inner-page banner: clean solid header with a breadcrumb.
     Optional banner image sits under a flat navy scrim (no brand gradient). --}}
<section class="gsm-page-banner {{ $bannerUrl ? 'has-image' : '' }}"
         @if ($bannerUrl) style="background-image: url('{{ $bannerUrl }}');" @endif>
    <div class="container text-center">
        <h1 class="gsm-page-banner__title gsm-serif">{{ $title }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="{{ route('front.home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
    </div>
</section>
