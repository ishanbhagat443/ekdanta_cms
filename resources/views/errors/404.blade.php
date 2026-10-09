@extends('errors.layout')

@section('code', '404')
@section('title', 'Page not found')

{{-- Block form: an apostrophe inside @section('message', '…') ends the
     argument early and the whole view prints as raw Blade. --}}
@section('message')
    The page you are looking for doesn't exist, or it has moved.
@endsection

@section('secondary')
    <a class="btn btn--ghost" href="{{ route('front.blog.index') }}">Read the blog</a>
@endsection
