@extends('admin.layouts.app')

@section('page_title', $gradient->exists ? 'Edit Gradient' : 'New Gradient')

@section('page_content')
<form method="POST"
      action="{{ $gradient->exists ? route('admin.gradients.update', $gradient) : route('admin.gradients.store') }}"
      id="gsm-gradient-form">
    @csrf
    @if ($gradient->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-fill-drip mr-2"></i>Gradient</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $gradient->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Slug <small class="text-muted">(auto if empty)</small></label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $gradient->slug) }}" placeholder="sunset-gradient">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description', $gradient->description) }}">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Type</label>
                            <select name="type" id="gsm-grad-type" class="form-control">
                                @foreach (['linear' => 'Linear', 'radial' => 'Radial', 'conic' => 'Conic'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $gradient->type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Angle (deg)</label>
                            <input type="number" name="angle" id="gsm-grad-angle" class="form-control"
                                   min="0" max="360" value="{{ old('angle', $gradient->angle ?? 90) }}">
                            <small class="text-muted">Linear &amp; conic</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Position</label>
                            <input type="text" name="position" id="gsm-grad-position" class="form-control"
                                   value="{{ old('position', $gradient->position) }}" placeholder="center / 50% 0%">
                            <small class="text-muted">Radial &amp; conic center</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="gsm-grad-active" name="is_active" value="1"
                                   @checked(old('is_active', $gradient->is_active))>
                            <label class="custom-control-label" for="gsm-grad-active">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title"><i class="fas fa-grip-lines mr-2"></i>Color Stops <small class="text-muted ml-2">unlimited — drag to reorder</small></h3>
                    <button type="button" class="btn btn-sm btn-primary ml-auto" id="gsm-stop-add">
                        <i class="fas fa-plus mr-1"></i> Add Stop
                    </button>
                </div>
                <div class="card-body p-2">
                    @error('stops')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                    <div id="gsm-stop-rows">
                        @foreach (old('stops', $gradient->stops ?? []) as $i => $stop)
                            <div class="gsm-stop-row d-flex align-items-center p-2 border rounded mb-2 bg-light">
                                <i class="fas fa-grip-vertical text-muted mr-2 gsm-stop-handle" style="cursor:grab;"></i>
                                <input type="color" class="form-control mr-2" style="width:52px; padding:2px; height:34px;"
                                       name="stops[{{ $i }}][color]" value="{{ $stop['color'] ?? '#000000' }}">
                                <div class="input-group input-group-sm mr-2" style="width:130px;">
                                    <input type="number" class="form-control" name="stops[{{ $i }}][opacity]"
                                           min="0" max="100" step="1" value="{{ $stop['opacity'] ?? 100 }}">
                                    <div class="input-group-append"><span class="input-group-text">% op</span></div>
                                </div>
                                <div class="input-group input-group-sm mr-2" style="width:130px;">
                                    <input type="number" class="form-control" name="stops[{{ $i }}][position]"
                                           min="0" max="100" step="0.5" value="{{ $stop['position'] ?? 0 }}">
                                    <div class="input-group-append"><span class="input-group-text">% pos</span></div>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-secondary mr-1 gsm-stop-duplicate" title="Duplicate">
                                    <i class="fas fa-clone"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger gsm-stop-delete" title="Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> {{ $gradient->exists ? 'Save Gradient' : 'Create Gradient' }}
            </button>
            <a href="{{ route('admin.gradients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card" style="position: sticky; top: 60px;">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-eye mr-2"></i>Live Preview</h3></div>
                <div class="card-body">
                    <div id="gsm-grad-preview" style="height:180px; border-radius:10px; border:1px solid #e5e7eb;"></div>
                    <label class="mt-3 mb-1"><small>Generated CSS</small></label>
                    <pre id="gsm-grad-css" class="bg-dark text-light p-2 rounded small mb-0" style="white-space:pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    'use strict';

    var rows = document.getElementById('gsm-stop-rows');

    function rowTemplate(color, opacity, position) {
        var wrap = document.createElement('div');
        wrap.className = 'gsm-stop-row d-flex align-items-center p-2 border rounded mb-2 bg-light';
        wrap.innerHTML =
            '<i class="fas fa-grip-vertical text-muted mr-2 gsm-stop-handle" style="cursor:grab;"></i>' +
            '<input type="color" class="form-control mr-2" style="width:52px; padding:2px; height:34px;" value="' + color + '">' +
            '<div class="input-group input-group-sm mr-2" style="width:130px;">' +
              '<input type="number" class="form-control" min="0" max="100" step="1" value="' + opacity + '">' +
              '<div class="input-group-append"><span class="input-group-text">% op</span></div></div>' +
            '<div class="input-group input-group-sm mr-2" style="width:130px;">' +
              '<input type="number" class="form-control" min="0" max="100" step="0.5" value="' + position + '">' +
              '<div class="input-group-append"><span class="input-group-text">% pos</span></div></div>' +
            '<button type="button" class="btn btn-xs btn-outline-secondary mr-1 gsm-stop-duplicate" title="Duplicate"><i class="fas fa-clone"></i></button>' +
            '<button type="button" class="btn btn-xs btn-outline-danger gsm-stop-delete" title="Delete"><i class="fas fa-times"></i></button>';
        return wrap;
    }

    // Re-number input names in DOM order so submission order == visual order.
    function reindex() {
        rows.querySelectorAll('.gsm-stop-row').forEach(function (row, i) {
            var inputs = row.querySelectorAll('input');
            inputs[0].name = 'stops[' + i + '][color]';
            inputs[1].name = 'stops[' + i + '][opacity]';
            inputs[2].name = 'stops[' + i + '][position]';
        });
    }

    function hexToRgba(hex, opacity) {
        var h = hex.replace('#', '');
        if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
        var r = parseInt(h.substr(0, 2), 16), g = parseInt(h.substr(2, 2), 16), b = parseInt(h.substr(4, 2), 16);
        return opacity >= 100 ? hex : 'rgba(' + r + ',' + g + ',' + b + ',' + (opacity / 100) + ')';
    }

    function buildCss() {
        var stops = [];
        rows.querySelectorAll('.gsm-stop-row').forEach(function (row) {
            var inputs = row.querySelectorAll('input');
            stops.push({
                color: inputs[0].value,
                opacity: parseFloat(inputs[1].value || 100),
                position: parseFloat(inputs[2].value || 0)
            });
        });
        stops.sort(function (a, b) { return a.position - b.position; });
        var list = stops.map(function (s) { return hexToRgba(s.color, s.opacity) + ' ' + s.position + '%'; }).join(', ');
        if (!list) return 'none';

        var type = document.getElementById('gsm-grad-type').value;
        var angle = document.getElementById('gsm-grad-angle').value || 90;
        var position = document.getElementById('gsm-grad-position').value || 'center';

        if (type === 'radial') return 'radial-gradient(circle at ' + position + ', ' + list + ')';
        if (type === 'conic') return 'conic-gradient(from ' + angle + 'deg at ' + position + ', ' + list + ')';
        return 'linear-gradient(' + angle + 'deg, ' + list + ')';
    }

    function refresh() {
        reindex();
        var css = buildCss();
        document.getElementById('gsm-grad-preview').style.backgroundImage = css;
        document.getElementById('gsm-grad-css').textContent = css;
    }

    rows.addEventListener('click', function (e) {
        var dup = e.target.closest('.gsm-stop-duplicate');
        var del = e.target.closest('.gsm-stop-delete');
        if (dup) {
            var row = dup.closest('.gsm-stop-row');
            var inputs = row.querySelectorAll('input');
            row.after(rowTemplate(inputs[0].value, inputs[1].value, inputs[2].value));
            refresh();
        }
        if (del) {
            if (rows.querySelectorAll('.gsm-stop-row').length <= 2) {
                alert('A gradient needs at least two stops.');
                return;
            }
            del.closest('.gsm-stop-row').remove();
            refresh();
        }
    });

    document.getElementById('gsm-stop-add').addEventListener('click', function () {
        rows.appendChild(rowTemplate('#c29b54', 100, 100));
        refresh();
    });

    document.getElementById('gsm-gradient-form').addEventListener('input', refresh);
    document.getElementById('gsm-gradient-form').addEventListener('change', refresh);

    new Sortable(rows, { handle: '.gsm-stop-handle', animation: 150, onSort: refresh });

    refresh();
})();
</script>
@endpush
