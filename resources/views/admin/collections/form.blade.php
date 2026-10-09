@extends('admin.layouts.app')

@section('page_title', $item->exists ? $item->title : 'Add '.strtolower($type->singular()))

@section('page_actions')
    <a href="{{ route('admin.collections.index', $type->key()) }}" class="btn btn-outline-secondary btn-sm">Back to {{ strtolower($type->label()) }}</a>
    @if ($item->exists && ($url = $item->url()))
        <a href="{{ $url }}?preview=1" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">View page</a>
    @endif
@stop

@section('page_content')
<form method="POST" action="{{ $item->exists ? route('admin.collections.update', [$type->key(), $item]) : route('admin.collections.store', $type->key()) }}">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-body">
                    @foreach ($type->fields() as $field)
                        @include('admin.builder.partials.field', [
                            'field' => array_merge($field, ['device' => false]),
                            'value' => old('data.'.$field['key'], $item->data[$field['key']] ?? ($field['default'] ?? null)),
                            'device' => 'desktop',
                            'sectionId' => 'c'.($item->id ?? 'new'),
                            'menus' => collect(),
                        ])
                        @error('data.'.$field['key'])<div class="text-danger small mt-n2 mb-2" role="alert">{{ $message }}</div>@enderror
                    @endforeach
                </div>
            </div>

            @if ($type->template() !== null)
                @include('admin.partials.seo-fields', ['seo' => $item->seoMeta])
            @endif
        </div>

        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="ci-title">{{ $type->titleLabel() }} <span class="text-danger" aria-hidden="true">*</span></label>
                        <input type="text" name="title" id="ci-title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $item->title) }}" required maxlength="200">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="ci-slug">Slug <small class="text-muted">(made from the {{ strtolower($type->titleLabel()) }} if empty)</small></label>
                        <input type="text" name="slug" id="ci-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $item->slug) }}" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group col-7">
                            <label for="ci-status">Status</label>
                            <select name="status" id="ci-status" class="form-control">
                                <option value="published" @selected(old('status', $item->status) === 'published')>Published</option>
                                <option value="draft" @selected(old('status', $item->status) === 'draft')>Draft (hidden)</option>
                            </select>
                        </div>
                        <div class="form-group col-5">
                            <label for="ci-order">Order</label>
                            <input type="number" name="sort_order" id="ci-order" class="form-control" min="0" value="{{ old('sort_order', $item->sort_order) }}">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">{{ $item->exists ? 'Save' : 'Add '.strtolower($type->singular()) }}</button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@push('scripts')
    <script src="{{ asset('js/admin/media-picker-field.js') }}?v={{ gsm_version() }}"></script>
    <script src="{{ asset('js/admin/repeater.js') }}?v={{ gsm_version() }}"></script>
@endpush
