<?php

namespace App\Models\PettyCashModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashRequestExpense extends Model
{
    use HasFactory;
    protected $table = 'pettycashrequest';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = [
        'id',
        'requester_name',
        'requester_title',
        'requester_department',
        'location',
        'comments',
    ];
    public $timestamps = true;	
}
