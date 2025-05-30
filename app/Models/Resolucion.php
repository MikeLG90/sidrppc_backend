<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Resolucion extends Model
{
    
    protected $table = 'resoluciones';
    protected $primaryKey = 'resolucion_id';
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'titulo',
        'fecha_aper',
        'tipo',
        'categoria',
        'estado',
        'urgencia',
        'impacto',
        'prioridad',
        'descripcion',
        'file_path',
        'estatus',
        'tipo_resolucion'
        ];

    public function files()
    {
        return $this->hasMany(ResolucionFile::class);
    }

    public static function indexResoluciones() 
    {
        $resolucioens = DB::table('resoluciones as r')
        ->join('usuarios as u', 'r.usuario_id', '=', 'u.usuario_id')
        ->join('persona as p', 'u.usuario_id', '=', 'p.usuario_id')
        ->select(
            'r.*',
            DB::raw('p.nombre || \' \' || p.ape_paterno || \' \' || p.ape_materno AS remitente')
        );
    
    if (auth()->user()->rol_id != 1 && auth()->user()->rol_id != 6) {
        $resolucioens->where('r.oficina_dest', auth()->user()->oficina_id);
    }
    
    return $resolucioens->get();
    ;
    }

    public static function resolucion($resolucion_id = null) 
    {
        $resolucion = DB::table('resoluciones as r')
        ->join('usuarios as u', 'r.usuario_id', '=', 'u.usuario_id')
        ->join('persona as p', 'u.usuario_id', '=', 'p.usuario_id')
        ->select(
            'r.*',
            DB::raw('p.nombre || \' \' || p.ape_paterno || \' \' || p.ape_materno AS remitente')
        )
        ->where('r.resolucion_id', '=', $resolucion_id)
        ->get();
    

    return $resolucion;

    }

    public static function resolucionData()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Lunes
        $endOfWeek = Carbon::now()->endOfWeek(); // Domingo

        $resoluciones = DB::table('resoluciones')
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(*) as total'))
            ->when(!in_array(auth()->user()->rol_id, [6, 8]), function ($query) {
                return $query->where('resoluciones.oficina_dest', '=', auth()->user()->oficina_id);
            })
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();

        return $resoluciones;
    }
}
