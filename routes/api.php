<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [UserController::class, 'login']);



Route::post('article/createarticle',[ArticleController::class, 'createArticle']);
Route::get('article/',[ArticleController::class, 'allArticle']);
Route::get('aticle/detailarticle/{article_id}/',[ArticleController::class, 'getArticle']);
Route::post('aticle/updatearticle/{article_id}/',[ArticleController::class, 'updateArticle']);
Route::delete('article/deletearticle/{article_id}', [ArticleController::class, 'deleteArticle']);

Route::post('event/createevent',[EventController::class, 'createEvent']);
Route::get('event/',[EventController::class, 'allevent']);
Route::get('event/detailevent/{event_id}/',[EventController::class, 'getevent']);
Route::post('event/updateevent/{event_id}/',[EventController::class, 'updateevent']);
Route::delete('event/deleteevent/{event_id}', [EventController::class, 'deleteevent']);

Route::post('customer/createcustomer',[CustomerController::class, 'createcustomer']);
Route::get('customer/',[CustomerController::class, 'allcustomer']);
Route::get('customer/detailcustomer/{customer_id}/',[CustomerController::class, 'getcustomer']);
Route::post('customer/updatecustomer/{customer_id}/',[CustomerController::class, 'updatecustomer']);
Route::delete('customer/deletecustomer/{customer_id}', [CustomerController::class, 'deletecustomer']);


Route::post('transaction/createtransaction',[TransactionController::class, 'createTransaction']);
Route::get('transaction/',[TransactionController::class, 'allTransaction']);
Route::get('transaction/detailtransaction/{transaction_id}/',[TransactionController::class, 'getTransaction']);
Route::post('transaction/updatetransaction/{transaction_id}/',[TransactionController::class, 'updateTransaction']);
Route::delete('transaction/deletetransaction/{transaction_id}', [TransactionController::class, 'deletetransaction']);
