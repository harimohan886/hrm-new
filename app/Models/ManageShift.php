<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageShift extends Model
{
    use HasFactory;

    protected $table = 'manage_shift'; 

    protected $fillable = [
        'shift_code',
        'shift_name',
        'start_time',
        'end_time',
        'shift_hours',
        // Add more columns as needed
    ];
}
