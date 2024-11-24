<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmNote extends Model
{
    protected $table = 'pm_notes';
    protected $fillable = ['projectId', 'note'];
    use HasFactory;
}
