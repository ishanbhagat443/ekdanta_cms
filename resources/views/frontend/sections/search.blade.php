@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $display = $data['display'] ?? 'icon';
    $action = $data['action_url'] ?? route('front.blog.index');
    $placeholder = $data['placeholder'] ?? 'Search…';
    $searchId = 'gsm-search-'.$section->id;
@endphp

{{-- Both modes use the live suggestions in resources/js/frontend/app.js
     ([data-live-search] inside [data-live-search-wrap]). --}}
@if ($display === 'input')
    {{-- Pill field on tablet and desktop; an icon that opens a search sheet on phones. --}}
    <div class="gsm-site-header__search d-none d-md-block {{ $classes }}" data-live-search-wrap>
        <form action="{{ $action }}" method="GET" role="search">
            <i class="fas fa-magnifying-glass gsm-search-icon" aria-hidden="true"></i>
            <input type="search" name="search" class="gsm-search-input" placeholder="{{ $placeholder }}"
                   value="{{ request('search') }}" autocomplete="off" data-live-search aria-label="Search">
        </form>
        <div class="gsm-search-results" data-live-search-results hidden></div>
    </div>

    <button type="button" class="gsm-header-icon d-md-none {{ $classes }}" data-bs-toggle="offcanvas"
            data-bs-target="#{{ $searchId }}" aria-label="Search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
    </button>

    @push('after-header')
        <div class="offcanvas offcanvas-top gsm-search-sheet d-md-none" tabindex="-1" id="{{ $searchId }}" style="height: auto;"
             aria-label="Search">
            <div class="offcanvas-header pb-1">
                <h5 class="offcanvas-title">Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-1" data-live-search-wrap>
                <form action="{{ $action }}" method="GET" role="search">
                    <input type="search" name="search" class="gsm-search-input" placeholder="{{ $placeholder }}"
                           autocomplete="off" data-live-search aria-label="Search">
                </form>
                <div class="gsm-search-results mt-2" data-live-search-results hidden></div>
            </div>
        </div>
    @endpush
@else
    <div class="position-relative {{ $classes }}">
        <button type="button" class="gsm-header-icon" data-bs-toggle="collapse"
                data-bs-target="#{{ $searchId }}" aria-expanded="false" aria-controls="{{ $searchId }}" aria-label="Search">
            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        </button>
        <div class="collapse position-absolute end-0 bg-white shadow p-3 gsm-search-drop" id="{{ $searchId }}" data-live-search-wrap>
            <form action="{{ $action }}" method="GET" role="search">
                <input type="search" name="search" class="gsm-search-input" placeholder="{{ $placeholder }}"
                       autocomplete="off" data-live-search aria-label="Search">
            </form>
            <div class="gsm-search-results mt-2" data-live-search-results hidden></div>
        </div>
    </div>
@endif
