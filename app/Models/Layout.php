<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Layout extends Model
{
    public const AREA_HEADER = 'header';

    public const AREA_TOPBAR = 'topbar';

    public const AREA_FOOTER = 'footer';

    protected $fillable = [
        'area',
        'settings',
        'custom_css',
        'custom_js',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function sections(): MorphMany
    {
        return $this->morphMany(Section::class, 'sectionable')->orderBy('sort_order');
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
