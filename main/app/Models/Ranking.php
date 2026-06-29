<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    protected $fillable = [
        'user_id',
        'wins',
        'losses',
        'points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper — win ratio calculation
    public function getWinRateAttribute(): float
    {
        $total = $this->wins + $this->losses;
        return $total > 0 ? round(($this->wins / $total) * 100, 2) : 0;
    }
}