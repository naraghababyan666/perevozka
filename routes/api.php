<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\CompanyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\ManagerController;
use \App\Http\Controllers\ReviewController;
use \App\Http\Controllers\GoodsOrdersController;
use \App\Http\Controllers\RegionController;

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

Route::post('/registration', [AuthController::class, 'registration']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('city/{cityName}', [RegionController::class, 'filterCity']);
Route::get('infoCity/{CityId}', [RegionController::class, 'getInfoCityById']);
//Route::get('company/{id}', [CompanyController::class, 'companyById'])->name('company-by-id');
Route::middleware('auth:sanctum')->group(function (){
    Route::prefix('manager')->group(function () {
        Route::post('create', [ManagerController::class, 'create'])->name('create-manager');
        Route::post('delete/{id}', [ManagerController::class, 'delete'])->name('delete-manager');
    });
    Route::prefix('review')->group(function () {
        Route::post('/create', [ReviewController::class, 'create'])->name('create-review');
    });
    Route::prefix('favorite')->group(function (){
        Route::get('list', [FavoritesController::class, 'getFavoritesList']);
        Route::post('add', [FavoritesController::class, 'addToFavorite']);
        Route::post('delete', [FavoritesController::class, 'deleteFromFavoriteList']);
    });
    Route::get("user", [FavoritesController::class, 'user']);
    Route::post('makeDisabled/{id}', [SiteController::class, 'makeOrderDisable']);


    Route::post('create-ride', [CompanyController::class, 'createRide'])->name('create-ride');
    Route::post('createOrder', [GoodsOrdersController::class, 'createOrder'])->name('create-order');
    Route::get('getOrders', [CompanyController::class, 'getOrders']);
    Route::get('getMyOrders', [CompanyController::class, 'getMyOrders']);
    Route::get('company/{company}', [CompanyController::class, 'companyByName'])->name('find-company');
});

Route::get('test', [CompanyController::class, 'testt']);

