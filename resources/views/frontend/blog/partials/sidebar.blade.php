{{-- Blog sidebar (design B2): search, recent, popular, archives, categories,
     tags, newsletter. $sidebarSettings (Blog Listing / Blog Detail CMS —
     each page has its own copy) gates each widget; every default below
     matches today's exact output (Tags/Newsletter default OFF since neither
     exists in the current design — enabling them is an explicit admin
     opt-in, not a change to what ships today). --}}
@php
    $sidebarSettings = $sidebarSettings ?? [];
    $showSearch = $sidebarSettings['search'] ?? true;
    $showCategories = $sidebarSettings['categories'] ?? true;
    $showRecent = $sidebarSettings['recent'] ?? true;
    $showPopular = $sidebarSettings['popular'] ?? true;
    $showTags = $sidebarSettings['tags'] ?? false;
    $showNewsletter = $sidebarSettings['newsletter'] ?? false;
@endphp
<aside class="border rounded p-3" style="border-color: #c29b54 !important;">
    {{-- Search --}}
    @if ($showSearch)
        <h5 class="gsm-serif">Search</h5>
        <form method="GET" action="{{ route('front.blog.index') }}" class="mb-4">
            <div class="input-group input-group-sm">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       class="form-control" placeholder="Search posts">
                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
            </div>
        </form>
    @endif

    {{-- Recent --}}
    @if ($showRecent && $sidebar['recent']->isNotEmpty())
        <h5 class="gsm-serif">Recent Posts</h5>
        <div class="mb-4">
            @foreach ($sidebar['recent'] as $recent)
                @include('frontend.blog.partials.mini-card', ['item' => $recent])
            @endforeach
        </div>
    @endif

    {{-- Popular --}}
    @if ($showPopular && $sidebar['popular']->where('views_count', '>', 0)->isNotEmpty())
        <h5 class="gsm-serif">Popular Posts</h5>
        <div class="mb-4">
            @foreach ($sidebar['popular'] as $popular)
                @include('frontend.blog.partials.mini-card', ['item' => $popular])
            @endforeach
        </div>
    @endif

    {{-- Archives (not independently toggled — travels with the page today) --}}
    @if ($sidebar['archives']->isNotEmpty())
        <h5 class="gsm-serif">Archives</h5>
        <div class="d-flex flex-wrap gap-1 mb-4">
            @foreach ($sidebar['archives'] as $month => $archive)
                <a href="{{ route('front.blog.index', ['archive' => $month]) }}"
                   class="badge rounded-pill text-decoration-none px-2 py-1"
                   style="background: #f5f0e6; color: #c29b54;">{{ $archive['label'] }}</a>
            @endforeach
        </div>
    @endif

    {{-- Categories --}}
    @if ($showCategories && $sidebar['categories']->isNotEmpty())
        <h5 class="gsm-serif">Categories</h5>
        <div class="d-flex flex-wrap gap-1 mb-4">
            @foreach ($sidebar['categories'] as $category)
                <a href="{{ route('front.blog.index', ['category' => $category->slug]) }}"
                   class="badge rounded-pill text-decoration-none px-2 py-1"
                   style="background: #f5f0e6; color: #c29b54;">
                    {{ $category->name }} ({{ $category->posts_count }})
                </a>
            @endforeach
        </div>
    @endif

    {{-- Tags (new widget, opt-in — same badge-pill style already used above) --}}
    @if ($showTags && ($sidebar['tags'] ?? collect())->isNotEmpty())
        <h5 class="gsm-serif">Tags</h5>
        <div class="d-flex flex-wrap gap-1 mb-4">
            @foreach ($sidebar['tags'] as $tag)
                <a href="{{ route('front.blog.index', ['tag' => $tag->slug]) }}"
                   class="badge rounded-pill text-decoration-none px-2 py-1"
                   style="background: #f5f0e6; color: #c29b54;">#{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif

    {{-- Newsletter (new widget, opt-in — reuses the exact existing Newsletter
         module: same route, same Subscriber model, same success/error flow
         as the footer newsletter form; no duplicate implementation). --}}
    @if ($showNewsletter)
        <h5 class="gsm-serif">Newsletter</h5>
        <div class="mb-2">
            @if (session('newsletter_success'))
                <div class="alert alert-success py-2 small">Thank you for subscribing!</div>
            @endif
            @error('email')
                <div class="alert alert-danger py-2 small">{{ $message }}</div>
            @enderror
            <form action="{{ route('front.newsletter') }}" method="POST">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off"
                       style="position:absolute; left:-9999px;" aria-hidden="true">
                <div class="input-group input-group-sm">
                    <input type="email" name="email" required class="form-control" placeholder="Your email">
                    <button type="submit" class="btn gsm-btn-gradient text-white"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    @endif
</aside>
