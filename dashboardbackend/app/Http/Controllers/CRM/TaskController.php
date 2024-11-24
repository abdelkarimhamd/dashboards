<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Task;
use App\Models\CRM\Deal;
use App\Models\CRM\Contact;
use App\Models\CRM\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     *
     * Optionally, you can filter tasks by status, priority, due date, etc.
     */
    public function index(Request $request)
    {
        $query = Task::with(['assignee', 'creator', 'updater', 'associatedRecord']);

        // Optional filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->input('due_date'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('task_title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
                // Add more fields as needed
            });
        }

        // Pagination parameters
        $perPage = $request->input('per_page', 10); // Default to 10 tasks per page
        $page = $request->input('page', 1);

        $tasks = $query->orderBy('due_date', 'asc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json($tasks);
    }
    /**
     * Store a newly created task.
     */
    /**
 * Get tasks formatted for a calendar view.
 */
public function calendar(Request $request)
{
    // Fetch tasks within a date range (for example, a month's worth)
    $start = Carbon::parse($request->input('start'));
    $end = Carbon::parse($request->input('end'));
    $user=Auth::user();
    $tasks = Task::whereBetween('due_date', [$start, $end])
    ->where('created_by', $user->id)->orWhere('assigned_to', $user->id)
        ->with(['assignee', 'creator', 'associatedRecord'])
        ->get();

    // Format tasks for the calendar
    $events = $tasks->map(function ($task) {
        return [
            'id' => $task->id,
            'title' => $task->task_title,
            'start' => Carbon::parse($task->due_date)->format('Y-m-d'),
            'end' => Carbon::parse($task->due_date)->format('Y-m-d'),
            'description' => $task->description,
            'priority' => $task->priority,
            'reminder_at' => $task->reminder_at,
            'status' => $task->status,
            'assignee' => optional($task->assignee)->name,
        ];
    });

    return response()->json($events);
}

    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'task_title'             => 'required|string|max:255',
            'task_type'              => 'required|in:Call,Message,Email,Todo,Proposal,Assessment,Blueprint,Meeting,Note',
            'associated_record_type' => 'required|in:deal,contact,company',
            'associated_record_id'   => 'required|integer',
            'priority'               => 'required|in:Low,Medium,High',
            'assigned_to'            => 'required|integer|exists:users,id',
            'due_date'               => 'required|date|after_or_equal:today',
            'reminder_at'            => 'nullable|date|before:due_date',
            'description'            => 'nullable|string',
            'status'                 => 'required|in:Pending,In Progress,Completed,Cancelled',
        ];

        // Custom validation messages
        $messages = [
            'associated_record_id.required' => 'The associated record is required.',
            'associated_record_id.integer'  => 'The associated record ID must be an integer.',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if the associated record exists
        $validator->after(function ($validator) use ($request) {
            $associatedRecord = $this->findAssociatedRecord($request->input('associated_record_type'), $request->input('associated_record_id'));

            if (!$associatedRecord) {
                $validator->errors()->add('associated_record_id', 'The associated record does not exist.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Prepare data for creation
        $data = $request->only([
            'task_title',
            'task_type',
            'associated_record_type',
            'associated_record_id',
            'priority',
            'assigned_to',
            'due_date',
            'reminder_at',
            'description',
            'status',
        ]);

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        // Create the task
        $task = Task::create($data);

        // Load relationships
        $task->load(['assignee', 'creator', 'updater', 'associatedRecord']);

        return response()->json([
            'message' => 'Task created successfully',
            'task'    => $task,
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show($id)
    {
        $task = Task::with(['assignee', 'creator', 'updater', 'associatedRecord'])->findOrFail($id);

        return response()->json($task);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Authorization check (optional)
        // if (Auth::id() !== $task->created_by) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // Validation rules
        $rules = [
            'task_title'             => 'sometimes|required|string|max:255',
            'task_type'              => 'sometimes|required|in:Call,Message,Email,Todo,Proposal,Assessment,Blueprint,Meeting,Note',
            'associated_record_type' => 'sometimes|required|in:deal,contact,company',
            'associated_record_id'   => 'sometimes|required|integer',
            'priority'               => 'sometimes|required|in:Low,Medium,High',
            'assigned_to'            => 'sometimes|required|integer|exists:users,id',
            'due_date'               => 'sometimes|required|date|after_or_equal:today',
            'reminder_at'            => 'nullable|date|before:due_date',
            'description'            => 'nullable|string',
            'status'                 => 'sometimes|required|in:Pending,In Progress,Completed,Cancelled',
        ];

        // Custom validation messages
        $messages = [
            'associated_record_id.required' => 'The associated record is required.',
            'associated_record_id.integer'  => 'The associated record ID must be an integer.',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if the associated record exists
        if ($request->has('associated_record_type') && $request->has('associated_record_id')) {
            $validator->after(function ($validator) use ($request) {
                $associatedRecord = $this->findAssociatedRecord($request->input('associated_record_type'), $request->input('associated_record_id'));

                if (!$associatedRecord) {
                    $validator->errors()->add('associated_record_id', 'The associated record does not exist.');
                }
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Prepare data for update
        $data = $request->only([
            'task_title',
            'task_type',
            'associated_record_type',
            'associated_record_id',
            'priority',
            'assigned_to',
            'due_date',
            'reminder_at',
            'description',
            'status',
        ]);

        $data['updated_by'] = Auth::id();

        // Update the task
        $task->update($data);

        // Load relationships
        $task->load(['assignee', 'creator', 'updater', 'associatedRecord']);

        return response()->json([
            'message' => 'Task updated successfully',
            'task'    => $task,
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Authorization check (optional)
        // if (Auth::id() !== $task->created_by) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $task->delete(); // Soft delete

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Helper method to find the associated record.
     */
    protected function findAssociatedRecord($type, $id)
    {
        switch ($type) {
            case 'deal':
                return Deal::find($id);
            case 'contact':
                return Contact::find($id);
            case 'company':
                return Company::find($id);
            default:
                return null;
        }
    }
}
