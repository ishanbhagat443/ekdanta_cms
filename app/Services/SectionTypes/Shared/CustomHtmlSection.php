<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Shared;

use App\Services\SectionTypes\SectionType;

class CustomHtmlSection extends SectionType
{
    public function key(): string
    {
        return 'custom-html';
    }

    public function label(): string
    {
        return 'Custom HTML';
    }

    public function icon(): string
    {
        return 'fas fa-code';
    }

    public function allowedAreas(): array
    {
        return ['header', 'topbar', 'footer', 'page'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'html', 'label' => 'HTML', 'type' => 'code', 'device' => true, 'rules' => ['nullable', 'string', 'max:20000'], 'hint' => 'Rendered as-is. Admin-only feature — script tags allowed.'],
        ];
    }
}
