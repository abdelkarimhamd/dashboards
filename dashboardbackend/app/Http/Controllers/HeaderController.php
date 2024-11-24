<?php

namespace App\Http\Controllers;
use App\Models\ExpectedPettyCashValue;
use App\Models\Header;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyReport;
use App\Models\ActualValueMonthlyBase;
use App\Models\CashInMonthlyBase;
use App\Models\ManpowerValueMonthlyBase;
use App\Models\OperationValue;
use App\Models\PettyCashMonthlyBase;
use App\Models\SalaryMonthlyBase;
use App\Models\StaffRegistartion;
use App\Models\SuppliersMonthlyBase;
use App\Models\ActualHr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ActualStaff;
use App\Models\PlanStaff;
use App\Models\ActualInvoice;
use App\Models\FcInvoice;
use App\Models\Cashin;
use App\Models\Cashout;
use App\Models\CertifiedInvoice;
use App\Models\ExpectedCollectionValue;
use App\Models\ExpectedSalaryValue;
use App\Models\ExpectedSuppliersValue;
use App\Models\FitOutProject;
use App\Models\Hocostoverhd;
use App\Models\PmFcInvoiceCurrentMonth;

class HeaderController extends Controller
{
    public function addHeader(Request $request)
{
    // Validate the incoming request data
    $request->validate([
        'fileName' => 'required|file',
        'ProjectImageFilePath' => 'required|file',
        'selectedOption' => 'required|string',
        'projectName' => 'required|string',
        'clientName' => 'required|string',
        'location' => 'required|string',
        'duration' => 'required|integer',
        'selectedDate' => 'required|date',
        'value' => 'required|numeric',
        'manPower' => 'required|integer',
        'projectManagerName' => 'required|string',
        'branch' => 'required|string',
    ]);

    try {
        // Create and save the header
        $header = new Header();
        $header->filePath = $request->file('fileName')->store('Logos');
        $header->ProjectImageFilePath = $request->file('ProjectImageFilePath')->store('projectImages');
        $header->projectType = $request->input('selectedOption');
        $header->projectName = $request->input('projectName');
        $header->clientName = $request->input('clientName');
        $header->projectLocation = $request->input('location');
        $header->projectDuration = $request->input('duration');
        $header->projectDate = $request->input('selectedDate');
        $header->projectValue = $request->input('value');
        $header->ProjetPeakManpower = $request->input('manPower');
        $header->projectManagerName = $request->input('projectManagerName');
        $header->branch = $request->input('branch');
        $header->save();

        // Convert the project start date to a Carbon instance
        $startDate = Carbon::createFromFormat('Y-m-d', $request->input('selectedDate'));

        // Calculate the end date by adding the project duration (in months)
        $endDate = $startDate->copy()->addMonths($header->projectDuration);
    
        // Calculate the number of years based on duration in months
        $totalYears = ceil($header->projectDuration / 12);
    
        // Loop through each year and insert data into project_details
        for ($year = 0; $year <= $totalYears; $year++) {
            $currentYear = $startDate->copy()->addYears($year)->year;

            $projectDetails = [
                'ProjectID' => $header->id, // Foreign key to headers table
                'branch' => $header->branch,
                'ProjectName' => $header->projectName,
                'YearSelected' => $currentYear,
                'MainScope' => $header->projectType,
                'ProjectManager' => $header->projectManagerName, // Assuming this needs to be added in the table
            ];

            DB::table('project_details')->insert($projectDetails);
        }

        // Initialize related data
        $this->insertMonthlyForcast($header->id, $header->branch, $header->projectValue, $header->projectDuration, $startDate);
        $this->insertPlanStaff($header->id, $header->branch, $header->ProjetPeakManpower, $header->projectDuration, $startDate);
        $this->initializingActualInvoiceValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingActualStaffValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->insertMonthlyBudget($header->id, $header->branch, $header->projectValue, $header->projectDuration, $startDate);
        $this->insertManpowerValue($header->id, $header->branch, $header->ProjetPeakManpower, $header->projectDuration, $startDate);
        $this->initializingActualValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->intializingActualHrValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingOperationValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingPettyCashValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingSalaryValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingStaffRegistartionValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingSuppliersValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingCashinValue($header->id, $header->branch, $header->projectDuration, $startDate);
        
        // New methods for initializing values
        
        
        $this->initializingCashinplValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingCashoutValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingCertifiedCostValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingOverHeadValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingPmFcinvoiceValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingExpectedSalaryValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingExpectedSuppliersValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingExpectedPettyCashValue($header->id, $header->branch, $header->projectDuration, $startDate);
        $this->initializingExpectedCollectionValue($header->id, $header->branch, $header->projectDuration, $startDate);
       
        // Return response including the calculated end date
       
        return response()->json([
            'header' => $header,
            'project_end_date' => $endDate->format('Y-m-d')  // Include the end date in the response
        ], 201);
        
    } catch (\Exception $e) {
        Log::error('Error adding header: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to add header', 'error' => $e->getMessage()], 500);
    }
}
//initializingPmFcinvoiceValue
protected function initializingPmFcinvoiceValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualValueMonthlyBase for the current year
          $actualValue = new PmFcInvoiceCurrentMonth();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual Value initialized successfully'], 201);
  }
  protected function initializingExpectedSalaryValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualValueMonthlyBase for the current year
          $actualValue = new ExpectedSalaryValue();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual Value initialized successfully'], 201);
  }

  protected function initializingExpectedCollectionValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualValueMonthlyBase for the current year
          $actualValue = new ExpectedCollectionValue();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual Value initialized successfully'], 201);
  }
  protected function initializingExpectedSuppliersValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualValueMonthlyBase for the current year
          $actualValue = new ExpectedSuppliersValue();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual Value initialized successfully'], 201);
  }
  protected function initializingExpectedPettyCashValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualValueMonthlyBase for the current year
          $actualValue = new ExpectedPettyCashValue();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual Value initialized successfully'], 201);
  }
  protected function initializingActualValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualValueMonthlyBase for the current year
          $actualValue = new ActualValueMonthlyBase();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual Value initialized successfully'], 201);
  }
  
  protected function intializingActualHrValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new ActualHr for the current year
          $actualValue = new ActualHr();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Actual HR Value initialized successfully'], 201);
  }
  
  protected function initializingCashinValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new CashInMonthlyBase for the current year
          $actualValue = new CashInMonthlyBase();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Cashin Value initialized successfully'], 201);
  }
  
  protected function initializingOperationValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new OperationValue for the current year
          $actualValue = new OperationValue();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Operation Value initialized successfully'], 201);
  }
  
  protected function initializingPettyCashValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new PettyCashMonthlyBase for the current year
          $actualValue = new PettyCashMonthlyBase();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Petty Cash Value initialized successfully'], 201);
  }
  
  protected function initializingSuppliersValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new SuppliersMonthlyBase for the current year
          $actualValue = new SuppliersMonthlyBase();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              break;
          }
      }
  
      return response()->json(['message' => 'Suppliers Value initialized successfully'], 201);
  }
  
  protected function initializingSalaryValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new SalaryMonthlyBase for the current year
          $actualValue = new SalaryMonthlyBase();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Salary Value initialized successfully'], 201);
  }
  
  protected function initializingStaffRegistartionValue($headerId, $branch, $duration, Carbon $startDate)
  {
      // Calculate the end date of the project
      $endDate = $startDate->copy()->addMonths($duration - 1);
  
      // Initialize the date for iteration
      $currentDate = $startDate->copy();
  
      while ($currentDate->lessThanOrEqualTo($endDate)) {
          // Create a new StaffRegistartion for the current year
          $actualValue = new StaffRegistartion();
          $actualValue->projectId = $headerId;
          $actualValue->branch = $branch; // Add branch value
          $actualValue->year = $currentDate->year;
  
          // Initialize all months to 0
          $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
          foreach ($months as $month) {
              $actualValue->{$month} = 0;
          }
  
          $actualValue->save();
  
          // Move to the next year, ensuring not to skip any months in the final year
          if ($currentDate->year < $endDate->year) {
              $currentDate->addYear()->startOfYear();
          } else {
              // Ensure we break the loop after saving the final year's data
              break;
          }
      }
  
      return response()->json(['message' => 'Staff Registration Value initialized successfully'], 201);
  }
  
  protected function insertMonthlyBudget($headerId, $branch, $totalValue, $duration, Carbon $startDate)
{
    $monthlyBudgetValue = $totalValue / $duration;

    // Calculate the end date of the project
    $endDate = $startDate->copy()->addMonths($duration);

    // Initialize the date for iteration
    $currentDate = $startDate->copy();

    $overallTotal = 0; // Initialize the total that will be used to update Fcinvoice

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Create a new monthly report for the current year
        $monthlyReport = new MonthlyReport();
        $monthlyReport->projectId = $headerId;
        $monthlyReport->branch = $branch; // Add branch value
        $monthlyReport->year = $currentDate->year;

        // Initialize all months to 0
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
        foreach ($months as $month) {
            $monthlyReport->{$month} = 0;
        }

        // Fill the months with the budget value for the current year
        $startMonth = $currentDate->year === $startDate->year ? $startDate->month : 1;
        $endMonth = $currentDate->year === $endDate->year ? $endDate->month : 12;

        $yearlyTotal = 0; // To keep track of the total for this yearly report

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $monthName = $months[$month - 1]; // Adjust for 0-indexed array
            $monthlyReport->{$monthName} = $monthlyBudgetValue;
            $yearlyTotal += $monthlyBudgetValue; // Add the budget value to the yearly total
        }

        $monthlyReport->save();

        $overallTotal += $yearlyTotal; // Add this year's total to the overall total

        // Move to the next year
        $currentDate->addYear()->startOfYear();
       
    }

    $this->updateFcInvoiceTotal($headerId);

    return response()->json(['message' => 'Monthly budget inserted and Fcinvoice total updated successfully'], 201);
}

protected function updateFcInvoiceTotal($projectId)
{


    // Get all monthly budget records for the given project
    $monthlyReports = MonthlyReport::where('projectId', $projectId)->get();
 


    // Create an array to accumulate totals for each year
    $yearlyTotals = [];

    foreach ($monthlyReports as $report) {
        // Initialize total for the year if not already set
        if (!isset($yearlyTotals[$report->year])) {
            $yearlyTotals[$report->year] = 0;
        }

        // Sum all the monthly values for the year
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $month) {
            $yearlyTotals[$report->year] += $report->{$month};
        }
    }

    // Update the FcInvoice table for each year
    foreach ($yearlyTotals as $year => $total) {
        $originalTotal = $total;
        $total *= 1000000;  // Multiply the total by 1,000,000

        // Find or create a new record in the FcInvoice table for the specific project and year
        $fcInvoice = FcInvoice::updateOrCreate(
            ['ProjectID' => $projectId, 'Year' => $year],  // Match based on ProjectID and Year
            ['Total' => $total]  // Update or set the total
        );
       
    }

    // Get all ActualInvoice records for the project
    $actualInvoiceRecords = ActualInvoice::where('ProjectID', $projectId)->get();


    // Iterate over ActualInvoice records and calculate VarianceYTD
    foreach ($actualInvoiceRecords as $actualInvoice) {
        $year = $actualInvoice->Year;

        // Check if there's a corresponding FcInvoice total for the same year
        if (isset($yearlyTotals[$year])) {
            $fcInvoiceTotal = $yearlyTotals[$year]*1000000;
            
            // Calculate VarianceYTD as the negative of the FcInvoice total
            $actualVarianceYTD = -$fcInvoiceTotal;

            // Update ActualInvoice table with VarianceYTD
            $actualInvoice->update([
                'VarianceYTD' => $actualVarianceYTD,
            ]);
           
            // Also update the FcInvoice record with the same variance (if needed)
            FcInvoice::where('ProjectID', $projectId)
                     ->where('Year', $year)
                     ->update(['VarianceYTD' => $actualVarianceYTD]);
            
        } else {
            Log::warning('No FcInvoice record found for ProjectID: ' . $projectId . ', Year: ' . $year);
        }
    }

  

    return response()->json(['message' => 'FcInvoice totals and ActualInvoice values updated successfully'], 200);
}




  public function getCurrentMonthBudgetValue(Request $request, $projectId)
{
    $monthMapping = [
        'January' => 'jan',
        'February' => 'feb',
        'March' => 'mar',
        'April' => 'apr',
        'May' => 'may',
        'June' => 'jun',
        'July' => 'jul',
        'August' => 'aug',
        'September' => 'sep',
        'October' => 'oct',
        'November' => 'nov',
        'December' => 'december',
    ];

    // Get the previous month and year
    $previousMonthFullName = Carbon::now()->subMonth()->format('F'); // 'January', 'February', etc.
    $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
    $currentYear = Carbon::now()->year;

    // Attempt to retrieve the OperationValue record for the previous month and year
    $monthlyValueRecord = MonthlyReport::where('ProjectId', $projectId)
                                         ->where('year', $currentYear)
                                         ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No operation value record found for the previous month'], 404);
    }

    // Return the value for the previous month
    return response()->json([
        'month' => $previousMonthFullName,
        'year' => $currentYear,
        'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
    ]);
}

protected function insertManpowerValue($headerId, $branch, $totalValue, $duration, Carbon $startDate)
{
    $monthlyManpowerValue = $totalValue / $duration;

    // Calculate the end date of the project
    $endDate = $startDate->copy()->addMonths($duration);

    // Initialize the date for iteration
    $currentDate = $startDate->copy();

    $overallTotal = 0; // Initialize the total manpower for updating planStaff

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Create a new monthly report for the current year
        $monthlyReport = new ManpowerValueMonthlyBase();
        $monthlyReport->projectId = $headerId;
        $monthlyReport->branch = $branch; // Add branch value
        $monthlyReport->year = $currentDate->year;

        // Initialize all months to 0
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
        foreach ($months as $month) {
            $monthlyReport->{$month} = 0;
        }

        // Fill the months with the manpower value for the current year
        $startMonth = $currentDate->year === $startDate->year ? $startDate->month : 1;
        $endMonth = $currentDate->year === $endDate->year ? $endDate->month : 12;

        $yearlyTotal = 0; // To keep track of the yearly total

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $monthName = $months[$month - 1]; // Adjust for 0-indexed array
            $monthlyReport->{$monthName} = $monthlyManpowerValue;
            $yearlyTotal += $monthlyManpowerValue; // Add to yearly total
        }

        $monthlyReport->save();

        $overallTotal += $yearlyTotal; // Add yearly total to overall total

        // Move to the next year
        $currentDate->addYear()->startOfYear();
    }

    $this->updatePlanStaffTotal($headerId);

    return response()->json(['message' => 'Monthly manpower inserted and planStaff total updated successfully'], 201);
}

protected function updatePlanStaffTotal($projectId)
{
    

    // Get all monthly manpower records for the given project
    $monthlyReports = ManpowerValueMonthlyBase::where('projectId', $projectId)->get();
   

    // Create an array to accumulate totals for each year from PlanStaff
    $yearlyTotals = [];

    foreach ($monthlyReports as $report) {
        // Initialize total for the year if not already set
        if (!isset($yearlyTotals[$report->year])) {
            $yearlyTotals[$report->year] = 0;
        }

        // Sum all the monthly values for the year
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $month) {
            $yearlyTotals[$report->year] += $report->{$month};  // Add each month's value to the year's total
        }
    }

    // Update the PlanStaff table for each year
    foreach ($yearlyTotals as $year => $planTotal) {
        // Find or create a new record in the PlanStaff table for the specific project and year
        $planStaff = PlanStaff::updateOrCreate(
            ['ProjectID' => $projectId, 'Year' => $year],  // Match based on ProjectID and Year
            ['Total' => $planTotal]  // Update or set the total
        );
       
    }

    // Get all ActualStaff records for the project
    $actualStaffRecords = ActualStaff::where('ProjectID', $projectId)->get();
   

    // Iterate over ActualStaff records and calculate VarianceYTD
    foreach ($actualStaffRecords as $actualStaff) {
        $year = $actualStaff->Year;
        
        // Check if there's a corresponding PlanStaff total for the same year
        if (isset($yearlyTotals[$year])) {
            $planTotal = $yearlyTotals[$year];
            // Calculate VarianceYTD as the negative of the PlanStaff total
            $actualVarianceYTD = -$planTotal;

            // Update ActualStaff table with VarianceYTD
            $actualStaff->update([
                'VarianceYTD' => $actualVarianceYTD,
            ]);
           

            // Also update the PlanStaff record with the same variance
            PlanStaff::where('ProjectID', $projectId)
                     ->where('Year', $year)
                     ->update(['VarianceYTD' => $actualVarianceYTD]);
           
        } else {
            Log::warning('No PlanStaff record found for ProjectID: ' . $projectId . ', Year: ' . $year);
        }
    }

  

    return response()->json(['message' => 'PlanStaff totals and ActualStaff values updated successfully'], 200);
}



  public function getCurrentMonthManpowerValue(Request $request, $projectId)
{
     // Define month abbreviation mapping
     $monthMapping = [
        'January' => 'jan',
        'February' => 'feb',
        'March' => 'mar',
        'April' => 'apr',
        'May' => 'may',
        'June' => 'jun',
        'July' => 'jul',
        'August' => 'aug',
        'September' => 'sep',
        'October' => 'oct',
        'November' => 'nov',
        'December' => 'december',
    ];

    // Get the previous month and year
    $previousMonthFullName = Carbon::now()->subMonth()->format('F'); // 'January', 'February', etc.
    $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
    $currentYear = Carbon::now()->year;

    // Attempt to retrieve the OperationValue record for the previous month and year
    $monthlyValueRecord = ManpowerValueMonthlyBase::where('ProjectId', $projectId)
                                         ->where('year', $currentYear)
                                         ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No operation value record found for the previous month'], 404);
    }

    // Return the value for the previous month
    return response()->json([
        'month' => $previousMonthFullName,
        'year' => $currentYear,
        'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
    ]);
}
  public function calculateCumulativeBudget($projectId) {
    $previousMonth = now()->subMonth()->format('m'); // Get the previous month number
    $currentYear = now()->year;
    
    $header = Header::where('id', $projectId)->first();

    if (!$header) {
        return response()->json(['message' => 'Project not found'], 404);
    }

    $totalProjectValue = $header->projectValue;
    
    // Retrieve all monthly reports for the given project ID up to the current month and year
    $monthlyReports = MonthlyReport::where('projectId', $projectId)
                        ->where('year', '<=', $currentYear)
                        ->get();

    $cumulativeBudget = 0;

    foreach ($monthlyReports as $report) {
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
            // If the report is from a past year or the previous month of the current year, add to cumulative
            if ($report->year < $currentYear || ($report->year == $currentYear && $index + 1 <= $previousMonth)) {
                $cumulativeBudget += $report->{$month};
            }
        }
    }

    $percentageSpent = $totalProjectValue > 0 ? ($cumulativeBudget / $totalProjectValue) * 100 : 0;

    return response()->json([
        'cumulativeBudget' => $cumulativeBudget,
        'percentageSpent' => $percentageSpent
    ]);
}

public function storeBudgetMonthlyValue(Request $request, $projectId)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);
    
        $value = $request->value;
    
        $header = Header::where('id', $projectId)->first();
        if (!$header) {
            return response()->json(['message' => 'Header not found'], 404);
        }
    
        // Mapping of months
        $monthMapping = [
            'January' => 'jan', 'February' => 'feb', 'March' => 'mar',
            'April' => 'apr', 'May' => 'may', 'June' => 'jun',
            'July' => 'jul', 'August' => 'aug', 'September' => 'sep',
            'October' => 'oct', 'November' => 'nov', 'December' => 'december',
        ];
    
        // Get the previous month and year
        $previousMonthFullName = Carbon::now()->subMonth()->format('F'); // Get the previous month
        $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
        $previousYear = Carbon::now()->subMonth()->year;
    
        // Retrieve or create a record for the previous year
        $monthlyValueRecord = MonthlyReport::firstOrCreate(
            ['projectId' => $projectId, 'year' => $previousYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );
    
        // Store the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Monthly value saved for the previous month successfully']);
    }

    public function storePlannedMonthlyValue(Request $request, $projectId)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);
    
        $value = $request->value;
    
        $header = Header::where('id', $projectId)->first();
        if (!$header) {
            return response()->json(['message' => 'Header not found'], 404);
        }
    
        // Mapping of months
        $monthMapping = [
            'January' => 'jan', 'February' => 'feb', 'March' => 'mar',
            'April' => 'apr', 'May' => 'may', 'June' => 'jun',
            'July' => 'jul', 'August' => 'aug', 'September' => 'sep',
            'October' => 'oct', 'November' => 'nov', 'December' => 'december',
        ];
    
        // Get the previous month and year
        $previousMonthFullName = Carbon::now()->subMonth()->format('F'); // Get the previous month
        $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
        $previousYear = Carbon::now()->subMonth()->year;
    
        // Retrieve or create a record for the previous year
        $monthlyValueRecord = ManpowerValueMonthlyBase::firstOrCreate(
            ['projectId' => $projectId, 'year' => $previousYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );
    
        // Store the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Planned value for the previous month saved successfully']);
    
    }

public function calculateCumulativeManpower($projectId) {
    $previousMonth = now()->subMonth()->format('m'); // Get the previous month number
    $currentYear = now()->year;
    
    $header = Header::where('id', $projectId)->first();

    if (!$header) {
        return response()->json(['message' => 'Project not found'], 404);
    }

    $totalProjectValue = $header->ProjetPeakManpower;
    
    // Retrieve all monthly reports for the given project ID up to the current month and year
    $monthlyReports = ManpowerValueMonthlyBase::where('projectId', $projectId)
                        ->where('year', '<=', $currentYear)
                        ->get();

    $cumulativeManpower = 0;

    foreach ($monthlyReports as $report) {
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
            // If the report is from a past year or the previous month of the current year, add to cumulative
            if ($report->year < $currentYear || ($report->year == $currentYear && $index + 1 <= $previousMonth)) {
                $cumulativeManpower += $report->{$month};
            }
        }
    }

    $percentageSpent = $totalProjectValue > 0 ? ($cumulativeManpower / $totalProjectValue) * 100 : 0;

    return response()->json([
        'cumulativeManpower' => $cumulativeManpower,
        'percentageSpent' => $percentageSpent
    ]);
}

public function getUAEProjectYears()
{


    $projects = Header::where('branch', 'UAE')->get();
    return response()->json(['projects' => $projects], Response::HTTP_OK);
}
public function getKSAProjectYears()
{


    $projects = Header::where('branch', 'KSA')->get();
    return response()->json(['projects' => $projects], Response::HTTP_OK);
}

public function listAll()
{
    return Header::all();
}

public function list()
{
    $user = Auth::user();
    $projects = Header::where('branch', $user->branch)->get();
    return response()->json(['projects' => $projects], Response::HTTP_OK);
}

    public function getManagedProjects($username)
    {
        $projects = Header::where('projectManagerName', $username)->get();
        return response()->json(['projects' => $projects], 200);
    }

    
    public function getProjectsFODepartment()
    {
        $user = Auth::user();
        $projects = [];
    
        if ($user->role === 'Department Head' ) {
            if ($user->department === 'Fit Out') {
                $projectTypes = ['FO', 'FOM', 'DNP'];
                $projects = FitOutProject::whereIn('project_type', $projectTypes)
                                  ->where('branch', $user->branch)
                                  ->get();
            }
            // Add more conditions for other departments if needed
        } else {
            // If the user is not a Department Head, return projects filtered by their branch
            $projects = FitOutProject::where('branch', $user->branch)->get();
        }
    
        return response()->json(['projects' => $projects], Response::HTTP_OK);
    }
    public function getUAEFoProjectYears()
    {
    
    
        $projects = FitOutProject::where('branch', 'UAE')->get();
        return response()->json(['projects' => $projects], Response::HTTP_OK);
    }
    public function getKSAFoProjectYears()
    {
    
        $projects = FitOutProject::where('branch', 'KSA')->get();
        return response()->json(['projects' => $projects], Response::HTTP_OK);
    }  

    public function getProjectsBasedOnRoleAndDepartment()
    {
        $user = Auth::user();
        $projects = [];
    
        if ($user->role === 'Department Head') {
            if ($user->department === 'Asset Management' || $user->department === 'Facility Management') {
                $projectTypes = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
                $projects = Header::whereIn('projectType', $projectTypes)
                                  ->where('branch', $user->branch)
                                  ->get();
            } else if ($user->department === 'Fit Out') {
                $projectTypes = ['FO', 'FOM', 'DNP'];
                $projects = Header::whereIn('projectType', $projectTypes)
                                  ->where('branch', $user->branch)
                                  ->get();
            }
            // Add more conditions for other departments if needed
        } else {
            // If the user is not a Department Head, return projects filtered by their branch
            $projects = Header::where('branch', $user->branch)->get();
        }
    
        return response()->json(['projects' => $projects], Response::HTTP_OK);
    }

    

    public function getProjectByName($projectName)
{
    $project = Header::where('projectName', $projectName)->first();

    if ($project) {
        // Assuming 'projectDate' is a date field and 'projectDuration' is in months
        $startDate = \Carbon\Carbon::parse($project->projectDate);
        $durationInMonths = $project->projectDuration;

        // Calculate the end date by adding the duration in months to the start date
        $endDate = $startDate->copy()->addMonths($durationInMonths);

        // Convert the project data to an array and add the calculated end date
        $projectData = $project->toArray();
        $projectData['endDate'] = $endDate->format('Y-m-d');

        // Return the project data including the end date
        return response()->json($projectData);
    } else {
        return response()->json(['message' => 'Project not found'], 404);
    }
}

public function getFOProjectByName($projectName)
{
    $project = FitOutProject::where('project_name', $projectName)->first();

    if ($project) {
        
        $projectData = $project->toArray();

        return response()->json($projectData);
    } else {
        return response()->json(['message' => 'Project not found'], 404);
    }
}    
// initialize p & l

protected function initializingActualStaffValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $actualStaff = new ActualStaff();
        $actualStaff->ProjectID = $headerId;
        $actualStaff->branch = $branch;
        $actualStaff->Year = $currentDate->year;

        $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $actualStaff->{$month} = 0;
        }

        $actualStaff->Total = 0;
        $actualStaff->VarianceYTD = 0;
        $actualStaff->Performance = 0;
        $actualStaff->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Actual Staff Value initialized successfully'], 201);
}

protected function initializingPlanStaffValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $planStaff = new PlanStaff();
        $planStaff->ProjectID = $headerId;
        $planStaff->branch = $branch;
        $planStaff->Year = $currentDate->year;

        $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $planStaff->{$month} = 0;
        }

        $planStaff->Total = 0;
        $planStaff->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Plan Staff Value initialized successfully'], 201);
}

protected function initializingCashoutValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $cashout = new Cashout();
        $cashout->ProjectID = $headerId;
        $cashout->branch = $branch;
        $cashout->Year = $currentDate->year;

        $months =['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $cashout->{$month} = 0;
        }

        $cashout->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Cashout Value initialized successfully'], 201);
}

protected function initializingCashinplValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $cashout = new Cashin();
        $cashout->ProjectID = $headerId;
        $cashout->branch = $branch;
        $cashout->Year = $currentDate->year;

        $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $cashout->{$month} = 0;
        }

        $cashout->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Cashout Value initialized successfully'], 201);
}

protected function initializingActualInvoiceValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $actualInvoice = new ActualInvoice();
        $actualInvoice->ProjectID = $headerId;
        $actualInvoice->branch = $branch;
        $actualInvoice->Year = $currentDate->year;

        $months =['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $actualInvoice->{$month} = 0;
        }

        $actualInvoice->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Actual Invoice Value initialized successfully'], 201);
}
protected function initializingOverHeadValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $actualInvoice = new Hocostoverhd();
        $actualInvoice->ProjectID = $headerId;
        $actualInvoice->branch = $branch;
        $actualInvoice->Year = $currentDate->year;

        $months =['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $actualInvoice->{$month} = 0;
        }

        $actualInvoice->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Actual Invoice Value initialized successfully'], 201);
}

protected function initializingCertifiedCostValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $actualInvoice = new CertifiedInvoice();
        $actualInvoice->ProjectID = $headerId;
        $actualInvoice->branch = $branch;
        $actualInvoice->Year = $currentDate->year;

        $months =['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $actualInvoice->{$month} = 0;
        }

        $actualInvoice->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Actual Invoice Value initialized successfully'], 201);
}


protected function initializingFcInvoiceValue($headerId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $fcInvoice = new FcInvoice();
        $fcInvoice->ProjectID = $headerId;
        $fcInvoice->branch = $branch;
        $fcInvoice->Year = $currentDate->year;

        $months =['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $fcInvoice->{$month} = 0;
        }

        $fcInvoice->save();

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'FC Invoice Value initialized successfully'], 201);
}
protected function insertMonthlyForcast($headerId, $branch, $totalValue, $duration, Carbon $startDate)
{ 
   

    $monthlyBudgetValue = $totalValue / $duration;

    // Calculate the end date of the project
    $endDate = $startDate->copy()->addMonths($duration);

    // Initialize the date for iteration
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Create a new monthly report for the current year
        $monthlyReport = new FcInvoice();
        $monthlyReport->projectId = $headerId;
        $monthlyReport->branch = $branch; // Add branch value
        $monthlyReport->year = $currentDate->year;

        // Initialize all months to 0
        $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $monthlyReport->{$month} = 0;
        }

        // Fill the months with the budget value for the current year
        $startMonth = $currentDate->year === $startDate->year ? $startDate->month : 1;
        $endMonth = $currentDate->year === $endDate->year ? $endDate->month : 12;

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $monthName = $months[$month - 1]; // Adjust for 0-indexed array
            $monthlyReport->{$monthName} = $monthlyBudgetValue * 1000000;
        }

        $monthlyReport->save();

        // Move to the next year
        $currentDate->addYear()->startOfYear();
    }

    return response()->json(['message' => 'Monthly budget inserted successfully'], 201);
}
protected function insertPlanStaff($headerId, $branch, $totalValue, $duration, Carbon $startDate)
{
    
    $monthlyManpowerValue = $totalValue / $duration;

    // Calculate the end date of the project
    $endDate = $startDate->copy()->addMonths($duration);

    // Initialize the date for iteration
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Create a new monthly report for the current year
        $monthlyReport = new PlanStaff();
        $monthlyReport->projectId = $headerId;
        $monthlyReport->branch = $branch; // Add branch value
        $monthlyReport->year = $currentDate->year;

        // Initialize all months to 0
        $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12','Total','VarianceYTD','Performance'];
        foreach ($months as $month) {
            $monthlyReport->{$month} = 0;
        }

        // Fill the months with the budget value for the current year
        $startMonth = $currentDate->year === $startDate->year ? $startDate->month : 1;
        $endMonth = $currentDate->year === $endDate->year ? $endDate->month : 12;

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $monthName = $months[$month-1]; // Adjust for 0-indexed array
            $monthlyReport->{$monthName} = $monthlyManpowerValue;
        }

        $monthlyReport->save();

        // Move to the next year
        $currentDate->addYear()->startOfYear();
    }

   

    return response()->json(['message' => 'Monthly manpower inserted successfully'], 201);
}
public function getDataByProjectId(Request $request, $id, $year)
{
    // Validate ProjectID and year
    if (!$id || !$year) {
        return response()->json(['error' => 'ProjectID and Year are required'], 400);
    }

    // Define the columns to select
    //$monthColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
    $monthColumnsnb = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];

    // Query the data
    $data = [
        'FcInvoices' => FcInvoice::where('ProjectID', $id)->where('Year', $year)->select($monthColumnsnb)->get(),
        'ActualInvoices' => ActualInvoice::where('ProjectID', $id)->where('Year', $year)->select($monthColumnsnb)->get(),
        'ActualStaffs' => ActualStaff::where('ProjectID', $id)->where('Year', $year)->select($monthColumnsnb)->get(),
        'PlanStaffs' => PlanStaff::where('ProjectID', $id)->where('Year', $year)->select($monthColumnsnb)->get(),
        'CashIns' => Cashin::where('ProjectID', $id)->where('Year', $year)->select($monthColumnsnb)->get(),
        'CashOuts' => Cashout::where('ProjectID', $id)->where('Year', $year)->select($monthColumnsnb)->get(),
    ];



    // Check if all data is empty
    if (empty($data['FcInvoices']) && empty($data['ActualInvoices']) && empty($data['ActualStaffs']) &&
        empty($data['PlanStaffs']) && empty($data['HoCostOverhds']) && empty($data['certifiedInvoices']) && empty($data['CashIns']) && empty($data['CashOuts'])) {
        return response()->json(['error' => 'No data found for the given ProjectID and Year'], 404);
    }

    return response()->json($data, 200);
}
}
