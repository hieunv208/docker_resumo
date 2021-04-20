<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function user_profile()
    {
        return $this->hasOne(UserInfo::class);
    }

    public function support()
    {
        return $this->hasMany(Support::class);
    }

    public function routeNotificationForSMS(Notification $notification=null)
    {
        return $this->phone_number;
    }


    protected $table = 'users';
    protected $fillable = [
        'email',
        'name',
        'phone_number',
        'active',
        'user_type',
        'code_sms'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


}
