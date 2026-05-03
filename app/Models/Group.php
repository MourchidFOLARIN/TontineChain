<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'creator_id',
        'contract_address',
        'contract_tx_hash',
        'contribution_amount',
        'contribution_token',
        'max_members',
        'current_members',
        'frequency',
        'payout_method',
        'current_cycle',
        'total_cycles',
        'status',
        'start_date',
        'next_due_date',
        'insurance_fund',
        'insurance_percent',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
}
