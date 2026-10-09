<?php

declare(strict_types=1);

use App\Support\SectionMotion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Older animation settings become Motion tab tracks: a section's
 * style.anim (per device) becomes an "enter" track, and the Hero Banner's
 * "Content Entrance Animation" becomes its content track. Pages look the same.
 *
 * Safe both ways: the old values stay where they were (only style.motion
 * is added), and down() removes style.motion only where nobody has edited
 * it since. Sections and saved blocks alike.
 */
return new class extends Migration
{
    private const TABLES = ['sections', 'global_blocks'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            DB::table($table)->orderBy('id')->each(function (object $row) use ($table): void {
                $style = (array) json_decode((string) $row->style, true);

                if (isset($style['motion'])) {
                    return;
                }

                $motion = SectionMotion::fromLegacy($style, (array) json_decode((string) $row->data_desktop, true), (string) $row->type);

                if ($motion !== []) {
                    DB::table($table)->where('id', $row->id)->update(['style' => json_encode($style + ['motion' => $motion])]);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            DB::table($table)->orderBy('id')->each(function (object $row) use ($table): void {
                $style = (array) json_decode((string) $row->style, true);

                if (! isset($style['motion'])) {
                    return;
                }

                $unchanged = $style['motion'] == SectionMotion::fromLegacy($style, (array) json_decode((string) $row->data_desktop, true), (string) $row->type);

                if ($unchanged) {
                    unset($style['motion']);
                    DB::table($table)->where('id', $row->id)->update(['style' => json_encode($style)]);
                }
            });
        }
    }
};
