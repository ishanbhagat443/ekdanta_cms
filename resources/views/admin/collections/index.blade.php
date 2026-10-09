@extends('admin.layouts.app')

@section('page_title', $type->label())

@section('page_actions')
    <a href="{{ route('admin.collections.create', $type->key()) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1" aria-hidden="true"></i> Add {{ strtolower($type->singular()) }}
    </a>
@stop

@section('page_content')
<ul class="nav nav-pills mb-3 flex-wrap" aria-label="Collections">
    @foreach ($types as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab->key() === $type->key() ? 'active' : '' }}" href="{{ route('admin.collections.index', $tab->key()) }}"
               @if ($tab->key() === $type->key()) aria-current="page" @endif>
                <i class="{{ $tab->icon() }} mr-1" aria-hidden="true"></i>{{ $tab->label() }}
            </a>
        </li>
    @endforeach
</ul>

@if ($type->hint())
    <p class="text-muted">{{ $type->hint() }}</p>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;"><span class="sr-only">Drag to reorder</span></th>
                    <th style="width: 70px;">Order</th>
                    <th>{{ $type->titleLabel() }}</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th class="text-right" style="width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody id="gsm-collection-rows" data-reorder-url="{{ route('admin.collections.reorder', $type->key()) }}">
                @forelse ($items as $item)
                    <tr data-id="{{ $item->id }}">
                        <td class="gsm-row-handle text-muted" style="cursor: grab;" title="Drag to reorder"><i class="fas fa-grip-vertical" aria-hidden="true"></i></td>
                        <td data-order>{{ $item->sort_order }}</td>
                        <td>
                            <a href="{{ route('admin.collections.edit', [$type->key(), $item]) }}"><strong>{{ $item->title }}</strong></a>
                            @if ($url = $item->url()) <br><small><a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a></small> @endif
                        </td>
                        <td><code>{{ $item->slug }}</code></td>
                        <td><span class="badge badge-{{ $item->status === 'published' ? 'success' : 'secondary' }}">{{ ucfirst($item->status) }}</span></td>
                        <td class="text-right text-nowrap">
                            <a href="{{ route('admin.collections.edit', [$type->key(), $item]) }}" class="btn btn-xs btn-outline-primary" aria-label="Edit {{ $item->title }}"><i class="fas fa-pen" aria-hidden="true"></i></a>
                            <form action="{{ route('admin.collections.destroy', [$type->key(), $item]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete {{ addslashes($item->title) }}? Sections that show this list stop showing it.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" aria-label="Delete {{ $item->title }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Nothing here yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small">Drag rows to change the order, or set "Order" on an item's page.</p>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        (function () {
            var rows = document.getElementById('gsm-collection-rows');
            if (!rows || !window.Sortable) return;
            new Sortable(rows, {
                handle: '.gsm-row-handle', animation: 150,
                onSort: function () {
                    var ids = Array.prototype.map.call(rows.querySelectorAll('tr[data-id]'), function (row, i) {
                        row.querySelector('[data-order]').textContent = i + 1;
                        return Number(row.dataset.id);
                    });
                    fetch(rows.dataset.reorderUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ ids: ids })
                    });
                }
            });
        })();
    </script>
@endpush
