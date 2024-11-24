<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'task_title' => $this->task_title,
            'task_type' => $this->task_type,
            'associated_record_type' => $this->associated_record_type,
            'associated_record_id' => $this->associated_record_id,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date->toDateTimeString(),
            'reminder_at' => $this->reminder_at ? $this->reminder_at->toDateTimeString() : null,
            'description' => $this->description,
            'status' => $this->status,
            'assignee' => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
                // Include other necessary user fields
            ] : null,
            'creator' => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                // Include other necessary user fields
            ] : null,
            'updater' => $this->updater ? [
                'id' => $this->updater->id,
                'name' => $this->updater->name,
                // Include other necessary user fields
            ] : null,
            'associatedRecord' => $this->associatedRecord ? [
                'id' => $this->associatedRecord->id,
                'type' => $this->associatedRecord->type,
                'name' => $this->associatedRecord->name,
                // Include other necessary fields based on type
            ] : null,
        ];
    }
}
