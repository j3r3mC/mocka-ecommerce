<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
}