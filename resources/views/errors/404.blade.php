@extends('errors.layout')

@section('code', '404')
@section('title', 'Page not found')
@section('message', 'The page you are looking for doesn't exist, or it has moved.')
@section('secondary')
    <a class="btn btn--ghost" href="{{ url('/blogs') }}">Read the blog</a>
@endsection
