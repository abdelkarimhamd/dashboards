<?php

namespace App\Models\ProcurementModels;

use App\Http\Middleware\Authenticate;
use App\Notifications\ProcurementResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class ProcurementUser extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable;
      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */protected $fillable = ['username','name','email', 'password', 'role','branch'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ProcurementResetPassword($token));
    }
}
