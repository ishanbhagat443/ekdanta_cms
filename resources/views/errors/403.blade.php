@extends('errors.layout')

@section('code', '403')
@section('title', 'Access denied')
@section('message')
    You don't have permission to view this page.
@endsection
@section('secondary')
    <a class="btn btn--ghost" href="{{ url('/cms/login') }}">Sign in</a>
@endsection
