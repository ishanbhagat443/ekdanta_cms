@extends('admin.layouts.app')

@section('page_title', 'Brand')

@section('page_content')
    {{-- The front door to how the site looks. Everything here is a summary of
         a setting that lives elsewhere, with one link per job — so a new user
         has one obvious first stop instead of four screens to discover. --}}
    <p class="text-muted mb-4" style="max-width: 44rem;">
        Set these once and they carry across every page: your logo, your colours, your
        fonts and how your buttons look.
    </p>

    <div class="gsm-brand-grid">

        {{-- Logo ------------------------------------------------------ --}}
        <section class="gsm-brand-card">
            <h2 class="gsm-brand-card__title"><i class="fas fa-fingerprint mr-2" aria-hidden="true"></i>Logo</h2>

            <div class="gsm-brand-logo">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $companyName }} logo">
                @else
                    <p class="gsm-brand-empty">No logo yet — your site shows the name “{{ $companyName }}” instead.</p>
                @endif
            </div>

            <dl class="gsm-brand-facts">
                <dt>Site name</dt>
                <dd>{{ $companyName }}</dd>
                <dt>Favicon</dt>
                <dd>{{ $favicon ? 'Set' : 'Not set' }}</dd>
            </dl>

            <a href="{{ route('admin.settings.edit', 'branding') }}" class="btn btn-outline-primary btn-sm">
                {{ $logo ? 'Change logo' : 'Add your logo' }}
            </a>
        </section>

        {{-- Colours --------------------------------------------------- --}}
        <section class="gsm-brand-card">
            <h2 class="gsm-brand-card__title"><i class="fas fa-palette mr-2" aria-hidden="true"></i>Colours</h2>

            <ul class="gsm-swatches">
                @foreach ($colors as $color)
                    <li class="gsm-swatch">
                        <span class="gsm-swatch__chip" style="background: {{ $color['value'] }};" aria-hidden="true"></span>
                        <span class="gsm-swatch__label">{{ $color['label'] }}</span>
                        <code class="gsm-swatch__value">{{ $color['value'] }}</code>
                    </li>
                @endforeach
            </ul>

            <a href="{{ route('admin.theme.index') }}#colors" class="btn btn-outline-primary btn-sm">Change colours</a>
        </section>

        {{-- Fonts ----------------------------------------------------- --}}
        <section class="gsm-brand-card">
            <h2 class="gsm-brand-card__title"><i class="fas fa-font mr-2" aria-hidden="true"></i>Fonts</h2>

            <ul class="gsm-font-list">
                @foreach ($fonts as $family => $roles)
                    <li>
                        <span class="gsm-font-list__name" style="font-family: {{ $family }};">{{ $family }}</span>
                        <span class="gsm-font-list__roles">{{ implode(', ', $roles) }}</span>
                    </li>
                @endforeach
            </ul>

            <a href="{{ route('admin.theme.index') }}#typography" class="btn btn-outline-primary btn-sm">Change fonts</a>
        </section>

        {{-- Buttons and corners --------------------------------------- --}}
        <section class="gsm-brand-card">
            <h2 class="gsm-brand-card__title"><i class="fas fa-toggle-on mr-2" aria-hidden="true"></i>Buttons &amp; corners</h2>

            <p class="text-muted small mb-2">Corner rounding: <strong>{{ $radius }}px</strong></p>

            @if ($buttonStyles->isNotEmpty())
                <ul class="gsm-brand-chips">
                    @foreach ($buttonStyles as $style)
                        <li>{{ $style->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="gsm-brand-empty">No saved button styles yet.</p>
            @endif

            @if ($gradients->isNotEmpty())
                <p class="text-muted small mb-1 mt-2">Gradients</p>
                <ul class="gsm-brand-chips">
                    @foreach ($gradients as $gradient)
                        <li>{{ $gradient->name }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-2" style="display:flex; gap:.4rem; flex-wrap:wrap;">
                <a href="{{ route('admin.button-styles.index') }}" class="btn btn-outline-primary btn-sm">Button builder</a>
                <a href="{{ route('admin.gradients.index') }}" class="btn btn-outline-secondary btn-sm">Gradients</a>
            </div>
        </section>
    </div>

    <p class="text-muted small mt-3">
        Need finer control? The <a href="{{ route('admin.theme.index') }}">Theme Customizer</a> has every
        spacing, shadow and dark-mode setting.
    </p>
@stop

@push('styles')
    <link href="{{ asset('css/admin/builder.css') }}?v={{ gsm_version() }}" rel="stylesheet">
@endpush
