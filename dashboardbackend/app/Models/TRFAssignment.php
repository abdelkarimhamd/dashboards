<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TRFAssignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'tender_id',
        'assigned_to',
        'status',
        'review_comments',
        'assigned_by',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
