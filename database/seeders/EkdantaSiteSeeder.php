<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Collections\CollectionRegistry;
use App\Models\CollectionItem;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Services\MediaService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * The Ekdanta site's content, from the design files. Safe to run again:
 * everything is found by its stable key or slug and left alone if it is
 * already there. Run with `php artisan ekdanta:seed`.
 */
class EkdantaSiteSeeder extends Seeder
{
    private const ASSETS = 'docs/ekdanta-design/assets/';

    /** The two client-logo strips: 270 × 96 px per logo, in this order (design: LOGO_A, LOGO_B). */
    public const LOGOS = [
        'client-logos-strip-a.jpg' => ['Trilok Infratech', 'Trilok Precast', 'Global Silk Mills', 'Yantra Design', 'Amplus', 'Shree Ambica Automotive', 'Aditya Laces', 'Heaven Solar', 'Sagar Enterprise', 'The Zula House', 'Cruisemax Sports', 'Syasi Designers', 'M.M. Fashion', 'Fab De Print', 'Belvae', 'Qbits', 'Euro', 'Jalpooree', 'Raj Wafers', 'Jenish Spices', 'Maharana', 'Vidhi Ghee', 'Gaukrish Ghee', 'SRK Dairy'],
        'client-logos-strip-b.jpg' => ['Suzuki dealership', 'Samsung authorised distributor', 'Dharmaraj Suzuki Citylight', 'Vijay Mobile', '09 Mobile Gallery', 'Her & He Cosmetics', 'Karvi Realtors', 'Nainvish', 'Zorko', 'Chapter.1', 'ValleyRides', 'Slimiphy', 'Zeronine Retail ERP', 'Zeronine Tax Consultancy', 'MoneyMatters AM', 'Jiya Financial Services', 'Karma Realtors', 'Signs360', 'GenZ Lifestyle', 'Ruparel'],
    ];

    public function run(): void
    {
        $this->media();
        $this->collections();
        $this->business();
    }

    /** Settings → Business from the design; only values that are still empty are filled. */
    public function business(): void
    {
        $address = 'B-402–403, Bhatar Trade Center, near Bhatar Char Rasta, Uma Bhawan Society, Bhatar, Surat, Gujarat 395017';
        $values = [
            'company_name' => 'Ekdanta Branding and Marketing',
            'email' => 'info@ekdanta.in',
            'country_code' => '91',
            'phones' => [
                ['label' => 'All services', 'number' => '90994 17987', 'menu_label' => 'Call', 'whatsapp' => true],
                ['label' => 'All services', 'number' => '90994 17949', 'menu_label' => '', 'whatsapp' => false],
                ['label' => 'Sales', 'number' => '63544 33194', 'menu_label' => 'Sales', 'whatsapp' => false],
            ],
            'offices' => [
                ['name' => 'Surat', 'address' => $address, 'directions_url' => 'https://share.google/EAOxrObclZeY4QkQR', 'latitude' => '', 'longitude' => '', 'hours' => '[Add office hours]'],
                ['name' => 'Ahmedabad', 'address' => '[Add Ahmedabad address]', 'directions_url' => '', 'latitude' => '', 'longitude' => '', 'hours' => ''],
            ],
        ];

        $missing = array_filter($values, fn ($value, string $key) => blank(setting("business.{$key}")), ARRAY_FILTER_USE_BOTH);
        if ($missing !== []) {
            app(SettingsService::class)->updateGroup('business', $missing);
        }
    }

    /** Case studies, services, clients, reels, FAQs and team roles (database/data/ekdanta-collections.php). */
    public function collections(): void
    {
        $registry = app(CollectionRegistry::class);

        foreach (require database_path('data/ekdanta-collections.php') as $collection => $rows) {
            $defaults = $registry->find($collection)->defaults();
            foreach ($rows as $order => [$slug, $title, $data]) {
                if (CollectionItem::withTrashed()->where('collection', $collection)->where('slug', $slug)->exists()) {
                    continue;
                }
                CollectionItem::create([
                    'collection' => $collection, 'slug' => $slug, 'title' => $title, 'status' => CollectionItem::STATUS_PUBLISHED,
                    'sort_order' => $order + 1, 'data' => array_replace($defaults, $data),
                ]);
            }
        }
    }

    /** Design assets into the Media Library as "ekdanta/<file name>", each logo as "ekdanta/client-<name>". */
    public function media(): void
    {
        $folder = MediaFolder::query()->firstOrCreate(['name' => 'Ekdanta', 'parent_id' => null]);

        foreach (glob(base_path(self::ASSETS.'*.{png,jpg}'), GLOB_BRACE) as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (! str_starts_with($name, 'client-logos-strip')) {
                $this->store($file, 'ekdanta/'.$name, $folder->id);
            }
        }

        foreach (self::LOGOS as $strip => $names) {
            $source = imagecreatefromjpeg(base_path(self::ASSETS.$strip));
            foreach ($names as $i => $name) {
                $key = 'ekdanta/client-'.Str::slug($name);
                if (Media::query()->where('key', $key)->exists()) {
                    continue;
                }
                $logo = imagecrop($source, ['x' => $i * 270, 'y' => 0, 'width' => 270, 'height' => 96]);
                $path = tempnam(sys_get_temp_dir(), 'ek').'.jpg';
                imagejpeg($logo, $path, 92);
                imagedestroy($logo);
                $this->store($path, $key, $folder->id, Str::slug($name).'.jpg', $name);
                @unlink($path);
            }
            imagedestroy($source);
        }
    }

    private function store(string $path, string $key, int $folderId, ?string $filename = null, ?string $alt = null): void
    {
        if (Media::query()->where('key', $key)->exists()) {
            return;
        }

        $upload = new UploadedFile($path, $filename ?? basename($path), mime_content_type($path) ?: null, null, true);
        $media = app(MediaService::class)->upload($upload, $folderId, null);
        $media->forceFill(['key' => $key, 'alt' => $alt])->save();
    }
}
