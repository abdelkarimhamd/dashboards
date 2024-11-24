<?php

// app/Models/OutlookToken.php

namespace App\Models;

use App\Models\CRM\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutlookToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'access_token', 'refresh_token', 'expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
