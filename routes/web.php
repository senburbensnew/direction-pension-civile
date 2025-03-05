<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/liens-utiles', function () {
    $links = [
        [
            'name' => "Ministère de l'Economie et des Finances",
            'abbr' => "MEF",
            'link' => "https://mef.gouv.ht/"
        ],
        [
            'name' => "Direction Générale du Budget",
            'abbr' => "DGB",
            'link' => "https://budget.gouv.ht/"
        ],
        [
            'name' => "Administration Générale des Douanes",
            'abbr' => "AGD",
            'link' => "https://www.douane.gouv.ht/"
        ],
        [
            'name' => "Banque de la République d’Haïti",
            'abbr' => "BRH",
            'link' => "https://www.brh.ht/"
        ],
        [
            'name' => "Bureau de Monétisation des Programmes d'Aide au Développement",
            'abbr' => "BMPAD",
            'link' => "https://bmpad.gouv.ht/"
        ],
        [
            'name' => "Direction Générale des Impôts",
            'abbr' => "DGI",
            'link' => "https://dgi.gouv.ht/"
        ],
        [
            'name' => "Inspection Générale des Finances",
            'abbr' => "IGF",
            'link' => "https://igf.gouv.ht/"
        ],
        [
            'name' => "Institut Haïtien de Statistique et d'Informatique",
            'abbr' => "IHSI",
            'link' => "https://ihsi.gouv.ht/"
        ],
        [
            'name' => "Office d'Assurance Véhicules Contre Tiers",
            'abbr' => "OAVCT",
            'link' => "https://oavct.gouv.ht/"
        ],
        [
            'name' => "Société Nationale des Parcs Industriels",
            'abbr' => "SONAPI",
            'link' => "https://sonapi.gouv.ht/"
        ],
        [
            'name' => "Banque Nationale de Crédit",
            'abbr' => "BNC",
            'link' => "https://www.bnconline.com/"
        ],
    ];

    return view('liens-utiles.index', compact('links'));
})->name('liens-utiles');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
