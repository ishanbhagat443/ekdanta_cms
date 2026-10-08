{{--
    Reusable media picker.

    Usage:
      <x-admin.media-picker name="banner_media_id" :media="$page->banner" label="Banner Image" />
      <x-admin.media-picker name="gallery_ids" multiple label="Gallery Images" />

    Stores the selected media ID (or comma-separated IDs when multiple)
    in a hidden input. Opens the Media Library in picker mode inside a
    modal iframe; selection arrives via postMessage.
--}}
@props([
    'name',
    'label' => 'Image',
    'media' => null,        // App\Models\Media|null — single current value
    'mediaList' => null,    // iterable<Media>|null — current values when multiple
    'multiple' => false,
    'required' => false,
])

@php
    $pickerId = 'mp_'.str_replace(['[', ']', '.'], '_', $name).'_'.uniqid();
    $items = collect($multiple ? ($mediaList ?? []) : ($media ? [$media] : []));
    $currentId = old($name, $items->pluck('id')->implode(','));
@endphp

<div class="form-group gsm-media-picker" id="{{ $pickerId }}"
     data-multiple="{{ $multiple ? '1' : '0' }}"
     data-picker-src="{{ route('admin.media.index', ['picker' => 1, 'multiple' => $multiple ? 1 : 0]) }}">

    <label>{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>

    <input type="hidden" name="{{ $name }}" value="{{ $currentId }}" class="gsm-mp-value">

    <div class="d-flex align-items-start" style="gap:.75rem;">
        <div class="gsm-mp-preview border rounded bg-light d-flex align-items-center justify-content-center flex-wrap"
             style="min-width:120px; min-height:80px; max-width:100%; padding:4px; gap:4px;">
            @forelse ($items as $item)
                @if ($item->thumb_url)
                    <img src="{{ $item->thumb_url }}" alt="" style="max-height:72px; border-radius:4px;">
                @else
                    <i class="fas fa-file fa-2x text-secondary m-2" title="{{ $item->filename }}"></i>
                @endif
            @empty
                <i class="fas fa-image fa-2x text-muted"></i>
            @endforelse
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary btn-sm gsm-mp-open">
                <i class="fas fa-photo-video mr-1"></i> Choose from Library
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm gsm-mp-clear {{ $currentId ? '' : 'd-none' }}">
                <i class="fas fa-times mr-1"></i> Clear
            </button>
        </div>
    </div>

    @error($name) <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/admin/media-picker.js') }}?v={{ gsm_version() }}"></script>
    @endpush
@endonce
