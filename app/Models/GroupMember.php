<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupMember extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'group_id',
        'user_id',
        'position',
        'status',
        'has_received',
        'cycle_received',
        'joined_at',
    ];

    protected $casts = [
        'has_received' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public $timestamps = false; // As per migration joined_at is used

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
