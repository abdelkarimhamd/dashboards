<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManpowerValueMonthlyBase extends Model
{
    use HasFactory;

    protected $table = 'manpower_monthly_base';

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

        static::updated(function ($manpowerValue) {
            // Update the corresponding PlanStaff records when ManpowerValueMonthlyBase is updated
            $planStaff = PlanStaff::where('ProjectID', $manpowerValue->projectId)
                                  ->where('Year', $manpowerValue->year)
                                  ->where('branch', $manpowerValue->branch)
                                  ->first();

            if ($planStaff) {
                // Update the monthly values in PlanStaff
                $planStaff->update([
                    'M01' => $manpowerValue->jan,
                    'M02' => $manpowerValue->feb,
                    'M03' => $manpowerValue->mar,
                    'M04' => $manpowerValue->apr,
                    'M05' => $manpowerValue->may,
                    'M06' => $manpowerValue->jun,
                    'M07' => $manpowerValue->jul,
                    'M08' => $manpowerValue->aug,
                    'M09' => $manpowerValue->sep,
                    'M10' => $manpowerValue->oct,
                    'M11' => $manpowerValue->nov,
                    'M12' => $manpowerValue->december,
                ]);

                // Recalculate the total for PlanStaff
                $planTotal = $planStaff->M01 + $planStaff->M02 + $planStaff->M03 +
                             $planStaff->M04 + $planStaff->M05 + $planStaff->M06 +
                             $planStaff->M07 + $planStaff->M08 + $planStaff->M09 +
                             $planStaff->M10 + $planStaff->M11 + $planStaff->M12;

                // Update the total in PlanStaff
                $planStaff->update([
                    'Total' => $planTotal,
                ]);

                // Update VarianceYTD and Performance in the ActualStaff table
                $actualStaff = ActualStaff::where('ProjectID', $planStaff->ProjectID)
                                          ->where('Year', $planStaff->Year)
                                          ->where('branch', $planStaff->branch)
                                          ->first();

                if ($actualStaff) {
                    $actualStaffTotal = $actualStaff->M01 + $actualStaff->M02 + $actualStaff->M03 +
                                        $actualStaff->M04 + $actualStaff->M05 + $actualStaff->M06 +
                                        $actualStaff->M07 + $actualStaff->M08 + $actualStaff->M09 +
                                        $actualStaff->M10 + $actualStaff->M11 + $actualStaff->M12;

                    // Update VarianceYTD and Performance in PlanStaff
                    $planVarianceYTD = $actualStaffTotal-$planTotal;
                    $planPerformance = $planTotal == 0 ? 0 : ( $actualStaffTotal/$planTotal ) * 100;

                    $planStaff->update([
                        'VarianceYTD' => $planVarianceYTD,
                        'Performance' => $planPerformance,
                    ]);

                    // Update VarianceYTD and Performance in ActualStaff
                    $actualVarianceYTD = $actualStaffTotal - $planTotal;
                    $actualPerformance = $planTotal == 0 ? 0 : ($actualStaffTotal / $planTotal) * 100;

                    $actualStaff->update([
                        'VarianceYTD' => $actualVarianceYTD,
                        'Performance' => $actualPerformance,
                    ]);
                }
            }
        });
    }
}
