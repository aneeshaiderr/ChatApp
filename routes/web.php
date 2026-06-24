<?php
use App\Http\Controllers\chatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[chatController::class,'index'])->name('user.login');
Route::post('/broadcast',[chatController::class,'broadcastChat'])->name('broadcast.chat');
Route::get('/chat',[chatController::class,'notFound'])->name('no.chat');
Route::post('/chat',[chatController::class,'chat'])->name('chat');
