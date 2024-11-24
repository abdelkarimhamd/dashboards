<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tender;
use App\Models\TenderingUser;
use Illuminate\Support\Facades\Mail;
class SendCEOExecutiveReminderEmail extends Command
{
   // The name and signature of the command
   protected $signature = 'email:sendReminder {tenderId}';

   // The console command description
   protected $description = 'Send reminder email to CEO and Executive Director to remind President';

   /**
    * Execute the console command.
    */
   public function handle()
   {
       // Fetch the tender using the passed tenderId argument
       $tenderId = $this->argument('tenderId');
       $tender = Tender::find($tenderId);

       if (!$tender) {
           $this->error('Tender not found.');
           return;
       }

       // Get the CEO and Executive Director
       $ceo = TenderingUser::where('role', 'Executives (CEO)')->first();
       $executiveDirector = TenderingUser::where('role', 'Executives Managing Director')->first();

       if ($ceo && $executiveDirector) {
           // Prepare email data, including the recipient's name
           $emailData = [
               'tender' => $tender,
               'ceoName' => $ceo->name,
               'executiveDirectorName' => $executiveDirector->name
           ];

           // Send the reminder email
           Mail::send('emails.reminder', $emailData, function ($message) use ($ceo, $executiveDirector) {
               $message->to([$ceo->email, $executiveDirector->email])
                       ->subject('Reminder: President’s action required on tender');
           });

           // Log the success message in the console
           $this->info('Reminder email sent to CEO and Executive Director.');
       } else {
           $this->error('CEO or Executive Director not found.');
       }
   }
}
