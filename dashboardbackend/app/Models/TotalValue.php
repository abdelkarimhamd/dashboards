<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalValue extends Model
{
    use HasFactory;

    protected $table = 'totalvalues';


    protected $fillable = [
        'TotalFCInvoice',
        'TotalActualInvoice',
        'TotalActualCashin',
        'TotalActualCashout',
        'yearSelected',
    ];


    public $timestamps = true;
}
