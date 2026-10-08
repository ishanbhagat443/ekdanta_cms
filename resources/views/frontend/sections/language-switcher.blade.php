@php
    $data = $section->dataFor('desktop');
    $classes = $section->visibilityClasses();
    $languages = (array) ($data['languages'] ?? []);
    $current = $data['current'] ?? 'EN';
@endphp

@if ($languages !== [])
    <div class="dropdown order-3 ms-lg-2 {{ $classes }}">
        <button class="btn btn-link btn-sm text-dark dropdown-toggle px-2" data-bs-toggle="dropdown" type="button">
            <i class="fas fa-globe me-1"></i>{{ $current }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            @foreach ($languages as $lang)
                <li>
                    <a class="dropdown-item {{ ($lang['code'] ?? '') === $current ? 'active' : '' }}"
                       href="{{ $lang['url'] ?? '#' }}">{{ $lang['label'] ?? ($lang['code'] ?? '') }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
