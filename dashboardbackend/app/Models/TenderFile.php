<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'tender_id',
        'name',
        'path'
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }
}
