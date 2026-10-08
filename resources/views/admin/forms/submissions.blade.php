@extends('admin.layouts.app')

@section('page_title', 'Submissions — ' . $form->name)

@section('page_actions')
    <div>
        <a href="{{ route('admin.forms.export', $form) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel (CSV)
        </a>
        <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-default btn-sm">
            <i class="fas fa-pen mr-1"></i> Edit Form
        </a>
    </div>
@stop

@section('page_content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        @foreach ($form->fields as $field)
                            <th>{{ $field->label }}</th>
                        @endforeach
                        <th>Date</th>
                        <th>IP</th>
                        <th class="text-center">Status</th>
                        <th class="text-right" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr class="{{ $submission->status === 'new' ? 'font-weight-bold' : '' }}">
                            <td>{{ $submission->id }}</td>
                            @foreach ($form->fields as $field)
                                <td>
                                    @php $value = $submission->valueFor($field->name); @endphp
                                    @if ($field->type === 'file' && $value)
                                        <a href="{{ $value }}" target="_blank" rel="noopener"><i class="fas fa-paperclip mr-1"></i>View file</a>
                                    @else
                                        {{ \Illuminate\Support\Str::limit($value, 60) }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-nowrap">{{ $submission->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $submission->ip }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $submission->status === 'new' ? 'danger' : 'secondary' }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if ($submission->status === 'new')
                                    <form action="{{ route('admin.forms.submissions.read', [$form, $submission]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-xs btn-outline-primary" title="Mark as read"><i class="fas fa-check"></i></button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.forms.submissions.destroy', [$form, $submission]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this submission?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $form->fields->count() }}" class="text-center text-muted py-4">No submissions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($submissions->hasPages())
            <div class="card-footer">{{ $submissions->links() }}</div>
        @endif
    </div>
@stop
