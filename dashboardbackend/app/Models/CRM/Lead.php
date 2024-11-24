<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $table = 'CRM_leads';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'stage', 'status', 'source', 'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function dealLeads()
    {
        return $this->hasMany(DealLead::class);
    }
}
