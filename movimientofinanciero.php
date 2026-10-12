namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoFinanciero extends Model
{
    protected $table = 'movimientos_financieros';

    protected $fillable = [
        'descripcion', 'tipo', 'monto', 'fecha', 'categoria'
    ];
}