<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CRM\Task;
use App\Jobs\SendTaskReminderJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for tasks at their reminder time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Current time
        $now = Carbon::now();

        // Fetch tasks where reminder_at is <= now and not null
        $tasks = Task::whereNotNull('reminder_at')
        ->where('reminder_at', '<=', $now)
        ->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks found for sending reminders.');
            Log::info('No tasks found for sending reminders at ' . $now);
            return 0;
        }

        foreach ($tasks as $task) {
            // Dispatch job
            SendTaskReminderJob::dispatchSync($task);
            $this->info("Reminder job dispatched for Task ID: {$task->id}");
            Log::info("Reminder job dispatched for Task ID: {$task->id}");
        }

        $this->info('Task reminders processed successfully.');
        Log::info('Task reminders processed successfully at ' . $now);

        return 0;
    }
}
