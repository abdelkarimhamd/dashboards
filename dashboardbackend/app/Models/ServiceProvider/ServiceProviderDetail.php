<?php

namespace App\Models\ServiceProvider;

use App\Models\Header;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderDetail extends Model
{
    use HasFactory;
    protected $table = 'service_provider_details';
    protected $fillable = [
        'name', 'email', 'phone_number', 'address', 'username', 'account_id',
        'subscribed_plan', 'start_date', 'expiration_date', 'terms',
        'payment_method', 'billing_history', 'invoices',
    ];
    protected $casts = [
        'billing_history' => 'array',
        'invoices' => 'array',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'service_provider_id', 'id');
    }
    public function project()
    {
        return $this->belongsTo(Header::class, 'project_id');
    }


    public function variationOrders()
    {
        return $this->hasMany(VariationOrder::class, 'service_provider_id', 'id');
    }
}

