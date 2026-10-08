{{-- Reusable SEO fieldset. $seo = SeoMeta|null. Field names match HasSeoMeta::seoRules(). --}}
<div class="card card-outline card-secondary collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-search mr-1"></i> SEO</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label><small>Meta Title</small></label>
                    <input type="text" name="seo_meta_title" class="form-control form-control-sm @error('seo_meta_title') is-invalid @enderror"
                           value="{{ old('seo_meta_title', $seo?->meta_title) }}">
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label><small>Meta Keywords <span class="text-muted">(comma separated)</span></small></label>
                    <input type="text" name="seo_meta_keywords" class="form-control form-control-sm"
                           value="{{ old('seo_meta_keywords', $seo?->meta_keywords) }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><small>Meta Description</small></label>
            <textarea name="seo_meta_description" rows="2" class="form-control form-control-sm">{{ old('seo_meta_description', $seo?->meta_description) }}</textarea>
        </div>
        <div class="form-row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label><small>Canonical URL</small></label>
                    <input type="url" name="seo_canonical_url" class="form-control form-control-sm @error('seo_canonical_url') is-invalid @enderror"
                           value="{{ old('seo_canonical_url', $seo?->canonical_url) }}">
                    @error('seo_canonical_url') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label><small>Robots</small></label>
                    <select name="seo_robots" class="form-control form-control-sm">
                        @foreach (['' => 'Default (index, follow)', 'noindex' => 'noindex', 'nofollow' => 'nofollow', 'noindex,nofollow' => 'noindex, nofollow'] as $robotsValue => $robotsLabel)
                            <option value="{{ $robotsValue }}" @selected(old('seo_robots', $seo?->robots ?? '') === $robotsValue)>{{ $robotsLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label><small>Open Graph Title</small></label>
                    <input type="text" name="seo_og_title" class="form-control form-control-sm"
                           value="{{ old('seo_og_title', $seo?->og_title) }}">
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label><small>OG Image (media ID)</small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="seo_og_media_id" id="seo_og_media" class="form-control"
                               value="{{ old('seo_og_media_id', $seo?->og_media_id) }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                                    data-target="seo_og_media"><i class="fas fa-photo-video"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><small>OG Description</small></label>
            <textarea name="seo_og_description" rows="2" class="form-control form-control-sm">{{ old('seo_og_description', $seo?->og_description) }}</textarea>
        </div>
        <div class="form-group">
            <label><small>Schema JSON-LD <span class="text-muted">(valid JSON)</span></small></label>
            <textarea name="seo_schema_json" rows="3" spellcheck="false"
                      class="form-control form-control-sm text-monospace @error('seo_schema_json') is-invalid @enderror">{{ old('seo_schema_json', $seo?->schema_json) }}</textarea>
            @error('seo_schema_json') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <hr>
        <p class="text-muted small mb-2"><i class="fab fa-twitter mr-1"></i> Twitter Card <span class="text-muted">(falls back to Open Graph when empty)</span></p>
        <div class="form-row">
            <div class="col-12 col-lg-4">
                <div class="form-group">
                    <label><small>Card Type</small></label>
                    <select name="seo_twitter_card" class="form-control form-control-sm">
                        <option value="" @selected(old('seo_twitter_card', $seo?->twitter_card ?? '') === '')>Auto</option>
                        <option value="summary" @selected(old('seo_twitter_card', $seo?->twitter_card) === 'summary')>summary</option>
                        <option value="summary_large_image" @selected(old('seo_twitter_card', $seo?->twitter_card) === 'summary_large_image')>summary_large_image</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="form-group">
                    <label><small>Twitter Title</small></label>
                    <input type="text" name="seo_twitter_title" class="form-control form-control-sm"
                           value="{{ old('seo_twitter_title', $seo?->twitter_title) }}">
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="form-group">
                    <label><small>Twitter Image (media ID)</small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="seo_twitter_media_id" id="seo_twitter_media" class="form-control"
                               value="{{ old('seo_twitter_media_id', $seo?->twitter_media_id) }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary gsm-bf-media-pick"
                                    data-target="seo_twitter_media"><i class="fas fa-photo-video"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><small>Twitter Description</small></label>
            <textarea name="seo_twitter_description" rows="2" class="form-control form-control-sm">{{ old('seo_twitter_description', $seo?->twitter_description) }}</textarea>
        </div>

        <hr>
        <div class="form-row align-items-end">
            <div class="col-12 col-lg-3">
                <div class="form-group">
                    <label><small>Breadcrumb Title <span class="text-muted">(override)</span></small></label>
                    <input type="text" name="seo_breadcrumb_title" class="form-control form-control-sm"
                           value="{{ old('seo_breadcrumb_title', $seo?->breadcrumb_title) }}">
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="form-group">
                    <label><small>Sitemap Priority</small></label>
                    <select name="seo_sitemap_priority" class="form-control form-control-sm">
                        <option value="">Default</option>
                        @foreach (['1.0', '0.9', '0.8', '0.7', '0.6', '0.5', '0.4', '0.3', '0.2', '0.1'] as $priority)
                            <option value="{{ $priority }}" @selected(old('seo_sitemap_priority', $seo?->sitemap_priority) === $priority)>{{ $priority }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="form-group">
                    <label><small>Sitemap Change Freq.</small></label>
                    <select name="seo_sitemap_changefreq" class="form-control form-control-sm">
                        <option value="">Default</option>
                        @foreach (['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'] as $freq)
                            <option value="{{ $freq }}" @selected(old('seo_sitemap_changefreq', $seo?->sitemap_changefreq) === $freq)>{{ $freq }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="seo_sitemap_include" value="0">
                        <input type="checkbox" class="custom-control-input" id="seo_sitemap_include" name="seo_sitemap_include" value="1"
                               @checked(old('seo_sitemap_include', $seo?->sitemap_include ?? true))>
                        <label class="custom-control-label" for="seo_sitemap_include"><small>Include in XML Sitemap</small></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
