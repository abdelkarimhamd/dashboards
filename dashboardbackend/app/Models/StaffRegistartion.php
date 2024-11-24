<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffRegistartion extends Model
{
    protected $table = 'staffregistration';

    protected $fillable = [
        'projectId',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'december',
        'year',
        'branch'
    ];
  
    public $timestamps = false;
    use HasFactory;
}
