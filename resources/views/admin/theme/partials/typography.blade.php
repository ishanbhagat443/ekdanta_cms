{{--
    Typography tab: one accordion card per role (config('theme.typography.roles')).
    Google Fonts + locally uploaded fonts share one <select>; live preview
    text renders in the chosen family/weight/transform via inline style + JS.
--}}
<form method="POST" action="{{ route('admin.theme.typography.update') }}" id="gsm-typography-form">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-upload mr-1"></i> Local Font Upload</h3></div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Name</th><th>Weight</th><th>Style</th><th>Formats</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($fonts as $font)
                            <tr>
                                <td style="font-family:'{{ $font->name }}';">{{ $font->name }}</td>
                                <td>{{ $font->font_weight }}</td>
                                <td>{{ $font->font_style }}</td>
                                <td>
                                    @if ($font->woff2_path)<span class="badge badge-light">woff2</span>@endif
                                    @if ($font->woff_path)<span class="badge badge-light">woff</span>@endif
                                    @if ($font->ttf_path)<span class="badge badge-light">ttf</span>@endif
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.theme.fonts.destroy', $font) }}" class="d-inline js-confirm-delete">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-2">No local fonts uploaded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <form method="POST" action="{{ route('admin.theme.fonts.store') }}" enctype="multipart/form-data" class="form-row align-items-end">
                @csrf
                <div class="col-12 col-md-3 mb-2">
                    <label class="small mb-0">Font Name</label>
                    <input type="text" name="name" required class="form-control form-control-sm" placeholder="e.g. Runiga">
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <label class="small mb-0">Weight</label>
                    <select name="font_weight" class="form-control form-control-sm">
                        @foreach (config('theme.typography.weights') as $w => $label)
                            <option value="{{ $w }}" @selected($w === '400')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <label class="small mb-0">Style</label>
                    <select name="font_style" class="form-control form-control-sm">
                        <option value="normal">Normal</option>
                        <option value="italic">Italic</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <label class="small mb-0">.woff2</label>
                    <input type="file" name="woff2" accept=".woff2" class="form-control-file form-control-sm">
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <label class="small mb-0">.woff / .ttf</label>
                    <input type="file" name="woff" accept=".woff" class="form-control-file form-control-sm mb-1">
                    <input type="file" name="ttf" accept=".ttf" class="form-control-file form-control-sm">
                </div>
                <div class="col-12 col-md-1 mb-2">
                    <button class="btn btn-primary btn-sm btn-block"><i class="fas fa-upload"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div id="gsm-typography-accordion">
        @foreach ($schema['roles'] as $role => $roleSchema)
            @php $roleValues = $values[$role] ?? []; @endphp
            <div class="card mb-2">
                <div class="card-header py-2" id="heading-{{ $role }}" data-toggle="collapse" data-target="#collapse-{{ $role }}"
                     style="cursor:pointer;" role="button">
                    <h3 class="card-title mb-0" style="font-size: 1rem;">
                        <i class="fas fa-angle-right mr-2"></i>{{ $roleSchema['label'] }}
                        <span class="text-muted small ml-2 gsm-typo-preview-label" data-role="{{ $role }}"></span>
                    </h3>
                </div>
                <div class="collapse @if($loop->first) show @endif" id="collapse-{{ $role }}">
                    <div class="card-body">
                        <p class="border rounded p-3 mb-3 gsm-typo-preview" data-role="{{ $role }}" style="background:#fafafa;">
                            The quick brown fox jumps over the lazy dog — {{ $roleSchema['label'] }}
                        </p>

                        <div class="form-row">
                            <div class="col-12 col-md-6 mb-2">
                                <label class="small mb-0">Font Family</label>
                                <select name="roles[{{ $role }}][family]" class="form-control form-control-sm gsm-typo-family" data-role="{{ $role }}">
                                    <optgroup label="Google Fonts">
                                        @foreach ($schema['google_fonts'] as $googleFont)
                                            <option value="{{ $googleFont }}" @selected(($roleValues['family'] ?? $roleSchema['default_family']) === $googleFont)>{{ $googleFont }}</option>
                                        @endforeach
                                    </optgroup>
                                    @if ($fonts->isNotEmpty())
                                        <optgroup label="Uploaded Local Fonts">
                                            @foreach ($fonts as $font)
                                                <option value="local:{{ $font->slug }}" data-family-name="{{ $font->name }}"
                                                        @selected(($roleValues['family'] ?? '') === 'local:'.$font->slug)>{{ $font->name }} (local)</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <label class="small mb-0">Weight</label>
                                <select name="roles[{{ $role }}][weight]" class="form-control form-control-sm gsm-typo-field" data-role="{{ $role }}" data-css="font-weight">
                                    @foreach (config('theme.typography.weights') as $w => $label)
                                        <option value="{{ $w }}" @selected(($roleValues['weight'] ?? '400') === $w)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <label class="small mb-0">Text Transform</label>
                                <select name="roles[{{ $role }}][transform]" class="form-control form-control-sm gsm-typo-field" data-role="{{ $role }}" data-css="textTransform">
                                    @foreach (config('theme.typography.transforms') as $t => $label)
                                        <option value="{{ $t }}" @selected(($roleValues['transform'] ?? 'none') === $t)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-4 col-md-2 mb-2">
                                <label class="small mb-0">Size — Desktop (px)</label>
                                <input type="number" name="roles[{{ $role }}][size_desktop]" class="form-control form-control-sm gsm-typo-field"
                                       data-role="{{ $role }}" data-css="fontSize" data-unit="px"
                                       value="{{ $roleValues['size_desktop'] ?? $roleSchema['default_size'] }}" min="8" max="120">
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <label class="small mb-0">Size — Tablet (px)</label>
                                <input type="number" name="roles[{{ $role }}][size_tablet]" class="form-control form-control-sm"
                                       value="{{ $roleValues['size_tablet'] ?? round($roleSchema['default_size'] * 0.9, 1) }}" min="8" max="120">
                            </div>
                            <div class="col-4 col-md-2 mb-2">
                                <label class="small mb-0">Size — Mobile (px)</label>
                                <input type="number" name="roles[{{ $role }}][size_mobile]" class="form-control form-control-sm"
                                       value="{{ $roleValues['size_mobile'] ?? round($roleSchema['default_size'] * 0.8, 1) }}" min="8" max="120">
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <label class="small mb-0">Letter Spacing (px)</label>
                                <input type="number" step="0.1" name="roles[{{ $role }}][letter_spacing]" class="form-control form-control-sm gsm-typo-field"
                                       data-role="{{ $role }}" data-css="letterSpacing" data-unit="px"
                                       value="{{ $roleValues['letter_spacing'] ?? 0 }}" min="-5" max="20">
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <label class="small mb-0">Line Height</label>
                                <input type="number" step="0.1" name="roles[{{ $role }}][line_height]" class="form-control form-control-sm gsm-typo-field"
                                       data-role="{{ $role }}" data-css="lineHeight"
                                       value="{{ $roleValues['line_height'] ?? 1.5 }}" min="0.8" max="3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary btn-block mt-2">
        <i class="fas fa-save mr-1"></i> Save Typography
    </button>
</form>
