<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('blog.manage') ?? false;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('posts', 'slug')->ignore($this->route('post'))],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string', 'max:120000'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery_ids' => ['nullable', 'string', 'max:2000', 'regex:/^\d+(,\d+)*$/'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'is_featured' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled'],
            'blog_category_ids' => ['sometimes', 'array'],
            'blog_category_ids.*' => ['integer', 'exists:blog_categories,id'],
            'tags_csv' => ['nullable', 'string', 'max:1000'],
            'related_ids' => ['sometimes', 'array', 'max:8'],
            'related_ids.*' => ['integer', 'exists:posts,id'],
        ], Post::seoRules());
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('status') === Post::STATUS_SCHEDULED
                && $this->filled('published_at')
                && strtotime((string) $this->input('published_at')) <= time()) {
                $v->errors()->add('published_at', 'Scheduled time must be in the future.');
            }
        });
    }
}
