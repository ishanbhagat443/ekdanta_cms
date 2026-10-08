<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * Repairs media that was uploaded before the upload-time size cap existed.
 *
 * A browser decodes an image to roughly width x height x 4 bytes no matter how
 * small it is displayed, so a full-resolution 8000x1800 logo costs ~58 MB of
 * bitmap. iOS Safari enforces a per-page image-memory budget and, once it is
 * exceeded, shows the broken-image placeholder for EVERY image on the page —
 * while desktop Chrome renders the same page fine.
 *
 * Rewrites oversized originals in place, so every reference (media id, path,
 * URL) keeps working. Run with --dry to preview.
 */
class DownscaleMedia extends Command
{
    protected $signature = 'mm:downscale-media
                            {--max= : Longest edge in px (default: config gsm.media.max_dimension)}
                            {--dry : List what would change without writing}';

    protected $description = 'Downscale oversized media originals that break image rendering on iOS Safari.';

    public function handle(): int
    {
        $max = (int) ($this->option('max') ?: config('gsm.media.max_dimension', 2400));
        $dry = (bool) $this->option('dry');

        if ($max <= 0) {
            $this->error('--max must be greater than zero.');

            return self::FAILURE;
        }

        $manager = new ImageManager(new GdDriver());
        $disk = Storage::disk('public');
        $quality = (int) config('gsm.media.quality', 82);

        $scanned = 0;
        $changed = 0;
        $savedBytes = 0;

        foreach (Media::where('type', 'image')->cursor() as $media) {
            $ext = strtolower(pathinfo($media->path, PATHINFO_EXTENSION));

            if (in_array($ext, ['svg', 'ico'], true) || ! $disk->exists($media->path)) {
                continue;
            }

            $scanned++;
            $before = $disk->size($media->path);

            try {
                $image = $manager->read($disk->get($media->path));
            } catch (\Throwable $e) {
                $this->warn("  skipped (unreadable): {$media->path}");

                continue;
            }

            if ($image->width() <= $max && $image->height() <= $max) {
                continue;
            }

            $mp = round(($image->width() * $image->height()) / 1_000_000, 1);
            $this->line(sprintf(
                '  #%d %s  %dx%d (%s MP, %d KB)',
                $media->id, $media->path, $image->width(), $image->height(), $mp, $before / 1024
            ));

            if ($dry) {
                $changed++;

                continue;
            }

            $image->scaleDown(width: $max, height: $max);

            $encoded = match ($ext) {
                'jpg', 'jpeg' => $image->toJpeg($quality)->toString(),
                'webp' => $image->toWebp($quality)->toString(),
                default => $image->encodeByExtension($ext)->toString(),
            };

            $disk->put($media->path, $encoded);
            $media->update([
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => strlen($encoded),
            ]);

            $savedBytes += max(0, $before - strlen($encoded));
            $changed++;
            $this->info(sprintf('     -> %dx%d (%d KB)', $image->width(), $image->height(), strlen($encoded) / 1024));
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d of %d image(s) over %dpx.%s',
            $dry ? 'Would downscale' : 'Downscaled',
            $changed,
            $scanned,
            $max,
            $dry ? ' Re-run without --dry to apply.' : sprintf(' Saved %.1f MB.', $savedBytes / 1_048_576)
        ));

        return self::SUCCESS;
    }
}
