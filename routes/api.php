<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Items\ItemController;
use App\Http\Controllers\Machines\MachineController;
use App\Http\Controllers\Items\ProductionItemMasterController;

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

// Item search API endpoint
Route::get('/items/search', [ItemController::class, 'getAjaxItemSearchBarList']);

// Machines API endpoint
Route::get('/machines', [MachineController::class, 'getMachines']);
//REAL API Endpoint

Route::get('/getreal', [ProductionItemMasterController::class, 'getAjaxReal']);



