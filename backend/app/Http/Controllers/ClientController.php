<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $clients = User::query()
            ->where('role', UserRole::Client)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($clients);
    }
}
