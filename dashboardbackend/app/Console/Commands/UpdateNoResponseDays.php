<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tender;
use Carbon\Carbon;

class UpdateNoResponseDays extends Command
{
    protected $signature = 'tenders:update-no-response-days';
    protected $description = 'Update no response days for tenders';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $tenders = Tender::where('no_response', true)
                          ->where('completed', true)
                          ->whereNotNull('no_response_start_date')
                          ->get();

        foreach ($tenders as $tender) {
            $noResponseStartDate = Carbon::parse($tender->no_response_start_date);
            $now = Carbon::now();
            $days = $noResponseStartDate->diffInDays($now);
            $tender->no_response_days = $days;
            $tender->save();
        }

        $this->info('No response days updated successfully.');
    }
}
