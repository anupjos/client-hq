<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $projects = Project::query()
            ->with(['client:id,name,email', 'creator:id,name,email'])
            ->withCount('files')
            ->when($user->isClient(), fn ($q) => $q->where('client_id', $user->id))
            ->orderByDesc('id')
            ->get();

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $project->load(['client:id,name,email', 'creator:id,name,email']);

        return response()->json($project, 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('view', $project);

        $project->load([
            'client:id,name,email',
            'creator:id,name,email',
            'files' => fn ($q) => $q->orderByDesc('id'),
        ]);

        return response()->json($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());
        $project->load(['client:id,name,email', 'creator:id,name,email']);

        return response()->json($project);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return response()->json(null, 204);
    }
}
