<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'phone',
        'email',
        'full_name',
        'first_name',
        'last_name',
        'profession',
        'npi_hash',
        'wallet_address',
        'encrypted_private_key',
        'score_confiance',
        'kyc_status',
        'preferred_language',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'encrypted_private_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'score_confiance' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'creator_id');
    }

    public function memberships()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'beneficiary_id');
    }
}
