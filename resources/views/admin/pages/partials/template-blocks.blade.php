{{-- Editable blocks for bespoke storefront screens (product detail,
     collection, cart, checkout, auth …). Definitions come from
     config/page_templates.php; values are saved into settings.blocks.
     Changing a block here updates EVERY page rendered by this template. --}}
@php
    $template = app(\App\Services\PageTemplateService::class);
    $definition = $template->definition($page->slug);
    $values = $definition ? $template->blocks($page->slug) : [];
@endphp

@if ($definition)
    <div class="card-header bg-light">
        <h3 class="card-title">
            <i class="fas fa-cubes mr-1"></i> {{ $definition['label'] ?? 'Page Blocks' }}
        </h3>
    </div>
    <div class="card-body">
        @if (! empty($definition['hint']))
            <div class="alert alert-info py-2 small mb-3">
                <i class="fas fa-info-circle mr-1"></i> {{ $definition['hint'] }}
            </div>
        @endif

        <div class="accordion" id="template-blocks">
            @foreach ($definition['blocks'] as $blockKey => $block)
                <div class="card mb-2">
                    <div class="card-header p-0" id="block-head-{{ $blockKey }}">
                        <button class="btn btn-link btn-block text-left px-3 py-2 font-weight-bold" type="button"
                                data-toggle="collapse" data-target="#block-{{ $blockKey }}">
                            <i class="fas fa-cube mr-1 text-muted"></i> {{ $block['label'] }}
                            @if (array_key_exists('enabled', $block['fields']) && ! ($values[$blockKey]['enabled'] ?? true))
                                <span class="badge badge-secondary ml-1">Hidden</span>
                            @endif
                        </button>
                    </div>

                    <div id="block-{{ $blockKey }}" class="collapse" data-parent="#template-blocks">
                        <div class="card-body py-2">
                            @foreach ($block['fields'] as $fieldKey => $field)
                                @php
                                    $name = "blocks[{$blockKey}][{$fieldKey}]";
                                    $value = $values[$blockKey][$fieldKey] ?? ($field['default'] ?? null);
                                @endphp

                                @if (($field['type'] ?? 'text') === 'toggle')
                                    <div class="form-group mb-2">
                                        <input type="hidden" name="{{ $name }}" value="0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input"
                                                   id="blk-{{ $blockKey }}-{{ $fieldKey }}"
                                                   name="{{ $name }}" value="1" @checked((bool) $value)>
                                            <label class="custom-control-label" for="blk-{{ $blockKey }}-{{ $fieldKey }}">
                                                {{ $field['label'] }}
                                            </label>
                                        </div>
                                    </div>
                                @elseif (($field['type'] ?? 'text') === 'textarea')
                                    <div class="form-group mb-2">
                                        <label class="small mb-1">{{ $field['label'] }}</label>
                                        <textarea name="{{ $name }}" rows="2" class="form-control form-control-sm">{{ $value }}</textarea>
                                    </div>
                                @elseif (($field['type'] ?? 'text') === 'number')
                                    <div class="form-group mb-2">
                                        <label class="small mb-1">{{ $field['label'] }}</label>
                                        <input type="number" name="{{ $name }}" value="{{ $value }}" class="form-control form-control-sm">
                                    </div>
                                @else
                                    <div class="form-group mb-2">
                                        <label class="small mb-1">{{ $field['label'] }}</label>
                                        <input type="text" name="{{ $name }}" value="{{ $value }}" class="form-control form-control-sm">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
