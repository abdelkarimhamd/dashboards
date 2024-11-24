<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitOutKeyIssues extends Model
{
    use HasFactory;
    protected $table = 'fitout_keyissuesnotes';
    protected $fillable = ['projectId', 'note'];
}
