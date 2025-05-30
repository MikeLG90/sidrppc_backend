<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TiffController;
use App\Http\Controllers\FolioController;
use App\Http\Controllers\SATQController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CatastroApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\APIUsuarios;
use App\Http\Controllers\APIOficinas;
use App\Http\Controllers\APIFolio;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\SolicitudResolucionController;
use App\Http\Controllers\ResolucionFirmadaController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DelegacioneController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\WebServicesController;
use App\Mail\NotificacionMailable;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login-user', [AuthController::class, 'login']);
Route::post('/register-user', [AuthController::class, 'store']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/satq/token', [SATQController::class,'obtenerToken'])->name('satq.token');
Route::get('/satq/concepto/{token}/{referencia}', [SATQController::class,'obtenerConceptosPagados'])->name('satq.conP');
Route::get('/satq/log/{conceptoId}/{token}', [SATQController::class,'obtenerConceptoLog'])->name('satq.conL');


Route::get('/folios', [FolioController::class, 'index1']);
Route::get('/folios-antecedentes', [FolioController::class, 'foliosAnt']);
Route::get('/cabezas-sector', [FolioController::class, 'getCabezasSector']);
Route::get('/dependencias/{cabezaSector}', [FolioController::class, 'getDependencias']);
Route::get('/delegaciones/{dependencia}', [FolioController::class, 'getDelegaciones']);
Route::get('/areas/{delegacion}', [FolioController::class, 'getAreas']);


// Ruta para obtener tickets filtrados por estado
Route::get('/tickets', [TicketController::class, 'getTickets']);

// Ruta para obtener detalles de un ticket específico por ID
Route::get('/tickets/{id}', [TicketController::class, 'getTicketById']);

// Apis para visor
Route::get('/libros/{id_oficina}', [LibroController::class,'getLibros'])->name('lib.get');
Route::get('/libros-st/{seccion}/{tomo}/{id_oficina}', [LibroController::class,'getLibrosST'])->name('lib.getS');
Route::get('/libro/imagenes/{id_oficina}/{id_libro}', [LibroController::class,'getImagenesLibros'])->name('img.get');
Route::get('/inscripciones/{seccion}/{tomo}/{id_oficina}/{inscripcion}', [LibroController::class,'getInscripciones'])->name('lib.ins');

// Ruta para obtener las resolciones con o sin filtrado
Route::get('/listado-resoluciones', [ResolucionController::class,'index1']);

Route::get('/listado-resoluciones3', [ResolucionController::class,'index3']);
Route::get('/listado-resoluciones4', [ResolucionController::class,'index4']);


Route::get('/catastro/rppc/{cve_catastro}', [CatastroApiController::class,'obtenerDatosRppc'])->name('satq.conL');


// ------------------------    APIS PARA EL FRONTEND DE ANGULAR  --------------------------------------------
Route::get('usuarios-folio', [APIUsuarios::class,'getUsers']);
Route::get('oficinas-folio', [APIOficinas::class,'getOficinas']);

// FOLIOS

Route::post('/folio-store', [APIFolio::class, 'store']);


// RESOLUCIONES

Route::get('/listado-resoluciones', [ResolucionController::class,'index1']);
// envaiados

Route::get('/listado-resoluciones2', [ResolucionController::class,'index2']);

Route::get('/listado-alertas', [ResolucionController::class,'index3']);
Route::get('/listado-resoluciones/dj', [ResolucionController::class,'index4']);

Route::get('/listado-resoluciones/dg', [ResolucionController::class,'indexDireccionGeneral']);

Route::post('/resolucion-store', [ResolucionController::class,'store']);
Route::get('/listado-oficinas', [ResolucionController::class,'oficinas']);
Route::get('/ver-resolucion/{resolucion_id}', [ResolucionController::class,'view']);
Route::get('/file/{filename}', [FileController::class, 'getFile']);
Route::get('/resolucion/file/{id}', [ResolucionController::class, 'resolucionFile']);
Route::get('/resolucion/sol/{id}', [ResolucionController::class, 'resolucionFileSol']);
Route::get('/resolucion/oficio/{id}', [ResolucionController::class, 'resolucionFileOficio']);
Route::any('/comentario/store', [ComentarioController::class, 'store']);
Route::post('/comentario/leido', [ComentarioController::class, 'cambiarLeido']);

Route::get('/get-notificaciones/{id}', [ComentarioController::class, 'getNotificaciones']);

Route::post('/resolucion/leido', [ResolucionController::class, 'cambiarLeido']);
Route::post('/resoluciones/cambiar-estado', [ResolucionController::class, 'cambiarEstado']);
Route::post('/resoluciones/{id}/replace-files', [ResolucionController::class, 'replaceFiles']);

// Resoluciones firmadas
Route::any('/resoluciones/cargar', [ResolucionFirmadaController::class, 'store']);
Route::get('/resoluciones/firmada/{id}', [ResolucionFirmadaController::class, 'getResolucionFirmada']);



// solicutd para resoluciones
Route::apiResource('solicitudes', SolicitudResolucionController::class);
Route::get('/solicitud/{id}', [SolicitudResolucionController::class, 'getSol']);
Route::get('/solicitud-file/{id}', [SolicitudResolucionController::class, 'solFile']);
Route::post('/solicitud-leido', [SolicitudResolucionController::class, 'cambiarLeido']);

// usuairos
Route::post('/user/register', [RegisteredUserController::class, 'store']);
Route::get('/usuarios', [UserController::class, 'index']);
Route::get('/areas/delegacion/{delId}', [AreaController::class, 'area']);
Route::get('/roles', [UserController::class, 'roles']);
Route::put('/usuarios/{userId}/rol', [UserController::class, 'cambioRolApi']);

// web services
Route::get('/consulta-catastro/{municipio}/{cve}', [WebServicesController::class,'obtenerDatosCatastro'])->name('grupo.index');


// Mail

Route::post('/enviar-correo', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'mensaje' => 'required|string',
    ]);

    Mail::to($request->email)->send(new NotificacionMailable($request->mensaje));

    return response()->json(['message' => 'Correo enviado correctamente']);
});

// imagenes de libros
Route::get('/pdf/view/{nombre_archivo}/{libro_id}/{oficina_id}', [TiffController::class, 'viewBookimg']);