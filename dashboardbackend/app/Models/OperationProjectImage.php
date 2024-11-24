<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Header; // Import the correct model

class OperationProjectImage extends Model
{
    use HasFactory;

    protected $table = 'operation_project_images';
    protected $fillable = ['project_id', 'image_path'];

    // Define the relationship to the 'headers' table
    public function project()
    {
        return $this->belongsTo(Header::class, 'project_id', 'id'); // Correct foreign and local keys
    }
}
