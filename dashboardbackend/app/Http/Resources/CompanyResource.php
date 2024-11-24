<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'industry' => $this->industry,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'parent' => $this->parent ? new CompanyResource($this->parent) : null,
            'children' => CompanyResource::collection($this->children),
            'created_by' => $this->createdBy ? new UserResource($this->createdBy) : null,
            'contacts' => ContactResource::collection($this->contacts),
            // 'deals' => DealResource::collection($this->deals),
        ];
    }
}
