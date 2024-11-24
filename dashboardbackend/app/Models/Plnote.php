<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plnote extends Model
{
    use HasFactory;
    protected $table = 'plnotes';

    protected $fillable = [
        'year', 
        'branch',
        'notes'
    ];
}
