<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Office;
use App\Position;

class CustomerController extends Controller
{

    public function index(){
        $offices = Office::all();
        $positions = Position::all();
        $customers = Customer::all();
        $editingCustomer = null; 
        return view('leave.select', compact('offices', 'positions', 'customers', 'editingCustomer'));

    }
    public function edit(Customer $customer)
    {   
        $customers = Customer::all();
        $offices = Office::all();
        $positions = Position::all();
        $editingCustomer = $customer;
        $pageTitle = 'Edit Employee';
        
        return view('leave.select', compact('editingCustomer', 'customers', 'offices', 'positions', 'pageTitle'));
    }

    public function updateRemarks(Request $request)
    {


            $request->validate([
                'id' => 'required|exists:customers,id',
                'remarks' => 'required|string',
            ]);
                $customer = Customer::find($request->id);
            if ($customer) {
                $customer->remarks = $request->input('remarks');
                $customer->save();
                return redirect()->back();
            }

 
    }
        

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'surname' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'telepon' => 'nullable|string|max:20',
            'employee_id' => 'nullable|string|max:50|unique:customers,employee_id,' . $customer->id,
            'alamat' => 'nullable|string',
            'sex' => 'nullable|in:Male,Female',
            'civil_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'office_id' => 'required|exists:offices,id',
            'position_id' => 'required|exists:positions,id',
            'origappnt_date' => 'required|date',
            'lastprmtn_date' => 'nullable|date',
            'status' => 'nullable|in:Casual,Permanent,Retired,Resigned,Transferred',
            'balance_forwarded_vl' => 'nullable|numeric|min:0',
            'balance_forwarded_sl' => 'nullable|numeric|min:0',
            'leave_credits_old' => 'nullable|numeric|min:0',
            'leave_credits_new' => 'nullable|numeric|min:0',
                'vl' => 'nullable|numeric|min:0',
                'sl' => 'nullable|numeric|min:0',
                'spl' => 'nullable|numeric|min:0',
                'fl' => 'nullable|numeric|min:0',
                'solo_parent' => 'nullable|numeric|min:0',
                'ml' => 'nullable|numeric|min:0',
                'pl' => 'nullable|numeric|min:0',
                'ra9710' => 'nullable|numeric|min:0',
                'rl' => 'nullable|numeric|min:0',
                'sel' => 'nullable|numeric|min:0',
                'study_leave' => 'nullable|numeric|min:0',
                'adopt' => 'nullable|numeric|min:0',
                'vawc' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string|max:255',
        ]);

        try {
            $customer->update([
                'surname' => $request->surname,
                'given_name' => $request->given_name,
                'middle_name' => $request->middle_name,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'employee_id' => $request->employee_id,
                'alamat' => $request->alamat,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'office_id' => $request->office_id,
                'position_id' => $request->position_id,
                'origappnt_date' => $request->origappnt_date,
                'lastprmtn_date' => $request->lastprmtn_date,
                'status' => $request->status,
                'balance_forwarded_vl' => $request->balance_forwarded_vl ?? 0,
                'balance_forwarded_sl' => $request->balance_forwarded_sl ?? 0,
                'leave_credits_old' => $request->leave_credits_old ?? 0,
                'leave_credits_new' => $request->leave_credits_new ?? 0,
                'vl' => $request->vl ?? 0,
                'sl' => $request->sl ?? 0,
                'spl' => $request->spl ?? 0,
                'fl' => $request->fl ?? 0,
                'solo_parent' => $request->solo_parent ?? 0,
                'ml' => $request->ml ?? 0,
                'pl' => $request->pl ?? 0,
                'ra9710' => $request->ra9710 ?? 0,
                'rl' => $request->rl ?? 0,
                'sel' => $request->sel ?? 0,
                'study_leave' => $request->study_leave ?? 0,
                'vawc' => $request->vawc ?? 0,
                'adopt' => $request->adopt ?? 0,
                'remarks' => $request->remarks,
            ]);

            if ($customer['position_id'] >= 0 && $customer['position_id'] <= 39) {
                return redirect()->route('leave.customer.index', ['customer_id' => $customer->id])->with('success', 'Teaching employee converted to nonteaching employee successfully!');
            } else {
                return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])->with('success', 'Nonteaching employee converted to teaching employee successfully!');
            }
            return redirect()->route('leave.select')->with('success', 'Employee updated successfully!');
            
        } catch (ValidationException $e) {
            dd($e->errors());
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'customer' => 'nullable|exists:customers,id',
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
            'status' => 'nullable|string',
            'leave_credits_old' => 'nullable|numeric|min:0',
            'balance_forwarded_vl' => 'nullable|numeric',
            'balance_forwarded_sl' => 'nullable|numeric',
            'vl' => 'nullable|numeric|min:0',
            'sl' => 'nullable|numeric|min:0',
            'spl' => 'nullable|numeric|min:0',
            'fl' => 'nullable|numeric|min:0',
            'solo_parent' => 'nullable|numeric|min:0',
            'ml' => 'nullable|numeric|min:0',
            'pl' => 'nullable|numeric|min:0',
            'ra9710' => 'nullable|numeric|min:0',
            'rl' => 'nullable|numeric|min:0',
            'sel' => 'nullable|numeric|min:0',
            'study_leave' => 'nullable|numeric|min:0',
            'adopt' => 'nullable|numeric|min:0',
            'vawc' => 'nullable|numeric|min:0',
            'employee_id' => 'nullable|numeric|min:0',

        ]);


        $nama = trim($request->given_name . ' ' . $request->middle_name . ' ' . $request->surname);


        $model = new Customer(); 

    $model->nama = $nama;
    $model->email = $request->email;
    $model->employee_id = $request->employee_id;
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
    $model->status = $request->status;
    $model->leave_credits_old = $request->leave_credits_old ?? 0;
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



        $model->save();
        // Redirect based on position
        if ($model->position_id >= 0 && $model->position_id <= 39) {
            return redirect()->route('leave.customer.index', ['customer_id' => $model->id])->with('success', 'Employee created successfully!');
        } elseif ($model->position_id >= 40 && $model->position_id <= 52) {
            return redirect()->route('leave.teaching.index', ['customer_id' => $model->id])->with('success', 'Employee created successfully!');
        }

        return redirect()->route('leave.select')->with('success', 'Customer created successfully!');
    }

    public function convert(Request $request)
    {
        try{
            $validated = $request->validate([
                'id' => 'required|exists:customers,id',
                'position_id' => 'required|exists:positions,id',
                'convert_date' => 'required|date',
                'leave_credits_old' => 'nullable|numeric|min:0',
                'leave_credits_new' => 'nullable|numeric|min:0',
                'balance_forwarded_vl' => 'nullable|numeric|min:0',
                'balance_forwarded_sl' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string|max:255',
            ]);

            $customer = Customer::find($request->id);
            $convert_dates = $customer->convert_dates ?? [];
            $convert_dates[] = $validated['convert_date'];
            $customer->convert_dates = $convert_dates;

            $updateData = [
                'position_id' => $validated['position_id'],
'remarks' => trim(($customer->remarks ?? '') . ' ' . ($validated['remarks'] ?? '')),
            ];

            // teaching to nonteaching
            if ($validated['position_id'] >= 0 && $validated['position_id'] <= 39) {
                $updateData['balance_forwarded_vl'] = $validated['balance_forwarded_vl'] ?? 0;
                $updateData['balance_forwarded_sl'] = $validated['balance_forwarded_sl'] ?? 0;
            } else {
                $updateData['leave_credits_old'] = $validated['leave_credits_old'] ?? 0;
                $updateData['leave_credits_new'] = $validated['leave_credits_new'] ?? 0;
            }

            $customer->update($updateData); // update data
            $customer->save(); // save convert data

            if ($validated['position_id'] >= 0 && $validated['position_id'] <= 39) {
                return redirect()->route('leave.customer.index', ['customer_id' => $customer->id])->with('success', 'Teaching employee converted to nonteaching employee successfully!');
            } else {
                return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])->with('success', 'Nonteaching employee converted to teaching employee successfully!');
            }
        }catch (ValidationException $e) {
                    dd($e->errors());
           
        }
       
    }
}

