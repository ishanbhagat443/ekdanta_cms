@extends('admin.layouts.app')

@section('page_title', 'Ekdanta guide')

@php
    $kinds = ['text' => 'Text', 'textarea' => 'Lines', 'richtext' => 'Rich text', 'link' => 'Link', 'media' => 'Image', 'number' => 'Number',
        'select' => 'Choice', 'toggle' => 'Switch', 'repeater' => 'List', 'color' => 'Colour', 'menu' => 'Menu', 'code' => 'Code'];
    $reduce = ['final' => 'final state', 'start' => 'kept as drawn', 'hide' => 'hidden', 'step' => 'switches at its point'];
    $short = fn ($value) => match (true) {
        is_bool($value) => $value ? 'on' : 'off',
        is_array($value) => count($value).' '.\Illuminate\Support\Str::plural('row', count($value)),
        $value === null || $value === '' => '—',
        default => \Illuminate\Support\Str::limit(str_replace("\n", ' / ', (string) $value), 90),
    };
    $timing = fn (array $t) => implode(', ', array_filter([
        isset($t['range']) ? 'scroll '.$t['range'][0].' → '.$t['range'][1].' px' : null,
        isset($t['duration']) ? $t['duration'].' ms' : null,
        ! empty($t['delay']) ? 'delay '.$t['delay'].' ms' : null,
        ! empty($t['stagger']) ? 'stagger '.$t['stagger'] : null,
        ! empty($t['direction']) && $t['direction'] !== 'normal' ? $t['direction'] : null,
        ! empty($t['ease']) ? $t['ease'] : null,
    ]));
    $movement = fn (array $t) => isset($t['preset']) ? $t['preset'] : (isset($t['keyframes']) ? 'keyframes: '.implode(', ', array_keys($t['keyframes'])) : '—');
@endphp

@section('page_content')
    <style>
        .ek-guide { max-width: 980px; }
        .ek-guide h1 { font-size: 1.6rem; } .ek-guide h2 { font-size: 1.3rem; margin-top: 1.8rem; } .ek-guide h3 { font-size: 1.1rem; margin-top: 1.4rem; }
        .ek-guide table { width: 100%; margin: .5rem 0 1rem; border-collapse: collapse; font-size: .875rem; }
        .ek-guide th, .ek-guide td { border: 1px solid #dee2e6; padding: .35rem .5rem; vertical-align: top; text-align: left; }
        .ek-guide th { background: #f4f6f9; }
        .ek-guide code { white-space: normal; }
    </style>

    <div class="card">
        <div class="card-body ek-guide">
            {!! $guide !!}

            <h2 id="pages-in-order">Pages, in order</h2>
            <p>Add these sections in this order (Add a section → Ekdanta), then <strong>Add a saved block → Big Brand Finale</strong> last.</p>
            <table>
                <thead><tr><th>Page</th><th>Address</th><th>Page settings</th><th>Sections</th></tr></thead>
                <tbody>
                    @foreach ($pages as $slug => [$title, $settings, $role, $sectionTypes])
                        <tr>
                            <td>{{ $title }}</td>
                            <td>@if (isset($settings['template_for']))<em>none (a template)</em>@else<code>/{{ $slug === 'home' ? '' : $slug }}</code>@endif</td>
                            <td>
                                @if ($role) Use this page as: {{ \App\Support\PageRoles::ROLES[$role] }}<br>@endif
                                @if (isset($settings['template_for'])) Template for: {{ \App\Support\Templates::TYPES[$settings['template_for']] }}<br>@endif
                                @if (isset($settings['header']['menu'])) Header menu: {{ \App\Models\Menu::query()->where('slug', $settings['header']['menu'])->value('name') ?? $settings['header']['menu'] }}@endif
                            </td>
                            <td>
                                <ol class="mb-0 pl-3">
                                    @foreach ($sectionTypes as $type)
                                        <li><a href="#{{ 'ekdanta-'.$type }}">{{ $types['ekdanta-'.$type]?->label() ?? 'ekdanta-'.$type }}</a></li>
                                    @endforeach
                                    <li>Big Brand Finale (saved block)</li>
                                </ol>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 id="sections">Every Ekdanta section</h2>
            <p>Fields as they appear on the section's Desktop tab. "Phones" marks fields the Mobile tab can change. Motion parts are what the Motion tab's tracks move; the tracks listed are the design's (Desktop, then Mobile).</p>
            <ul class="small" style="columns: 2;">
                @foreach ($types as $key => $type)
                    <li><a href="#{{ $key }}">{{ $type->label() }}</a></li>
                @endforeach
            </ul>

            @foreach ($types as $key => $type)
                @php
                    $fields = $type->fields();
                    $anchor = collect($fields)->firstWhere('key', 'anchor')['default'] ?? '';
                    $motion = $type->motionDefaults();
                @endphp
                <h3 id="{{ $key }}">{{ $type->label() }}</h3>
                <p class="mb-1 small text-muted">Key <code>{{ $key }}</code> · anchor <code>#{{ $anchor }}</code>@if ($type->motionParts()) · motion parts: @foreach ($type->motionParts() as $part)<code>{{ $part }}</code>@if (! $loop->last), @endif @endforeach @endif</p>
                <table>
                    <thead><tr><th style="width: 32%;">Field</th><th>Kind</th><th>Phones</th><th>Design default</th></tr></thead>
                    <tbody>
                        @foreach ($fields as $field)
                            <tr>
                                <td>{{ $field['label'] }} <code class="small">{{ $field['key'] }}</code></td>
                                <td>{{ $kinds[$field['type'] ?? 'text'] ?? $field['type'] }}@if (($field['type'] ?? '') === 'repeater')<br><span class="small text-muted">each: {{ collect($field['fields'] ?? [])->pluck('label')->implode(', ') }}</span>@endif</td>
                                <td>{{ ! empty($field['device']) ? 'yes' : '' }}</td>
                                <td>{{ $short($field['default'] ?? null) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @foreach (['desktop' => 'Desktop', 'mobile' => 'Mobile'] as $device => $deviceLabel)
                    @php $config = (array) ($motion[$device] ?? []); @endphp
                    @continue($config === [])
                    <p class="mb-1 small">
                        <strong>{{ $deviceLabel }} motion.</strong>
                        @if (! empty($config['stage']['pin'])) Pinned stage, {{ $config['stage']['length'] ?? 0 }} px of scroll. @else Flows with the page. @endif
                        @if (! empty($config['steps'])) Steps: the items of <code>{{ $config['steps']['part'] }}</code>, over scroll {{ implode(' → ', (array) ($config['steps']['range'] ?? [])) }} px. @endif
                        @if (! empty($config['scene']['params'])) Scene settings: {{ implode(', ', array_keys($config['scene']['params'])) }}. @endif
                    </p>
                    @if (! empty($config['tracks']))
                        <table>
                            <thead><tr><th>Track</th><th>Part</th><th>When</th><th>Movement</th><th>Timing</th><th>Reduced motion</th></tr></thead>
                            <tbody>
                                @foreach ($config['tracks'] as $name => $track)
                                    <tr>
                                        <td>{{ $name }}</td>
                                        <td><code>{{ $track['part'] ?? '' }}</code>{{ ! empty($track['children']) ? ' (each child)' : '' }}</td>
                                        <td>{{ \App\Support\SectionMotion::TRIGGERS[$track['trigger'] ?? ''] ?? '' }}</td>
                                        <td>{{ $movement($track) }}</td>
                                        <td>{{ $timing($track) }}</td>
                                        <td>{{ $reduce[$track['reduce'] ?? 'final'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
@stop
