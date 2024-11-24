<?php

namespace App\Models\PettyCashModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashGeneralExpense extends Model
{
    use HasFactory;
    protected $table = 'petty_cash_general_expenses';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = ['material', 'amount'];
    public $timestamps = true;	
    
}
