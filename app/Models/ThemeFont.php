<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ThemeFont extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'woff2_path',
        'woff_path',
        'ttf_path',
        'font_weight',
        'font_style',
    ];

    /** CSS font-family value used to reference this upload, e.g. "local:my-font". */
    public function cssValue(): string
    {
        return "local:{$this->slug}";
    }

    /** @font-face block for this upload — skipped entirely if no file exists. */
    public function fontFaceCss(): string
    {
        $sources = [];

        if ($this->woff2_path) {
            $sources[] = "url('".Storage::disk('public')->url($this->woff2_path)."') format('woff2')";
        }
        if ($this->woff_path) {
            $sources[] = "url('".Storage::disk('public')->url($this->woff_path)."') format('woff')";
        }
        if ($this->ttf_path) {
            $sources[] = "url('".Storage::disk('public')->url($this->ttf_path)."') format('truetype')";
        }

        if ($sources === []) {
            return '';
        }

        return "@font-face { font-family: '{$this->name}'; src: ".implode(', ', $sources)."; ".
            "font-weight: {$this->font_weight}; font-style: {$this->font_style}; font-display: swap; }";
    }
}
