@php /** @var \App\Models\Post|null $post */ $post = $post ?? null; @endphp

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Content</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" required value="{{ old('title', $post?->title) }}"
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Slug <small class="text-muted">(blank = auto)</small></label>
                    <input type="text" name="slug" value="{{ old('slug', $post?->slug) }}"
                           class="form-control @error('slug') is-invalid @enderror">
                    @error('slug') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Excerpt <small class="text-muted">(blank = auto from body)</small></label>
                    <textarea name="excerpt" rows="2" class="form-control">{{ old('excerpt', $post?->excerpt) }}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label>Body</label>
                    <textarea name="body" rows="10" class="form-control gsm-summernote">{{ old('body', $post?->body) }}</textarea>
                    {{-- The editor's "Insert block" menu: the Ekdanta article blocks, styled and animated by the article template. --}}
                    <script type="application/json" id="gsm-article-blocks">@json(\App\Support\ArticleBody::blocks())</script>
                </div>
            </div>
        </div>

        @include('admin.partials.seo-fields', ['seo' => $post?->seoMeta])
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Publish</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label><small>Status</small></label>
                    <select name="status" id="post-status" class="form-control form-control-sm">
                        @foreach (['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled'] as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(old('status', $post?->status ?? 'draft') === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label><small>Publish Date/Time <span class="text-muted">(required for Scheduled)</span></small></label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}"
                           class="form-control form-control-sm @error('published_at') is-invalid @enderror">
                    @error('published_at') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="custom-control custom-switch mb-2">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" class="custom-control-input" id="post-is-featured"
                           name="is_featured" value="1" @checked((bool) old('is_featured', $post?->is_featured))>
                    <label class="custom-control-label" for="post-is-featured">
                        Featured <small class="text-muted">(pins to top of blog grid; shows the Featured badge)</small>
                    </label>
                </div>
                <hr>
                <div class="form-group">
                    <label><small>Author (account)</small></label>
                    <select name="user_id" class="form-control form-control-sm">
                        <option value="">— None —</option>
                        @foreach ($allAuthors as $author)
                            <option value="{{ $author->id }}" @selected((int) old('user_id', $post?->user_id) === $author->id)>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label><small>Display Author Name <span class="text-muted">(shown on site, e.g. the brand name)</span></small></label>
                    <input type="text" name="author_name" value="{{ old('author_name', $post?->author_name) }}"
                           class="form-control form-control-sm">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Media</h3></div>
            <div class="card-body">
                <x-admin.media-picker name="featured_media_id" label="Featured Image" :media="$post?->featuredMedia" />
                <x-admin.media-picker name="gallery_ids" label="Gallery (ordered)" :multiple="true" :media-list="$post?->galleryMedia" />
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Organize</h3></div>
            <div class="card-body">
                <label><small>Categories</small></label>
                <div class="border rounded p-2 mb-3" style="max-height: 140px; overflow-y: auto;">
                    @forelse ($allCategories as $category)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="bcat_{{ $category->id }}"
                                   name="blog_category_ids[]" value="{{ $category->id }}"
                                   @checked(collect(old('blog_category_ids', $post?->categories->pluck('id') ?? []))->contains($category->id))>
                            <label class="custom-control-label" for="bcat_{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                    @empty
                        <span class="text-muted small">No categories yet — <a href="{{ route('admin.blog-categories.index') }}">add some</a>.</span>
                    @endforelse
                </div>

                <div class="form-group">
                    <label><small>Tags <span class="text-muted">(comma separated — created automatically)</span></small></label>
                    <input type="text" name="tags_csv" placeholder="georgette, wholesale, festive"
                           value="{{ old('tags_csv', $post?->tags->pluck('name')->implode(', ')) }}"
                           class="form-control form-control-sm">
                </div>

                <div class="form-group mb-0">
                    <label><small>Related Posts <span class="text-muted">(blank = auto same category)</span></small></label>
                    <select name="related_ids[]" multiple size="6" class="form-control form-control-sm">
                        @php $selectedRelated = collect(old('related_ids', $post?->relatedPosts->pluck('id') ?? [])); @endphp
                        @foreach ($relatedOptions as $option)
                            <option value="{{ $option->id }}" @selected($selectedRelated->contains($option->id))>{{ $option->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-3">
            <i class="fas fa-save mr-1"></i> {{ $post ? 'Save Post' : 'Create Post' }}
        </button>
    </div>
</div>
