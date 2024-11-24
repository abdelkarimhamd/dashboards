<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SalaryMonthlyBase extends Model
{
    use HasFactory;

    protected $table = 'salary_monthly_base';

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
        'branch'
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($salaryMonthlyBase) {
            Log::info('Found CashOut record for projectId: ' . $salaryMonthlyBase->projectId . ', year: ' . $salaryMonthlyBase->year);
            // Update the corresponding CashOut records when SalaryMonthlyBase is updated
            $cashOut = Cashout::where('ProjectID', $salaryMonthlyBase->projectId)
                              ->where('Year', $salaryMonthlyBase->year)
                              ->where('branch', $salaryMonthlyBase->branch)
                              ->first();

            if ($cashOut) {
                // Get the values from Suppliers and PettyCash tables
                $suppliers = SuppliersMonthlyBase::where('ProjectID', $salaryMonthlyBase->projectId)
                                      ->where('year', $salaryMonthlyBase->year)
                                      ->where('branch', $salaryMonthlyBase->branch)
                                      ->first();

                $pettyCash = PettyCashMonthlyBase::where('ProjectID', $salaryMonthlyBase->projectId)
                                      ->where('year', $salaryMonthlyBase->year)
                                      ->where('branch', $salaryMonthlyBase->branch)
                                      ->first();
                
                
                Log::info('Values for August - Salary: ' . $salaryMonthlyBase->aug . ', Suppliers: ' . ($suppliers->aug ?? 'null') . ', PettyCash: ' . ($pettyCash->aug ?? 'null'));

                // Update the monthly values in CashOut by summing Salary, Suppliers, and PettyCash
                $cashOut->update([
                    'M01' => ($salaryMonthlyBase->jan == 0 || ($suppliers->jan ?? 0) == 0 || ($pettyCash->jan ?? 0) == 0) ? 0 : ($salaryMonthlyBase->jan * 1000000 + ($suppliers->jan ?? 0) * 1000000 + ($pettyCash->jan ?? 0) * 1000000),
                    'M02' => ($salaryMonthlyBase->feb == 0 || ($suppliers->feb ?? 0) == 0 || ($pettyCash->feb ?? 0) == 0) ? 0 : ($salaryMonthlyBase->feb * 1000000 + ($suppliers->feb ?? 0) * 1000000 + ($pettyCash->feb ?? 0) * 1000000),
                    'M03' => ($salaryMonthlyBase->mar == 0 || ($suppliers->mar ?? 0) == 0 || ($pettyCash->mar ?? 0) == 0) ? 0 : ($salaryMonthlyBase->mar * 1000000 + ($suppliers->mar ?? 0) * 1000000 + ($pettyCash->mar ?? 0) * 1000000),
                    'M04' => ($salaryMonthlyBase->apr == 0 || ($suppliers->apr ?? 0) == 0 || ($pettyCash->apr ?? 0) == 0) ? 0 : ($salaryMonthlyBase->apr * 1000000 + ($suppliers->apr ?? 0) * 1000000 + ($pettyCash->apr ?? 0) * 1000000),
                    'M05' => ($salaryMonthlyBase->may == 0 || ($suppliers->may ?? 0) == 0 || ($pettyCash->may ?? 0) == 0) ? 0 : ($salaryMonthlyBase->may * 1000000 + ($suppliers->may ?? 0) * 1000000 + ($pettyCash->may ?? 0) * 1000000),
                    'M06' => ($salaryMonthlyBase->jun == 0 || ($suppliers->jun ?? 0) == 0 || ($pettyCash->jun ?? 0) == 0) ? 0 : ($salaryMonthlyBase->jun * 1000000 + ($suppliers->jun ?? 0) * 1000000 + ($pettyCash->jun ?? 0) * 1000000),
                    'M07' => ($salaryMonthlyBase->jul == 0 || ($suppliers->jul ?? 0) == 0 || ($pettyCash->jul ?? 0) == 0) ? 0 : ($salaryMonthlyBase->jul * 1000000 + ($suppliers->jul ?? 0) * 1000000 + ($pettyCash->jul ?? 0) * 1000000),
                    'M08' => ($salaryMonthlyBase->aug == 0 || ($suppliers->aug ?? 0) == 0 || ($pettyCash->aug ?? 0) == 0) ? 0 : ($salaryMonthlyBase->aug * 1000000 + ($suppliers->aug ?? 0) * 1000000 + ($pettyCash->aug ?? 0) * 1000000),
                    'M09' => ($salaryMonthlyBase->sep == 0 || ($suppliers->sep ?? 0) == 0 || ($pettyCash->sep ?? 0) == 0) ? 0 : ($salaryMonthlyBase->sep * 1000000 + ($suppliers->sep ?? 0) * 1000000 + ($pettyCash->sep ?? 0) * 1000000),
                    'M10' => ($salaryMonthlyBase->oct == 0 || ($suppliers->oct ?? 0) == 0 || ($pettyCash->oct ?? 0) == 0) ? 0 : ($salaryMonthlyBase->oct * 1000000 + ($suppliers->oct ?? 0) * 1000000 + ($pettyCash->oct ?? 0) * 1000000),
                    'M11' => ($salaryMonthlyBase->nov == 0 || ($suppliers->nov ?? 0) == 0 || ($pettyCash->nov ?? 0) == 0) ? 0 : ($salaryMonthlyBase->nov * 1000000 + ($suppliers->nov ?? 0) * 1000000 + ($pettyCash->nov ?? 0) * 1000000),
                    'M12' => ($salaryMonthlyBase->december == 0 || ($suppliers->december ?? 0) == 0 || ($pettyCash->december ?? 0) == 0) ? 0 : ($salaryMonthlyBase->december * 1000000 + ($suppliers->december ?? 0) * 1000000 + ($pettyCash->december ?? 0) * 1000000),
                ]);
                Log::info('CashOut updated for projectId: ' . $salaryMonthlyBase->projectId . ', year: ' . $salaryMonthlyBase->year . ", cashout values: " . $cashOut);

                // Recalculate the total for CashOut
                $total = $cashOut->M01 + $cashOut->M02 + $cashOut->M03 +
                         $cashOut->M04 + $cashOut->M05 + $cashOut->M06 +
                         $cashOut->M07 + $cashOut->M08 + $cashOut->M09 +
                         $cashOut->M10 + $cashOut->M11 + $cashOut->M12;


                Log::info('Total recalculated for CashOut: ' . $total);

                 // Get the total from the CashIn table
                 $cashInTotal = CashIn::where('ProjectID', $cashOut->ProjectID)
                 ->where('Year', $cashOut->Year)
                 ->where('branch', $cashOut->branch)
                 ->sum('Total');

                // Calculate VarianceYTD and Performance for CashOut
                $varianceYTD = $total - $cashInTotal;
                $performance = $cashInTotal == 0 ? 0 : ($total / $cashInTotal) * 100;

                // Update VarianceYTD and Performance in CashOut
                $cashOut->update([
                'Total' => $total,
                'VarianceYTD' => $varianceYTD,
                'Performance' => $performance,
                ]);
                Log::info('CashOut VarianceYTD and Performance updated for projectId: ' . $salaryMonthlyBase->projectId . ', year: ' . $salaryMonthlyBase->year);
            }
        });
    }
}
