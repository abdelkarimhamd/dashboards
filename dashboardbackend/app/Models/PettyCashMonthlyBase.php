<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
class PettyCashMonthlyBase extends Model
{
    use HasFactory;

    protected $table = 'petty_cash_monthly_base';

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

        static::updated(function ($pettyCashMonthlyBase) {
            // Update the corresponding CashOut records when PettyCashMonthlyBase is updated
            $cashOut = Cashout::where('ProjectID', $pettyCashMonthlyBase->projectId)
                              ->where('Year', $pettyCashMonthlyBase->year)
                              ->where('branch', $pettyCashMonthlyBase->branch)
                              ->first();

            if ($cashOut) {
                // Get the values from Suppliers and Salary tables
                $suppliers = SuppliersMonthlyBase::where('ProjectID', $pettyCashMonthlyBase->projectId)
                                      ->where('year', $pettyCashMonthlyBase->year)
                                      ->where('branch', $pettyCashMonthlyBase->branch)
                                      ->first();

                $salary = SalaryMonthlyBase::where('ProjectID', $pettyCashMonthlyBase->projectId)
                                           ->where('year', $pettyCashMonthlyBase->year)
                                           ->where('branch', $pettyCashMonthlyBase->branch)
                                           ->first();

             Log::info('Values for August - Petty cash: ' . $pettyCashMonthlyBase->aug . ', Suppliers: ' . ($suppliers->aug ) . ', Salary: ' . ($salary->aug ));
                // Update the monthly values in CashOut by summing Salary, Suppliers, and PettyCash
                $cashOut->update([
                    'M01' => ($pettyCashMonthlyBase->jan == 0 || ($suppliers->jan ?? 0) == 0 || ($salary->jan ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->jan * 1000000 + ($suppliers->jan ?? 0) * 1000000 + ($salary->jan ?? 0) * 1000000),
                    'M02' => ($pettyCashMonthlyBase->feb == 0 || ($suppliers->feb ?? 0) == 0 || ($salary->feb ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->feb * 1000000 + ($suppliers->feb ?? 0) * 1000000 + ($salary->feb ?? 0) * 1000000),
                    'M03' => ($pettyCashMonthlyBase->mar == 0 || ($suppliers->mar ?? 0) == 0 || ($salary->mar ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->mar * 1000000 + ($suppliers->mar ?? 0) * 1000000 + ($salary->mar ?? 0) * 1000000),
                    'M04' => ($pettyCashMonthlyBase->apr == 0 || ($suppliers->apr ?? 0) == 0 || ($salary->apr ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->apr * 1000000 + ($suppliers->apr ?? 0) * 1000000 + ($salary->apr ?? 0) * 1000000),
                    'M05' => ($pettyCashMonthlyBase->may == 0 || ($suppliers->may ?? 0) == 0 || ($salary->may ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->may * 1000000 + ($suppliers->may ?? 0) * 1000000 + ($salary->may ?? 0) * 1000000),
                    'M06' => ($pettyCashMonthlyBase->jun == 0 || ($suppliers->jun ?? 0) == 0 || ($salary->jun ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->jun * 1000000 + ($suppliers->jun ?? 0) * 1000000 + ($salary->jun ?? 0) * 1000000),
                    'M07' => ($pettyCashMonthlyBase->jul == 0 || ($suppliers->jul ?? 0) == 0 || ($salary->jul ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->jul * 1000000 + ($suppliers->jul ?? 0) * 1000000 + ($salary->jul ?? 0) * 1000000),
                    'M08' => ($pettyCashMonthlyBase->aug == 0 || ($suppliers->aug ?? 0) == 0 || ($salary->aug ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->aug * 1000000 + ($suppliers->aug ?? 0) * 1000000 + ($salary->aug ?? 0) * 1000000),
                    'M09' => ($pettyCashMonthlyBase->sep == 0 || ($suppliers->sep ?? 0) == 0 || ($salary->sep ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->sep * 1000000 + ($suppliers->sep ?? 0) * 1000000 + ($salary->sep ?? 0) * 1000000),
                    'M10' => ($pettyCashMonthlyBase->oct == 0 || ($suppliers->oct ?? 0) == 0 || ($salary->oct ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->oct * 1000000 + ($suppliers->oct ?? 0) * 1000000 + ($salary->oct ?? 0) * 1000000),
                    'M11' => ($pettyCashMonthlyBase->nov == 0 || ($suppliers->nov ?? 0) == 0 || ($salary->nov ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->nov * 1000000 + ($suppliers->nov ?? 0) * 1000000 + ($salary->nov ?? 0) * 1000000),
                    'M12' => ($pettyCashMonthlyBase->december == 0 || ($suppliers->december ?? 0) == 0 || ($salary->december ?? 0) == 0) ? 0 : ($pettyCashMonthlyBase->december * 1000000 + ($suppliers->december ?? 0) * 1000000 + ($salary->december ?? 0) * 1000000),
                ]);
                

                // Recalculate the total for CashOut
                $total = $cashOut->M01 + $cashOut->M02 + $cashOut->M03 +
                         $cashOut->M04 + $cashOut->M05 + $cashOut->M06 +
                         $cashOut->M07 + $cashOut->M08 + $cashOut->M09 +
                         $cashOut->M10 + $cashOut->M11 + $cashOut->M12;


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
            }
        });
    }
}
