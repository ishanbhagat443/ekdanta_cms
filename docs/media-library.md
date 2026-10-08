# Media Library

WordPress-style media manager (`/admin/media`, permission `media.manage`).

## Storage

- `media_folders` — nested folder tree (`parent_id` self-reference).
- `media` — one row per uploaded file: disk, path, filename, mime, `type`
  (image/video/pdf/document), size, `variants` JSON, `uploaded_by`.

## Upload pipeline (`MediaService`)

1. Validate extension against `config/gsm.php` media type map.
2. Store original on the `public` disk.
3. For raster images only (skips SVG/ICO): generate resized variants
   (`thumb` 400px, `card` 800px — widths configurable in
   `config('gsm.media.variants')`), never upscaling originals smaller than
   the target width.
4. Variants are quality-encoded (`config('gsm.media.quality')`, default 82)
   via Intervention Image `toJpeg()`/`toWebp()` — not just resized, actually
   compressed.
5. An additional `.webp` copy of the largest variant is generated for every
   raster upload (`config('gsm.media.webp')`, default on) — modern-format
   delivery without the admin doing anything.
6. Replace ("WP-style replace") swaps the file in place, **keeping the same
   Media row ID** — every place that references `media_id` (section
   images, menu icons, page banners, etc.) automatically picks up the
   new file without re-selecting it anywhere.

## Picker component

Two levels of reuse:
- **Full picker** (`.gsm-media-picker` + `media-picker.js`) — grid browser
  in a modal iframe, supports multi-select, shows thumbnail previews of the
  current selection.
- **Lightweight field picker** (`.gsm-bf-media-pick` buttons +
  `media-picker-field.js`) — single-ID input + a button that opens the same
  Media Library in picker mode (`?picker=1`, bare layout, no admin chrome)
  and writes the chosen ID back into the target input. This is what every
  builder form (Header/Footer/Page/Menu/Theme) uses — it's why the picker
  script's `builderRoot()` function has a growing list of builder root
  element IDs it recognizes for the "picker src" data attribute.

## CKEditor integration

`admin/media/ckeditor` endpoint returns a plain `{url}` JSON response for
CKEditor's image-upload adapter — rich-text fields (blog body, product
descriptions) use the same underlying `MediaService` upload path, so
images inserted via the editor still get variants/webp generated.

## Frontend consumption

`Media::getUrlAttribute()` / `variantUrl($name)` accessors are used
everywhere an image renders — all frontend `<img>` tags carry
`loading="lazy" decoding="async"` (added in the optimization pass) except
the header logo (always above the fold, intentionally excluded).
