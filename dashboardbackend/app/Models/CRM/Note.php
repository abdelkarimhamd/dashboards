<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'CRM_notes';

    protected $fillable = [
        'content', 'activity_id',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
