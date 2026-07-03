<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\PatientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/doctor/login', [MedecinController::class, 'login']);
Route::post('/patient/login', [PatientController::class, 'login']);

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('/admin')->group(function () {
        Route::post('/logout/{id}', [AdminController::class, 'logout']);

        Route::get('/doctor/all', [MedecinController::class, 'index']);
        Route::get('/doctor/show/{id}', [MedecinController::class, 'show']);
        Route::post('/doctor/store', [MedecinController::class, 'store']);
        Route::put('/doctor/update/{id}', [MedecinController::class, 'update']);
        Route::delete('/doctor/destroy/{id}', [MedecinController::class, 'destroy']);

        Route::get('/patient/all', [PatientController::class, 'index']);
        Route::get('/patient/show/{id}', [PatientController::class, 'show']);
        Route::post('/patient/store', [PatientController::class, 'store']);
        Route::put('/patient/update/{id}', [PatientController::class, 'update']);
        Route::delete('/patient/destroy/{id}', [PatientController::class, 'destroy']);
    });

    Route::prefix('/doctor')->group(function () {
        Route::post('/logout/{id}', [MedecinController::class, 'logout']);
        Route::get('/rdvs', [MedecinController::class, 'prochainsRdv']);

        Route::prefix('/consultations')->group(function () {
            Route::get('/all/{id}', [ConsultationController::class, 'index']);
            Route::get('/show/{id}', [ConsultationController::class, 'show']);
            Route::post('/store', [ConsultationController::class, 'store']);
            // Route::put('/update/{id}', [ConsultationController::class, 'update']);
            Route::delete('/destroy/{id}', [ConsultationController::class, 'destroy']);
        });
    });

    Route::prefix('/patient')->group(function () {
        Route::post('/logout', [PatientController::class, 'logout']);
        Route::get('/info', [PatientController::class, 'info']);
        Route::get('/rdvs', [PatientController::class, 'rdvs']);
        Route::get('/consultations/{id}', [ConsultationController::class, 'index']);
        Route::get('/consultation/{id}', [ConsultationController::class, 'show']);
    });
});
