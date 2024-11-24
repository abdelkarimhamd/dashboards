<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'fact_sheet_id', // Foreign key to associate the image with a FactSheet
        'file_name',     // The name of the image file
        'file_path',     // The path where the image is stored
    ];
    public function factsheet()
    {
        return $this->belongsTo(FactSheet::class);
    }
    // In app/Models/FactSheet.php


}
