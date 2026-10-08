@extends('admin.layouts.app')

@section('page_title', 'Dashboard')

@section('page_content')
    @if (! $onboardingComplete)
        {{-- The first thing a new user sees. It answers "what now?" with an
             ordered list, and it disappears by itself once the site is set up
             — nothing to dismiss, nothing to tick by hand. --}}
        <section class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-list-check mr-2" aria-hidden="true"></i>Set up your site
                </h3>
                <span class="ml-auto text-muted small">
                    {{ $steps->count() - $remaining }} of {{ $steps->count() }} done
                </span>
            </div>

            <div class="card-body">
                <div class="gsm-progress" role="progressbar"
                     aria-valuenow="{{ $steps->count() - $remaining }}" aria-valuemin="0"
                     aria-valuemax="{{ $steps->count() }}"
                     aria-label="Setup progress">
                    <span class="gsm-progress__bar"
                          style="width: {{ round((($steps->count() - $remaining) / max($steps->count(), 1)) * 100) }}%"></span>
                </div>

                <ol class="gsm-checklist">
                    @foreach ($steps as $step)
                        <li class="gsm-checklist__item {{ $step['done'] ? 'is-done' : '' }}">
                            <span class="gsm-checklist__mark" aria-hidden="true">
                                <i class="fas {{ $step['done'] ? 'fa-circle-check' : 'fa-circle' }}"></i>
                            </span>

                            <span class="gsm-checklist__body">
                                <span class="gsm-checklist__title">
                                    {{ $step['title'] }}
                                    <span class="sr-only">{{ $step['done'] ? '(done)' : '(still to do)' }}</span>
                                </span>
                                <span class="gsm-checklist__text">{{ $step['text'] }}</span>
                            </span>

                            @unless ($step['done'])
                                <a href="{{ $step['url'] }}" class="btn btn-primary btn-sm gsm-checklist__action">
                                    {{ $step['action'] }}
                                </a>
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    <div class="gsm-stat-row">
        <a class="gsm-stat" href="{{ route('admin.pages.index') }}">
            <i class="fas fa-file-lines" aria-hidden="true"></i>
            <span class="gsm-stat__value">{{ $counts['pages'] }}</span>
            <span class="gsm-stat__label">{{ Str::plural('Page', $counts['pages']) }}</span>
        </a>
        <a class="gsm-stat" href="{{ route('admin.posts.index') }}">
            <i class="fas fa-newspaper" aria-hidden="true"></i>
            <span class="gsm-stat__value">{{ $counts['posts'] }}</span>
            <span class="gsm-stat__label">Blog {{ Str::plural('post', $counts['posts']) }}</span>
        </a>
        <a class="gsm-stat" href="{{ route('admin.media.index') }}">
            <i class="fas fa-photo-film" aria-hidden="true"></i>
            <span class="gsm-stat__value">{{ $counts['media'] }}</span>
            <span class="gsm-stat__label">{{ Str::plural('File', $counts['media']) }}</span>
        </a>
        <a class="gsm-stat" href="{{ route('admin.enquiries.index') }}">
            <i class="fas fa-envelope-open-text" aria-hidden="true"></i>
            <span class="gsm-stat__value">{{ $counts['enquiries'] }}</span>
            <span class="gsm-stat__label">{{ Str::plural('Enquiry', $counts['enquiries']) }}</span>
        </a>
    </div>

    @if ($onboardingComplete)
        <p class="text-muted small mt-3">
            Your site is set up. <a href="{{ url('/') }}" target="_blank" rel="noopener">View it</a>
            or <a href="{{ route('admin.pages.index') }}">keep building</a>.
        </p>
    @endif
@stop

@push('styles')
    <link href="{{ asset('css/admin/builder.css') }}?v={{ gsm_version() }}" rel="stylesheet">
@endpush
