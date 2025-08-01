<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

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



Route::get('/messages', [MessageController::class, 'index']);
Route::post('/message', [MessageController::class, 'store']);
Route::get('/message/{id}', [MessageController::class, 'show']);
Route::post('/message/{id}/retry', [MessageController::class, 'reprocess']);