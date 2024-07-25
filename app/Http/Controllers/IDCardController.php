<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Image;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\User;
use DateTime;

class IDCardController extends Controller
{
    // public function idCardDownload(Request $request)
    // {
    //     $data = [
    //         'name' => 'Gaurav Sharma',
    //         'id_no' => '0000000026',
    //         'designation' => 'Laravel Developer',
    //         'join_date' => '05/Dec/2024',
    //         'phone' => '9389486561',
    //         'email' => 'gauravsharma20607@gmail.com',
    //         'photo' => asset('https://hrm.junglesafariindia.in/storage/uploads/avatar/download%20(9)_1719830761.jfif')
    //     ];

    //     // $pdf = PDF::loadView('id_card', $data);
    //     $pdf = PDF::loadView('id_card', $data)->setPaper('a4', 'landscape');
        
    //     return $pdf->download('id_card.pdf');
    // }

    public function idCardDownload(Request $request, $id)
    {   
        $employees = Employee::where('id', $id)->first();
        
        $designation =  Designation::where('id',$employees->designation_id)->first();
        // dd($designation );

        $date = new DateTime($employees->company_doj);

        $user =  User::where('id',$employees->user_id)->first();
        // dd($employees->email);

        $data = [
            'name' => $employees->name,
            'id_no' => 'EMP000000'.$employees->employee_id,
            'designation' => $designation->name,
            'join_date' => $date->format('d/M/Y'),
            'phone' => $employees->phone,
            'email' => $employees->email,
            'photo' => $user->avatar
        ];
        
        return view('id_card', compact('data'));
    }
}
