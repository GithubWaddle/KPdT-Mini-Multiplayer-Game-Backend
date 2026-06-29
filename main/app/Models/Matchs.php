<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matchs extends Model
{
    protected $fillable = [
        'player1_id',
        'player2_id',
        'status',
        'winner_id',
    ];

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function hasPlayer(int $userId): bool
    {
        return $this->player1_id === $userId || $this->player2_id === $userId;
    }
}