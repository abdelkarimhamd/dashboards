<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MonthlyReport extends Model
{
    use HasFactory;

    protected $table = 'monthly_reports';

    protected $fillable = [
        'projectId',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'december',
        'year',
        'branch',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($monthlyReport) {
            Log::info('Monthly Report Updated:', $monthlyReport->toArray());
            // Update the corresponding fcinvoice records when MonthlyReport is updated
            $fcInvoice = Fcinvoice::where('ProjectID', $monthlyReport->projectId)
                                  ->where('Year', $monthlyReport->year)
                                  ->where('branch', $monthlyReport->branch)
                                  ->first();

            if ($fcInvoice) {
                // Update the monthly values in FcInvoice
                $fcInvoice->update([
                    'M01' => $monthlyReport->jan * 1000000,
                    'M02' => $monthlyReport->feb * 1000000,
                    'M03' => $monthlyReport->mar * 1000000,
                    'M04' => $monthlyReport->apr * 1000000,
                    'M05' => $monthlyReport->may * 1000000,
                    'M06' => $monthlyReport->jun * 1000000,
                    'M07' => $monthlyReport->jul * 1000000,
                    'M08' => $monthlyReport->aug * 1000000,
                    'M09' => $monthlyReport->sep * 1000000,
                    'M10' => $monthlyReport->oct * 1000000,
                    'M11' => $monthlyReport->nov * 1000000,
                    'M12' => $monthlyReport->december * 1000000,
                ]);

                // Recalculate the total for FcInvoice
                $total = $fcInvoice->M01 + $fcInvoice->M02 + $fcInvoice->M03 +
                         $fcInvoice->M04 + $fcInvoice->M05 + $fcInvoice->M06 +
                         $fcInvoice->M07 + $fcInvoice->M08 + $fcInvoice->M09 +
                         $fcInvoice->M10 + $fcInvoice->M11 + $fcInvoice->M12;

                // Update the total in FcInvoice
                $fcInvoice->update([
                    'Total' => $total,
                ]);

                // Update VarianceYTD and Performance in the ActualInvoice table
                $actualInvoice = ActualInvoice::where('ProjectID', $fcInvoice->ProjectID)
                                              ->where('Year', $fcInvoice->Year)
                                              ->where('branch', $fcInvoice->branch)
                                              ->first();

                if ($actualInvoice) {
                    $actualInvoiceTotal = $actualInvoice->M01 + $actualInvoice->M02 + $actualInvoice->M03 +
                                          $actualInvoice->M04 + $actualInvoice->M05 + $actualInvoice->M06 +
                                          $actualInvoice->M07 + $actualInvoice->M08 + $actualInvoice->M09 +
                                          $actualInvoice->M10 + $actualInvoice->M11 + $actualInvoice->M12;

                    $varianceYTD = $actualInvoiceTotal - $total;
                    $performance = $total == 0 ? 0 : ($actualInvoiceTotal / $total) * 100;

                    $actualInvoice->update([
                        'VarianceYTD' => $varianceYTD,
                        'Performance' => $performance,
                    ]);
                    
                }
            }
        });
    }
}
