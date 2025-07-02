<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Office;
use App\Position;

class CustomerController extends Controller
{
    public function create()
    {
        $offices = Office::all();
        $positions = Position::all();
        
        return view('customers.create', compact('offices', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'nullable|string|max:191',
            'email' => 'nullable|email|unique:customers,email',
            'district' => 'nullable|string|max:50',
            'office_id' => 'nullable|integer',
            'position_id' => 'nullable|integer',
            'surname' => 'nullable|string|max:255',
            'given_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'civil_status' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:191',
            'alamat' => 'nullable|string',
            'lastprmtn_date' => 'nullable|date',
            'origappnt_date' => 'nullable|date',
            'permanency_date' => 'nullable|date',
            'leave_credits' => 'nullable|numeric|min:0',
            'balance_forwarded_vl' => 'nullable|numeric',
            'balance_forwarded_sl' => 'nullable|numeric',
            'vl' => 'nullable|integer',
            'sl' => 'nullable|integer',
            'spl' => 'nullable|integer',
            'fl' => 'nullable|integer',
            'solo_parent' => 'nullable|integer',
            'ml' => 'nullable|integer',
            'pl' => 'nullable|integer',
            'ra9710' => 'nullable|integer',
            'rl' => 'nullable|integer',
            'sel' => 'nullable|integer',
            'study_leave' => 'nullable|integer',
            'adopt' => 'nullable|integer',
            'vawc' => 'nullable|integer',
            'employee_id' => 'nullable|integer',

        ]);


    $nama = trim($request->given_name . ' ' . $request->middle_name . ' ' . $request->surname);


    $model = new Customer(); 

    $model->nama = $nama;
    $model->email = $request->email;
    $model->district = $request->district;
    $model->office_id = $request->office_id;
    $model->position_id = $request->position_id;
    $model->surname = $request->surname;
    $model->given_name = $request->given_name;
    $model->middle_name = $request->middle_name;
    $model->civil_status = $request->civil_status;
    $model->sex = $request->sex;
    $model->telepon = $request->telepon;
    $model->alamat = $request->alamat;
    $model->lastprmtn_date = $request->lastprmtn_date;
    $model->origappnt_date = $request->origappnt_date;
    $model->permanency_date = $request->permanency_date;
    $model->leave_credits = $request->leave_credits ?? 0;
    $model->balance_forwarded_vl = $request->balance_forwarded_vl ?? 0;
    $model->balance_forwarded_sl = $request->balance_forwarded_sl ?? 0;
    $model->vl = $request->vl ?? 0;
    $model->sl = $request->sl ?? 0;
    $model->spl = $request->spl ?? 0;
    $model->fl = $request->fl ?? 0;
    $model->solo_parent = $request->solo_parent ?? 0;
    $model->ml = $request->ml ?? 0;
    $model->pl = $request->pl ?? 0;
    $model->ra9710 = $request->ra9710 ?? 0;
    $model->rl = $request->rl ?? 0;
    $model->sel = $request->sel ?? 0;
    $model->study_leave = $request->study_leave ?? 0;
    $model->adopt = $request->adopt ?? 0;
    $model->vawc = $request->vawc ?? 0;



    $model->save();




        return redirect()->route('leave.select')->with('success', 'Customer created successfully!');
    }
}

