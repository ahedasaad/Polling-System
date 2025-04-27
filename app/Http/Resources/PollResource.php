<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'expires_at' => $this->expires_at->format('Y-m-d H:i:s'),
            'anoymous_voting' => $this->anoymous_voting,
            'created_by' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'options' => PollOptionResource::collection($this->options),
        ];
    }
}
