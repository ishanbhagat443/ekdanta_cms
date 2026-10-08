{{--
    Overrides adminlte::partials.common.brand-logo-xs.

    The packaged partial always emits <img src="{{ asset(config('adminlte.logo_img')) }}">.
    config()'s default only applies when the key is ABSENT, so a configured
    null produced src="/" — a broken-image glyph on every admin page, taking
    layout width and pushing into the brand text.

    Here the image renders only when there is one to render: the logo an admin
    uploaded under Settings → Branding first, then the static adminlte.logo_img,
    otherwise nothing but the brand text.
--}}
@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');
    $dashboard_url = config('adminlte.use_route_url', false)
        ? ($dashboard_url ? route($dashboard_url) : '')
        : ($dashboard_url ? url($dashboard_url) : '');

    // The sidebar is dark, so the light logo is the right variant.
    $configured = config('adminlte.logo_img');
    $brandLogo = setting_image('branding.logo_light')
        ?: ($configured ? asset($configured) : null);
@endphp

<a href="{{ $dashboard_url }}"
    @if($layoutHelper->isLayoutTopnavEnabled())
        class="navbar-brand {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link {{ $brandLogo ? '' : 'gsm-brand-no-logo' }} {{ config('adminlte.classes_brand') }}"
    @endif>

    @if ($brandLogo)
        <img src="{{ $brandLogo }}"
             alt="{{ config('adminlte.logo_img_alt', config('app.name')) }}"
             class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}"
             style="opacity:.8">
    @endif

    <span class="brand-text font-weight-light {{ config('adminlte.classes_brand_text') }}">
        {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
    </span>
</a>
