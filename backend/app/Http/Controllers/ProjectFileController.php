<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectFileRequest;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectFileController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('view', $project);

        return response()->json(
            $project->files()->orderByDesc('id')->get()
        );
    }

    public function store(StoreProjectFileRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('uploadFile', $project);

        $uploaded = $request->file('file');
        $path = $uploaded->store("project_files/{$project->id}");

        $file = ProjectFile::create([
            'project_id' => $project->id,
            'uploaded_by' => $request->user()->id,
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
        ]);

        return response()->json($file, 201);
    }

    public function show(Request $request, Project $project, ProjectFile $file): StreamedResponse
    {
        Gate::authorize('view', $project);
        abort_unless($file->project_id === $project->id, 404);

        return Storage::download($file->stored_path, $file->original_name);
    }

    public function destroy(Request $request, Project $project, ProjectFile $file): JsonResponse
    {
        Gate::authorize('update', $project);
        abort_unless($file->project_id === $project->id, 404);

        Storage::delete($file->stored_path);
        $file->delete();

        return response()->json(null, 204);
    }
}
