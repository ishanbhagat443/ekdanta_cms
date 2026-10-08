<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.manage') ?? false;
    }

    public function rules(): array
    {
        $allowed = collect((array) config('gsm.media.allowed'))->flatten()->implode(',');

        return [
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => [
                'required',
                'file',
                'mimes:'.$allowed,
                'max:'.config('gsm.media.max_upload_kb'),
            ],
            'folder_id' => ['nullable', 'integer', Rule::exists('media_folders', 'id')],
        ];
    }
}
