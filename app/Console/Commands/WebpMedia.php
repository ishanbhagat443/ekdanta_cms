<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * Writes each image's WebP copy at its full size (the stored original, already
 * capped at gsm.media.max_dimension). Uploads do this themselves now; this
 * brings older images up to date. Pages draw the copy through Media::web_url.
 */
class WebpMedia extends Command
{
    protected $signature = 'mm:webp-media {--dry : List what would change without writing}';

    protected $description = 'Give every image a full-size WebP copy (smaller files for the same picture).';

    public function handle(): int
    {
        $manager = new ImageManager(new GdDriver());
        $disk = Storage::disk('public');
        $quality = (int) config('gsm.media.quality', 82);
        $done = 0;

        foreach (Media::where('type', 'image')->cursor() as $media) {
            $ext = strtolower(pathinfo($media->path, PATHINFO_EXTENSION));
            if (in_array($ext, ['svg', 'ico', 'gif', 'webp'], true) || ! $disk->exists($media->path)) {
                continue;
            }
            $webp = $media->variants['webp'] ?? 'media/conversions/'.dirname(substr($media->path, strlen('media/'))).'/'.pathinfo($media->path, PATHINFO_FILENAME).'/webp.webp';
            $this->line("  #{$media->id} {$media->path} → {$webp}");
            if ($this->option('dry')) {
                continue;
            }
            try {
                $disk->put($webp, $manager->read($disk->get($media->path))->toWebp($quality)->toString());
            } catch (\Throwable) {
                $this->warn('  skipped (unreadable)');

                continue;
            }
            $media->forceFill(['variants' => array_merge((array) $media->variants, ['webp' => $webp])])->save();
            $done++;
        }

        $this->info("{$done} WebP copies written.");

        return self::SUCCESS;
    }
}
