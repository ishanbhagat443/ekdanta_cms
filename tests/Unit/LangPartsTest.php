<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LangPartsTest extends TestCase
{
    public function test_devanagari_text_is_marked_hindi_and_markup_is_left_alone(): void
    {
        $html = '<h2 aria-label="हमारा काम">Aapki branding ke विघ्नहर्ता, कर्ता-धर्ता है।</h2>'
            .'<script type="application/json">{"t":"शुभ"}</script><p>English only</p>';

        $this->assertSame(
            '<h2 aria-label="हमारा काम">Aapki branding ke <span lang="hi">विघ्नहर्ता</span>, <span lang="hi">कर्ता-धर्ता है।</span></h2>'
            .'<script type="application/json">{"t":"शुभ"}</script><p>English only</p>',
            lang_parts($html),
        );
        $this->assertSame('<p>No Hindi</p>', lang_parts('<p>No Hindi</p>'));
    }
}
