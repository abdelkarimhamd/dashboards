<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualValueMonthlyBase extends Model
{
    use HasFactory;

    protected $table = 'actual_value_monthly_bases';

    protected $fillable = [
        'projectId',
        'branch',
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
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($actualValue) {
            // Update the ActualInvoice table
            ActualInvoice::where('ProjectID', $actualValue->projectId)
                         ->where('Year', $actualValue->year)
                         ->where('branch', $actualValue->branch)
                         ->update([
                             'M01' => $actualValue->jan * 1000000,
                             'M02' => $actualValue->feb * 1000000,
                             'M03' => $actualValue->mar * 1000000,
                             'M04' => $actualValue->apr * 1000000,
                             'M05' => $actualValue->may * 1000000,
                             'M06' => $actualValue->jun * 1000000,
                             'M07' => $actualValue->jul * 1000000,
                             'M08' => $actualValue->aug * 1000000,
                             'M09' => $actualValue->sep * 1000000,
                             'M10' => $actualValue->oct * 1000000,
                             'M11' => $actualValue->nov * 1000000,
                             'M12' => $actualValue->december * 1000000,
                         ]);

            // Recalculate Total, VarianceYTD, and Performance in the ActualInvoice table
            $actualInvoice = ActualInvoice::where('ProjectID', $actualValue->projectId)
                                          ->where('Year', $actualValue->year)
                                          ->where('branch', $actualValue->branch)
                                          ->first();

            if ($actualInvoice) {
                $total = $actualInvoice->M01 + $actualInvoice->M02 + $actualInvoice->M03 +
                         $actualInvoice->M04 + $actualInvoice->M05 + $actualInvoice->M06 +
                         $actualInvoice->M07 + $actualInvoice->M08 + $actualInvoice->M09 +
                         $actualInvoice->M10 + $actualInvoice->M11 + $actualInvoice->M12;

                         $fcInvoiceTotal = FcInvoice::where('ProjectID', $actualValue->projectId)
                         ->where('Year', $actualValue->year)
                         ->where('branch', $actualValue->branch)
                         ->sum('Total');

                            $varianceYTD = $total - $fcInvoiceTotal;
                            $performance = $fcInvoiceTotal == 0 ? 0 : ($total / $fcInvoiceTotal) * 100;

                            $actualInvoice->update([
                            'Total' => $total,
                            'VarianceYTD' => $varianceYTD,
                            'Performance' => $performance,
                            ]);
                            }

            // Update CashIn variance and performance when ActualInvoice total changes
            $cashIn = CashIn::where('ProjectID', $actualValue->projectId)
                            ->where('Year', $actualValue->year)
                            ->where('branch', $actualValue->branch)
                            ->first();

            if ($cashIn) {
                $cashInTotal = $cashIn->M01 + $cashIn->M02 + $cashIn->M03 +
                               $cashIn->M04 + $cashIn->M05 + $cashIn->M06 +
                               $cashIn->M07 + $cashIn->M08 + $cashIn->M09 +
                               $cashIn->M10 + $cashIn->M11 + $cashIn->M12;

                $varianceYTD = $cashInTotal - $total;
                $performance = $total == 0 ? 0 : ($cashInTotal / $total) * 100;

                $cashIn->update([
                    'VarianceYTD' => $varianceYTD,
                    'Performance' => $performance,
                ]);
            }
        });
    }
}
