<?php

namespace App\Http\Controllers\Employees;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Employees\Employee;

class EmployeeController extends Controller
{
    
       
    /**
     * Create a new user.
     *
     * This function returns a view to create a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create() : View {
        return view('employees.create');
    }

    /**
     * Edit a user.
     *
     * @param int $id The ID of the user to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) : View {
        $employee = Employee::find($id);
        return view('employees.edit', compact('employee'));
    }
    /**
     * Return JsonResponse
     * */
   

    public function store(Request $request)
    {        
        DB::beginTransaction();
        $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'status' => 'in:Active,Inactive',
        ]);
        Employee::create([
            ...$request->all(),
            'created_by' => auth()->id() 
        ]);
        DB::commit();
        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

     public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'status' => 'in:Active,Inactive',
        ]);
        $employee->update($request->all());        
        return redirect()->route('employee.list')->with('success', 'Employee updated.');
    }

    public function list() : View {
        return view('employees.list');
    }


    public function datatableList(Request $request){

        $data = Employee::select('employees.*');
                    // ->where('users.id', '!=', auth()->id());


        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    // ->addColumn('role_name', function ($row) {
                    //     return $row->role->name ?? null;
                    // })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                            $editUrl = route('employee.edit', ['id' => $id]);
                            $deleteUrl = route('employee.delete', ['id' => $id]);


                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                                </li>
                            </ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

   public function delete(Request $request) : JsonResponse{

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = Employee::find($recordId);
            if (!$record) {
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id',['record_id' => $recordId]),
                ]);

            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */
        Employee::whereIn('id', $selectedRecordIds)->delete();

        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

}
