<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoProjectImage extends Model
{
    use HasFactory;

    protected $table = 'fo_project_images';
    protected $fillable = ['project_id', 'image_path'];

    public function project()
    {
        return $this->belongsTo(FitOutProject::class);
    }
}
