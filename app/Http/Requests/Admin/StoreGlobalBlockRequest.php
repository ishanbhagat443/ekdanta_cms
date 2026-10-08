<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;

class StoreGlobalBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('blocks.manage') ?? false;
    }

    public function rules(): array
    {
        $types = implode(',', app(SectionTypeRegistry::class)->all()->map(fn ($t) => $t->key())->all());

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'category_id' => ['nullable', 'integer', 'exists:global_block_categories,id'],
            'type' => ['required', 'string', 'in:'.$types],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:active,draft,archived'],
        ];
    }
}
