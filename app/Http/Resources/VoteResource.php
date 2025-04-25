<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteResource extends JsonResource
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
            'user' => new UserResource($this->user),
            'poll' => new PollResource($this->poll),
            'poll_option' => new PollOptionResource($this->pollOption),
            'percentage' => $this->percentage? $this->percentage : 0,
        ];
    }
}
