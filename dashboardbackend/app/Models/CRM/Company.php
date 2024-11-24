<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'CRM_companies';

    protected $fillable = [
        'name', 'industry', 'website', 'email', 'phone', 'address', 'status', 'parent_company_id', 'description',
    ];
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function parent()
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    public function children()
    {
        return $this->hasMany(Company::class, 'parent_company_id');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class, 'associated_record_id')->where('associated_record_type', 'company');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
