<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id', 'project_id', 'po_number', 'po_date', 'amount',
        'currency', 'status', 'description', 'payment_terms', 'delivery_date', 'comments',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProviderDetail::class);
    }
}
