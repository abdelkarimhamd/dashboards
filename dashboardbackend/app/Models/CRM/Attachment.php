<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'crm_attachments';

    protected $fillable = [
        'deal_id', 'file_name', 'file_path', 'file_type', 'uploaded_by',
    ];

    /**
     * Relationship: Attachment belongs to Deal
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    /**
     * Relationship: Attachment belongs to User (uploaded by)
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
