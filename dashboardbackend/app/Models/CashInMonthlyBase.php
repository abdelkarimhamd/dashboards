<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashInMonthlyBase extends Model
{
    use HasFactory;

    protected $table = 'cash_in_monthly_base';

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
        'year'
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($cashInMonthlyBase) {
            // Calculate the total
            $total = $cashInMonthlyBase->jan * 1000000
                + $cashInMonthlyBase->feb * 1000000
                + $cashInMonthlyBase->mar * 1000000
                + $cashInMonthlyBase->apr * 1000000
                + $cashInMonthlyBase->may * 1000000
                + $cashInMonthlyBase->jun * 1000000
                + $cashInMonthlyBase->jul * 1000000
                + $cashInMonthlyBase->aug * 1000000
                + $cashInMonthlyBase->sep * 1000000
                + $cashInMonthlyBase->oct * 1000000
                + $cashInMonthlyBase->nov * 1000000
                + $cashInMonthlyBase->december * 1000000;

            // Get the total from the ActualInvoice table
            $actualInvoiceTotal = ActualInvoice::where('ProjectID', $cashInMonthlyBase->projectId)
                ->where('Year', $cashInMonthlyBase->year)
                ->sum('Total');

            // Calculate VarianceYTD and Performance
            $varianceYTD = $total - $actualInvoiceTotal;
            $performance = $actualInvoiceTotal == 0 ? 0 : ($total / $actualInvoiceTotal) * 100;

            // Update CashIn with the calculated values
            CashIn::where('ProjectID', $cashInMonthlyBase->projectId)
                ->where('Year', $cashInMonthlyBase->year)
                ->where('branch', $cashInMonthlyBase->branch)
                ->update([
                    'M01' => $cashInMonthlyBase->jan * 1000000,
                    'M02' => $cashInMonthlyBase->feb * 1000000,
                    'M03' => $cashInMonthlyBase->mar * 1000000,
                    'M04' => $cashInMonthlyBase->apr * 1000000,
                    'M05' => $cashInMonthlyBase->may * 1000000,
                    'M06' => $cashInMonthlyBase->jun * 1000000,
                    'M07' => $cashInMonthlyBase->jul * 1000000,
                    'M08' => $cashInMonthlyBase->aug * 1000000,
                    'M09' => $cashInMonthlyBase->sep * 1000000,
                    'M10' => $cashInMonthlyBase->oct * 1000000,
                    'M11' => $cashInMonthlyBase->nov * 1000000,
                    'M12' => $cashInMonthlyBase->december * 1000000,
                    'Total' => $total,
                    'VarianceYTD' => $varianceYTD,
                    'Performance' => $performance,
                ]);


                 // Get the total from the CashOut table
            $cashOutTotal = CashOut::where('ProjectID', $cashInMonthlyBase->projectId)
            ->where('Year', $cashInMonthlyBase->year)
            ->where('branch', $cashInMonthlyBase->branch)
            ->sum('Total');

        // Calculate VarianceYTD and Performance for CashOut
        $varianceYTD = $cashOutTotal - $total;
        $performance = $total == 0 ? 0 : ($cashOutTotal / $total) * 100;

        // Update CashOut with the calculated VarianceYTD and Performance
        Cashout::where('ProjectID', $cashInMonthlyBase->projectId)
        ->where('Year', $cashInMonthlyBase->year)
        ->where('branch', $cashInMonthlyBase->branch)
        ->update([
            'VarianceYTD' => $varianceYTD,
            'Performance' => $performance,
        ]);


        });
    }
}
