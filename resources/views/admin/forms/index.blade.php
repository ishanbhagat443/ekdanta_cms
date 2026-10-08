@extends('admin.layouts.app')

@section('page_title', 'Forms')

@section('page_content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">All Forms</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th class="text-center">Fields</th>
                                <th class="text-center">Submissions</th>
                                <th class="text-center">Status</th>
                                <th class="text-right" style="width: 210px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($forms as $form)
                                <tr>
                                    <td class="font-weight-bold">{{ $form->name }}</td>
                                    <td><code>{{ $form->slug }}</code></td>
                                    <td class="text-center">{{ $form->fields_count }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.forms.submissions', $form) }}">{{ $form->submissions_count }}</a>
                                        @if ($form->new_submissions_count)
                                            <span class="badge badge-danger ml-1">{{ $form->new_submissions_count }} new</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $form->is_active ? 'success' : 'secondary' }}">
                                            {{ $form->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-xs btn-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                        <a href="{{ route('admin.forms.submissions', $form) }}" class="btn btn-xs btn-info" title="Submissions"><i class="fas fa-inbox"></i></a>
                                        <a href="{{ route('admin.forms.export', $form) }}" class="btn btn-xs btn-success" title="Export Excel (CSV)"><i class="fas fa-file-excel"></i></a>
                                        <form action="{{ route('admin.forms.destroy', $form) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this form and ALL its submissions?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No forms yet — create one on the right.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">New Form</h3></div>
                <form action="{{ route('admin.forms.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="name">Form Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Contact Form" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">You'll add fields on the next screen.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i> Create &amp; Add Fields</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
