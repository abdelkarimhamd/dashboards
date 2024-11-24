<?php

namespace App\Models\ProcurementModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementSupplier extends Model
{
    use HasFactory;
    protected $table = 'procurement_suppliers';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = [
       "id","project_description","supplier_name","amount","remarks","priority","invoice_status","status","services"
    ];  
    public $timestamps = true;	
}
