<?php

namespace App\Http\Controllers;

use App\Exports\ExportSuppliers;
use App\Imports\SuppliersImport;
use App\User;
use App\Office;
use App\Customer;
use App\Position;
use Illuminate\Support\Facades\Hash;
use Excel;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class UserController extends Controller {
	public function __construct() {
		$this->middleware('role:admin');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {

		$office = Office::orderBy('office','ASC')
            ->get()
            ->pluck('office','id');
        $position = Position::orderBy('position', 'ASC')
            ->get()
            ->pluck('position', 'id');

		$users = User::all();
		return view('user.index', compact('users' , 'office' , 'position'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required',
			'emp_id' => 'required|unique',
			'email' => 'required|unique:suppliers',
		]);

		User::create($request->all());

		return response()->json([
			'success' => true,
			'message' => 'User Created',
		]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
	    $users = User::find($id);
	    $office = Office::find($users->office_id);
        $position = Position::find($users->position_id);

	    return response()->json([
            'users' => $users,
            'office' => $office,
            'position' => $position,

        ]);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
    $this->validate($request, [
        'name' => 'required',
        'employee_id' => 'required|numeric',
        'email' => 'required|email',
        'role' => 'required',
        'office_id' => 'required',  
        'position_id' => 'required',
        'district' => 'required',  
        'password' => 'nullable|min:8|confirmed',
    ]);

    $user = User::findOrFail($id);

    $data = $request->except(['password']);

    // Check if password field is not empty or null and only then hash and update it.
    if (!empty($request->password)) {
        $data['password'] = Hash::make($request->password);
    }

    // Update the user
    $user->update($data);

    // Now update the role of the customer
    $customer = Customer::where('employee_id', $user->employee_id)->first();
    if ($customer) {
        $customer->update(['role' => $request->role]);
    }

    return response()->json([
        'success' => true,
        'message' => 'User Updated',
    ]);
}






	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {

		$user = User::findOrFail($id);

        $user->delete();


		return response()->json([
			'success' => true,
			'message' => 'User Deleted',
		]);
	}

	public function apiUsers() {
		$users = User::orderBy('created_at', 'DESC')->get();

		return Datatables::of($users)
			->addColumn('office_name', function ($users){
                return $users->office->office;
            })
			->addColumn('position_name', function ($users){
                return $users->position->position;
            })
			->addColumn('action', function ($users) {
				return '<a onclick="editForm(' . $users->id . ')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a> ' .
				'<a onclick="deleteData(' . $users->id . ')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i>Delete</a>';
			})
			->rawColumns(['action'])->make(true);
	}

	public function ImportExcel(Request $request) { 

		$this->validate($request, [
			'file' => 'required|mimes:xls,xlsx',
		]);

		if ($request->hasFile('file')) {
			//UPLOAD FILE
			$file = $request->file('file'); //GET FILE
			Excel::import(new SuppliersImport, $file); //IMPORT FILE
			return redirect()->back()->with(['success' => 'Upload file data suppliers !']);
		}

		return redirect()->back()->with(['error' => 'Please choose file before!']);
	}

	public function exportSuppliersAll() {
		$suppliers = Supplier::all();
		$pdf = PDF::loadView('suppliers.SuppliersAllPDF', compact('suppliers'));
		return $pdf->download('suppliers.pdf');
	}

	public function exportExcel() {
		return (new ExportSuppliers)->download('suppliers.xlsx');
	}
}
