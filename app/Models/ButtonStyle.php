<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Button Builder style. The whole definition lives in `config` (JSON):
 *
 *  typography: family, size, weight, letter_spacing, transform, line_height
 *  padding:    {desktop|tablet|mobile: {top,right,bottom,left}}
 *  border:     width, style, color, radius {tl,tr,br,bl} — free CSS values
 *  background: mode (solid|gradient|image|video|transparent), color,
 *              gradient_slug (Gradient Library), media_id, video_media_id,
 *              text_color
 *  hover:      mode/color/gradient_slug/text_color, border overrides,
 *              radius overrides, shadow override
 *  animation:  hover_effect (none|scale|ripple|glow|lift|slide|pulse),
 *              duration (ms)
 *  icon:       left, right (icon classes), gap (px)
 *  shadows:    [{x,y,blur,spread,color,opacity,inset}, …] — unlimited
 */
class ButtonStyle extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'config'];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /** CSS class this style is published under. */
    public function cssClass(): string
    {
        return 'gsm-btn-'.$this->slug;
    }

    public function get(string $dottedKey, mixed $default = null): mixed
    {
        return data_get($this->config, $dottedKey, $default);
    }

    /** Sensible starting config for the Button Builder form. */
    public static function defaults(): array
    {
        return [
            'typography' => ['family' => 'role:buttons', 'size' => 15, 'weight' => '600', 'letter_spacing' => 0.5, 'transform' => 'none', 'line_height' => 1.4],
            'padding' => [
                'desktop' => ['top' => 12, 'right' => 28, 'bottom' => 12, 'left' => 28],
                'tablet' => ['top' => 11, 'right' => 24, 'bottom' => 11, 'left' => 24],
                'mobile' => ['top' => 10, 'right' => 20, 'bottom' => 10, 'left' => 20],
            ],
            'border' => ['width' => 0, 'style' => 'solid', 'color' => '#ffffff', 'radius' => ['tl' => '999px', 'tr' => '999px', 'br' => '999px', 'bl' => '999px']],
            'background' => ['mode' => 'solid', 'color' => '#111827', 'gradient_slug' => null, 'media_id' => null, 'video_media_id' => null, 'text_color' => '#ffffff'],
            'hover' => ['mode' => null, 'color' => null, 'gradient_slug' => null, 'text_color' => null, 'border' => [], 'radius' => [], 'shadows' => []],
            'animation' => ['hover_effect' => 'lift', 'duration' => 250],
            'icon' => ['left' => null, 'right' => null, 'gap' => 8],
            'shadows' => [],
        ];
    }
}
