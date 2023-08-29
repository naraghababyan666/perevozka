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
        Route::post('createOrder', [GoodsOrdersController::class, 'createOrder'])->name('create-order'); //+
        Route::post('updateOrder/{id}', [GoodsOrdersController::class, 'updateOrder']); //+
        Route::get('getRides', [CompanyController::class, 'getRides'])->middleware('isSubscribed');
        Route::get('getMyOrders', [CompanyController::class, 'getMyOrders']); //+
        Route::delete('delete-order/{id}', [CompanyController::class, 'deleteOrder']);

    });


    Route::prefix('manager')->group(function () {
        Route::post('create', [ManagerController::class, 'create'])->name('create-manager');
        Route::post('delete/{id}', [ManagerController::class, 'delete'])->name('delete-manager');
        Route::get('/list', [ManagerController::class, 'getMyManagersList']);
    });

    Route::middleware('isDriver')->group(function (){
        Route::post('create-ride', [CompanyController::class, 'createRide'])->name('create-ride'); //+

        Route::post('update-ride/{id}', [CompanyController::class, 'updateRide']); //+
        Route::get('getOrders', [CompanyController::class, 'getOrders'])->middleware('isSubscribed'); //+
        Route::get('getMyRides', [CompanyController::class, 'getMyRides']); //+
        Route::delete('delete-ride/{id}', [CompanyController::class, 'deleteRide']);

    });
    Route::middleware('isAdmin')->group(function (){
        Route::get('confirm-review/{id}', [AdminController::class, 'confirmReview']);
        Route::get('decline-review/{id}', [AdminController::class, 'declineReview']);
        Route::get('review-list', [AdminController::class, 'reviewList']);
    });

    Route::get('/companies', [CompanyController::class, 'companyList'])->middleware('isSubscribed');
    Route::post('subscribe',[SubscriptionsController::class, 'subscribe']);
    Route::post('profile-update', [CompanyController::class, 'updateProfile']);
    Route::post('company-delete', [CompanyController::class, 'deleteCompany']);
    Route::post('change-subscribe-message-status', [CompanyController::class, 'changeSubscribeMessageStatus']);

//    Route::post('payment-api', [CompanyController::class, 'paymentApi']);
//    Route::match(['GET', 'POST'], '/payments/callback', [SubscriptionsController::class, 'callback'])->name('payment.callback');
    Route::post('payments/create', [SubscriptionsController::class, 'create']);
    Route::get('payments/list', [SubscriptionsController::class, 'getMyOrders']);
    Route::prefix('review')->group(function () {
        Route::post('/create', [ReviewController::class, 'create'])->name('create-review');
    });
    Route::prefix('favorite')->group(function (){
        Route::get('list', [FavoritesController::class, 'getFavoritesList']);
        Route::post('add-goods', [FavoritesController::class, 'addToFavoriteGoods']);
        Route::post('add-ride', [FavoritesController::class, 'addToFavoriteRide']);
        Route::post('add-company', [FavoritesController::class, 'addToFavoriteCompany']);
        Route::post('delete', [FavoritesController::class, 'deleteFromFavoriteList']);
    });
    Route::get("user", [FavoritesController::class, 'user']);
    Route::post('makeDisabled/{id}', [SiteController::class, 'makeOrderDisable']);
    Route::get('company-reviews/{id}', [CompanyController::class, 'companyReviews']);

    Route::post('/change-isPaymentWorking', [CompanyController::class, 'changeIsPaymentWorking']);
});



