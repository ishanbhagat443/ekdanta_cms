<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

class CopyrightSection extends SectionType
{
    public function key(): string
    {
        return 'copyright';
    }

    public function label(): string
    {
        return 'Copyright Bar';
    }

    public function icon(): string
    {
        return 'far fa-copyright';
    }

    public function allowedAreas(): array
    {
        return ['footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'text', 'label' => 'Copyright Text', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:300'], 'hint' => '{year} is replaced with the current year.'],
        ];
    }
}
