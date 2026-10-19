<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\MediaFolder;
use App\Repositories\Contracts\MediaRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use enshrined\svgSanitize\Sanitizer as SvgSanitizer;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class MediaService
{
    private const DISK = 'public';

    public function __construct(protected MediaRepositoryInterface $media)
    {
    }

    /* ─────────────────────────── Uploads ─────────────────────────── */

    public function upload(UploadedFile $file, ?int $folderId, ?int $userId): Media
    {
        $type = $this->detectType($file);
        $path = mm_store_upload($file, $this->directoryFor());
        $this->sanitizeSvg($path, $file);
        $this->capOriginal($path, $file, $type);

        // Measure what was actually stored: capOriginal() may have downscaled it.
        [$width, $height] = $this->storedDimensions($path, $type, $file);

        /** @var Media $media */
        $media = $this->media->create([
            'folder_id' => $folderId,
            'disk' => self::DISK,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?? 'application/octet-stream',
            'type' => $type,
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'variants' => $this->generateVariants($path, $file, $type),
            'uploaded_by' => $userId,
        ]);

        return $media;
    }

    /**
     * WordPress-style replace: same media ID (references stay valid),
     * new file. Old file + variants removed, variants regenerated.
     */
    public function replace(Media $media, UploadedFile $file): Media
    {
        $newType = $this->detectType($file);

        if ($newType !== $media->type) {
            throw ValidationException::withMessages([
                'file' => "Replacement must be the same media type ({$media->type}).",
            ]);
        }

        $this->deletePhysicalFiles($media);

        $path = mm_store_upload($file, $this->directoryFor());
        $this->sanitizeSvg($path, $file);
        $this->capOriginal($path, $file, $newType);
        [$width, $height] = $this->storedDimensions($path, $newType, $file);

        return $this->media->update($media, [
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'variants' => $this->generateVariants($path, $file, $newType),
        ]);
    }

    public function updateMeta(Media $media, array $data): Media
    {
        $decorative = array_key_exists('is_decorative', $data) ? (bool) $data['is_decorative'] : $media->is_decorative;

        return $this->media->update($media, [
            'title' => $data['title'] ?? $media->title,
            // A decorative picture has no description (screen readers skip it).
            'alt' => $decorative ? null : (array_key_exists('alt', $data) ? $data['alt'] : $media->alt),
            'is_decorative' => $decorative,
            'folder_id' => array_key_exists('folder_id', $data) ? $data['folder_id'] : $media->folder_id,
        ]);
    }

    public function delete(Media $media): void
    {
        $this->deletePhysicalFiles($media);
        $this->media->delete($media);
    }

    /** @param list<int> $ids */
    public function bulkDelete(array $ids): int
    {
        $count = 0;

        foreach ($this->media->byIdsOrdered($ids) as $media) {
            $this->delete($media);
            $count++;
        }

        return $count;
    }

    /* ─────────────────────────── Folders ─────────────────────────── */

    public function createFolder(string $name, ?int $parentId): MediaFolder
    {
        $this->assertFolderNameFree($name, $parentId);

        return MediaFolder::create(['name' => $name, 'parent_id' => $parentId]);
    }

    public function renameFolder(MediaFolder $folder, string $name): MediaFolder
    {
        if ($name !== $folder->name) {
            $this->assertFolderNameFree($name, $folder->parent_id);
            $folder->update(['name' => $name]);
        }

        return $folder;
    }

    public function deleteFolder(MediaFolder $folder): void
    {
        if ($folder->media()->exists() || $folder->children()->exists()) {
            throw ValidationException::withMessages([
                'folder' => 'Folder is not empty. Move or delete its contents first.',
            ]);
        }

        $folder->delete();
    }

    /* ─────────────────────────── Internals ───────────────────────── */

    public function detectType(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        foreach ((array) config('gsm.media.allowed') as $type => $extensions) {
            if (in_array($ext, $extensions, true)) {
                return $type;
            }
        }

        throw ValidationException::withMessages([
            'file' => "File type .{$ext} is not allowed.",
        ]);
    }

    private function directoryFor(): string
    {
        return 'media/'.now()->format('Y/m');
    }

    /** @return array{0: ?int, 1: ?int} */
    private function imageDimensions(UploadedFile $file, string $type): array
    {
        if ($type !== 'image' || $file->getClientOriginalExtension() === 'svg') {
            return [null, null];
        }

        $info = @getimagesize($file->getRealPath());

        return $info === false ? [null, null] : [(int) $info[0], (int) $info[1]];
    }

    /**
     * Dimensions of the file as written to disk (post-cap). Falls back to the
     * uploaded file when the stored copy cannot be measured.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function storedDimensions(string $path, string $type, UploadedFile $file): array
    {
        if ($type !== 'image' || strtolower($file->getClientOriginalExtension()) === 'svg') {
            return [null, null];
        }

        $info = @getimagesizefromstring(Storage::disk(self::DISK)->get($path));

        return $info === false ? $this->imageDimensions($file, $type) : [(int) $info[0], (int) $info[1]];
    }

    /**
     * Downscale a stored original whose longest edge exceeds
     * gsm.media.max_dimension, rewriting the file in place.
     *
     * Browsers decode an image to roughly width x height x 4 bytes regardless
     * of the CSS size it is displayed at, so an 8000x1800 logo costs ~58 MB of
     * bitmap per copy. iOS Safari caps that per page and, once over budget,
     * shows the broken-image placeholder for EVERY image on the page.
     */
    /**
     * Strip scripts, event handlers and external references out of an
     * uploaded SVG before anything can serve it. An SVG is an XML document
     * the browser executes in OUR origin, so an unsanitised one is stored
     * XSS — every upload path routes through here.
     *
     * Fails closed: an SVG the sanitiser cannot parse is deleted and
     * rejected rather than stored as-is.
     */
    private function sanitizeSvg(string $path, UploadedFile $file): void
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'svg') {
            return;
        }

        $disk = Storage::disk(self::DISK);
        $sanitizer = new SvgSanitizer();
        $sanitizer->removeRemoteReferences(true);

        $clean = $sanitizer->sanitize((string) $disk->get($path));

        if ($clean === false || trim($clean) === '') {
            $disk->delete($path);

            throw ValidationException::withMessages([
                'file' => 'That SVG could not be parsed safely and was rejected.',
            ]);
        }

        $disk->put($path, $clean);
    }

    private function capOriginal(string $path, UploadedFile $file, string $type): void
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($type !== 'image' || in_array($ext, ['svg', 'ico'], true)) {
            return;
        }

        $max = (int) config('gsm.media.max_dimension', 2400);

        if ($max <= 0) {
            return;
        }

        $disk = Storage::disk(self::DISK);

        try {
            $image = (new ImageManager(new GdDriver()))->read($disk->get($path));
        } catch (\Throwable) {
            return; // unreadable/corrupt: leave the upload untouched
        }

        if ($image->width() <= $max && $image->height() <= $max) {
            return;
        }

        $image->scaleDown(width: $max, height: $max);
        $disk->put($path, $this->encodeVariant($image, $ext, (int) config('gsm.media.quality', 82)));
    }

    /**
     * Generate resized variants for raster images. SVG/ICO and non-images
     * get no variants (original is used directly).
     *
     * @return array<string, string>|null
     */
    private function generateVariants(string $path, UploadedFile $file, string $type): ?array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($type !== 'image' || in_array($ext, ['svg', 'ico'], true)) {
            return null;
        }

        $manager = new ImageManager(new GdDriver());
        $disk = Storage::disk(self::DISK);
        $variants = [];

        $basename = pathinfo($path, PATHINFO_FILENAME);
        $dir = 'media/conversions/'.now()->format('Y/m').'/'.$basename;

        $quality = (int) config('gsm.media.quality', 82);

        foreach ((array) config('gsm.media.variants') as $name => $maxWidth) {
            $image = $manager->read($disk->get($path));

            // Never upscale small originals.
            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $variantPath = "{$dir}/{$name}.{$ext}";
            $disk->put($variantPath, $this->encodeVariant($image, $ext, $quality));
            $variants[$name] = $variantPath;
        }

        // WebP copy at full size (the original is already capped): the same
        // picture as a smaller file, drawn through Media::web_url.
        if (config('gsm.media.webp', true) && ! in_array($ext, ['webp', 'gif'], true)) {
            $image = $manager->read($disk->get($path));
            $webpPath = "{$dir}/webp.webp";
            $disk->put($webpPath, $image->toWebp($quality)->toString());
            $variants['webp'] = $webpPath;
        }

        return $variants;
    }

    /** Lossy formats get the configured quality; PNG/GIF stay lossless. */
    private function encodeVariant($image, string $ext, int $quality): string
    {
        return match ($ext) {
            'jpg', 'jpeg' => $image->toJpeg($quality)->toString(),
            'webp' => $image->toWebp($quality)->toString(),
            default => $image->encodeByExtension($ext)->toString(),
        };
    }

    private function deletePhysicalFiles(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        if ($disk->exists($media->path)) {
            $disk->delete($media->path);
        }

        foreach ((array) ($media->variants ?? []) as $variantPath) {
            if ($disk->exists($variantPath)) {
                $disk->delete($variantPath);
            }
        }
    }

    private function assertFolderNameFree(string $name, ?int $parentId): void
    {
        $exists = MediaFolder::query()
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'A folder with this name already exists here.',
            ]);
        }
    }
}
