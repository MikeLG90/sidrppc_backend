<?php

namespace App\Http\Controllers;
use App\Models\SolicitudResolucion;
use App\Models\ArchivoSolicitud;


use Illuminate\Http\Request;

class SolicitudResolucionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = SolicitudResolucion::with('archivos');

        if ($request->has('oficina_id')) {
            $query->where('oficina_id', $request->oficina_id);
        }
        
        return $query->paginate(10);
    }

    public function solFile($id)
    {
        $solicitud = SolicitudResolucion::with('archivos')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $solicitud
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $solicitud = SolicitudResolucion::create([
            'promovente' => $request->promovente,
            'oficina_id' => $request->oficina_id,
            'descripcion' => $request->descripcion,
        ]);

        $solicitud->estatus = 3;
        $solicitud->save();
    
        // Verifica si se han enviado archivos adjuntos
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Obtener el nombre original del archivo
                $originalName = $file->getClientOriginalName();
                // Generar un nombre único para el archivo
                $uniqueName = "FILE-" . $solicitud->id . '-' . pathinfo($originalName, PATHINFO_FILENAME) . '-' . time() . '.' . $file->getClientOriginalExtension();
    
                // Almacena el archivo en el disco FTP
                $path = $file->storeAs('archivos', $uniqueName, 'ftp'); 
    
                // Crea un registro de archivo relacionado con la solicitud
                ArchivoSolicitud::create([
                    'solicitud_id' => $solicitud->id,
                    'file_path' => $path,
                ]);
            }
        }
    
        // Devuelve la respuesta con el estado 201 (creado) y los datos de la solicitud
        return response()->json($solicitud, 201);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validated = $request->validate([
            'promovente' => 'required|string',
            'oficina_id' => 'required|integer',
            'descripcion' => 'required|string',
        ]);

        $solicitud = SolicitudResolucion::create($validated);

        return response()->json($solicitud, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $solicitud = SolicitudResolucion::findOrFail($id);
        $solicitud->update($request->all());

        return response()->json($solicitud);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        SolicitudResolucion::destroy($id);
        return response()->json(['message' => 'Solicitud eliminada']);
    }

    public function getSol($id)
    {
        $solicitud = SolicitudResolucion::findOrFail($id);

        return response()->json($solicitud, 201);
    }

    public function cambiarLeido(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);
    
        $id = $request->id;
        
        $solicitud = SolicitudResolucion::find($id);
        
        // Verifica que la resolución exista
        if ($solicitud) {
            $solicitud->leido = true;
            $solicitud->save();
            return response()->json(['message' => 'Estado actualizado con éxito.'], 200);
        } else {
            return response()->json(['message' => 'Resolución no encontrada.'], 404);
        }
}
}
