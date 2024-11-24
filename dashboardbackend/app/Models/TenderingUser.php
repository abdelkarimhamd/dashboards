<?php

namespace App\Models;

use App\Notifications\TenderingUserResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class TenderingUser extends Authenticatable 
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
    public function tender()
    {
        return $this->hasMany(Tender::class);
    }
    // In TenderingUser.php
public function tendersCreated()
{
    return $this->hasMany(Tender::class, 'created_by');
}

public function tendersUpdated()
{
    return $this->hasMany(Tender::class, 'updated_by');
}
public function sendPasswordResetNotification($token)
{
    $this->notify(new TenderingUserResetPassword($token));
}

}
