<?php

declare(strict_types=1);

namespace App\Support;

/**
 * CSV export helpers.
 *
 * Every exported row is visitor-supplied text (enquiry names, form answers,
 * subscriber emails). Excel, LibreOffice and Google Sheets treat a cell that
 * starts with = + - @ as a FORMULA, so `=1+2` computes and worse forms —
 * `=HYPERLINK(...)`, `=cmd|'/c calc'!A1` — can exfiltrate data or prompt the
 * spreadsheet to run a command. Neutralise on the way out, never on the way
 * in: the stored value must stay exactly what the visitor typed so the admin
 * screens show the truth.
 */
final class Csv
{
    /** Leading characters a spreadsheet may interpret as the start of a formula. */
    private const DANGEROUS = ['=', '+', '-', '@', "\t", "\r"];

    /** Neutralise one cell. */
    public static function cell(mixed $value): mixed
    {
        // Real numbers/bools/null are never formulas — leave their type alone
        // so numeric columns still sort as numbers in the spreadsheet.
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        $value = (string) $value;

        if ($value === '') {
            return $value;
        }

        return in_array($value[0], self::DANGEROUS, true) ? "'".$value : $value;
    }

    /**
     * Neutralise a whole row.
     *
     * @param  array<int|string, mixed>  $row
     * @return array<int|string, mixed>
     */
    public static function row(array $row): array
    {
        return array_map(static fn (mixed $cell): mixed => self::cell($cell), $row);
    }

    /**
     * fputcsv() with every cell neutralised. Drop-in replacement.
     *
     * @param  resource  $handle
     * @param  array<int|string, mixed>  $row
     */
    public static function put($handle, array $row): void
    {
        fputcsv($handle, self::row($row));
    }
}
