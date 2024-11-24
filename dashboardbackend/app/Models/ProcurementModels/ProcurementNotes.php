<?php

namespace App\Models\ProcurementModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementNotes extends Model
{
    protected $table = 'procurement_notes';

    protected $fillable = [
        'month', 
        'branch',
        'notes'
    ];
    public $timestamps = true;	
}
