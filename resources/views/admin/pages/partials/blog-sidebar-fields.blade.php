{{-- Shared "Sidebar Widgets" toggle set — same shape on Blog Listing & Blog
     Detail (BlogController::sidebarDefaults()). Expects $blogSidebar. --}}
@foreach ([
    'search' => 'Search',
    'categories' => 'Categories',
    'recent' => 'Recent Posts',
    'popular' => 'Popular Posts',
    'tags' => 'Tags',
    'newsletter' => 'Newsletter',
] as $key => $label)
    <div class="custom-control custom-switch mb-1">
        <input type="hidden" name="blog_sidebar[{{ $key }}]" value="0">
        <input type="checkbox" class="custom-control-input" id="bs_{{ $key }}_{{ $page->id }}"
               name="blog_sidebar[{{ $key }}]" value="1" @checked((bool) ($blogSidebar[$key] ?? false))>
        <label class="custom-control-label" for="bs_{{ $key }}_{{ $page->id }}">{{ $label }}</label>
    </div>
@endforeach
