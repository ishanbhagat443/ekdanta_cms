{{-- Renders one Form Builder form. $form = App\Models\Form (with fields). --}}
<div id="form-{{ $form->slug }}" class="card border-0 shadow-sm">
    <div class="card-body p-4">
        @if ($form->heading)
            <h3 class="gsm-serif mb-3">{{ $form->heading }}</h3>
        @endif

        @if (session("form_success_{$form->id}"))
            <div class="alert alert-success">{{ session("form_success_{$form->id}") }}</div>
        @endif

        @if ($errors->any() && old('_gsm_form') === $form->slug)
            <div class="alert alert-danger py-2 small">Please correct the errors below.</div>
        @endif

        {{-- AJAX (no-reload) success / error messages are injected here. --}}
        <div class="gsm-form-alert" data-gsm-form-alert hidden></div>

        <form method="POST" action="{{ route('front.form.submit', $form->slug) }}" enctype="multipart/form-data"
              novalidate data-gsm-ajax-form>
            @csrf
            <input type="hidden" name="_gsm_form" value="{{ $form->slug }}">
            {{-- Honeypot --}}
            <input type="text" name="website" tabindex="-1" autocomplete="off"
                   style="position:absolute; left:-9999px;" aria-hidden="true">

            <div class="row g-3">
                {{-- ponytail: steps are shown together here; the Ekdanta Brief Builder walks them one by one. Add a stepper when another site needs one. --}}
                @foreach ($form->fields->where('type', 'hidden') as $field)
                    <input type="hidden" name="{{ $field->name }}" value="" @if ($field->setting('autofill')) data-autofill="{{ $field->setting('autofill') }}" @endif>
                @endforeach
                @foreach ($form->fields->where('type', '!=', 'hidden') as $field)
                    @php
                        $isWide = in_array($field->type, ['textarea', 'checkbox', 'radio', 'file', 'consent'], true);
                        $fieldErrors = old('_gsm_form') === $form->slug ? $errors->first($field->name) : null;
                    @endphp
                    <div class="{{ $isWide ? 'col-12' : 'col-12 col-md-6' }}">
                        @unless ($field->type === 'consent')
                            <label class="form-label small fw-semibold" for="ff_{{ $form->id }}_{{ $field->name }}">
                                {{ $field->label }} @if ($field->is_required)<span class="text-danger">*</span>@endif
                            </label>
                        @endunless

                        @switch($field->type)
                            @case('textarea')
                                <textarea name="{{ $field->name }}" id="ff_{{ $form->id }}_{{ $field->name }}" rows="4"
                                          class="form-control {{ $fieldErrors ? 'is-invalid' : '' }}"
                                          placeholder="{{ $field->placeholder }}"
                                          @if($field->is_required) required @endif>{{ old($field->name) }}</textarea>
                                @break

                            @case('select')
                                <select name="{{ $field->name }}" id="ff_{{ $form->id }}_{{ $field->name }}"
                                        class="form-select {{ $fieldErrors ? 'is-invalid' : '' }}"
                                        @if($field->is_required) required @endif>
                                    <option value="">{{ $field->placeholder ?: '— Select —' }}</option>
                                    @foreach ((array) $field->options as $option)
                                        <option value="{{ $option }}" @selected(old($field->name) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @break

                            @case('radio')
                            @case('checkbox')
                                <div class="d-flex flex-wrap {{ $field->setting('display') === 'chips' ? 'gap-2 gsm-chips' : 'gap-3' }}" role="group" aria-label="{{ $field->label }}">
                                    @foreach ((array) $field->options as $i => $option)
                                        <div class="form-check">
                                            <input type="{{ $field->type }}" class="form-check-input"
                                                   id="ff_{{ $form->id }}_{{ $field->name }}_{{ $i }}"
                                                   name="{{ $field->name }}{{ $field->type === 'checkbox' ? '[]' : '' }}"
                                                   value="{{ $option }}"
                                                   @if ($field->type === 'checkbox')
                                                       @checked(collect(old($field->name, []))->contains($option))
                                                   @else
                                                       @checked(old($field->name) === $option)
                                                   @endif>
                                            <label class="form-check-label small"
                                                   for="ff_{{ $form->id }}_{{ $field->name }}_{{ $i }}">{{ $option }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('consent')
                                {{-- One box, unticked to start; the link sits in the note when there is one, else after the label. --}}
                                @php
                                    $link = $field->setting('link_text') ? '<a href="'.e(link_href($field->setting('link_url'))).'">'.e($field->setting('link_text')).'</a>' : '';
                                    $note = $field->setting('note');
                                @endphp
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input {{ $fieldErrors ? 'is-invalid' : '' }}" value="1"
                                           id="ff_{{ $form->id }}_{{ $field->name }}" name="{{ $field->name }}"
                                           @if ($note) aria-describedby="ff_{{ $form->id }}_{{ $field->name }}_note" @endif
                                           @checked(old($field->name)) @if($field->is_required) required @endif>
                                    <label class="form-check-label small" for="ff_{{ $form->id }}_{{ $field->name }}">{{ $field->label }}@if ($field->is_required)<span class="text-danger"> *</span>@endif @unless ($note){!! $link !!}@endunless</label>
                                    @if ($note)
                                        <p class="small text-muted mb-0" id="ff_{{ $form->id }}_{{ $field->name }}_note">{{ $note }} {!! $link !!}</p>
                                    @endif
                                </div>
                                @break

                            @case('file')
                                <input type="file" name="{{ $field->name }}" id="ff_{{ $form->id }}_{{ $field->name }}"
                                       class="form-control {{ $fieldErrors ? 'is-invalid' : '' }}"
                                       @if($field->is_required) required @endif>
                                <small class="text-muted">Max 10 MB — images, PDF or Office files.</small>
                                @break

                            @default
                                @php
                                    $inputType = match ($field->type) {
                                        'email' => 'email', 'phone' => 'tel', 'date' => 'date', default => 'text',
                                    };
                                @endphp
                                <input type="{{ $inputType }}" name="{{ $field->name }}"
                                       id="ff_{{ $form->id }}_{{ $field->name }}"
                                       value="{{ old($field->name) }}" placeholder="{{ $field->placeholder }}"
                                       class="form-control {{ $fieldErrors ? 'is-invalid' : '' }}"
                                       @if($field->is_required) required @endif>
                        @endswitch

                        @if ($fieldErrors)
                            <div class="invalid-feedback d-block">{{ $fieldErrors }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn gsm-btn-gradient text-white rounded-pill px-5 mt-4 w-100">
                {{ $form->button_label }}
            </button>
        </form>
    </div>
</div>
