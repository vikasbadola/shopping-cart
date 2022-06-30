<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::post('/auth/register',[AuthController::class, 'register'])->name('register.api');
    Route::post('/auth/login',[AuthController::class, 'login'])->name('login.api');
});

//Route::middleware('auth:api')->group(function () {
//    Route::get('/products',[ProductController::class, 'index'])->name('products.api');
//    Route::post('/products',[ProductController::class, 'store'])->name('products.api');
//    Route::post('/products/{id}',[ProductController::class, 'show'])->name('products.api');
//});

Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class);
});
Route::apiResource('cart', CartController::class);