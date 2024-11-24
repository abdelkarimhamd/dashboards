<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'note',
        'created_by',
        'updated_by',
    ];

    // Relationship to the Tender model
    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    // Relationship to the User model for the creator
    public function creator()
    {
        return $this->belongsTo(TenderingUser::class, 'created_by');
    }

    // Relationship to the User model for the updater
    public function updater()
    {
        return $this->belongsTo(TenderingUser::class, 'updated_by');
    }
}
