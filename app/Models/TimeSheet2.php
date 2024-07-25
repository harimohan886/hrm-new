<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSheet2 extends Model
{
    use HasFactory;

    protected $table = 'time_sheets2'; 

    protected $fillable = [
        'policy_name',
        'permitted_late_arrival',
        'permitted_early_departure',
        'mark_half_day_hours',
        'mark_absent_hours',
        'late_1',
        'late_2',
        'late_3',
        'late_4',
        'deduct_percentage_1',
        'deduct_percentage_2',
        'deduct_percentage_3',
        'deduct_percentage_4',
        'early_going_1',
        'early_going_2',
        'early_going_3',
        'early_going_4',
        'deduct_percentage_early_going_1',
        'deduct_percentage_early_going_2',
        'deduct_percentage_early_going_3',
        'deduct_percentage_early_going_4',
        // Add more columns as needed
    ];
}
