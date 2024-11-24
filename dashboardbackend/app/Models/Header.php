<?php
namespace App\Models;

use App\Models\ServiceProvider\ServiceProviderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Header extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filePath',
        'ProjectImageFilePath',
        'projectType',
        'projectName',
        'clientName',
        'projectLocation',
        'projectDuration',
        'projectDate',
        'projectValue',
        'ProjetPeakManpower',
        'projectManagerName',
        'branch'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'projectDate' => 'date',
    ];
    public function serviceProviders()
{
    return $this->hasMany(ServiceProviderDetail::class, 'project_id');
}


    // You can also define relationships or additional methods here if needed
}
