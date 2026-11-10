<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Profile
 */
class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'avatar_path' => $this->avatar_path,
            'avatar_url' => $this->avatar_path !== null
                ? Storage::disk('media')->url($this->avatar_path)
                : null,
            'bio' => $this->bio,
            'city' => $this->city,
        ];
    }
}