<?php

namespace App\Jobs;

use App\Mail\TaskReminderMail;
use App\Models\CRM\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTaskReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param Task $task
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if the task still has a reminder_at set to prevent duplicates
        if (!$this->task->reminder_at) {
            Log::info("Task ID: {$this->task->id} has no reminder_at. Skipping email.");
            return;
        }

        try {
            // Send the email
            // Mail::to("ahamed@morganti.com.sa")->send(new TaskReminderMail($this->task));
            Mail::to($this->task->assignedUser->email)->send(new TaskReminderMail($this->task));
            Log::info("Reminder email sent for Task ID: {$this->task->id}");

            // Update reminder_at to prevent re-sending
            $this->task->reminder_at = null;
            $this->task->save();
            Log::info("Task ID: {$this->task->id} updated to set reminder_at as null.");
        } catch (\Exception $e) {
            Log::error("Failed to send reminder for Task ID: {$this->task->id}. Error: {$e->getMessage()}");
            // Optionally, you can throw the exception to mark the job as failed
            // throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error("Job failed for Task ID: {$this->task->id}. Error: {$exception->getMessage()}");
        // Optionally, notify administrators or take other actions
    }
}
