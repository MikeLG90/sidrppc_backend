<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folio;
use App\Models\User;
use App\Models\OficinaFolio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class APIFolio extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'persona' => 'required|string',
            'oficina' => 'required|string',
            'tipo_usuario' => 'sometimes|string',
            'tipo_oficina' => 'sometimes|string'
        ]);
    
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }
    
            $fechaActual = Carbon::now()->format('d/m/Y');
    
            $result = DB::table('cabeza_sector as c')
                ->select(DB::raw("CONCAT_WS('/', c.nombre, d.nombre, o.oficina, a.area) AS resultado_concatenado"))
                ->join('dependencia as d', 'd.cs_id', '=', 'c.cs_id')
                ->join('oficinas as o', 'o.dep_id', '=', 'd.dep_id')
                ->join('usuarios as u', 'u.oficina_id', '=', 'o.oficina_id')
                ->join('areas as a', 'u.area_id', '=', 'a.area_id')
                ->where('u.usuario_id', $userId)
                ->first();
    
            if (!$result) {
                return response()->json(['error' => 'No se pudo generar la estructura del folio'], 400);
            }
    
            $ultimoFolio = Folio::latest()->first();
            $num_folio = $ultimoFolio ? $ultimoFolio->num_folio + 1 : 1;
    
            $folio = Folio::create([
                'num_folio' => $num_folio,
                'usuario_id' => $userId,
                'fecha_hora' => $request->fecha,
                'destinatario' => $request->persona,
                'oficina' => $request->oficina,
                'folio_generado' => $result->resultado_concatenado . "/000" . $num_folio . "/" . $fechaActual,
                'tipo_usuario' => $request->tipo_usuario ?? null,
                'tipo_oficina' => $request->tipo_oficina ?? null
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Folio generado correctamente',
                'folio' => $folio,
                'folio_completo' => $folio->folio_generado
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el folio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    

    public function show(Folio $folio)
    {
        setlocale(LC_TIME, 'es_ES.UTF-8');
        Carbon::setLocale('es');
        $user = auth()->user();


        $fecha = $folio->fecha_hora;
        
        // traducir a lenguaje humano la fecha
        $fecha = Carbon::parse($folio->fecha_hora)->translatedFormat('j \d\e F \d\e Y');
        return view('rppc.folios.folio_generado', compact('folio', 'fecha', 'user'));
    }

    public function index () 
    {
        $folios = Folio::obtenerFolios();
        $registros = DB::table('registros')->get();

        

       // dd($folios);

        return view('rppc.folios.dash_folios', compact('folios', 'registros'));
    }

    public function misFolios () 
    {
        $folios = Folio::misFolios();

       // dd($folios);

        return view('rppc.folios.mis_folios', compact('folios'));
    }

    public function index1(Request $request)
    {
        $query = DB::table('folios as f')
            ->select(
                'f.num_folio as folio',
                DB::raw("CONCAT(p.nombre, ' ', p.ape_paterno, ' ', p.ape_materno) as generado_por"),
                'a.area',
                'o.oficina as delegacion',
                'd.nombre as dependencia',
                'c.nombre as cabeza_sector',
                DB::raw("CONCAT(f.destinatario, '-', f.oficina) as para"),
                'f.folio_generado'
            )
            ->join('usuarios as u', 'u.usuario_id', '=', 'f.usuario_id')
            ->join('areas as a', 'a.area_id', '=', 'u.area_id')
            ->join('oficinas as o', 'o.oficina_id', '=', 'a.oficina_id')
            ->join('dependencia as d', 'd.dep_id', '=', 'o.dep_id')
            ->join('cabeza_sector as c', 'c.cs_id', '=', 'd.cs_id')
            ->join('persona as p', 'p.persona_id', '=', 'u.persona_id');
    
        if ($request->has('cabeza_sector_id') && $request->cabeza_sector_id !== 'Todas') {
            $query->where('c.cs_id', $request->cabeza_sector_id);
        }
    
        if ($request->has('dependencia_id') && $request->dependencia_id !== 'Todas') {
            $query->where('d.dep_id', $request->dependencia_id);
        }
    
        if ($request->has('delegacion_id') && $request->delegacion_id !== 'Todas') {
            $query->where('o.oficina_id', $request->delegacion_id);
        }
    
        if ($request->has('area_id') && $request->area_id !== 'Todas') {
            $query->where('a.area_id', $request->area_id);
        }
    
        if ($request->has('busqueda')) {
            $query->where(function ($q) use ($request) {
                $q->where('f.num_folio', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('f.destinatario', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('p.nombre', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('p.ape_paterno', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('f.folio_generado', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('o.oficina', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('d.nombre', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('p.ape_materno', 'like', '%' . $request->busqueda . '%');
            });
        }
        $query->orderBy('f.num_folio', 'desc');
    
        $perPage = $request->input('per_page', 10); // Número de items por página, por defecto 10
        $folios = $query->paginate($perPage);
    
        return response()->json($folios);
    }

    public function getCabezasSector()
    {
        $cabezasSector = DB::table('cabeza_sector')
            ->select('cs_id as id', 'nombre')
            ->get();
        return response()->json($cabezasSector);
    }

    public function getDependencias($cabezaSectorId)
    {
        $dependencias = DB::table('dependencia as d')
            ->where('d.cs_id', $cabezaSectorId)
            ->select('d.dep_id as id', 'd.nombre')
            ->get();
        return response()->json($dependencias);
    }

    public function getDelegaciones($dependenciaId)
    {
        $delegaciones = DB::table('oficinas as o')
            ->where('o.dep_id', $dependenciaId)
            ->select('o.oficina_id as id', 'o.oficina as nombre')
            ->get();
        return response()->json($delegaciones);
    }

    public function getAreas($delegacionId)
    {
        $areas = DB::table('areas as a')
            ->where('a.oficina_id', $delegacionId)
            ->select('a.area_id as id', 'a.area as nombre')
            ->get();
        return response()->json($areas);
    }

    public function foliosAnt()
    {
        $registros = DB::table('registros')
        ->whereRaw('1')
        ->get();

        $perPage = $request->input('per_page', 10); // Número de items por página, por defecto 10
        $folios = $registros->paginate($perPage);
    return response()->json($registros);
    }
}
    


