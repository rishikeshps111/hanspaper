<?php

namespace App\Http\Controllers\Machines;
use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\ItemRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Tax;
use App\Models\Unit;
use Carbon\Carbon;
use App\Services\ItemTransactionService;
use App\Services\ItemService;
use App\Services\CacheService;
use App\Services\AccountTransactionService;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Machines\Machine;
use Spatie\Image\Image;
class MachineController extends Controller
{
    public function index()
    {
  
        $machines = Machine::orderBy('id', 'desc')->get();
        return view('machines.list', compact('machines'));
        // dd($machines);
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        'machine_name' => 'required|string|max:255|unique:machines,machine_name',
        ]);

        Machine::create([
            'machine_name' => $request->machine_name,
            'status' => 'Active',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
         return redirect()->route('machine.index')->with('success', ' created successfully.');
    }

    public function edit($id)
    {
        $machine = Machine::findOrFail($id);
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, $id)
    {
       $request->validate([
        'machine_name' => 'required|string|max:255|unique:machines,machine_name,' . $id,
        'status' => 'required|in:Active,Inactive',
      ]);

        $machine = Machine::findOrFail($id);
        $machine->update([
            'machine_name' => $request->machine_name,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

       return redirect()->route('machine.index')->with('success', ' Updated successfully.');
    }

    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();

        return redirect()->route('machines.index')->with('success', 'Machine deleted successfully.');
    }

    public function getMachines()
    {
        $machines = Machine::where('status', 'Active')
                          ->select('id', 'machine_name')
                          ->get();
        
        return response()->json($machines);
    }
}
