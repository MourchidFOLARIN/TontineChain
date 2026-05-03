<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contribution extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'group_id',
        'user_id',
        'cycle_number',
        'amount_fcfa',
        'amount_token',
        'status',
        'mobile_money_ref',
        'mobile_money_provider',
        'blockchain_tx_hash',
        'due_date',
        'paid_at',
        'confirmed_at',
        'is_late',
        'late_days',
    ];

    protected $casts = [
        'is_late' => 'boolean',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
