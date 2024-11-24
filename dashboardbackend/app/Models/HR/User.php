<?php

namespace App\Models\HR;

use App\Notifications\HRUserResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extend from Authenticatable
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword; // Use the trait
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    protected $table = 'hr_user';

    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password',
        'role',
    ];

    // Relationships
    public function reportedEmployees()
    {
        return $this->hasMany(Employee::class, 'reported_by');
    }

    public function statusChanges()
    {
        return $this->hasMany(StatusHistory::class, 'changed_by');
    }
    public function sendPasswordResetNotification($token)
{
    $this->notify(new HRUserResetPassword($token));
}
}
