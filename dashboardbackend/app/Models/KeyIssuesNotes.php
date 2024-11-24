<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyIssuesNotes extends Model
{
    protected $table = 'keyissuesnotes';
    protected $fillable = ['projectId', 'note'];
    use HasFactory;
}
