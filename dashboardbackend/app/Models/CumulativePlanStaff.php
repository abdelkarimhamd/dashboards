<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CumulativePlanStaff extends Model
{
    use HasFactory;
    protected $table = 'cumulativeplanstaff';
 
    protected $keyType = 'int';

    protected $fillable = [
        'M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12', 'yearSelected','branch'
    ];
    protected $casts = [
        'M01' => 'decimal:2',
        'M02' => 'decimal:2',
        'M03' => 'decimal:2',
        'M04' => 'decimal:2',
        'M05' => 'decimal:2',
        'M06' => 'decimal:2',
        'M07' => 'decimal:2',
        'M08' => 'decimal:2',
        'M09' => 'decimal:2',
        'M10' => 'decimal:2',
        'M11' => 'decimal:2',
        'M12' => 'decimal:2',
        'yearSelected' => 'integer'
    ];
}
