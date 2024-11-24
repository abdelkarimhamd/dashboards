<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'hr_positions';

    protected $fillable = [
        'position_name',
        'description',
    ];

    // Relationships
    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function orgCharts()
    {
        return $this->hasMany(OrgChart::class, 'position_id');
    }
}
