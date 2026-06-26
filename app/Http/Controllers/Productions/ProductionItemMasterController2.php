namespace App\Http\Controllers\Productions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductionItemMasterController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Production Index Page']);
    }
}
