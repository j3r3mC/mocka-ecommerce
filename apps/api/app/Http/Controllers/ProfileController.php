<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile;

        if ($profile === null) {
            return response()->json([
                'message' => 'Profil introuvable.',
            ], 404);
        }

        return response()->json([
            'profile' => new ProfileResource($profile),
        ]);
    }

    public function store(StoreProfileRequest $request): JsonResponse
{
    $user = $request->user();

    if ($user->profile()->exists()) {
        return response()->json([
            'message' => 'Un profil existe déjà pour cet utilisateur.',
        ], 409);
    }

    $profile = $user->profile()->create(
        $request->validated()
    );

    return response()->json([
        'profile' => new ProfileResource($profile),
    ], 201);
}
}