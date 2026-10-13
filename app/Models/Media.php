<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'key',
        'folder_id',
        'disk',
        'path',
        'filename',
        'mime',
        'type',
        'size',
        'width',
        'height',
        'alt',
        'title',
        'variants',
        'uploaded_by',
    ];

    protected $casts = [
        'variants' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = [
        'url',
        'thumb_url',
        'human_size',
    ];

    /** A media reference from a section or collection field: an id, or a stable key ("ekdanta/logo-colour"). */
    public static function isKey(mixed $ref): bool
    {
        return is_string($ref) && preg_match('#^[a-z0-9-]+(/[a-z0-9._-]+)+$#', $ref) === 1;
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** The full-size WebP copy when there is one (smaller file, same picture), else the original. */
    public function getWebUrlAttribute(): string
    {
        return isset($this->variants['webp']) ? Storage::disk($this->disk)->url($this->variants['webp']) : $this->url;
    }

    /** Thumbnail for grids: variant if generated, original for images, null for files. */
    public function getThumbUrlAttribute(): ?string
    {
        $variant = $this->variants['thumb'] ?? null;

        if ($variant !== null) {
            return Storage::disk($this->disk)->url($variant);
        }

        return $this->type === 'image' ? $this->url : null;
    }

    public function variantUrl(string $name): ?string
    {
        $path = $this->variants[$name] ?? null;

        return $path !== null ? Storage::disk($this->disk)->url($path) : null;
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (float) $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024 || $unit === 'GB') {
                return round($bytes, $unit === 'B' ? 0 : 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return (string) $this->size;
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }
}
