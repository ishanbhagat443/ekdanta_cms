<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SectionTypes\SectionType;
use App\Services\SectionTypes\SectionTypeRegistry;
use Database\Seeders\EkdantaSiteSeeder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

/**
 * Help → Ekdanta guide: docs/ekdanta-admin-guide.md, then the pages in
 * build order and every Ekdanta section's fields and default motion, read
 * from the code so the reference never drifts from what the CMS does.
 */
class HelpController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:pages.manage');
    }

    public function ekdanta(SectionTypeRegistry $registry): View
    {
        $types = $registry->all()->filter(fn (SectionType $type) => $type->group() === 'Ekdanta')->keyBy(fn (SectionType $type) => $type->key());

        return view('admin.help.ekdanta', [
            'guide' => Str::markdown((string) file_get_contents(base_path('docs/ekdanta-admin-guide.md')), ['html_input' => 'escape', 'allow_unsafe_links' => false]),
            'pages' => EkdantaSiteSeeder::PAGES,
            'types' => $types,
        ]);
    }
}
