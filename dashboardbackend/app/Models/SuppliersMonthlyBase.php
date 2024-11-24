<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SuppliersMonthlyBase extends Model
{
    use HasFactory;

    protected $table = 'suppliers_monthly_base';

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

        static::updated(function ($suppliersMonthlyBase) {
            // Update the corresponding CashOut records when SuppliersMonthlyBase is updated
            $cashOut = Cashout::where('ProjectID', $suppliersMonthlyBase->projectId)
                              ->where('Year', $suppliersMonthlyBase->year)
                              ->where('branch', $suppliersMonthlyBase->branch)
                              ->first();

            if ($cashOut) {
                // Get the values from Salary and PettyCash tables
                $salary = SalaryMonthlyBase::where('ProjectID', $suppliersMonthlyBase->projectId)
                                           ->where('year', $suppliersMonthlyBase->year)
                                           ->where('branch', $suppliersMonthlyBase->branch)
                                           ->first();

                $pettyCash = PettyCashMonthlyBase::where('ProjectID', $suppliersMonthlyBase->projectId)
                                                 ->where('year', $suppliersMonthlyBase->year)
                                                 ->where('branch', $suppliersMonthlyBase->branch)
                                                 ->first();
                Log::info('Values for August - Suppliers: ' . $suppliersMonthlyBase->aug . ', Salary: ' . ($salary->aug ) . ', PettyCash: ' . ($pettyCash->aug ));
                // Update the monthly values in CashOut by summing Salary, Suppliers, and PettyCash
                $cashOut->update([
                    'M01' => ($suppliersMonthlyBase->jan == 0 || ($salary->jan ?? 0) == 0 || ($pettyCash->jan ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->jan * 1000000 + ($salary->jan ?? 0) * 1000000 + ($pettyCash->jan ?? 0) * 1000000),
                    'M02' => ($suppliersMonthlyBase->feb == 0 || ($salary->feb ?? 0) == 0 || ($pettyCash->feb ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->feb * 1000000 + ($salary->feb ?? 0) * 1000000 + ($pettyCash->feb ?? 0) * 1000000),
                    'M03' => ($suppliersMonthlyBase->mar == 0 || ($salary->mar ?? 0) == 0 || ($pettyCash->mar ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->mar * 1000000 + ($salary->mar ?? 0) * 1000000 + ($pettyCash->mar ?? 0) * 1000000),
                    'M04' => ($suppliersMonthlyBase->apr == 0 || ($salary->apr ?? 0) == 0 || ($pettyCash->apr ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->apr * 1000000 + ($salary->apr ?? 0) * 1000000 + ($pettyCash->apr ?? 0) * 1000000),
                    'M05' => ($suppliersMonthlyBase->may == 0 || ($salary->may ?? 0) == 0 || ($pettyCash->may ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->may * 1000000 + ($salary->may ?? 0) * 1000000 + ($pettyCash->may ?? 0) * 1000000),
                    'M06' => ($suppliersMonthlyBase->jun == 0 || ($salary->jun ?? 0) == 0 || ($pettyCash->jun ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->jun * 1000000 + ($salary->jun ?? 0) * 1000000 + ($pettyCash->jun ?? 0) * 1000000),
                    'M07' => ($suppliersMonthlyBase->jul == 0 || ($salary->jul ?? 0) == 0 || ($pettyCash->jul ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->jul * 1000000 + ($salary->jul ?? 0) * 1000000 + ($pettyCash->jul ?? 0) * 1000000),
                    'M08' => ($suppliersMonthlyBase->aug == 0 || ($salary->aug ?? 0) == 0 || ($pettyCash->aug ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->aug * 1000000 + ($salary->aug ?? 0) * 1000000 + ($pettyCash->aug ?? 0) * 1000000),
                    'M09' => ($suppliersMonthlyBase->sep == 0 || ($salary->sep ?? 0) == 0 || ($pettyCash->sep ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->sep * 1000000 + ($salary->sep ?? 0) * 1000000 + ($pettyCash->sep ?? 0) * 1000000),
                    'M10' => ($suppliersMonthlyBase->oct == 0 || ($salary->oct ?? 0) == 0 || ($pettyCash->oct ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->oct * 1000000 + ($salary->oct ?? 0) * 1000000 + ($pettyCash->oct ?? 0) * 1000000),
                    'M11' => ($suppliersMonthlyBase->nov == 0 || ($salary->nov ?? 0) == 0 || ($pettyCash->nov ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->nov * 1000000 + ($salary->nov ?? 0) * 1000000 + ($pettyCash->nov ?? 0) * 1000000),
                    'M12' => ($suppliersMonthlyBase->december == 0 || ($salary->december ?? 0) == 0 || ($pettyCash->december ?? 0) == 0) ? 0 : ($suppliersMonthlyBase->december * 1000000 + ($salary->december ?? 0) * 1000000 + ($pettyCash->december ?? 0) * 1000000),
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
