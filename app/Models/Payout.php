<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payout extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'group_id',
        'beneficiary_id',
        'cycle_number',
        'total_amount_fcfa',
        'total_amount_token',
        'amount_fcfa',
        'blockchain_tx_hash',
        'status',
        'triggered_at',
        'completed_at',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }
}
