<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Neutral starter defaults. Only inserts missing keys — never
     * overwrites admin-edited values.
     */
    public function run(): void
    {
        $defaults = [
            'business' => [
                'company_name' => config('app.name'),
            ],
            'seo' => [
                'meta_title' => '{page} | '.config('app.name'),
                'robots' => "User-agent: *\nAllow: /\nDisallow: /cms",
            ],
        ];

        foreach ($defaults as $group => $values) {
            $schema = (array) config("settings.{$group}.fields", []);

            foreach ($values as $key => $value) {
                Setting::query()->firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => $schema[$key]['type'] ?? 'text']
                );
            }
        }

        app(SettingsService::class)->flushCache();
    }
}
