<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ThemePreset;
use Illuminate\Database\Seeder;

/**
 * Factory-default snapshot so "Reset Theme" always has a documented,
 * restorable baseline in the Backups list. Payload is empty — restoring it
 * calls the same code path as resetToDefault() (empty theme_settings table
 * means every value falls back to config/theme.php).
 */
class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        ThemePreset::query()->firstOrCreate(
            ['slug' => 'factory-default'],
            ['name' => 'Factory Default', 'payload' => [], 'is_system' => true],
        );
    }
}
