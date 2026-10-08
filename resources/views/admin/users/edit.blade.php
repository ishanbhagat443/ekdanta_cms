@extends('admin.layouts.app')

@section('page_title', 'Edit User')

@section('page_content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header"><h3 class="card-title">Edit User — {{ $user->name }}</h3></div>
            <div class="card-body">
                @include('admin.users.partials.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@stop
