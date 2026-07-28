<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\AuthController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::get('/products', [ProductController::class, 'index']);
  Route::post('/sales', [SaleController::class, 'store']);
  Route::get('/sales', [SaleController::class, 'index']);
  Route::get('/sales/{id}', [SaleController::class, 'show']);

  Route::get('/test', function () {
    return response()->json([
      'app' => config('app.name'),
      'env' => app()->environment(),
      'version' => app()->version(),
      'status' => 'ok',
    ]);
  });
});
