<?php

namespace App\Http\Controllers;

use App\Models\PollOption;
use App\Models\Poll;
use App\Http\Resources\VoteResource;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    public function store($option_id)
    {
        try {

            $option = PollOption::with('poll')->findOrFail($option_id);
            $poll = $option->poll;

            if ($poll->isExpired()) {
                return response()->json(['error' => 'Voting is closed for this poll.']);
            }

            $userId = Auth::id();

            $alreadyVoted = Vote::where('poll_id', $poll->id)
                ->where('user_id', $userId)
                ->exists();

            if ($alreadyVoted) {
                return response()->json(['message' => 'You have already voted in this poll.']);
            }

            $vote = Vote::create([
                'poll_id' => $poll->id,
                'poll_option_id' => $option->id,
                'user_id' => $userId,
            ]);

            $vote->load(['user', 'poll', 'pollOption']);

            return response()->json(new VoteResource($vote), 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getResults($id)
    {
        try {
            $poll = Poll::with(['options.votes'])->findOrFail($id);

            $userId = Auth::id();
            // Check if the user has voted in this poll
            $hasVoted = Vote::where('poll_id', $poll->id)
                ->where('user_id', $userId)
                ->exists();

            if (!$hasVoted) {
                return response()->json(['error' => 'You need to vote before viewing the results.']);
            }
            // Get total votes
            $totalVotes = $poll->options->sum(function ($option) {
                return $option->votes->count();
            });

            if ($totalVotes == 0) {
                return response()->json(['message' => 'No votes yet.'], 200);
            }

            // Get the results for each option
            $results = $poll->options->map(function ($option) use ($totalVotes) {
                $votesCount = $option->votes->count();
                $percentage = ($votesCount / $totalVotes) * 100;

                return [
                    'option_text' => $option->option_text,
                    'votes_count' => $votesCount,
                    'percentage' => number_format($percentage, 2), // Format to 2 decimal places
                ];
            });

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
