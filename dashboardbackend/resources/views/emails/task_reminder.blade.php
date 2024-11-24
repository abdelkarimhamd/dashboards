<!-- resources/views/emails/task_reminder.blade.php -->

@component('mail::message')
# Task Reminder

Hello {{ $task->assignedUser->name }},

This is a reminder for your upcoming task:

**Task Title:** {{ $task->task_title }}

**Due Date:** {{ \Carbon\Carbon::parse($task->due_date)->format('F j, Y, g:i a') }}

**Description:**
{{ $task->description }}

@component('mail::button', ['url' =>  'https://app.morgantigcc.com:3001/crm/tasks/'. $task->id])
View Task
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
