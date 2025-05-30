<?php

namespace App\Http\Controllers;
use App\Models\ResolucionFirmada;
use App\Models\Resolucion;
use Illuminate\Support\Facades\DB;
use App\Models\NotificacionResolucion;
use App\Models\SolicitudResolucion;


use Illuminate\Http\Request;

class ResolucionFirmadaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resolucion_id' => 'required|integer',
            'usuario_id' => 'required|integer',
            'tipo_res' => 'required|integer'
        ]);
    
        $request->validate([
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048', 
        ]);

        $resolucion = Resolucion::find($request->resolucion_id);

        $usuarios = DB::table('rppc.usuarios')
        ->whereIn('rol_id', [1, 5, 8, 3, 10])
        ->where('oficina_id', $resolucion->oficina_dest)
        ->get();
        


        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = "RF-" . $request->resolucion_firmada . '-' . pathinfo($originalName, PATHINFO_FILENAME) . '-' . time() . '.' . $file->getClientOriginalExtension();
    
                $path = $file->storeAs('archivos', $uniqueName, 'ftp'); 
    
                ResolucionFirmada::create([
                    'file_path' => $path,
                    'resolucion_id' => $request->resolucion_id,

                ]);
            }
        }

        foreach ($usuarios as $user) {
            NotificacionResolucion::create([
                'usuario_origen_id' => $request->usuario_id, 
                'usuario_destino_id' => $user->usuario_id,
                'mensaje' => 'La resolución con ID ' . $request->solicitud_resolucion_id . ' ya tiene firma de Dirección General',
                'link' => '/apps/email/resolucion/' . $request->resolucion_id,
                'leido' => false,
            ]); 
        }

        $resolucion->estatus = 3;
        $resolucion->leido = false;
        $resolucion->tipo_resolucion = $request->tipo_res;
        $resolucion->save();

        $solicitud = SolicitudResolucion::find($resolucion->solicitud_resolucion_id);
        $solicitud->estatus  = 2;
        $solicitud->save();

    
        return response()->json('Éxito', 201);
    }

    public function getResolucionFirmada($id) 
    {
        $resolucion_firmada = ResolucionFirmada::where('resolucion_id', $id)->first(); 


        return response()->json([
            'success' => true,
            'data' => $resolucion_firmada
        ]);
    }
}
