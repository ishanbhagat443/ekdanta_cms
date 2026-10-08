@php
    $data = $section->dataFor('desktop');
    $form = ($formId = $data['form_id'] ?? null)
        ? \App\Models\Form::query()->where('is_active', true)->with('fields')->find($formId)
        : null;
@endphp

@if ($form)
    <div class="gsm-footer-form">
        @if (!empty($data['title']))
            <h6 class="text-uppercase fw-bold mb-3">
                @include('frontend.partials.device-text', ['section' => $section, 'key' => 'title'])
            </h6>
        @endif

        @if (session("form_success_{$form->id}"))
            <div class="alert alert-success py-2 small">{{ session("form_success_{$form->id}") }}</div>
        @endif

        <form method="POST" action="{{ route('front.form.submit', $form->slug) }}"
              enctype="multipart/form-data" id="form-{{ $form->slug }}" novalidate>
            @csrf
            <input type="hidden" name="_gsm_form" value="{{ $form->slug }}">
            {{-- Honeypot --}}
            <input type="text" name="website" tabindex="-1" autocomplete="off"
                   style="position:absolute; left:-9999px;" aria-hidden="true">

            @foreach ($form->fields as $field)
                @php $fieldError = old('_gsm_form') === $form->slug ? $errors->first($field->name) : null; @endphp
                <div class="mb-2">
                    @if ($field->type === 'textarea')
                        <textarea name="{{ $field->name }}" rows="3"
                                  class="form-control form-control-sm {{ $fieldError ? 'is-invalid' : '' }}"
                                  placeholder="{{ $field->placeholder ?: $field->label }}"
                                  @if($field->is_required) required @endif>{{ old($field->name) }}</textarea>
                    @elseif ($field->type === 'select')
                        <select name="{{ $field->name }}" class="form-select form-select-sm {{ $fieldError ? 'is-invalid' : '' }}"
                                @if($field->is_required) required @endif>
                            <option value="">{{ $field->placeholder ?: $field->label }}</option>
                            @foreach ((array) $field->options as $option)
                                <option value="{{ $option }}" @selected(old($field->name) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif (in_array($field->type, ['radio', 'checkbox'], true))
                        <div class="small mb-1">{{ $field->label }}</div>
                        @foreach ((array) $field->options as $i => $option)
                            <div class="form-check form-check-inline">
                                <input type="{{ $field->type }}" class="form-check-input"
                                       id="ffoot_{{ $form->id }}_{{ $field->name }}_{{ $i }}"
                                       name="{{ $field->name }}{{ $field->type === 'checkbox' ? '[]' : '' }}"
                                       value="{{ $option }}"
                                       @if ($field->type === 'checkbox')
                                           @checked(collect(old($field->name, []))->contains($option))
                                       @else
                                           @checked(old($field->name) === $option)
                                       @endif>
                                <label class="form-check-label small"
                                       for="ffoot_{{ $form->id }}_{{ $field->name }}_{{ $i }}">{{ $option }}</label>
                            </div>
                        @endforeach
                    @else
                        @php
                            $inputType = match ($field->type) {
                                'email' => 'email', 'phone' => 'tel', 'date' => 'date', 'file' => 'file', default => 'text',
                            };
                        @endphp
                        <input type="{{ $inputType }}" name="{{ $field->name }}"
                               @unless($field->type === 'file') value="{{ old($field->name) }}" @endunless
                               class="form-control form-control-sm {{ $fieldError ? 'is-invalid' : '' }}"
                               placeholder="{{ $field->placeholder ?: $field->label }}"
                               @if($field->is_required) required @endif>
                    @endif
                    @if ($fieldError)
                        <div class="invalid-feedback d-block small">{{ $fieldError }}</div>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="btn gsm-btn-gradient btn-sm text-white px-4 w-100">
                {{ $form->button_label }}
            </button>
        </form>
    </div>
@endif
