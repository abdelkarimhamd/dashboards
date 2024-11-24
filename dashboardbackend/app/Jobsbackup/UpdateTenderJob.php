<?php

namespace App\Jobs;

use App\Models\FactSheet;
use App\Models\Tender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $factSheet;

    public function __construct(FactSheet $factSheet)
    {
        $this->factSheet = $factSheet;
    }

    public function handle()
    {
        $tender = $this->factSheet->tender;

        if ($tender) {
            $tender->projectName = $this->factSheet->project_name;
            $tender->employerName = $this->factSheet->client_name;
            $tender->location = $this->factSheet->location;
            $tender->startDate = $this->factSheet->commencement_date;
            $tender->contactDuration = $this->factSheet->duration_of_project;
            $tender->tender_value = $this->factSheet->tender_bond;
            $tender->contractType = $this->factSheet->type_of_contract;
            $tender->performanceBond = $this->factSheet->performance_bond;
            $tender->retention = $this->factSheet->retention;
            $tender->companyOrPartnershipName = $this->factSheet->jv_partner;

            $tender->save();
        }
    }
}
