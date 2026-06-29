<?php

namespace App\Services;

use App\Models\Matchs;
use App\Models\ChatMessage;

class ChatService
{
    public function sendMessage(int $userId, int $matchId, string $message): ChatMessage
    {
        // Verify the player is actually in this match
        $match = Matchs::findOrFail($matchId);

        if (!$match->hasPlayer($userId)) {
            abort(403, 'You are not part of this match');
        }

        $chatMessage = ChatMessage::create([
            'user_id'  => $userId,
            'match_id' => $matchId,
            'message'  => $message,
        ]);

        // Load user so the response includes sender name
        return $chatMessage->load('user:id,name');
    }

    public function getHistory(int $matchId, int $userId): ?array
    {
        $match = Matchs::find($matchId);

        // Return null so controller can respond with 403
        if (!$match || !$match->hasPlayer($userId)) {
            return null;
        }

        $messages = ChatMessage::where('match_id', $matchId)
                               ->with('user:id,name')
                               ->orderBy('created_at', 'asc')
                               ->get();

        return [
            'match_id' => $matchId,
            'messages' => $messages,
            'total'    => $messages->count(),
        ];
    }
}