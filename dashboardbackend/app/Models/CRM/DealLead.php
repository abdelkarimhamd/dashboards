<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class DealLead extends Model
{
    protected $table = 'CRM_deal_lead';

    protected $fillable = [
        'deal_id', 'lead_id',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
