<?php

use App\Enums\EventType;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AutoPostController;
use App\Http\Controllers\BlockedTermController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PunishableCommandController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RendogController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\SpecialCommandController;
use App\Http\Controllers\StreamdayController;
use App\Http\Controllers\StreamdaySlotController;
use App\Models\Event;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Laravel\Nightwatch\Http\Middleware\Sample;

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

Route::name('login')->get('/login', [LoginController::class, 'login']);

Route::middleware('guest')->group(function () {
    Route::name('login.redirect')->get('/auth/twitch/redirect', [LoginController::class, 'redirect']);
    Route::name('login.callback')->get('/auth/twitch/callback', [LoginController::class, 'callback']);
});

Route::name('home')->get('/', HomeController::class);

Route::name('logout')->post('/logout', [LoginController::class, 'logout']);

Route::resource('events', EventController::class)->scoped([
    'event' => 'slug',
]);

Route::get('mcc', function () {
    $event = Event::query()->upcoming()->where('type', EventType::MCC)->orderBy('start')->first();

    return redirect()->route('events.show', ['event' => $event]);
});

Route::middleware(['auth:sanctum', 'check.disabled'])->group(function () {
    Route::name('passkeys.create')->get('/passkey/create', [LoginController::class, 'createPasskey']);
    Route::name('passkeys.verify')->post('/passkey/verify', [LoginController::class, 'verifyPasskey']);

    Route::name('bot')->get('moderators/bot', [BotController::class, 'bot']);
    Route::name('bot.restart')->post('moderators/bot/restart', [BotController::class, 'restart']);
    Route::name('bot.start')->post('moderators/bot/start', [BotController::class, 'start']);
    Route::name('bot.stop')->post('moderators/bot/stop', [BotController::class, 'stop']);
    Route::name('bot.settings.update')->put('moderators/bot/settings/update', [BotController::class, 'updateSettings']);

    Route::resource('moderators/auto-posts', AutoPostController::class);

    Route::resource('moderators/commands', CommandController::class);
    Route::resource('moderators/punishable-commands', PunishableCommandController::class);
    Route::resource('moderators/special-commands', SpecialCommandController::class);
    Route::resource('moderators/creators', CreatorController::class);
    Route::resource('moderators/streamdays', StreamdayController::class);
    Route::resource('moderators/streamdays.slots', StreamdaySlotController::class)->except(['show']);
    Route::name('commands.chat')->post('/moderators/commands/{command}/chat', [CommandController::class, 'chat']);

    Route::name('rules.order.update')->put('/moderators/rules/order/update', [RuleController::class, 'updateOrder']);
    Route::resource('moderators/rules', RuleController::class);

    Route::name('quote.chat')->post('/moderators/quotes/{quote}/chat', [QuoteController::class, 'chat']);

    Route::resource('moderators/quotes', QuoteController::class);

    Route::resource('moderators/blocked-terms', BlockedTermController::class)
        ->scoped(['blocked_term' => 'twitch_id'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::name('rendog.thankyou')->get('/rendog/thankyou', [RendogController::class, 'thankyou']);

    Route::name('token.show')->get('/moderators/token', [ApiTokenController::class, 'showToken']);
    Route::name('token.create')->post('/moderators/token/create', [ApiTokenController::class, 'createToken']);
});

Route::name('rules')->get('/rules', [RuleController::class, 'display']);
Route::name('streamday')->get('/streamday', [StreamdayController::class, 'show']);

Route::redirect('/l/brother', 'https://open.spotify.com/artist/42Ut8SaEEooPqrGubG1C3M');
Route::redirect('/l/playlist', 'https://open.spotify.com/playlist/5d4vmTdLm9XN1hVaLe0EY9?si=d0820125401e434e');

Broadcast::routes([
    'middleware' => ['auth:sanctum', Sample::rate(0)],
]);
