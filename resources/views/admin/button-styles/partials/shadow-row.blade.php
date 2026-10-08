{{-- One shadow row (base or hover list). $name = input prefix, $row = values. --}}
<div class="gsm-shadow-row form-row align-items-end mb-2">
    <div class="form-group col mb-0">
        <small class="text-muted">X</small>
        <input type="number" step="0.5" class="form-control form-control-sm" name="{{ $name }}[x]" value="{{ $row['x'] ?? 0 }}">
    </div>
    <div class="form-group col mb-0">
        <small class="text-muted">Y</small>
        <input type="number" step="0.5" class="form-control form-control-sm" name="{{ $name }}[y]" value="{{ $row['y'] ?? 4 }}">
    </div>
    <div class="form-group col mb-0">
        <small class="text-muted">Blur</small>
        <input type="number" step="0.5" class="form-control form-control-sm" name="{{ $name }}[blur]" value="{{ $row['blur'] ?? 12 }}">
    </div>
    <div class="form-group col mb-0">
        <small class="text-muted">Spread</small>
        <input type="number" step="0.5" class="form-control form-control-sm" name="{{ $name }}[spread]" value="{{ $row['spread'] ?? 0 }}">
    </div>
    <div class="form-group col mb-0">
        <small class="text-muted">Color</small>
        <input type="color" class="form-control form-control-sm" style="padding:2px; height:31px;" name="{{ $name }}[color]" value="{{ $row['color'] ?? '#000000' }}">
    </div>
    <div class="form-group col mb-0">
        <small class="text-muted">Opacity %</small>
        <input type="number" min="0" max="100" class="form-control form-control-sm" name="{{ $name }}[opacity]" value="{{ $row['opacity'] ?? 25 }}">
    </div>
    <div class="form-group col-auto mb-0">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="{{ $name }}[inset]" value="0">
            <input type="checkbox" class="custom-control-input" id="inset-{{ md5($name) }}" name="{{ $name }}[inset]" value="1"
                   @checked(! empty($row['inset']))>
            <label class="custom-control-label" for="inset-{{ md5($name) }}"><small>Inset</small></label>
        </div>
    </div>
    <div class="form-group col-auto mb-0">
        <button type="button" class="btn btn-xs btn-outline-danger gsm-shadow-delete"><i class="fas fa-times"></i></button>
    </div>
</div>
