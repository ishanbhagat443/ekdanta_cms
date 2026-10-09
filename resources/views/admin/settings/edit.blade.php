@extends('admin.layouts.app')

@section('page_title', 'Settings')

@section('page_content')
    <div class="row">
        {{-- Group navigation --}}
        <div class="col-12 col-md-3 mb-3">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        @foreach ($groups as $groupKey => $groupSchema)
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.edit', $groupKey) }}"
                                   class="nav-link rounded-0 {{ $groupKey === $group ? 'active' : '' }}">
                                    <i class="{{ $groupSchema['icon'] ?? 'fas fa-cog' }} fa-fw mr-1"></i>
                                    {{ $groupSchema['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Active group form --}}
        <div class="col-12 col-md-9">
            <form method="POST"
                  action="{{ route('admin.settings.update', $group) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="{{ $schema['icon'] ?? 'fas fa-cog' }} mr-1"></i> {{ $schema['label'] }}
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @foreach ($schema['fields'] as $key => $field)
                                @include('admin.settings.partials.field', [
                                    'group' => $group,
                                    'key' => $key,
                                    'field' => $field,
                                    'value' => $values[$key] ?? null,
                                ])
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save {{ $schema['label'] }}
                        </button>
                    </div>
                </div>
            </form>

            @if (in_array($group, ['whatsapp', 'whatsapp_custom'], true))
                {{-- Send a test message through the active channel (Custom API wins when configured). --}}
                <form method="POST" action="{{ route('admin.whatsapp.test') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-success">
                        <i class="fab fa-whatsapp mr-1"></i> Send Test Message
                    </button>
                    <span class="text-muted small ml-2">Saves are applied first — save, then test.</span>
                </form>
            @endif
        </div>
    </div>
@stop

@push('scripts')
    <script>
        // "list" settings (phone numbers, offices): add and remove rows.
        document.addEventListener('click', function (e) {
            var add = e.target.closest('.gsm-repeater-add');
            if (add) {
                var repeater = add.closest('.gsm-repeater');
                var fragment = repeater.querySelector('.gsm-repeater-template').content.cloneNode(true);
                var index = Date.now();
                fragment.querySelectorAll('[data-name]').forEach(function (field) {
                    field.name = field.dataset.name.replace('__i__', index);
                });
                repeater.insertBefore(fragment, add);
                var first = add.previousElementSibling && add.previousElementSibling.querySelector('input:not([type=hidden]), textarea');
                if (first) first.focus();
            }
            var remove = e.target.closest('.gsm-repeater-remove');
            if (remove) remove.closest('.gsm-repeater-row').remove();
        });

        // Show chosen filename in Bootstrap custom-file inputs.
        document.querySelectorAll('.custom-file-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var label = this.parentElement.querySelector('.custom-file-label');
                if (label && this.files.length) {
                    label.textContent = this.files[0].name;
                }
            });
        });
    </script>
@endpush
