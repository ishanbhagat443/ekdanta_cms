<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A reusable, versioned Section template. Deliberately mirrors Section's
 * device/style JSON shape (dataFor/styleValue) so the entire existing
 * SectionType field schema, validation, and Blade rendering pipeline work
 * against a GlobalBlock exactly as they do against a Section — this is a
 * template library layered on the existing engine, not a second one.
 */
class GlobalBlock extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'type',
        'preview_media_id',
        'description',
        'status',
        'version',
        'created_by',
        'updated_by',
        'data_desktop',
        'data_tablet',
        'data_mobile',
        'style',
        'is_favorite',
        'use_count',
        'last_used_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'data_desktop' => 'array',
        'data_tablet' => 'array',
        'data_mobile' => 'array',
        'style' => 'array',
        'is_favorite' => 'boolean',
        'use_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(GlobalBlockCategory::class, 'category_id');
    }

    public function previewMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'preview_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(GlobalBlockVersion::class)->orderByDesc('version');
    }

    /** Every Section instance currently linked to this master. */
    public function instances(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Where this block actually appears, as a list a person can read.
     *
     * "Edit once, update everywhere" is only safe if you can see what
     * everywhere means before you press save.
     *
     * @return \Illuminate\Support\Collection<int, array{label: string, url: ?string, synced: bool}>
     */
    public function usage(): \Illuminate\Support\Collection
    {
        return $this->instances()
            ->with('sectionable')
            ->get()
            ->map(function (Section $section): ?array {
                $owner = $section->sectionable;

                if ($owner instanceof Page) {
                    return [
                        'label' => $owner->title,
                        'url' => route('admin.pages.edit', $owner),
                        'synced' => (bool) $section->is_synced,
                    ];
                }

                if ($owner instanceof Layout) {
                    return [
                        'label' => ucfirst($owner->area).' (site-wide)',
                        'url' => $owner->area === 'footer'
                            ? route('admin.footer.edit')
                            : route('admin.header.edit'),
                        'synced' => (bool) $section->is_synced,
                    ];
                }

                // An instance whose owner has been deleted: still worth
                // counting, but there is nowhere to send the user.
                return ['label' => 'Deleted page', 'url' => null, 'synced' => (bool) $section->is_synced];
            })
            ->filter()
            ->sortBy('label')
            ->values();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeFavorites(Builder $query): Builder
    {
        return $query->where('is_favorite', true);
    }

    /** Same inheritance rule as Section::dataFor() — tablet/mobile are sparse overrides. */
    public function dataFor(string $device): array
    {
        $desktop = (array) ($this->data_desktop ?? []);

        if ($device === 'desktop') {
            return $desktop;
        }

        $overrides = (array) ($this->{'data_'.$device} ?? []);
        $overrides = array_filter($overrides, fn ($v) => $v !== null && $v !== '');

        return array_replace($desktop, $overrides);
    }

    public function styleValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->style, $key, $default);
    }
}
