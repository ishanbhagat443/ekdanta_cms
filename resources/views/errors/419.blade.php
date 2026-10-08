@extends('errors.layout')

@section('code', '419')
@section('title', 'Page expired')
@section('message', 'Your session timed out for security. Please go back and try again.')
@section('secondary')
    <a class="btn btn--ghost" href="{{ url('/cms/login') }}">Sign in again</a>
@endsection
