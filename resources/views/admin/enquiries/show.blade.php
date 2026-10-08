@extends('admin.layouts.app')

@section('page_title', 'Enquiry #' . $enquiry->id . ' — ' . $enquiry->sourceLabel())

@section('page_actions')
    <a href="{{ route('admin.enquiries.index') }}" class="btn btn-default btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> All Enquiries
    </a>
@stop

@section('page_content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Details</h3></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr><th style="width: 160px;">Source</th><td><span class="badge badge-info">{{ $enquiry->sourceLabel() }}</span></td></tr>
                        <tr><th>Name</th><td>{{ $enquiry->name ?: '—' }}</td></tr>
                        <tr><th>Email</th><td>@if($enquiry->email)<a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>@else — @endif</td></tr>
                        <tr><th>Phone</th><td>@if($enquiry->phone)<a href="tel:{{ $enquiry->phone }}">{{ $enquiry->phone }}</a>@else — @endif</td></tr>
                        <tr><th>Message</th><td style="white-space: pre-line;">{{ $enquiry->message ?: '—' }}</td></tr>
                        @foreach ($enquiry->extra ?? [] as $label => $value)
                            <tr>
                                <th>{{ $label }}</th>
                                <td>
                                    @if (\Illuminate\Support\Str::startsWith((string) $value, ['http://', 'https://', '/storage/']))
                                        <a href="{{ $value }}" target="_blank" rel="noopener"><i class="fas fa-paperclip mr-1"></i>{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr><th>IP</th><td>{{ $enquiry->ip ?: '—' }}</td></tr>
                        <tr><th>Date</th><td>{{ $enquiry->created_at->format('d M Y, H:i') }}</td></tr>
                        @if ($enquiry->submission)
                            <tr>
                                <th>Raw Submission</th>
                                <td><a href="{{ route('admin.forms.submissions', $enquiry->submission->form_id) }}">Form submission #{{ $enquiry->submission->id }}</a></td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Status &amp; Notes</h3></div>
                <form action="{{ route('admin.enquiries.update', $enquiry) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($enquiry->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="admin_notes">Admin Notes</label>
                            <textarea name="admin_notes" id="admin_notes" rows="6" class="form-control"
                                      placeholder="Internal notes — never shown to the visitor.">{{ old('admin_notes', $enquiry->admin_notes) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                    </div>
                </form>
                <div class="card-footer">
                    <form action="{{ route('admin.enquiries.destroy', $enquiry) }}" method="POST"
                          onsubmit="return confirm('Delete this enquiry?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash mr-1"></i> Delete Enquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
