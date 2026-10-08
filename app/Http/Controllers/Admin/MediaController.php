<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Repositories\Contracts\MediaRepositoryInterface;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        protected MediaRepositoryInterface $media,
        protected MediaService $mediaService,
    ) {
        $this->middleware('can:media.manage');
    }

    /**
     * Library page shell. ?picker=1 renders the bare picker layout used
     * inside the reuse-modal iframe (single/multiple selection).
     */
    public function index(Request $request): View
    {
        return view('admin.media.index', [
            'picker' => $request->boolean('picker'),
            'multiple' => $request->boolean('multiple'),
            'ckeditor' => $request->string('CKEditorFuncNum')->toString(),
        ]);
    }

    /** AJAX: folder tree + paginated media for current view. */
    public function browse(Request $request): JsonResponse
    {
        $folderId = $request->filled('folder') ? (int) $request->input('folder') : null;
        $search = $request->string('search')->toString() ?: null;
        $type = $request->string('type')->toString() ?: null;

        // A search or type filter looks across ALL folders, WordPress-style.
        $anyFolder = $search !== null || $type !== null;

        $folder = $folderId !== null ? MediaFolder::query()->findOrFail($folderId) : null;

        return response()->json([
            'folders' => MediaFolder::query()
                ->where('parent_id', $folderId)
                ->orderBy('name')
                ->withCount(['media', 'children'])
                ->get(['id', 'name', 'parent_id']),
            'trail' => $folder !== null
                ? array_map(fn ($f) => ['id' => $f->id, 'name' => $f->name], $folder->trail())
                : [],
            'media' => $this->media->browse(
                $folderId,
                $search,
                $type,
                $anyFolder,
                (int) config('gsm.media.per_page')
            ),
        ]);
    }

    /** AJAX: multi-file upload (also the drag-drop target). */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $uploaded = [];

        foreach ($request->file('files', []) as $file) {
            $uploaded[] = $this->mediaService->upload(
                $file,
                $request->validated('folder_id'),
                $request->user()->id
            );
        }

        return response()->json(['media' => $uploaded], 201);
    }

    /** AJAX: update title / alt / move to folder. */
    public function update(Request $request, Media $medium): JsonResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:191'],
            'alt' => ['nullable', 'string', 'max:191'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        return response()->json(['media' => $this->mediaService->updateMeta($medium, $data)]);
    }

    /** AJAX: replace the physical file, keep the ID and references. */
    public function replace(Request $request, Media $medium): JsonResponse
    {
        $allowed = collect((array) config('gsm.media.allowed'))->flatten()->implode(',');

        $request->validate([
            'file' => ['required', 'file', 'mimes:'.$allowed, 'max:'.config('gsm.media.max_upload_kb')],
        ]);

        return response()->json([
            'media' => $this->mediaService->replace($medium, $request->file('file')),
        ]);
    }

    public function destroy(Media $medium): JsonResponse
    {
        $this->mediaService->delete($medium);

        return response()->json(['deleted' => true]);
    }

    /** AJAX: bulk delete selected items. */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        return response()->json(['deleted' => $this->mediaService->bulkDelete($data['ids'])]);
    }

    /**
     * CKEditor upload adapter endpoint. CKEditor 5 "simple upload" posts a
     * single file field named "upload" and expects {url: ...} back.
     */
    public function ckeditorUpload(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => [
                'required',
                'file',
                'mimes:'.implode(',', (array) config('gsm.media.allowed.image')),
                'max:'.config('gsm.media.max_upload_kb'),
            ],
        ]);

        $media = $this->mediaService->upload($request->file('upload'), null, $request->user()->id);

        return response()->json([
            'uploaded' => 1,
            'fileName' => $media->filename,
            'url' => $media->url,
        ]);
    }
}
