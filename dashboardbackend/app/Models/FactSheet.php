<?php
namespace App\Models;

use App\Jobs\UpdateTenderJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'project_name',
        'client_name',
        'location',
        'scope_of_work',
        'commencement_date',
        'duration_of_project',
        'tender_bond',
        'technical_requirements',
        'delay_damages',
        'type_of_contract',
        'procurement_route',
        'advance_payment',
        'performance_bond',
        'retention',
        'area',
        'tender_submission_date',
        'preliminary_estimate',
        'jv_partner',
        'dry_cost',
        'admin_and_salaries',
        'profit',
        'provisional_sum',
        'total_offer_price',
        'bid_bond',
    ];

    /**
     * Get the tender that owns the fact sheet.
     */
    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    /**
     * Sync with the Tender table after saving.
     */
    // protected static function booted()
    // {
    //     static::saved(function ($factSheet) {
    //         // Dispatch the job
    //         UpdateTenderJob::dispatch($factSheet);
    //     });
    // }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($factSheet) {
            // Update the corresponding Tender records when FactSheet is updated
            $tender = Tender::find($factSheet->tender_id);

            if ($tender) {
                $tender->update([
                    'projectName' => $factSheet->project_name ?? $tender->projectName,
                    'employerName' => $factSheet->client_name ?? $tender->employerName,
                    'location' => $factSheet->location ?? $tender->location,
                    'startDate' => $factSheet->commencement_date ?? $tender->startDate,
                    'contactDuration' => $factSheet->duration_of_project ?? $tender->contactDuration,
                    'tender_value' => $factSheet->total_offer_price ?? $tender->tender_value,
                    'contractType' => $factSheet->type_of_contract ?? $tender->contractType,
                    'performanceBond' => $factSheet->performance_bond ?? $tender->performanceBond,
                    'retention' => $factSheet->retention ?? $tender->retention,
                    'bid_bond' => $factSheet->bid_bond ?? $tender->bid_bond,
                    'submissionDate' => $factSheet->tender_submission_date ?? $tender->submissionDate, // Sync submission date
                    'companyOrPartnershipName' => $factSheet->jv_partner ?? $tender->companyOrPartnershipName,
                'estimatedMargin' => $factSheet->profit ?? $tender->estimatedMargin   , // Sync submission date
                    
                ]);
            }
        });
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
