<?php

namespace App\Models\ProcurementModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementPayments extends Model
{
    use HasFactory;
    protected $table = 'procurement_payments';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = [
       "id","supplier_name","scope_of_services","project_name","project_type","payment_contract","due_date","invoice_status","category_of_charging","invoice_amount","total_amount"
    ];  
    public $timestamps = true;	

}
