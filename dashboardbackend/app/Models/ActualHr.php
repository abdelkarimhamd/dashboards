<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualHr extends Model
{
    use HasFactory;

    protected $table = 'actual_HR';

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

        static::updated(function ($actualHr) {
            // Update the corresponding ActualStaff records when ActualHr is updated
            $actualStaff = ActualStaff::where('ProjectID', $actualHr->projectId)
                                      ->where('Year', $actualHr->year)
                                      ->where('branch', $actualHr->branch)
                                      ->first();

            if ($actualStaff) {
                // Update the monthly values in ActualStaff
                $actualStaff->update([
                    'M01' => $actualHr->jan,
                    'M02' => $actualHr->feb,
                    'M03' => $actualHr->mar,
                    'M04' => $actualHr->apr,
                    'M05' => $actualHr->may,
                    'M06' => $actualHr->jun,
                    'M07' => $actualHr->jul,
                    'M08' => $actualHr->aug,
                    'M09' => $actualHr->sep,
                    'M10' => $actualHr->oct,
                    'M11' => $actualHr->nov,
                    'M12' => $actualHr->december,
                ]);

                // Recalculate the total for ActualStaff
                $actualTotal = $actualStaff->M01 + $actualStaff->M02 + $actualStaff->M03 +
                               $actualStaff->M04 + $actualStaff->M05 + $actualStaff->M06 +
                               $actualStaff->M07 + $actualStaff->M08 + $actualStaff->M09 +
                               $actualStaff->M10 + $actualStaff->M11 + $actualStaff->M12;

                // Update the total in ActualStaff
                $actualStaff->update([
                    'Total' => $actualTotal,
                ]);

                // Update VarianceYTD and Performance in the ActualStaff table based on PlanStaff
                $planStaff = PlanStaff::where('ProjectID', $actualStaff->ProjectID)
                                      ->where('Year', $actualStaff->Year)
                                      ->where('branch', $actualStaff->branch)
                                      ->first();

                if ($planStaff) {
                    $planStaffTotal = $planStaff->M01 + $planStaff->M02 + $planStaff->M03 +
                                      $planStaff->M04 + $planStaff->M05 + $planStaff->M06 +
                                      $planStaff->M07 + $planStaff->M08 + $planStaff->M09 +
                                      $planStaff->M10 + $planStaff->M11 + $planStaff->M12;

                    // Update VarianceYTD and Performance in PlanStaff
                    $planVarianceYTD = $actualTotal - $planStaffTotal;
                    $planPerformance =  $planStaffTotal == 0 ? 0 : ($actualTotal / $planStaffTotal) * 100;

                    $planStaff->update([
                        'VarianceYTD' => $planVarianceYTD,
                        'Performance' => $planPerformance,
                    ]);

                    // Update VarianceYTD and Performance in ActualStaff
                    $actualVarianceYTD = $actualTotal - $planStaffTotal;
                    $actualPerformance = $planStaffTotal == 0 ? 0 : ($actualTotal / $planStaffTotal) * 100;

                    $actualStaff->update([
                        'VarianceYTD' => $actualVarianceYTD,
                        'Performance' => $actualPerformance,
                    ]);
                }
            }
        });
    }
}
