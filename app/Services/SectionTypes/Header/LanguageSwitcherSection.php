<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class LanguageSwitcherSection extends SectionType
{
    public function key(): string
    {
        return 'language-switcher';
    }

    public function label(): string
    {
        return 'Language Switcher';
    }

    public function icon(): string
    {
        return 'fas fa-globe';
    }

    public function allowedAreas(): array
    {
        return ['header', 'topbar'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'languages', 'label' => 'Languages', 'type' => 'repeater', 'rules' => ['max:10'], 'fields' => [
                ['key' => 'code', 'label' => 'Code', 'type' => 'text', 'rules' => ['required', 'string', 'max:5']],
                ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:30']],
                ['key' => 'url', 'label' => 'URL', 'type' => 'text', 'rules' => ['required', 'string', 'max:500']],
            ]],
            ['key' => 'current', 'label' => 'Current Language Code', 'type' => 'text', 'default' => 'EN', 'rules' => ['nullable', 'string', 'max:5']],
        ];
    }
}
