namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoFinanciero;

class FinanzasController extends Controller
{
    public function index()
    {
        $movimientos = MovimientoFinanciero::orderBy('fecha', 'desc')->get();
        return view('admin.finanzas.index', compact('movimientos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|in:Ingreso,Gasto',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'categoria' => 'required|string|max:100',
        ]);

        MovimientoFinanciero::create($request->all());

        return redirect()->route('finanzas.index')->with('success', 'Transacción registrada correctamente.');
    }
}