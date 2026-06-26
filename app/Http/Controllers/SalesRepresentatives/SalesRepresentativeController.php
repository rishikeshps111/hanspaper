<?php

namespace App\Http\Controllers\SalesRepresentatives;
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
use App\Models\SalesRepresentatives\SalesRepresentative;

class SalesRepresentativeController extends Controller
{
    
       
    /**
     * Create a new user.
     *
     * This function returns a view to create a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create() : View {
        return view('salesrepresentatives.create');
    }

    /**
     * Edit a user.
     *
     * @param int $id The ID of the user to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) : View {
        $employee = SalesRepresentative::find($id);
        return view('salesrepresentatives.edit', compact('employee'));
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
        SalesRepresentative::create([
            ...$request->all(),
            'created_by' => auth()->id() 
        ]);
        DB::commit();
        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    // public function update(Request $request, SalesRepresentative $employee)
    // {
    //     $request->validate([
    //         'full_name' => 'required|string|max:255',
    //         'mobile' => 'required|string|max:20',
    //         'email' => 'required|email|max:255',
    //         'status' => 'in:Active,Inactive',
    //     ]);
    //     $employee->update($request->all());        
    //     // return redirect()->route('representative.list')->with('success', 'SalesRepresentative updated.');
    //      return response()->json([
    //         'message' => __('app.record_saved_successfully'),
    //     ]);
    // }


    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'status' => 'in:Active,Inactive',
        ]);

        $machine = SalesRepresentative::findOrFail($id);
        $machine->update([
            'full_name' => $request->full_name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'status' => $request->status,
        ]);

       return redirect()->route('representative.list')->with('success', ' Updated successfully.');
    }
    public function list() : View {
        return view('salesrepresentatives.list');
    }


    public function datatableList(Request $request){

        $data = SalesRepresentative::select('sales_representatives.*');
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

                            $editUrl = route('representative.edit', ['id' => $id]);
                            $deleteUrl = route('representative.delete', ['id' => $id]);


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
            $record = SalesRepresentative::find($recordId);
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
        SalesRepresentative::whereIn('id', $selectedRecordIds)->delete();

        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

}
