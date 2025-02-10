<?php

use App\Http\Controllers\Api\PunishController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::webhooks('/webhooks/twitch', 'twitch');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/punishable-commands', [PunishController::class, 'punishableCommands']);

    Route::post('/punish', [PunishController::class, 'punish']);

    Route::get('/is-authenticated', function (Request $request) {
        return response()->json($request->user() !== null);
    });

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});
