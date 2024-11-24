<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationValue extends Model
{
    protected $table = 'operation_values';

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
