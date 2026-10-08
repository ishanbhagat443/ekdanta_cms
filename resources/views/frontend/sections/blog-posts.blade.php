@php
    $data = $section->dataFor('desktop');
    $bg = $mediaMap[$data['bg_media_id'] ?? 0] ?? null;
    $buttonLabel = $data['button_label'] ?? null;
    $buttonUrl = $data['button_url'] ?? null;

    // Latest live posts (bounded, eager-loaded — same in-blade pattern as
    // the category slider). Content is managed in Admin → Blog.
    $posts = \App\Models\Post::query()
        ->live()
        ->with(['featuredMedia', 'galleryMedia', 'author'])
        ->orderByDesc('published_at')
        ->limit(max(1, min(8, (int) ($data['limit'] ?? 5))))
        ->get();

    $large = $posts->take(2);
    $minis = $posts->slice(2);
@endphp

@if ($posts->isNotEmpty())
    <div class="gsm-blog"
         @if ($bg) style="background-image: url('{{ $bg->url }}'); background-size: cover;" @endif>
        <div class="container py-5">
            @include('frontend.partials.section-heading', ['section' => $section, 'ornament' => true])

            <div class="row g-4 mt-1" data-anim-children>
                @foreach ($large as $post)
                    <div class="col-lg-4">
                        <article class="gsm-blog-card">
                            @php $pair = collect([$post->featuredMedia, $post->galleryMedia->first()])->filter(); @endphp
                            @if ($pair->isNotEmpty())
                                <div class="gsm-blog-pair @if ($pair->count() === 1) gsm-blog-pair-single @endif">
                                    @foreach ($pair as $media)
                                        <img src="{{ $media->variantUrl('card') ?? $media->url }}"
                                             alt="{{ $media->alt ?: $post->title }}" loading="lazy" decoding="async">
                                    @endforeach
                                </div>
                            @endif
                            <div class="gsm-blog-card-body">
                                @include('frontend.sections.partials.blog-meta', ['post' => $post])
                                <h3 class="gsm-blog-title gsm-serif">
                                    <a href="{{ route('front.blog.show', $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="gsm-blog-excerpt gsm-text-paragraph">{{ \Illuminate\Support\Str::limit($post->autoExcerpt(), 110) }}</p>
                            </div>
                        </article>
                    </div>
                @endforeach

                @if ($minis->isNotEmpty())
                    <div class="col-lg-4 d-flex flex-column gap-4">
                        @foreach ($minis as $post)
                            <article class="gsm-blog-mini">
                                @if ($post->featuredMedia)
                                    <img src="{{ $post->featuredMedia->variantUrl('thumb') ?? $post->featuredMedia->url }}"
                                         alt="{{ $post->featuredMedia->alt ?: $post->title }}"
                                         class="gsm-blog-mini-thumb" loading="lazy" decoding="async">
                                @endif
                                <div class="gsm-blog-mini-body">
                                    @include('frontend.sections.partials.blog-meta', ['post' => $post])
                                    <h3 class="gsm-blog-title gsm-blog-title-sm gsm-serif">
                                        <a href="{{ route('front.blog.show', $post->slug) }}">{{ $post->title }}</a>
                                    </h3>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($buttonLabel && $buttonUrl)
                <div class="text-center mt-4">
                    <x-gsm-button :url="url($buttonUrl)" :label="$buttonLabel" :style="$data['button_style'] ?? null" />
                </div>
            @endif
        </div>
    </div>
@endif
