<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resolucion;
use App\Models\ResolucionFile;
use App\Models\SolicitudResolucion;
use App\Models\OficioResolucion;
use App\Models\ResSolicitudFile;
use App\Models\Oficina;
use App\Models\ArchivoSolicitud;
use App\Models\NotificacionResolucion;
use App\Models\Comentario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class ResolucionController extends Controller
{
    public function indexApi(): JsonResponse
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");

        $oficinas = Oficina::all();
        $resoluciones = Resolucion::indexResoluciones();

        $formattedResoluciones = $resoluciones->map(function ($resolucion) {
            return [
                ...$resolucion->toArray(),
                "fecha_aper_formatted" => Carbon::parse(
                    $resolucion->fecha_aper
                )->translatedFormat('j \d\e F \d\e Y'),
            ];
        });

        $cantidad_pendientes = $resoluciones->where("estatus", 0)->count();
        $cantidad_aprobadas = $resoluciones->where("estatus", 1)->count();
        $total = $cantidad_aprobadas + $cantidad_pendientes;

        return response()->json([
            "success" => true,
            "data" => [
                "oficinas" => $oficinas,
                "resoluciones" => $formattedResoluciones,
                "stats" => [
                    "pendientes" => $cantidad_pendientes,
                    "aprobadas" => $cantidad_aprobadas,
                    "total" => $total,
                ],
            ],
        ]);
    }

    // Buzon del total de resoluciones(las condiciones dependendel tipo de rol)
    public function index1(Request $request)
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");

        try {
            // Validar los parámetros de entrada
            $validated = $request->validate([
                "oficina_id" => "sometimes|string",
                "tipo" => "sometimes|string",
                "estatus" => "sometimes|string",
                "usuario_id" => "sometimes|integer",
                "per_page" => "sometimes|integer|min:1|max:100",
            ]);

            // Construir la consulta base
            $resultados = DB::table("rppc.resoluciones as r")
                ->join(
                    "rppc.usuarios as u",
                    "r.usuario_id",
                    "=",
                    "u.usuario_id"
                )
                ->join("rppc.persona as p", "p.usuario_id", "=", "u.usuario_id")
                ->join(
                    "rppc.oficinas as o",
                    "o.oficina_id",
                    "=",
                    DB::raw("r.oficina_dest::integer")
                )
                ->select(
                    "r.*",
                    "o.oficina",
                    DB::raw(
                        "p.nombre || ' ' || p.ape_paterno || ' ' || p.ape_materno AS nombre_completo"
                    )
                )
                ->orderBy("r.created_at", "desc");


            // Aplicar filtros
            if ($request->filled("oficina_id")) {
                $resultados->where("r.oficina_dest", $request->oficina_id);
            }

            if ($request->filled("tipo") && $request->tipo !== "Todos") {
                $resultados->where("r.tipo", $request->tipo);
            }

            if ($request->filled("estatus") && $request->estatus !== "Todos") {
                $resultados->where("r.estatus", $request->estatus);
            }

            if ($request->filled("usuario_id")) {
                $resultados->where("r.usuario_id", $request->usuario_id);
            }

            foreach ($resultados as $resultado) {
                // Asegúrate de que created_at esté definido antes de usarlo
                if (isset($resultado->created_at)) {
                    $resultado->created_at = Carbon::parse(
                        $resultado->created_at
                    )->translatedFormat('j \d\e F \d\e Y');
                }
            }

            $perPage = $request->input("per_page", 10);

            $resoluciones = $resultados->paginate($perPage);

            return response()->json([
                "success" => true,
                "data" => $resoluciones->items(),
                "pagination" => [
                    "total" => $resoluciones->total(),
                    "per_page" => $resoluciones->perPage(),
                    "current_page" => $resoluciones->currentPage(),
                    "last_page" => $resoluciones->lastPage(),
                    "from" => $resoluciones->firstItem(),
                    "to" => $resoluciones->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "Ocurrió un error en la API.",
                    "message" => $e->getMessage(),
                ],
                500
            );
        }
    }

    // api de las resoluciones enviadas por el usuario autenticado
    public function index2(Request $request)
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");
        try {
            $validated = $request->validate([
                "oficina_id" => "sometimes|string",
                "tipo" => "sometimes|string",
                "estatus" => "sometimes|string",
                "usuario_id" => "sometimes|integer",
                "per_page" => "sometimes|integer|min:1|max:100",
            ]);

            $resultados = DB::table("rppc.resoluciones as r")
                ->join(
                    "rppc.usuarios as u",
                    "r.usuario_id",
                    "=",
                    "u.usuario_id"
                )
                ->join("rppc.persona as p", "p.usuario_id", "=", "u.usuario_id")
                ->join(
                    "rppc.oficinas as o",
                    "o.oficina_id",
                    "=",
                    DB::raw("r.oficina_dest::integer")
                )
                ->select(
                    "r.*",
                    "o.oficina",
                    DB::raw(
                        "p.nombre || ' ' || p.ape_paterno || ' ' || p.ape_materno AS nombre_completo"
                    )
                )
                ->orderBy("r.created_at", "desc")
                ->where("r.usuario_id", $request->usuario_id);

                if ($request->filled("oficina_id")) {
                    $resultados->where("r.oficina_dest", $request->oficina_id);
                }

            if ($request->filled("tipo") && $request->tipo !== "Todos") {
                $resultados->where("r.tipo", $request->tipo);
            }

            if ($request->filled("estatus") && $request->estatus !== "Todos") {
                $resultados->where("r.estatus", $request->estatus);
            }
            foreach ($resultados as $resultado) {
                // Asegúrate de que created_at esté definido antes de usarlo
                if (isset($resultado->created_at)) {
                    $resultado->created_at = Carbon::parse(
                        $resultado->created_at
                    )->translatedFormat('j \d\e F \d\e Y');
                }
            }
            $perPage = $request->input("per_page", 10);
            $resoluciones = $resultados->paginate($perPage);

            return response()->json([
                "success" => true,
                "data" => $resoluciones->items(),
                "pagination" => [
                    "total" => $resoluciones->total(),
                    "per_page" => $resoluciones->perPage(),
                    "current_page" => $resoluciones->currentPage(),
                    "last_page" => $resoluciones->lastPage(),
                    "from" => $resoluciones->firstItem(),
                    "to" => $resoluciones->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    // api de las rsoluciones alertadas (Direccion general)
    public function index3(Request $request)
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");
        try {
            $validated = $request->validate([
                "oficina_id" => "sometimes|string",
                "tipo" => "sometimes|string",
                "estatus" => "sometimes|string|in:1", // Solo aprobadas
                "per_page" => "sometimes|integer|min:1|max:100",
            ]);

            $resultados = DB::table("rppc.resoluciones as r")
                ->join(
                    "rppc.usuarios as u",
                    "r.usuario_id",
                    "=",
                    "u.usuario_id"
                )
                ->join("rppc.persona as p", "p.usuario_id", "=", "u.usuario_id")
                ->join(
                    "rppc.oficinas as o",
                    "o.oficina_id",
                    "=",
                    DB::raw("r.oficina_dest::integer")
                )
                ->select(
                    "r.*",
                    "o.oficina",
                    DB::raw(
                        "p.nombre || ' ' || p.ape_paterno || ' ' || p.ape_materno AS nombre_completo"
                    )
                )
                ->where("r.estatus", 1);

            if (
                $request->filled("oficina_id") &&
                $request->oficina_id !== "Todos"
            ) {
                $resultados->where("r.oficina_dest", $request->oficina_id);
            }

            if ($request->filled("tipo") && $request->tipo !== "Todos") {
                $resultados->where("r.tipo", $request->tipo);
            }

            foreach ($resultados as $resultado) {
                // Asegúrate de que created_at esté definido antes de usarlo
                if (isset($resultado->created_at)) {
                    $resultado->created_at = Carbon::parse(
                        $resultado->created_at
                    )->translatedFormat('j \d\e F \d\e Y');
                }
            }
            $perPage = $request->input("per_page", 10);
            $resoluciones = $resultados->paginate($perPage);

            return response()->json([
                "success" => true,
                "data" => $resoluciones->items(),
                "pagination" => [
                    "total" => $resoluciones->total(),
                    "per_page" => $resoluciones->perPage(),
                    "current_page" => $resoluciones->currentPage(),
                    "last_page" => $resoluciones->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    // Buzón de dirección jurídica
    public function index4(Request $request)
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");
        try {
            $validated = $request->validate([
                "oficina_id" => "sometimes|string",
                "tipo" => "sometimes|string",
                "estatus" => "sometimes|string|in:0,Todos", // Solo pendientes
                "per_page" => "sometimes|integer|min:1|max:100",
            ]);

            $resultados = DB::table("rppc.resoluciones as r")
                ->join(
                    "rppc.usuarios as u",
                    "r.usuario_id",
                    "=",
                    "u.usuario_id"
                )
                ->join("rppc.persona as p", "p.usuario_id", "=", "u.usuario_id")
                ->join(
                    "rppc.oficinas as o",
                    "o.oficina_id",
                    "=",
                    DB::raw("r.oficina_dest::integer")
                )
                ->select(
                    "r.*",
                    "o.oficina",
                    DB::raw(
                        "p.nombre || ' ' || p.ape_paterno || ' ' || p.ape_materno AS nombre_completo"
                    )
                )
                ->where("r.estatus", 0);

                if ($request->filled("oficina_id")) {
                    $resultados->where("r.oficina_dest", $request->oficina_id);
                }

            if ($request->filled("tipo") && $request->tipo !== "Todos") {
                $resultados->where("r.tipo", $request->tipo);
            }

            foreach ($resultados as $resultado) {
                // Asegúrate de que created_at esté definido antes de usarlo
                if (isset($resultado->created_at)) {
                    $resultado->created_at = Carbon::parse(
                        $resultado->created_at
                    )->translatedFormat('j \d\e F \d\e Y');
                }
            }

            $perPage = $request->input("per_page", 10);
            $resoluciones = $resultados->paginate($perPage);

            return response()->json([
                "success" => true,
                "data" => $resoluciones->items(),
                "pagination" => [
                    "total" => $resoluciones->total(),
                    "per_page" => $resoluciones->perPage(),
                    "current_page" => $resoluciones->currentPage(),
                    "last_page" => $resoluciones->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function indexDireccionGeneral(Request $request)
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");
        try {
            $validated = $request->validate([
                "oficina_id" => "sometimes|string",
                "tipo" => "sometimes|string",
                "estatus" => "sometimes|string|in:0,Todos", // Se agrega 'Todos' como opción válida
                "per_page" => "sometimes|integer|min:1|max:100",
            ]);

            $resultados = DB::table("rppc.resoluciones as r")
                ->join(
                    "rppc.usuarios as u",
                    "r.usuario_id",
                    "=",
                    "u.usuario_id"
                )
                ->join("rppc.persona as p", "p.usuario_id", "=", "u.usuario_id")
                ->join(
                    "rppc.oficinas as o",
                    "o.oficina_id",
                    "=",
                    DB::raw("r.oficina_dest::integer")
                )
                ->select(
                    "r.*",
                    "o.oficina",
                    DB::raw(
                        "p.nombre || ' ' || p.ape_paterno || ' ' || p.ape_materno AS nombre_completo"
                    )
                )
                ->where("r.estatus", 2);

            if (
                $request->filled("oficina_id") &&
                $request->oficina_id !== "Todos"
            ) {
                $resultados->where("r.oficina_dest", $request->oficina_id);
            }

            if ($request->filled("tipo") && $request->tipo !== "Todos") {
                $resultados->where("r.tipo", $request->tipo);
            }

            foreach ($resultados as $resultado) {
                // Asegúrate de que created_at esté definido antes de usarlo
                if (isset($resultado->created_at)) {
                    $resultado->created_at = Carbon::parse(
                        $resultado->created_at
                    )->translatedFormat('j \d\e F \d\e Y');
                }
            }

            $perPage = $request->input("per_page", 10);
            $resoluciones = $resultados->paginate($perPage);

            return response()->json([
                "success" => true,
                "data" => $resoluciones->items(),
                "pagination" => [
                    "total" => $resoluciones->total(),
                    "per_page" => $resoluciones->perPage(),
                    "current_page" => $resoluciones->currentPage(),
                    "last_page" => $resoluciones->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    } 

    public function store(Request $request)
    {
        try {
            $request->validate([
                "titulo" => "required|string|max:255",
                "fecha_apertura" => "required|date",
                "tipo" => "required|string",
                "estado" => "required|string",
                "urgencia" => "required|string",
                "impacto" => "required|string",
                "prioridad" => "required|string",
                "oficina" => "required|string",
                "descripcion" => "required|string",
                "promovente" => "required|string",
                "usuario_id" => "required|integer",
                "attachments" => "nullable|array",
                "attachments.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
                "attachments1" => "nullable|array",
                "attachments1.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
                "attachments2" => "nullable|array",
                "attachments2.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
                "solicitud" => "sometimes|nullable",
            ]);
            // genera nueva resolucion
            $resolucion = new Resolucion();
            $resolucion->usuario_id = $request->usuario_id;
            $resolucion->titulo = $request->titulo;
            $resolucion->fecha_aper = $request->fecha_apertura;
            $resolucion->tipo = $request->tipo;
            $resolucion->estado = $request->estado;
            $resolucion->urgencia = $request->urgencia;
            $resolucion->impacto = $request->impacto;
            $resolucion->prioridad = $request->prioridad;
            $resolucion->oficina_dest = $request->oficina;
            $resolucion->leido = false;
            $resolucion->estatus = 0;
            $resolucion->descripcion = $request->descripcion;
            $resolucion->promovente = $request->promovente;
            $resolucion->solicitud_resolucion_id = $request->solicitud;
            $resolucion->save();

            
            $oficiales = DB::table('rppc.usuarios')
            ->whereIn('rol_id', [10])
            ->where('oficina_id', $resolucion->oficina_dest)
            ->get();



            $filesData = [];

            $usuarios = DB::table('rppc.usuarios')
            ->whereIn('rol_id', [3])
            ->where('oficina_id', $resolucion->oficina_dest)
            ->get();

            if($request->solicitud == 0) {
             // si se pasa un archvio en el fron end entonces crear 
                                $this->processFiles(
                                    $request,
                                    "attachments",
                                    $resolucion->resolucion_id,
                                    "RS",
                                    $filesData
                                );
            } else {
                                // buscar el archivo de la solicitud por medio de el id de la solicitud
                                $archivo = DB::table('rppc.archivos_solicitudes')
                                ->where('solicitud_id', $request->solicitud)
                                ->first();
                                ResolucionFile::create([
                                    "resolucion_id" => $resolucion->resolucion_id,
                                    "file_path" => $archivo->file_path,
                                ]);
                                   
    
            }

            $solicitud = SolicitudResolucion::find($request->solicitud);
            $solicitud->estatus = 0;
            $solicitud->save();

            $this->processFiles(
                $request,
                "attachments1",
                $resolucion->resolucion_id,
                "SOLICITUD_RS",
                $filesData
            );
            $this->processFiles(
                $request,
                "attachments2",
                $resolucion->resolucion_id,
                "OFICIO_RS",
                $filesData
            );

            foreach ($usuarios as $user) {
                NotificacionResolucion::create([
                    'usuario_origen_id' => $resolucion->usuario_id, 
                    'usuario_destino_id' => $user->usuario_id,
                    'mensaje' => 'La resolución con ID ' . $resolucion->solicitud_resolucion_id . ' ha sido capturada',
                    'link' => '/apps/email/resolucion/' . $resolucion->resolucion_id,
                    'leido' => false,
                    'resolucion_id' => $resolucion->resolucion_id,
                ]);         
            }

            foreach ($oficiales as $user) {
                NotificacionResolucion::create([
                    'usuario_origen_id' => $resolucion->usuario_id, 
                    'usuario_destino_id' => $user->usuario_id,
                    'mensaje' => 'La resolución con ID ' . $resolucion->solicitud_resolucion_id . ' ha sido capturada',
                    'link' => '/apps/email/resolucion/' . $resolucion->resolucion_id,
                    'leido' => false,
                    'resolucion_id' => $resolucion->resolucion_id,
                ]);         
            }
            return response()->json(
                [
                    "success" => true,
                    "message" => "Resolución creada correctamente",
                    "data" => $resolucion,
                    "files" => $filesData,
                ],
                201
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    // Modificar archivos de solicitud
    public function replaceFiles(Request $request, $id)
    {
        try {
            $request->validate([
                "resolucion_id" => "required|integer",
                "solicitud_id" => "nullable|integer",
                "oficio_id" => "nullable|integer",
                "attachments1" => "nullable|array",
                "attachments1.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
                "attachments2" => "nullable|array",
                "attachments2.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
            ]);
    
            $resolucion = Resolucion::findOrFail($id);
            $filesData = [];
            
            $usuarios = DB::table('rppc.usuarios')
                ->whereIn('rol_id', [1, 8, 3, 10])
                ->where('oficina_id', $resolucion->oficina_dest)
                ->get();
    

        if ($request->filled('solicitud_id') && $request->hasFile('attachments1')) {
            $solicitud = ResSolicitudFile::find($request->solicitud_id);
            if ($solicitud) {
                Storage::disk("ftp")->delete($solicitud->file_path);
                $solicitud->delete();
            }
            $this->processFiles(
                $request,
                "attachments1",
                $resolucion->resolucion_id,
                "SOLICITUD_RS",
                $filesData
            );
        } elseif ($request->hasFile('attachments1')) {
            // Solo subir si hay archivos pero no hay que reemplazar nada
            $this->processFiles(
                $request,
                "attachments1",
                $resolucion->resolucion_id,
                "SOLICITUD_RS",
                $filesData
            );
        }

        if ($request->filled('oficio_id') && $request->hasFile('attachments2')) {
            $oficio = OficioResolucion::find($request->oficio_id);
            if ($oficio) {
                Storage::disk("ftp")->delete($oficio->file_path);
                $oficio->delete();
            }
            $this->processFiles(
                $request,
                "attachments2",
                $resolucion->resolucion_id,
                "OFICIO_RS",
                $filesData
            );
        } elseif ($request->hasFile('attachments2')) {
         // Solo subir si hay archivos pero no hay que reemplazar nada
            $this->processFiles(
                $request,
                "attachments2",
                $resolucion->resolucion_id,
                "OFICIO_RS",
                $filesData
            );
        }


            foreach ($usuarios as $user) {
                NotificacionResolucion::create([
                    'usuario_origen_id' => $resolucion->usuario_id, 
                    'usuario_destino_id' => $user->usuario_id,
                    'mensaje' => 'La resolución con ID ' . $resolucion->solicitud_resolucion_id . ' ha sido modificada',
                    'link' => '/apps/email/resolucion/' . $request->resolucion_id,
                    'leido' => false,
                    'resolucion_id' => $resolucion->resolucion_id
                ]);         
            }
    
            return response()->json(
                [
                    "success" => true,
                    "message" => "Archivos reemplazados correctamente", 
                    "data" => $resolucion,
                    "files" => $filesData,
                ],
                200 // Cambiado a 200 para indicar éxito
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    private function processFiles(
        $request,
        $fieldName,
        $resolucionId,
        $prefix,
        &$filesData
    ) {
        if ($request->hasFile($fieldName)) {
            foreach ($request->file($fieldName) as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName =
                    $prefix .
                    "-" .
                    $request->titulo .
                    "-" .
                    pathinfo($originalName, PATHINFO_FILENAME) .
                    "-" .
                    time() .
                    "." .
                    $file->getClientOriginalExtension();

                $path = $file->storeAs("archivos", $uniqueName, "ftp");

                $fileModel = null;

                switch ($fieldName) {
                    case "attachments1":
                        $fileModel = ResSolicitudFile::create([
                            "resolucion_id" => $resolucionId,
                            "file_path" => $path,
                        ]);
                        break;
                    case "attachments2":
                        $fileModel = OficioResolucion::create([
                            "resolucion_id" => $resolucionId,
                            "file_path" => $path,
                        ]);
                        break;
                    default:
                        $fileModel = ResolucionFile::create([
                            "resolucion_id" => $resolucionId,
                            "file_path" => $path,
                        ]);
                }

                $filesData[] = [
                    "type" => $fieldName,
                    "path" => $path,
                    "original_name" => $originalName,
                ];
            }
        }
    }

    public function update()
    {
    }
    public function view($resolucion_id)
    {
        setlocale(LC_TIME, "es_ES.UTF-8");
        Carbon::setLocale("es");
    
        $resolucion = Resolucion::resolucion($resolucion_id);
        // transformando la fecha a un lenguaje humano
        $resolucion[0]->fecha_aper = Carbon::parse(
            $resolucion[0]->fecha_aper
        )->translatedFormat('j \d\e F \d\e Y');
    
        // buscar comentarios de la resolucion
        $comentarios = Comentario::comentariosR($resolucion_id);
    
        // buscar la oficina de la resolucion
        $oficina = Oficina::findOrFail($resolucion[0]->oficina_dest);
        $oficina_nombre = $oficina->oficina;
    
        return response()->json([
            'resolucion' => $resolucion,
            'comentarios' => $comentarios,
            'oficina_nombre' => $oficina_nombre
        ]);
    }
    public function getResolucionesData()
    {
        $resoluciones = Resolucion::resolucionData();

        // Array con la traducción de los días
        $daysInSpanish = [
            "Monday" => "Lunes",
            "Tuesday" => "Martes",
            "Wednesday" => "Miércoles",
            "Thursday" => "Jueves",
            "Friday" => "Viernes",
            "Saturday" => "Sábado",
            "Sunday" => "Domingo",
        ];

        $daysOfWeek = collect([
            "Lunes" => 0,
            "Martes" => 0,
            "Miércoles" => 0,
            "Jueves" => 0,
            "Viernes" => 0,
            "Sabado" => 0,
            "Domingo" => 0,
        ]);

        foreach ($resoluciones as $resolucion) {
            $dayName = Carbon::parse($resolucion->fecha)->format("l"); //Obtiene el nombre del día
            $dayInSpanish = $daysInSpanish[$dayName];
            $daysOfWeek[$dayInSpanish] = $resolucion->total;
        }

        return response()->json([
            "labels" => $daysOfWeek->keys(),
            "data" => $daysOfWeek->values(),
        ]);
    }

    public function resolucionFile($id)
    {
        $resolucion = Resolucion::resolucion();

        $file = DB::table("resolucion_file as r")
            ->join(
                "resoluciones as r1",
                "r.resolucion_id",
                "=",
                "r1.resolucion_id"
            )
            ->select("r.*")
            ->where("r.resolucion_id", "=", $id)
            ->get();

        return response()->json([
            "resolucion" => $resolucion,
            "files" => $file,
        ]);
    }

    public function resolucionFileSol($id)
    {
        $resolucion = Resolucion::resolucion();

        $file = DB::table("solicitud_res as r")
            ->join(
                "resoluciones as r1",
                "r.resolucion_id",
                "=",
                "r1.resolucion_id"
            )
            ->select("r.*")
            ->where("r.resolucion_id", "=", $id)
            ->get();

        return response()->json([
            "resolucion" => $resolucion,
            "files" => $file,
        ]);
    }

    public function resolucionFileOficio($id)
    {
        $resolucion = Resolucion::resolucion();

        $file = DB::table("oficio_res as r")
            ->join(
                "resoluciones as r1",
                "r.resolucion_id",
                "=",
                "r1.resolucion_id"
            )
            ->select("r.*")
            ->where("r.resolucion_id", "=", $id)
            ->get();

        return response()->json([
            "resolucion" => $resolucion,
            "files" => $file,
        ]);
    }
    public function cambiarEstado(Request $request) 
    {
        $validated = $request->validate([
            "usuario_id" => "required|integer",
            "resolucion_id" => "required|integer"
        ]);

        $resolucion_id = $request->resolucion_id;
        $user_id = $request->usuario_id;

        $user = DB::table("usuarios as u")
            ->join("persona as p", "u.usuario_id", "=", "p.usuario_id")
            ->select(
                'u.*',
                DB::raw(
                    'p.nombre || \' \' || p.ape_paterno || \' \' || p.ape_materno AS user'
                )
            )
            ->where("u.usuario_id", "=", $user_id)
            ->first();

        if (!$user) {
            return response()->json(["error" => "Usuario no encontrado"], 404);
        }

        $resolucion = Resolucion::find($resolucion_id);

        $usuarios = DB::table('rppc.usuarios')
        ->whereIn('rol_id', [1, 5, 8, 3, 10, 6])
        ->where('oficina_id', $resolucion->oficina_dest)
        ->get();
        
        foreach ($usuarios as $user1) {
            NotificacionResolucion::create([
                'usuario_origen_id' => $user->usuario_id, 
                'usuario_destino_id' => $user1->usuario_id,
                'mensaje' => 'La resolución con ID ' . $resolucion->solicitud_resolucion_id . ' ha sido aprobado por Dirección Jurídico',
                'link' => '/apps/email/resolucion/' . $resolucion->resolucion_id,
                'leido' => false,
                'resolucion_id' => $resolucion->resolucion_id,
            ]);         
        }



        if (!$resolucion) {
            return response()->json(["error" => "Resolución no encontrada"], 404);
        }

        switch ($user->rol_id) {
            case 6:
                $resolucion->estatus = 3;
                break;
            case 1:
            case 8:
                $resolucion->estatus = 2;
                $resolucion->leido = false;

                $solicitud = SolicitudResolucion::find($resolucion->solicitud_resolucion_id);
                $solicitud->estatus  = 1;
                $solicitud->save();
                break;
            default:
                return response()->json(["error" => "No tienes permiso para cambiar el estado"], 403);
        }

        $resolucion->save();

        return response()->json([
            "message" => "Estado actualizado con éxito",
            "resolucion" => $resolucion
        ], 200);
    }

    public function oficinas() 
    {
        return Oficina::all();

        
    }

    public function cambiarLeido(Request $request)
    {
        $request->validate([
            'resolucion_id' => 'required|integer'
        ]);
    
        $id = $request->resolucion_id;
        
        $resolucion = Resolucion::find($id);
        
        // Verifica que la resolución exista
        if ($resolucion) {
            $resolucion->leido = true;
            $resolucion->save();
            return response()->json(['message' => 'Estado actualizado con éxito.'], 200);
        } else {
            return response()->json(['message' => 'Resolución no encontrada.'], 404);
        }
    }

}