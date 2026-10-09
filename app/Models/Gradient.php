<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Global Gradient Library entry. Unlimited color stops, each with color,
 * opacity (0–100 %) and position (0–100 %). Reused by buttons, sections,
 * theme surfaces — anywhere via var(--gsm-gradient-{slug}).
 */
class Gradient extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'type', 'angle', 'position', 'stops', 'is_active'];

    protected $casts = [
        'stops' => 'array',
        'angle' => 'integer',
        'is_active' => 'boolean',
    ];

    /** CSS custom property name this gradient is published under. */
    public function cssVar(): string
    {
        return '--gsm-gradient-'.$this->slug;
    }

    /**
     * Valid CSS gradient value, e.g.
     * linear-gradient(90deg, #981E63 2%, #B14D86 19%, #F5791A 50%, #6b7280 100%).
     */
    public function toCss(): string
    {
        $stops = collect($this->stops ?? [])
            ->filter(fn ($s) => ! empty($s['color']))
            ->sortBy(fn ($s) => (float) ($s['position'] ?? 0))
            ->map(fn ($s) => self::stopColor($s).' '.round((float) ($s['position'] ?? 0), 2).'%')
            ->implode(', ');

        if ($stops === '') {
            return 'none';
        }

        $position = trim((string) ($this->position ?? '')) ?: 'center';

        return match ($this->type) {
            'radial' => "radial-gradient(circle at {$position}, {$stops})",
            'conic' => "conic-gradient(from {$this->angle}deg at {$position}, {$stops})",
            default => "linear-gradient({$this->angle}deg, {$stops})",
        };
    }

    /** Stop color honouring opacity: plain hex at 100 %, rgba() below. */
    public static function stopColor(array $stop): string
    {
        $color = (string) ($stop['color'] ?? '#000000');
        $opacity = $stop['opacity'] ?? 100;
        $opacity = max(0, min(100, (float) $opacity));

        if ($opacity >= 100 || ! preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) {
            return $color;
        }

        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

        return 'rgba('.$r.','.$g.','.$b.','.rtrim(rtrim(number_format($opacity / 100, 2), '0'), '.').')';
    }
}
