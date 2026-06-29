<?php

namespace App\Http\Controllers;

use App\Services\MatchmakingService;
use Illuminate\Http\Request;

class MatchmakingController extends Controller
{
    public function __construct(protected MatchmakingService $matchmakingService) {}

    // POST /api/matchmaking/join
    // Player joins the queue, gets matched or waits
    public function join(Request $request)
    {
        $userId = auth('api')->id();
        $result = $this->matchmakingService->joinQueue($userId);

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    // POST /api/matchmaking/leave
    // Player leaves the queue before being matched
    public function leave(Request $request)
    {
        $userId = auth('api')->id();
        $this->matchmakingService->leaveQueue($userId);

        return response()->json([
            'status'  => 'success',
            'message' => 'Left matchmaking queue',
        ]);
    }

    // GET /api/matchmaking/status
    // Check if player has been matched yet
    public function status(Request $request)
    {
        $userId = auth('api')->id();
        $status = $this->matchmakingService->getStatus($userId);

        return response()->json([
            'status' => 'success',
            'data'   => $status,
        ]);
    }

    // POST /api/matchmaking/finish
    // Mark a match as finished and declare winner
    public function finish(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'match_id'  => 'required|exists:matches,id',
            'winner_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $match = $this->matchmakingService->finishMatch(
            $request->match_id,
            $request->winner_id
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Match finished',
            'data'    => $match,
        ]);
    }
}