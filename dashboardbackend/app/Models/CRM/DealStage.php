<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class DealStage extends Model
{
    protected $table = 'CRM_deal_stages';

    protected $fillable = [
        'name', 'description',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
