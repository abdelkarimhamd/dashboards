<?php

namespace App\Models\ProcurementModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementDailyTransfers extends Model
{
    use HasFactory;
    protected $table = 'procurement_daily_transfers';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = [
        "id","supplier_contractor","services","project_name","contract","transfer_amount","transfer_type","status","transfer_date",	"category_of_charging","invoice"	

    ];  
    public $timestamps = true;	
}
