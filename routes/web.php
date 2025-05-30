<?php

use App\Http\Controllers\OficinaFolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FolioController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DelegacioneController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\LibroController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\WebServicesController;
use App\Http\Controllers\CatastroApiController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/guardar/catastro', action: [CatastroApiController::class, 'store']);
Route::get('/consulta/catastro', action: [CatastroApiController::class, 'index']);


Route::get('/send-test-email', [MailController::class, 'sendMail']);
Route::get('/listado-resoluciones2', [ResolucionController::class,'index2']);



// Rutas de las oficinas recurrentes
Route::get('/oficinas-folios', [OficinaFolioController::class, 'index'])->name('index-oficina-folio');
Route::post('/oficinas-folios/store', [OficinaFolioController::class, 'store'])->name('store-oficina-folio');
Route::put('/oficinas-folios/update/{id}', [OficinaFolioController::class, 'update'])->name('update-oficina-folio');
Route::delete('/oficinas-folios/delete/{oficina_folio_id}', [OficinaFolioController::class, 'destroy'])->name('destroy-oficina-folio');

use App\Http\Controllers\TiffController;
Route::get('/callback/google', [GoogleAuthController::class, 'callback']);
Route::get('/enviar-correo', [EmailController::class, 'enviarCorreo']);



Route::post('/preview-tiff', [TiffController::class, 'previewTiff'])->name('preview-tiff');
Route::get('/visor', [TiffController::class, 'index'])->name('index-tiff');
Route::get('/tiff/view/{fileName}/{idLibro}/{oficinaId}', [TiffController::class, 'viewTiff'])->name('viewer-tiff');
Route::get('/tiff/ins/{fileName}/{oficinaId}', [TiffController::class, 'viewTiffIns'])->name('viewer-ins');




Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::post('/editar-perfil', [UserController::class, 'updateUser']);
    Route::put('/editar-user/{userId}', [UserController::class, 'cambioRol']);


});
Route::get('/user', [UserController::class, 'DatosUser']);
// Visor
Route::middleware('auth')->group(function () {
    Route::get('/visor_img', function () {
        return view('rppc.visor.visor_img');        
    });
    Route::get('/visor_inscripciones', function () {
        return view('rppc.visor.visor_inscripciones');        
    });   

});



// ConsultaSATQ 
Route::get('/satq', function () {
    return view('rppc.satq.consultSatq');
});
Route::get('/bienvenida', function () {
    return view('rppc.bienvrenida');
});
// Api catastro
Route::get('/catastro', function () {
    return view('rppc.web_services.ig_api');
});

// Consulta Catastro
Route::get('/consulta-catastro/{municipio}/{cve}', [WebServicesController::class,'obtenerDatosCatastro'])->name('grupo.index');


Route::get('/gmail-send', function () {
    return view('mail-ejemplo');
});

Route::put('/not/update/{notId}', [NotificacionController::class, 'update'])->name('not.update');

//Rutas para grupos

Route::middleware('auth')->group(function () {
    Route::get('/grupos', [GrupoController::class,'index'])->name('grupo.index');
    Route::post('/guardar-grupo', [GrupoController::class, 'store'])->name('grupo.store');
    Route::get('/miembros/{group_id}', [GrupoController::class,'getMembers'])->name('grupo.members');

});

//Rutas para delegacion
Route::middleware('auth')->group(function () {
    Route::get('/delegaciones', [DelegacioneController::class, 'index'])->name('del.index');
    Route::post('/delegacion/store', [DelegacioneController::class, 'store'])->name('del.store');
    Route::put('/editar-delegacion/{id}', [DelegacioneController::class, 'update'])->name('del.update');
    Route::delete('/delegacion/destroy/{oficina_id}', [DelegacioneController::class, 'destroy'])->name('del.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/dependencias', [DependenciaController::class, 'index'])->name('dep.index');
    Route::post('/dependencia/store', [DependenciaController::class, 'store'])->name('dep.store');
    Route::put('/editar-dependencia/{id}', [DependenciaController::class, 'update'])->name('dep.update');
    Route::delete('/dependencia/destroy/{dep_id}', [DependenciaController::class, 'destroy'])->name('del.destroy');
});


Route::get('/file/{filename}', [FileController::class, 'getFile']);

//Rutas para los folios
Route::middleware('auth')->group(function () {
    Route::get('/crear-folio', [FolioController::class, 'create'])->name('folio.create');
    Route::post('/guardar-folio', [FolioController::class, 'store'])->name('folio.store');
    Route::get('/folios/{folio}', [FolioController::class, 'show'])->name('folios.show');
    Route::get('/mis-folios', [FolioController::class, 'misFolios'])->name('folio.index');

});
Route::get('/folios', [FolioController::class, 'getFolios'])->name('folios.get');

//Rutas para los tickets
Route::middleware('auth')->group(function () {
    Route::get('/inbox', [TicketController::class, 'index'])->name('ticket.index');
    Route::post('/store-tck', [TicketController::class, 'store'])->name('ticket.store');
    Route::get('/antecedentes', [TicketController::class, 'antecedentes'])->name('antecedentes');
    Route::get('/usuarios/oficina/{oficinaId}', [TicketController::class, 'userOficina']);
    Route::put('/ticket/update/{ticketId}', [TicketController::class, 'update'])->name('ticket.update');
    Route::get('/ticket/create', [TicketController::class, 'create'])->name('create.t');

});

// Rutas para las resoluciones

Route::middleware('auth')->group(function () {
    Route::get('/resoluciones', [ResolucionController::class, 'index'])->name('resolucion.index');
    Route::post('/nueva-resolucion', [ResolucionController::class, 'store'])->name(name: 'resolucion.store');
    Route::get('/resolucion/{id}', [ResolucionController::class, 'view'])->name('resolucion.view');
    Route::get('/resoluciones-chart-data', [ResolucionController::class, 'getResolucionesData'])->name('resolucion-data.index');
    Route::get('/resolucion/file/{id}', [ResolucionController::class, 'resolucionFile'])->name('resolucion.file');
    Route::get('/resolucion/sol/{id}', [ResolucionController::class, 'resolucionFileSol'])->name('resolucion.file');
    Route::get('/resolucion/oficio/{id}', [ResolucionController::class, 'resolucionFileOficio'])->name('resolucion.file');
    Route::post('/aprobar/resolucion/{resolucion_id}', [ResolucionController::class, 'cambiarEstado'])->name(name: 'resolucion.estado');


});

// Rutas para los comentarios
Route::post('/comentario/store', [ComentarioController::class, 'store'])->name('comentario.store');


Route::get('/ticket/files/{id}', [TicketController::class, 'ticketFiles']);


 

Route::middleware('checkRole:Trabajador')->group(function () {
    Route::get('/index-folios', [FolioController::class, 'index'])->name('folio.index');
});
//Rutas para las áreas
Route::middleware('checkRole:Trabajador')->group(function () {
    Route::get('/areas', [AreaController::class, 'index'])->name('area.index');
    Route::post('/area/guardar', [AreaController::class, 'store'])->name('area.store');
    Route::put('/editar-area/{id}', [AreaController::class, 'update'])->name('area.update');
    Route::delete('/areas/{area_id}', [AreaController::class, 'destroy'])->name('areas.destroy');
    Route::get('/oficinas/dependencia/{depId}', [AreaController::class, 'delegacion']);
});    

Route::get('/areas/delegacion/{delId}', [AreaController::class, 'area']);

Route::get('/perfil', function () {
    return view('rppc.user-perfil');
});

Route::get('/tif', function () {
    return view('viewer-tiff');
});


Route::get('/poblacion', function () {
    return view('rppc.resoluciones.preview-res');
});

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/pruebas', function () {
    return view('prueba');
});

Route::get('/folios-app', function () {
    return view('prueba2');
});


require __DIR__.'/auth.php';
