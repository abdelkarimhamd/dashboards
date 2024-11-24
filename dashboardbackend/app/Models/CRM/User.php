<?php

namespace App\Models\CRM;

use App\Models\OutlookToken;
use App\Notifications\CRMUserResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // Add this line
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use SoftDeletes, HasApiTokens, Notifiable; // Add Notifiable

    protected $table = 'CRM_users';

    // Fillable fields for mass assignment
    protected $fillable = [
        'name', 'username', 'email', 'password', 'role',
    ];

    // Hidden fields for security reasons (don't return password in responses)
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Soft delete dates
    protected $dates = ['deleted_at'];

    /**
     * A user can create many companies.
     */
    public function companies()
    {
        return $this->hasMany(Company::class, 'created_by');
    }

    /**
     * A user can be associated with many activities.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
// app/Models/User.php

public function outlookToken()
{
    return $this->hasOne(OutlookToken::class);
}

    /**
     * A user can create many tasks.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * A user can create many deals.
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'created_by');
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CRMUserResetPassword($token));
    }
}
