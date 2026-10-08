@php $data = $section->dataFor('desktop'); @endphp

<div class="gsm-newsletter" id="gsm-newsletter">
    @if (!empty($data['title']))
        <h6 class="text-uppercase fw-bold mb-3">
            @include('frontend.partials.device-text', ['section' => $section, 'key' => 'title'])
        </h6>
    @endif
    @if (!empty($data['text']))
        <p class="small">@include('frontend.partials.device-text', ['section' => $section, 'key' => 'text'])</p>
    @endif

    @if (session('newsletter_success'))
        <div class="alert alert-success py-2 small">{{ $data['success_message'] ?? 'Thank you for subscribing!' }}</div>
    @endif
    @error('email')
        <div class="alert alert-danger py-2 small">{{ $message }}</div>
    @enderror

    <form action="{{ route('front.newsletter') }}" method="POST">
        @csrf
        {{-- Honeypot --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off"
               style="position:absolute; left:-9999px;" aria-hidden="true">
        <div class="input-group">
            <input type="email" name="email" required class="form-control form-control-sm"
                   placeholder="{{ $data['placeholder'] ?? 'Email' }}">
            <button type="submit" class="btn gsm-btn-gradient btn-sm text-white px-3">
                {{ $data['button_label'] ?? 'Submit' }}
            </button>
        </div>
    </form>
</div>
