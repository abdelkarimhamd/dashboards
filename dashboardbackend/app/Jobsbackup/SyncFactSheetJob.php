<?php
namespace App\Jobs;

use App\Models\Tender;
use App\Models\FactSheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFactSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tender;

    public function __construct(Tender $tender)
    {
        $this->tender = $tender;
    }

    public function handle()
    {
        $factSheet = FactSheet::where('tender_id', $this->tender->id)->first();

        if ($factSheet) {
            $factSheet->project_name = $this->tender->projectName;
            $factSheet->client_name = $this->tender->employerName;
            $factSheet->location = $this->tender->location;
            $factSheet->commencement_date = $this->tender->startDate;
            $factSheet->duration_of_project = $this->tender->contactDuration;
            $factSheet->tender_bond = $this->tender->tender_value;
            $factSheet->type_of_contract = $this->tender->contractType;
            $factSheet->performance_bond = $this->tender->performanceBond;
            $factSheet->retention = $this->tender->retention;
            $factSheet->jv_partner = $this->tender->companyOrPartnershipName;

            $factSheet->save();
        }
    }
}
