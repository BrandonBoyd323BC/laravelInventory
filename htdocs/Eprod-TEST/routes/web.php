<?php

use Illuminate\Support\Facades\Route;
use App\Models\Lists;
use App\Models\Testing;
use App\Models\Singular;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;

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

// Route::get('/', function () {
//     return view('welcome');
// });

//All Listings
Route::get('/', function () {
    return view('test', [
        'heading' => 'Latest List',
        'subHeader' => 'Under the Header',
        'items' => Testing::all()
    ]);
});

// Single Listing
Route::get('/item-view/{id}', function($id) {
    return view('item-view', [
        'heading' => 'This Item Number:  '.$id,
        'list' => Testing::find($id)
    ]);
});


//Alternative Page to Test

Route::get('/testing', function() {
    return view('alternative', [
        'heading' => 'Do the databases have to be pluralized?',
        'answer' => "I don't understand how or why but looks like the answer is yes",
        'testers' => Singular::all()
    ]);
});
