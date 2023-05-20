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
use \App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\AdminController;

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
Route::get('company/{id}', [CompanyController::class, 'companyById'])->name('find-company');

Route::middleware('auth:sanctum')->group(function (){
    Route::middleware('isOwner')->group(function (){
        Route::post('createOrder', [GoodsOrdersController::class, 'createOrder'])->name('create-order');
        Route::get('getRides', [CompanyController::class, 'getRides'])->middleware('isSubscribed');
        Route::get('getMyOrders', [CompanyController::class, 'getMyOrders']);

    });
    Route::middleware('isDriver')->group(function (){
        Route::prefix('manager')->group(function () {
            Route::post('create', [ManagerController::class, 'create'])->name('create-manager');
            Route::post('delete/{id}', [ManagerController::class, 'delete'])->name('delete-manager');
        });
        Route::post('create-ride', [CompanyController::class, 'createRide'])->name('create-ride');
        Route::get('getOrders', [CompanyController::class, 'getOrders'])->middleware('isSubscribed');
        Route::get('getMyRides', [CompanyController::class, 'getMyRides']);
        Route::delete('delete-ride/{id}', [CompanyController::class, 'deleteRide']);

    });
    Route::middleware('isAdmin')->group(function (){
        Route::get('confirm-review/{id}', [AdminController::class, 'confirmReview']);
        Route::get('decline-review/{id}', [AdminController::class, 'declineReview']);
        Route::get('review-list', [AdminController::class, 'reviewList']);
    });

    Route::post('/companies', [CompanyController::class, 'companyList'])->middleware('isSubscribed');
    Route::post('subscribe',[SubscriptionsController::class, 'subscribe']);

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
    Route::get('company-reviews/{id}', [CompanyController::class, 'companyReviews']);
});



