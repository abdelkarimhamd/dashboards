<?php

namespace App\Models\ServiceProvider;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id', 'project_id', 'vo_number', 'vo_date', 'amount',
        'currency', 'status', 'description', 'reason', 'approved_by', 'comments',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProviderDetail::class);
    }
}
