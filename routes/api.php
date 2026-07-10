<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\BarberController;

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

// Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleCallback']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);
// Route::post('/auth/refresh', [AuthController::class, 'refresh']); // if using refresh tokens

// Appointment Routes
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::get('/appointments/available', [AppointmentController::class, 'available']);
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::get('/appointments/{uuid}', [AppointmentController::class, 'show']);
Route::delete('/appointments/{uuid}', [AppointmentController::class, 'destroy']);
Route::patch('/appointments/{uuid}/status', [AppointmentController::class, 'updateStatus']);

// Admin Appointment Routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/appointments', [AppointmentController::class, 'adminIndex']);
    Route::get('/admin/appointments/today', [AppointmentController::class, 'today']);
    Route::get('/admin/appointments/week', [AppointmentController::class, 'week']);
});

// Service Routes
Route::get('/services', [ServiceController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/services', [ServiceController::class, 'store']);
    Route::put('/admin/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/admin/services/{id}', [ServiceController::class, 'destroy']);
});

// Promotion Routes
Route::get('/promotions', [PromotionController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/promotions', [PromotionController::class, 'store']);
    Route::put('/admin/promotions/{id}', [PromotionController::class, 'update']);
    Route::delete('/admin/promotions/{id}', [PromotionController::class, 'destroy']);
    Route::post('/admin/push/send', [PushController::class, 'send']);
});

// Barber Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/barbers', [BarberController::class, 'index']);
    Route::post('/admin/barbers', [BarberController::class, 'store']);
    Route::put('/admin/barbers/{id}', [BarberController::class, 'update']);
    Route::get('/admin/barbers/{id}/schedule', [BarberController::class, 'schedule']);
    Route::put('/admin/barbers/{id}/schedule', [BarberController::class, 'updateSchedule']);
});