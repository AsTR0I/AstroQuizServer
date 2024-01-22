<?php

use App\Http\Controllers\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
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

Route::post('allUsers',[UserController::class,'allUsers']);

Route::group(['middleware'=>'api','prefix'=>'auth'],function(){
    Route::post('registration',[UserController::class,'registration']);
    Route::post('login',[UserController::class,'login']);
});
Route::group(['middleware'=>'api'],function(){
    Route::post('quiz/create',[QuizController::class,'store']);
    Route::post('quiz/get/{quiz_}',[QuizController::class,'getQuiz']);
});




