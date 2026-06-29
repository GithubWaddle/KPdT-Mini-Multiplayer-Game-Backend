<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService) {}

    // POST /api/chat/send
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'match_id' => 'required|exists:matches,id',
            'message'  => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $message = $this->chatService->sendMessage(
            auth('api')->id(),
            $request->match_id,
            $request->message
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Message sent',
            'data'    => $message,
        ], 201);
    }

    // GET /api/chat/{matchId}
    public function history(int $matchId)
    {
        $userId   = auth('api')->id();
        $messages = $this->chatService->getHistory($matchId, $userId);

        if ($messages === null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are not part of this match',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $messages,
        ]);
    }
}