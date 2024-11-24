<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        Log::info('Scheduler running. Current server time: ' . now()->toDateTimeString());
        //$schedule->command('send:extension-date-notifications')->dailyAt('08:30');
        $schedule->command('check:jobexdate')->dailyAt('11:32')->withoutOverlapping();
        $schedule->command('check:qadate')->dailyAt('11:34')->withoutOverlapping();
        $schedule->command('check:sitevisitdate')->dailyAt('11:36')->withoutOverlapping();
        $schedule->command('check:submissiondate')->dailyAt('11:14')->withoutOverlapping();
        $schedule->command('check:trfprocess')->dailyAt('11:05')->withoutOverlapping();
        $schedule->command('check:submissionCEOdate')->dailyAt('11:42')->withoutOverlapping();
        $schedule->command('tenders:update-no-response-days')->dailyAt('10:00')->withoutOverlapping();
        $schedule->command('send:project-reminder-emails')->dailyAt('11:27')->withoutOverlapping();
        $schedule->command('send:project-reminde-actual-emails')->dailyAt('10:44')->withoutOverlapping();
        $schedule->command('send:project-reminder-cashin-emails')->dailyAt('16:03')->withoutOverlapping();
        $schedule->command('send:send-salary-project-reminder-emails')->dailyAt('12:04')->withoutOverlapping();
        $schedule->command('send:send-petty-project-reminder-emails')->dailyAt('15:17')->withoutOverlapping();
        $schedule->command('send:send-suppliers-project-reminder-emails')->dailyAt('16:38')->withoutOverlapping();
        $schedule->command('tasks:send-reminders')->everyMinute();
        // $schedule->command('emails:fetch-and-create-activities')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
