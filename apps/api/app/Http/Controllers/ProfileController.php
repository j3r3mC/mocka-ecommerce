<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UploadProfileAvatarRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProfileRequest;
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

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->profile;

        if ($profile === null) {
            return response()->json([
                'message' => 'Profil introuvable.',
            ], 404);
        }

        $profile->update(
            $request->validated()
        );

        return response()->json([
            'profile' => new ProfileResource($profile->refresh()),
        ]);
    }

    public function uploadAvatar(
        UploadProfileAvatarRequest $request
        ): JsonResponse {
            $profile = $request->user()->profile;

            if ($profile === null) {
                return response()->json([
                    'message' => 'Profil introuvable.',
                ], 404);
            }

            $oldAvatarPath = $profile->avatar_path;

            $newAvatarPath = $request
                ->file('avatar')
                ->store('avatars', 'public');

            $profile->update([
                'avatar_path' => $newAvatarPath,
            ]);

            if ($oldAvatarPath !== null) {
                Storage::disk('public')->delete($oldAvatarPath);
            }

            return response()->json([
                'profile' => new ProfileResource($profile->refresh()),
            ]);
        }
}