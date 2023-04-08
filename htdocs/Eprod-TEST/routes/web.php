<?php

use App\Http\Controllers\ListingController;
use App\Models\Links;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;


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

Route::get('/', [ListingController::class, 'index']);

Route::get('/listings/create', [ListingController::class, 'create']);

Route::post('/listings', [ListingController::class, 'store']);



//Single listing at the end messes up the url.
Route::get('/listings/{listing}', [ListingController::class, 'show']);

Route::get('/links', function () {
    return view('links', [
        'heading' => 'Links to Sites',
        'links' => Links::getAllLinks()
    ]);
});

Route::get('/hello', function () {
    return response('<h2>test</h2>', 200)
        ->header('Content-Type', 'text/plain')
        ->header('Custom-Header', 'Some Value');
});

Route::get('/posts/{id}', function ($id) {
    return response('Post ' . $id);
})->where('id', '[0-9]+');

Route::get('/search', function (Request $request) {
    return $request->name . ' ' . $request->city;
});

Route::get('/welcome', function () {
    return view('welcome');
});
