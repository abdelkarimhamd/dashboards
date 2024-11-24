<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class DealContact extends Model
{
    protected $table = 'crm_deal_contact';

    protected $fillable = [
        'deal_id',
        'contact_id',
        // Include any additional fields here
    ];

    public $timestamps = true;

    // Define relationships if necessary
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
