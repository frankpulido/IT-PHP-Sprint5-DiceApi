<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayController;

Route::get('/', function () {
    return view('welcome');
});

?>