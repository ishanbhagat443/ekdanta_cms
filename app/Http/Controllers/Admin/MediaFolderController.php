<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaFolderController extends Controller
{
    public function __construct(protected MediaService $mediaService)
    {
        $this->middleware('can:media.manage');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[^\/\\\\]+$/'],
            'parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $folder = $this->mediaService->createFolder($data['name'], $data['parent_id'] ?? null);

        return response()->json(['folder' => $folder], 201);
    }

    public function update(Request $request, MediaFolder $folder): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[^\/\\\\]+$/'],
        ]);

        return response()->json(['folder' => $this->mediaService->renameFolder($folder, $data['name'])]);
    }

    public function destroy(MediaFolder $folder): JsonResponse
    {
        $this->mediaService->deleteFolder($folder);

        return response()->json(['deleted' => true]);
    }
}
