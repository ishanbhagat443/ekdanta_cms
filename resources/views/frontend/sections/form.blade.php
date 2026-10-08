@php
    $data = $section->dataFor('desktop');
    $form = ($formId = $data['form_id'] ?? null)
        ? \App\Models\Form::query()->where('is_active', true)->with('fields')->find($formId)
        : null;

    // Google Map: a section-level embed code wins, else the Business Info map.
    // The admin may paste a full <iframe …> or a bare embed URL — extract the
    // src either way, then rebuild the iframe with the CMS height / radius /
    // lazy settings (never hardcoded, never trusts pasted markup verbatim).
    $mapEmbed = $data['map_embed'] ?? null;
    if (empty($mapEmbed) && ($data['show_map'] ?? false)) {
        $mapEmbed = setting('business.google_map');
    }
    $mapSrc = null;
    if (! empty($mapEmbed)) {
        if (preg_match('/src\s*=\s*"([^"]+)"/i', $mapEmbed, $m)) {
            $mapSrc = $m[1];
        } elseif (\Illuminate\Support\Str::startsWith(trim($mapEmbed), ['http://', 'https://'])) {
            $mapSrc = trim($mapEmbed);
        }
    }
    $mapHeight = (int) ($data['map_height'] ?? 450);
    $mapRadius = (int) ($data['map_radius'] ?? 12);
    $mapLazy = (bool) ($data['map_lazy'] ?? true);
@endphp

@if ($form)
    <div class="container py-5">
        @include('frontend.partials.section-heading', ['section' => $section])

        <div class="row g-4 align-items-stretch gsm-contact-form-row">
            <div class="{{ $mapSrc ? 'col-12 col-lg-6' : 'col-12 mx-auto' }}" @unless($mapSrc) style="max-width: 720px;" @endunless>
                @include('frontend.partials.dynamic-form', ['form' => $form])
            </div>
            @if ($mapSrc)
                <div class="col-12 col-lg-6">
                    <iframe src="{{ $mapSrc }}" width="100%" height="{{ $mapHeight }}"
                            style="border:0; border-radius: {{ $mapRadius }}px; min-height: {{ $mapHeight }}px; height: 100%;"
                            @if ($mapLazy) loading="lazy" @endif
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen
                            title="Location map"></iframe>
                </div>
            @endif
        </div>
    </div>
@endif
