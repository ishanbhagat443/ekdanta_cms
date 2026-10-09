<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Collections\CollectionRegistry;
use App\Collections\CollectionType;
use App\Http\Controllers\Controller;
use App\Models\CollectionItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Collections: list (drag to reorder), add, edit, delete items of any collection type. */
class CollectionItemController extends Controller
{
    public function __construct(protected CollectionRegistry $registry)
    {
        $this->middleware('can:collections.manage');
    }

    public function index(string $collection): View
    {
        $type = $this->registry->find($collection);

        return view('admin.collections.index', [
            'type' => $type,
            'types' => $this->registry->all(),
            'items' => CollectionItem::query()->of($collection)->get(),
        ]);
    }

    public function create(string $collection): View
    {
        $type = $this->registry->find($collection);

        return view('admin.collections.form', ['type' => $type, 'item' => new CollectionItem([
            'collection' => $collection, 'status' => CollectionItem::STATUS_PUBLISHED, 'data' => $type->defaults(),
            'sort_order' => (int) CollectionItem::query()->where('collection', $collection)->max('sort_order') + 1,
        ])]);
    }

    public function store(Request $request, string $collection): RedirectResponse
    {
        $type = $this->registry->find($collection);
        $item = CollectionItem::create(['collection' => $collection] + $this->validated($request, $type));
        $this->syncSeo($request, $type, $item);

        return redirect()->route('admin.collections.edit', [$collection, $item])->with('success', $type->singular().' added.');
    }

    public function edit(string $collection, CollectionItem $item): View
    {
        abort_unless($item->collection === $collection, 404);

        return view('admin.collections.form', ['type' => $this->registry->find($collection), 'item' => $item]);
    }

    public function update(Request $request, string $collection, CollectionItem $item): RedirectResponse
    {
        abort_unless($item->collection === $collection, 404);
        $type = $this->registry->find($collection);
        $item->update($this->validated($request, $type, $item));
        $this->syncSeo($request, $type, $item);

        return redirect()->route('admin.collections.edit', [$collection, $item])->with('success', 'Saved.');
    }

    public function destroy(string $collection, CollectionItem $item): RedirectResponse
    {
        abort_unless($item->collection === $collection, 404);
        $item->delete(); // soft delete: restorable from the database

        return redirect()->route('admin.collections.index', $collection)->with('success', 'Deleted.');
    }

    /** Drag-and-drop order: ids in their new order. */
    public function reorder(Request $request, string $collection): JsonResponse
    {
        $this->registry->find($collection);
        $ids = $request->validate(['ids' => ['required', 'array', 'max:500'], 'ids.*' => ['integer']])['ids'];

        foreach ($ids as $position => $id) {
            CollectionItem::query()->where('collection', $collection)->whereKey($id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['saved' => true]);
    }

    private function validated(Request $request, CollectionType $type, ?CollectionItem $item = null): array
    {
        $data = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('collection_items', 'slug')->where('collection', $type->key())->ignore($item?->id)],
            'status' => ['required', Rule::in([CollectionItem::STATUS_DRAFT, CollectionItem::STATUS_PUBLISHED])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'data' => ['nullable', 'array'],
        ], $type->rules(), $type->template() !== null ? CollectionItem::seoRules() : []));

        $fields = collect($type->fields())->keyBy('key');
        $values = array_intersect_key((array) ($data['data'] ?? []), $fields->all());
        foreach ($fields as $key => $field) {
            if (($field['type'] ?? null) === 'toggle') {
                $values[$key] = (bool) ($values[$key] ?? false);
            }
            if (($field['type'] ?? null) === 'repeater') {
                // Rows left completely empty are dropped.
                $values[$key] = array_values(array_filter((array) ($values[$key] ?? []), fn ($row) => is_array($row) && array_filter($row, fn ($v) => $v !== null && $v !== '') !== []));
            }
        }

        return [
            'title' => $data['title'],
            'slug' => ($data['slug'] ?? null) ?: ($item?->slug ?? $this->uniqueSlug($type, $data['title'])),
            'status' => $data['status'],
            'sort_order' => (int) ($data['sort_order'] ?? $item?->sort_order ?? 0),
            'data' => $values,
        ];
    }

    private function syncSeo(Request $request, CollectionType $type, CollectionItem $item): void
    {
        if ($type->template() !== null) {
            $item->syncSeoMeta($request->only(array_keys(CollectionItem::seoRules())));
        }
    }

    private function uniqueSlug(CollectionType $type, string $title): string
    {
        $base = Str::slug($title) ?: 'item';
        $slug = $base;
        for ($i = 2; CollectionItem::withTrashed()->where('collection', $type->key())->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
