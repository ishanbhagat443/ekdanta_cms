@extends('admin.layouts.app')

@section('page_title', 'New Post')

@section('page_content')
    <form method="POST" action="{{ route('admin.posts.store') }}">
        @csrf
        @include('admin.posts.partials.form')
    </form>
@stop

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
    <script src="{{ asset('js/admin/media-picker.js') }}?v={{ gsm_version() }}"></script>
    <script src="{{ asset('js/admin/post-form.js') }}?v={{ gsm_version() }}"></script>
@endpush
