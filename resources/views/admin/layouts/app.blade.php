@extends('adminlte::page')

{{-- Base admin layout: all module views extend this instead of adminlte::page
     directly, so cross-cutting assets/partials are wired in one place. --}}

@section('title', trim($__env->yieldContent('page_title', 'Dashboard')) . ' | ' . config('gsm.name'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">@yield('page_title', 'Dashboard')</h1>
        @yield('page_actions')
    </div>
@stop

@section('content')
    @include('admin.layouts.partials.flash')
    @yield('page_content')
@stop

@section('js')
    @stack('scripts')
@stop

@section('css')
    <style>
        /* Brand with no logo image: AdminLTE's spacing assumes an <img> is
           there to hold the left gutter, so give the text its own. */
        .brand-link.gsm-brand-no-logo { padding-left: 1rem; }
        .brand-link.gsm-brand-no-logo .brand-text { margin-left: 0; }
    </style>
    @stack('styles')
@stop
