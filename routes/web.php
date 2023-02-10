<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware("guest")->group(function () {
    Route::name("welcome")->get("/", [WelcomeController::class, "welcome"]);

    Route::name("login.redirect")->get("/auth/twitch/redirect", [LoginController::class, "redirect"]);
    Route::name("login.callback")->get("/auth/twitch/callback", [LoginController::class, "callback"]);
});

Route::name("logout")->post("/logout", [LoginController::class, "logout"]);

Route::middleware(["auth:sanctum", "check.disabled"])->group(function () {
    Route::resource("moderators/commands", CommandController::class);

    Route::name("rules.order.update")->put("/moderators/rules/order/update", [RuleController::class, "updateOrder"]);
    Route::resource("moderators/rules", RuleController::class);
});

Route::name("rules")->get("/rules", [RuleController::class, "display"]);
