<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Carbon\Carbon;
use App\Http\Resources\PollResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollController extends Controller
{
    public function index()
    {
        try {
            $polls = Poll::with(['user', 'options'])
                ->active()
                ->paginate(20);

            return PollResource::collection($polls);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $poll = Poll::active()->where('id', $id)->firstOrFail();

        $poll->load(['user', 'options']);

        return response()->json(new PollResource($poll));
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', Poll::class);

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'expires_at' => 'required|date|after_or_equal:today',
                'options' => 'required|array',
                'options.*.option_text' => 'nullable|string|max:255',
            ]);

            $expiresAt = $request->expires_at;

            // If expires_at is today, set it to the end of the day
            if (Carbon::parse($expiresAt)->isToday()) {
                $expiresAt = Carbon::parse($expiresAt)->endOfDay();
            }

            $poll = Poll::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'expires_at' => $expiresAt,
                'status' => 'ACTIVE',
            ]);

            $options = collect($request->options)->map(function ($option) {
                return ['option_text' => $option];
            })->toArray();

            if (!empty($options)) {
                $poll->options()->createMany($options);
            }

            $poll->load(['user', 'options']);

            return response()->json(new PollResource($poll), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $poll = Poll::findOrFail($id);
            $this->authorize('update', $poll);

            if ($poll->isExpired()) {
                return response()->json(['error' => 'Poll has expired and cannot be updated.']);
            }

            $hasVotes = $poll->options()->whereHas('votes')->exists();
            if ($hasVotes) {
                return response()->json(['error' => 'Poll has votes and cannot be updated.']);
            }

            $request->validate([
                'title' => 'string|max:255',
                'description' => 'nullable|string',
                'expires_at' => 'date|after:now',
                'options' => 'nullable|array',
                'options.*.id' => 'nullable|exists:poll_options,id',
                'options.*.option_text' => 'nullable|string|max:255',
            ]);

            $poll->update(array_filter([
                'title' => $request->title ?? $poll->title,
                'description' => $request->description ?? $poll->description,
                'expires_at' => $request->expires_at ?? $poll->expires_at,
            ]));

            if ($request->has('options')) {
                foreach ($request->options as $option) {
                    if (isset($option['id'])) {
                        // Update existing option
                        $poll->options()->where('id', $option['id'])->update(['option_text' => $option['option_text']]);
                    } else {
                        // Add new option if no ID is provided
                        $poll->options()->create(['option_text' => $option['option_text']]);
                    }
                }
            }

            $poll->load(['user', 'options']);

            return response()->json(new PollResource($poll));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $poll = Poll::findOrFail($id);
            $this->authorize('delete', $poll);

            $poll->options()->delete();
            $poll->delete();

            return response()->json(['message' => 'Poll deleted successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
