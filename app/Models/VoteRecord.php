<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoteRecord extends Model
{
    protected $table = 'vote_records';
    
    protected $fillable = [
        'vote_id',
        'user_id',
        'choice',
    ];

    protected $casts = [
        'choice' => 'boolean',
    ];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
