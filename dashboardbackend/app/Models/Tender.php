<?php
namespace App\Models;

use App\Jobs\SyncFactSheetJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
class Tender extends Model
{
    use HasFactory;

    protected $casts = [
        'rfpDocuments' => 'array',
    ];

    
    protected $fillable = [
        'created_by',
        'updated_by',
        'gono',
        'canceled_reason',
        'status',
        'tenderTitle',
        'tenderNumber',
        'employerName',
        'location',
        'branch',
        'selectedOption',
        'sourceOption',
        'estimatedNbr',
        'companyPreQuilifiedOption',
        'contactDuration',
        'scopeServices',
        'submissionDate',
        'startDate',
        'contractType',
        'receivedDate',
        'jobexDate',
        'Q_ADate',
        'tender_value',
        'hyperlink',
        'extinsionDate',
        'siteVisitDate',
        'estimatedMargin',
        'validityPeriod',
        'conditions',
        'positionRecommendation',
        'currencyOptions',
        'performanceBond',
        'retention',
        'languageOptions',
        'trfProcess',
        'rfpSubmitted',
        'rfpDocument',
        'projectName',
        'companyOrPartnershipName',
        'contractPeriod',
        'dateTenderReceived',
        'financing',
        'completed',
        'no_response',
        'no_response_start_date',
        'no_response_days',
        'bid_bond',
        'projectSize',
        'lost', 
        'probability',
        'tender_logo',
        'tender_image',
    ];

    public function tenderuser()
    {
        return $this->hasMany(TenderingUser::class);
    }
// In Tender.php
public function createdBy()
{
    return $this->belongsTo(TenderingUser::class, 'created_by');
}

public function updatedBy()
{
    return $this->belongsTo(TenderingUser::class, 'updated_by');
}

    /**
     * Sync with the FactSheet table after saving.
     */
// In Tender.php
protected static function boot()
{
    parent::boot();

    static::updated(function ($tender) {
        // Add debugging output
        // Log::info('Tender Updated: ', ['id' => $tender->id]);

        $factSheet = FactSheet::where('tender_id', $tender->id)->first();

        if ($factSheet) {
            // Add debugging output
            // Log::info('FactSheet Found for Update: ', ['id' => $factSheet->id]);

            $factSheet->update([
                'project_name' => $tender->projectName ?? $factSheet->project_name,  // Use the existing value if null
                'client_name' => $tender->employerName ?? $factSheet->client_name,
                'location' => $tender->location ?? $factSheet->location,
                'commencement_date' => $tender->startDate ?? $factSheet->commencement_date,
                'duration_of_project' => $tender->contactDuration ?? $factSheet->duration_of_project,
                'total_offer_price' => $tender->tender_value ?? $factSheet->total_offer_price,
                'type_of_contract' => $tender->contractType ?? $factSheet->type_of_contract,
                'performance_bond' => $tender->performanceBond ?? $factSheet->performance_bond,
                'retention' => $tender->retention ?? $factSheet->retention,
                'bid_bond' => $tender->bid_bond ?? $factSheet->bid_bond,
                'tender_submission_date' => $tender->submissionDate ?? $factSheet->tender_submission_date  , // Sync submission date
                'profit' => $tender->estimatedMargin ?? $factSheet->profit  , // Sync submission date

                'jv_partner' => $tender->companyOrPartnershipName ?? $factSheet->jv_partner,
            ]);
        } else {
            // Log::warning('No FactSheet Found for Tender: ', ['id' => $tender->id]);
        }
    });
}


}

