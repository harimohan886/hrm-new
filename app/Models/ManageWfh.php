<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageWfh extends Model
{
    use HasFactory;

    protected $table = 'manage_wfh'; 

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'remark',
        'status',
        'created_by',
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

}
