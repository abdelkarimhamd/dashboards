<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualInvoice extends Model
{
    protected $dispatchesEvents = [
        'saved' => \App\Events\ActualInvoiceUpdated::class,
    ];
    use HasFactory;

    protected $table = 'actualinvoice';
    protected $primaryKey = 'actualinvoiceID'; 
    public $incrementing = true;
    protected $fillable = [
        'ProjectID', 'M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12', 
        'Total', 'VarianceYTD', 'Performance', 'Year','branch'
    ];
    public $timestamps = false;

    public function projectDetail()
    {
        return $this->belongsTo(ProjectDetail::class, 'ProjectID', 'ProjectID');
    }

    public function fcinvoice()
    {
        return $this->belongsTo(Fcinvoice::class, 'ProjectID', 'ProjectID');
    }
}
