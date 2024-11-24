<?php

namespace App\Models\PettyCashModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashRequest extends Model
{
    use HasFactory;
    protected $table = 'pettycashexpense';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = [
        'id',
        'petty_cash_request_id',
        'expenses_description',
        'project_name',
        'invoice_number',
        'expenses',
        'transport',
        'consumables',
        'chargable',
        'invoice_image',
    ];
    public $timestamps = true;	
}
