<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Page template blocks
|--------------------------------------------------------------------------
| Screens rendered by a bespoke blade rather than the drag-and-drop Page
| Builder declare their editable BLOCKS here, keyed by page slug.
|
| Every block is stored in the page's `settings.blocks` JSON, so one page row
| drives every screen of that kind.
|
| Adding a field here is all that is needed — the admin UI renders it, the
| controller saves it and page_block() reads it. No migration, no new view.
|
| Field types: text · textarea · toggle · number
|
| Shape:
| 'page-slug' => [
|     'label' => 'Page Label',
|     'blocks' => [
|         'block_key' => [
|             'label' => 'Block Label',
|             'fields' => [
|                 'field_key' => ['label' => 'Field', 'type' => 'text', 'default' => ''],
|             ],
|         ],
|     ],
| ],
*/

return [
    // No template pages yet — add them as bespoke screens are built.
];
