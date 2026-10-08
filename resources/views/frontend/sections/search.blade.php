@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $display = $data['display'] ?? 'icon';
    $action = $data['action_url'] ?? '/blogs';
    $placeholder = $data['placeholder'] ?? 'Search…';
    $searchId = 'gsm-search-'.$section->id;
@endphp

<div class="order-3 ms-lg-2 {{ $classes }}">
    @if ($display === 'input')
        <form action="{{ $action }}" method="GET" class="d-flex">
            <div class="input-group input-group-sm">
                <input type="search" name="search" class="form-control" placeholder="{{ $placeholder }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
    @else
        <button type="button" class="btn btn-link text-dark px-2" data-bs-toggle="collapse"
                data-bs-target="#{{ $searchId }}" aria-label="Search">
            <i class="fas fa-search"></i>
        </button>
        <div class="collapse position-absolute start-0 end-0 bg-white shadow p-3" id="{{ $searchId }}" style="top: 100%; z-index: 1030;">
            <form action="{{ $action }}" method="GET" class="container">
                <div class="input-group">
                    <input type="search" name="search" class="form-control" placeholder="{{ $placeholder }}" autofocus>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    @endif
</div>
