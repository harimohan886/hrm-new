<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageWeekOff extends Model
{
    use HasFactory;

    protected $table = 'manage_weekoff'; 

    protected $fillable = [
        'employee_id',
        'week_off_date',
        'day_name',
        'remark',
        'status',
        'created_by',
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

}
