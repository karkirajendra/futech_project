<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ?? 'user',
            'email_verified' => !is_null($this->email_verified_at),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Conditional relationships
            'blogs_count' => $this->when(
                $this->relationLoaded('blogs'),
                fn() => $this->blogs->count()
            ),
            'blogs' => BlogResource::collection($this->whenLoaded('blogs')),
        ];
    }
}
