<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CancionesCategoriaController;
use App\Http\Controllers\CancionesController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EnlacesController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\sisipedia\AportacionController;
use App\Http\Controllers\sisipedia\CategoryController;
use App\Http\Controllers\VideoCategoriaController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome'); })->name('index')->middleware('auth'); */
Route::get('/', [EnlacesController::class, 'index'])->name('index');
Auth::routes();
/* Route::get('administrador', [EnlacesController::class, 'admin'])->name('administrador')->middleware('auth'); */
Route::resource('administradores', AdminController::class)->names('admin')->middleware('auth');
Route::get('Lista-de-alumnos', [AdminController::class, 'alumnos'])->name('alumnos')->middleware('auth');
Route::get('Bibliotecario', [AdminController::class, 'bibliotecario'])->middleware('auth')->name('bibliotecario');
Route::get('Administrador-Videos', [AdminController::class, 'videos'])->middleware('auth')->name('adminVideos');
Route::get('Administrador-Canciones', [AdminController::class, 'canciones'])->middleware('auth')->name('adminCanciones');
Route::get('Admini-Sisichakunay', [AdminController::class, 'sisicha'])->middleware('auth')->name('adminSisicha');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Biblioteca
Route::resource('Secciones', SeccionController::class)->names('secciones')->middleware('auth');
Route::resource('Categorias', CategoriaController::class)->names('categorias')->middleware('auth');
Route::get('Categoria/{id}/', [CategoriaController::class, 'detallesCat'])->name('detallesCat')->middleware('auth');
Route::resource('Libros', LibroController::class)->names('libros')->middleware('auth');

Route::get('libros/create/{categoria?}', [LibroController::class, 'create'])->name('libros.create')->middleware('auth');
Route::get('Lista-de-Libros', [LibroController::class, 'libros'])->name('libros');

//Videos
Route::resource('Videos', VideoController::class)->names('videos');
Route::resource('video-categorias', VideoCategoriaController::class)->names('videoscat')->middleware('auth');
Route::get('videos-Puklla', [VideoCategoriaController::class, 'lista'])->name('videos')->middleware('auth');

//Canciones
Route::resource('Categorias-canciones', CancionesCategoriaController::class)->names('cancionescat')->middleware('auth');
Route::resource('canciones', CancionesController::class)->names('canciones')->middleware('auth');
Route::get('canciones-Puklla', [CancionesController::class, 'canciones'])->name('cancionesPuklla')->middleware('auth');

Route::resource('Dashboard', AdminController::class)->names('admin');
Route::get('resultados-de-busqueda', [EnlacesController::class, 'busqueda'])->name('busqueda');

Route::middleware('auth')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])->name('categories.update-order');
});

//Sisipedia
Route::prefix('sisipedia')->name('sisipedia.')->group(function () {
    // Admin primero (rutas fijas como /create, /reorder deben resolverse antes que {category})
    Route::middleware('auth')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('categories/{category}/admin', [CategoryController::class, 'adminShow'])
            ->name('categories.admin-show');
        Route::get('categories/{category}/children', [CategoryController::class, 'getChildren'])
            ->name('categories.children');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])
            ->name('categories.reorder');
        Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->name('categories.toggle-status');
        Route::delete('categories/{category}/files/{file}', [CategoryController::class, 'destroyFile'])
            ->name('categories.files.destroy');
    });

    // Aportaciones
    Route::middleware('auth')->group(function () {
        Route::get('aportaciones', [AportacionController::class, 'adminIndex'])
            ->name('aportaciones.index');
    });
    // Crear aportación: público (cualquier visitante puede aportar)
    Route::post('categories/{category}/aportaciones', [AportacionController::class, 'store'])
        ->name('categories.aportaciones.store');

    Route::post('aportaciones/general', [AportacionController::class, 'storeStandalone'])
        ->name('aportaciones.general.store');

    // Eliminar: solo admin o sisicha
    Route::middleware(['auth', 'role:admin|sisicha'])
        ->delete('categories/{category}/aportaciones/{aportacion}', [AportacionController::class, 'destroy'])
        ->name('categories.aportaciones.destroy');

    Route::middleware(['auth', 'role:admin|sisicha'])->group(function () {
        Route::post('aportaciones/{aportacion}/approve', [AportacionController::class, 'approve'])
            ->name('aportaciones.approve');
        Route::patch('aportaciones/{aportacion}', [AportacionController::class, 'update'])
            ->name('aportaciones.update');
        Route::delete('aportaciones/{aportacion}', [AportacionController::class, 'destroyStandalone'])
            ->name('aportaciones.destroy');
    });

    // Detalle público al final (wildcard {category} no captura rutas fijas de arriba)
    Route::get('categories/{category}', [CategoryController::class, 'show'])
        ->name('categories.show');
});

Route::get('sisipedia/registros', [CategoryController::class, 'registros'])->name('public.sisi');
Route::get('sisipedia/categorias', [CategoryController::class, 'publicIndex'])->name('public.categoria.sisi');
Route::get('sisipedia/search', [CategoryController::class, 'search'])->name('sisipedia.search');

/* Route::get('/limpiar-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    return 'Cache limpiada correctamente';
});
 */