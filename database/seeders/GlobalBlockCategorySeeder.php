<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GlobalBlockCategory;
use Illuminate\Database\Seeder;

class GlobalBlockCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_values($this->categories()) as $position => $name) {
            GlobalBlockCategory::query()->firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                ['name' => $name, 'sort_order' => $position],
            );
        }
    }

    /** @return list<string> */
    private function categories(): array
    {
        return [
            'Hero', 'Marketing', 'About', 'Content', 'Blogs',
            'Forms', 'Footer', 'Header', 'Widgets', 'Custom',
        ];
    }
}
