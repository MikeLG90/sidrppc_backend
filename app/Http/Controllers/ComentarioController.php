<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comentario;
use App\Models\User;
use App\Models\Resolucion;
use App\Models\NotificacionResolucion;
use App\Events\ComentarioPublicado;
use Illuminate\Support\Facades\DB;
use App\Mail\NotificacionMailable;
use Illuminate\Support\Facades\Mail;


class ComentarioController extends Controller
{

    public function getNotificaciones($id) 
    {
        return DB::table('rppc.notificaciones_res as r')
            ->join('rppc.usuarios as u', 'r.usuario_origen_id', '=', 'u.usuario_id')
            ->join('rppc.persona as p', 'u.persona_id', '=', 'p.persona_id')
            ->select(
                DB::raw("p.nombre || ' ' || p.ape_paterno || ' ' || p.ape_materno as nombre_completo"),
                'r.*'
            )
            ->where('r.usuario_destino_id', $id)
            ->orderBy('r.created_at', 'desc')
            ->get();
    }
    
    public function cambiarLeido(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);
    
        $id = $request->id;
        
        $not = NotificacionResolucion::find($id);
        
        // Verifica que la resolución exista
        if ($not) {
            $not->leido = true;
            $not->save();
            return response()->json(['message' => 'Estado actualizado con éxito.'], 200);
        } else {
            return response()->json(['message' => 'Resolución no encontrada.'], 404);
        }

    }
    public function store(Request $request)
    {
        $request->validate([
            'resolucion_id' => 'required|integer|exists:resoluciones,resolucion_id',
            'usuario_id' => 'required|integer|exists:usuarios,usuario_id',
            'contenido' => 'required|string',
            'rol_id' => 'required|integer',
        ]);
    

        try {

            $resolucion = Resolucion::findOrFail($request->resolucion_id);
            
            $comentario = new Comentario;
            $comentario->resolucion_id = $request->resolucion_id;
            $comentario->usuario_id = $request->usuario_id; 
            $comentario->contenido = $request->contenido;    
            $comentario->save();
    
            $ruta = '/apps/email/resolucion/' . $request->resolucion_id;
            $link = 'http://sistemas-rppc.qroo.gob.mx:8000' . $ruta;
            
            if (in_array($request->rol_id, [1, 3, 8])) {
                $resolucion->observaciones += 1;
            
                $mensaje = 'La resolución con ID ' . $resolucion->solicitud_resolucion_id . ' tiene una observación nueva.';
    
                $juridicos = DB::table('rppc.usuarios')
                ->where('rol_id', 5)
                ->where('oficina_id', $resolucion->oficina_dest)
                ->get();

                $this->notificarUsuarios($juridicos, $request->usuario_id, $mensaje, $ruta, 'Notificación de resolución SIDRPPC', $request->resolucion_id);

            }
            // Verificar si debe activarse la alerta
            if ($resolucion->observaciones >= 2) {
                $resolucion->alerta = true;
                $resolucion->estatus = 1;

                $mensaje = 'La resolución con ID ' . $resolucion->solicitud_resolucion_id . ' en estado de alerta.';
                
                $usuarios = DB::table('rppc.usuarios')
                ->whereIn('rol_id', [5, 3, 8])
                ->where('oficina_id', $resolucion->oficina_dest)
                ->get();

                $this->notificarUsuarios($juridicos, $request->usuario_id, $mensaje, $ruta, 'Notificacion de resolución SIDRPPC', $request->resolucion_id);

            }
    
            $resolucion->save();
    
            return response()->json([
                'message' => 'Comentario creado exitosamente',
                'comentario' => $comentario
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el comentario',
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        
        }
    }

    private function notificarUsuarios($usuarios, $usuarioOrigen, $mensaje, $link, $clase, $resolucionId)
    {
        foreach ($usuarios as $user) {
            NotificacionResolucion::create([
                'usuario_origen_id' => $usuarioOrigen, 
                'usuario_destino_id' => $user->usuario_id,
                'mensaje' => $mensaje,
                'link' => $link,
                'leido' => false,
                'resolucion_id' => $resolucionId
            ]);
            
            Mail::to($user->email)->send(new NotificacionMailable($mensaje, $link, $clase));
        }
    }
}
