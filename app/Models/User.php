<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;
use App\Traits\Uuid;
use Carbon\Carbon;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Uuid;
    public $incrementing = false;
    public $keyType = 'string';
	protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];
    public function guru()
    {
        return $this->hasOne(Guru::class, 'guru_id', 'guru_id');
    }
    public function sekolah()
    {
        return $this->hasOne(Sekolah::class, 'sekolah_id', 'sekolah_id');
    }
    public function getLastLoginAtAttribute($date)
    {
        return ($date) ? Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y H:i:s') : '';
    }
    public function getLoginTerakhirAttribute()
	{
        if($this->attributes['last_login_at']){
            return Carbon::parse($this->attributes['last_login_at'])->translatedFormat('d F Y').' Pukul '.Carbon::parse($this->attributes['last_login_at'])->format('H:i:s');
        } else {
            return '-';
        }
	}
}
