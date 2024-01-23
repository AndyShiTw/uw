<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/user/register', [UserController::class, 'registerUser']);
Route::post('/user/login', [UserController::class, 'login']);


Route::get('/post/list', [PostController::class, 'getPostList']);
Route::get('/post/custom/list', [PostController::class, 'getPostCustomList']);
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('/post/create', [PostController::class, 'createPost']);
    Route::post('/post/update', [PostController::class, 'updatePost']);
    Route::post('/post/delete', [PostController::class, 'deletePost']);
});
