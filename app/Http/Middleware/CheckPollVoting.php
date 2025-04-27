<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class CheckPollVoting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $option_id = $request->route('id');

        $option = PollOption::with('poll')->findOrFail($option_id);
        $poll = $option->poll;

        // if (!$poll instanceof Poll) {
        //     return response()->json(['message' => 'Poll not found'], 404);
        // }

        if ($poll->isExpired()) {
            return response()->json(['message' => 'Poll is Expired'], 403);
        }

        if (!$poll->anoymous_voting) {
            if (!Auth::check()) {
                return response()->json(['error' => 'Authentication required'], 401);
            }
        }
        if (!$poll->anoymous_voting && Vote::where('user_id', Auth::id())->where('poll_id', $poll->id)->exists()) {
            return response()->json(['error' => 'You have already voted in this poll.'], 400);
        }

        return $next($request);
    }
}
