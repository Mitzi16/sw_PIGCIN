<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleReviewController;
use App\Http\Controllers\ArticleTypeController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\CriterionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\KnowledgeAreaController;
use App\Http\Controllers\KnowledgeSubAreaController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\TemplateDownloadController;
use App\Http\Controllers\ArticleGraphController;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

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

Route::get('/', [WelcomeController::class, 'welcome'])->name('welcome');
Route::get('/comite', [WelcomeController::class, 'committee'])->name('welcome.committee');
Route::get('/lugar', [WelcomeController::class, 'place'])->name('welcome.place');
Route::get('/programa', [WelcomeController::class, 'program'])->name('welcome.program');
Route::get('/welcome/article/show/{code?}', [WelcomeController::class, 'showArticle'])->name('welcome.show.article');
Route::get('/welcome/article/search/{code?}', action: [WelcomeController::class, 'searchArticle'])->name('welcome.search.article');
Route::get('/welcome/article/search-live', [WelcomeController::class, 'searchArticleLive'])->name('welcome.search.article.live');
Route::get('/download/{filename}', [TemplateDownloadController::class, 'download'])
    ->where('filename', '.*')
    ->name('download');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Route::get('/dashboard', function () {
    //     return Inertia::render('Dashboard', ['users' => User::all(), 'modulos' => Module::all()]);
    // })->name('dashboard');

    Route::get('/graficas/article/institution', [ArticleGraphController::class, 'institution'])->name('article.graphs.institution');
    Route::get('/graficas/article/articleType', [ArticleGraphController::class, 'articleType'])->name('article.graphs.articleType');
    Route::get('/graficas/article/status', [ArticleGraphController::class, 'status'])->name('article.graphs.status');
    Route::get('/graficas/article/date', [ArticleGraphController::class, 'date'])->name('article.graphs.date');
    Route::get('/graficas/article/user', [ArticleGraphController::class, 'articlesByRole'])->name('article.graphs.user');
    Route::get('/graficas/article/program', [ArticleGraphController::class, 'articlesByProgram'])->name('article.graphs.program');
    Route::get('/graficas/article/area', [ArticleGraphController::class, 'articlesByArea'])->name('article.graphs.area');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile-photo', [ProfileController::class, 'destroyPhoto'])->name('profile.destroyPhoto');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Seguridad
    Route::resource('module', ModuleController::class)->parameters(['module' => 'module']);
    Route::resource('permission', PermissionController::class)->names('permission');
    Route::resource('role', RoleController::class)->parameters(['role' => 'role']);
    Route::resource('user', UserController::class)->parameters(['user' => 'user']);

    // Catalogos
    Route::resource('call', CallController::class)->parameters(['call' => 'call']);
    Route::resource('knowledgeArea', KnowledgeAreaController::class)->parameters(['knowledgeArea' => 'knowledgeArea']);
    Route::resource('criterion', CriterionController::class)->parameters(['criterion' => 'criterion']);
    Route::resource('knowledgeSubArea', KnowledgeSubAreaController::class)->parameters(['knowledgeSubArea' => 'knowledgeSubArea']);
    Route::resource('institution', InstitutionController::class)->parameters(['institution' => 'institution']);

    //
    Route::resource('article', ArticleController::class)->parameters(['article' => 'article']);
    Route::resource('articleTypes', ArticleTypeController::class)->names('articleType')->except(['show']);
    Route::get('articles/qualify', [ArticleController::class, 'index'])->middleware(['auth'])->name('article.qualify');
    Route::get('articles/diffuse', [ArticleController::class, 'index'])->middleware(['auth'])->name('article.diffuse'); 

    //Exports to Excel 

    Route::prefix('admin')->group(function () {
        Route::get('criterion/export', [CriterionController::class, 'export'])->name('criterion.export');
        Route::get('articles/export', [ArticleController::class, 'export'])->name('article.export');
        Route::get('calls/export', [CallController::class, 'export'])->name('call.export');
        Route::get('articleTypes/export', [ArticleTypeController::class, 'export'])->name('articleType.export');
        Route::get('institution/export', [InstitutionController::class, 'export'])->name('institution.export');
        Route::get('knowledgeArea/export', [KnowledgeAreaController::class, 'export'])->name('knowledgeArea.export');
        Route::get('knowledgeSubArea/export', [KnowledgeSubAreaController::class, 'export'])->name('knowledgeSubArea.export');

    });


    Route::resource('articleReview', ArticleReviewController::class)->parameters(['articleReview' => 'articleReview']);
    Route::resource('paymentVoucher', PaymentVoucherController::class)->parameters(['paymentVoucher' => 'paymentVoucher']);

    // apis
    Route::delete('destroy-author/{author}', [AuthorController::class, 'destroy'])->name('author.destroy');
    Route::patch('evaluate/{article}', [ArticleController::class, 'evaluate'])->name('article.evaluate');
    Route::get('paymentVoucher/create/{article}', [PaymentVoucherController::class, 'create'])->name('paymentVoucher.create');
    Route::get('paymentVoucher/show-validate/{paymentVoucher}', [PaymentVoucherController::class, 'showValidate'])->name('paymentVoucher.showValidate');
    Route::put('paymentVoucher/handle-validate/{paymentVoucher}', [PaymentVoucherController::class, 'handleValidate'])->name('paymentVoucher.handleValidate');


    Route::get('/sign-pdf/{article}', [ArticleController::class, 'signPdf'])->name('article.signPdf');
});

require __DIR__ . '/auth.php';
