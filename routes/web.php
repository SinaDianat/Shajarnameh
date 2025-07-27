<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FamilyTreeRequestController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth.jwt')->group(function () {
    Route::get('/user/panel', [UserController::class, 'dashboard'])->name('user.panel');
    Route::post('/user/profile', [UserController::class, 'updateProfile'])->name('user.update_profile');

    Route::get('/family-tree-requests/create', [FamilyTreeRequestController::class, 'create'])->name('family_tree_requests.create');
    Route::post('/family-tree-requests', [FamilyTreeRequestController::class, 'store'])->name('family_tree_requests.store');
    Route::get('/admin/family-tree-requests', [FamilyTreeRequestController::class, 'index'])->name('admin.family_tree_requests');
    Route::post('/admin/family-tree-requests/{id}/approve', [FamilyTreeRequestController::class, 'approve'])->name('family_tree_requests.approve');
    Route::post('/admin/family-tree-requests/{id}/reject', [FamilyTreeRequestController::class, 'reject'])->name('family_tree_requests.reject');

    Route::get('/admin/panel', [AdminController::class, 'index'])->name('admin.panel');
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'edit'])->name('admin.edit_user');
    Route::post('/admin/users/{id}', [AdminController::class, 'update'])->name('admin.update_user');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroy'])->name('admin.delete_user');
    Route::post('/admin/users/{id}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('admin.toggle_admin');
    Route::get('/admin/family-tree-requests/{id}/show', [AdminController::class, 'showFamilyTree'])->name('admin.show_family_tree');
    Route::get('/admin/family-tree-requests/{id}/grant-access', [AdminController::class, 'grantAccessForm'])->name('family_tree_requests.grant_access_form');
    Route::post('/admin/family-tree-requests/{id}/grant-access', [AdminController::class, 'grantAccess'])->name('family_tree_requests.grant_access');
    Route::get('/admin/people/{id}/edit', [AdminController::class, 'editPerson'])->name('admin.edit_person');
    Route::post('/admin/people/{id}', [AdminController::class, 'updatePerson'])->name('admin.update_person');
    Route::delete('/admin/people/{id}', [AdminController::class, 'destroyPerson'])->name('admin.delete_person');
    Route::get('/admin/people/{id}/add-parent/{type}', [AdminController::class, 'addParent'])->name('admin.add_parent');
    Route::post('/admin/people/{id}/add-parent/{type}', [AdminController::class, 'storeParent'])->name('admin.store_parent');
    Route::get('/admin/people/{id}/add-child', [AdminController::class, 'addChild'])->name('admin.add_child');
    Route::post('/admin/people/{id}/add-child', [AdminController::class, 'storeChild'])->name('admin.store_child');
    Route::get('/admin/people/{id}/add-partner', [AdminController::class, 'addPartner'])->name('admin.add_partner');
    Route::post('/admin/people/{id}/add-partner', [AdminController::class, 'storePartner'])->name('admin.store_partner');
    Route::delete('/admin/family-tree-requests/{id}', [AdminController::class, 'destroyFamilyTree'])->name('admin.delete_family_tree');

   Route::get('/family-tree', [PeopleController::class, 'index'])->name('people.family_tree');
    Route::get('/people/create', [PeopleController::class, 'create'])->name('people.create');
    Route::post('/people', [PeopleController::class, 'store'])->name('people.store');
    Route::get('/people/{id}', [PeopleController::class, 'show'])->name('people.show');
    Route::get('/people/{id}/edit', [PeopleController::class, 'edit'])->name('people.edit');
    Route::post('/people/{id}', [PeopleController::class, 'update'])->name('people.update');
    Route::get('/people/{id}/add-parent/{type}', [PeopleController::class, 'addParent'])->name('people.add-parent');
    Route::post('/people/{id}/add-parent/{type}', [PeopleController::class, 'storeParent'])->name('people.store-parent');
    Route::get('/people/{id}/add-child', [PeopleController::class, 'addChild'])->name('people.add-child');
    Route::post('/people/{id}/add-child', [PeopleController::class, 'storeChild'])->name('people.store-child');
    Route::get('/people/{id}/add-partner', [PeopleController::class, 'addPartner'])->name('people.add-partner');
    Route::post('/people/{id}/add-partner', [PeopleController::class, 'storePartner'])->name('people.store-partner');
    Route::delete('/people/{id}', [PeopleController::class, 'destroy'])->name('people.destroy');
});

Route::get('/api/cities', [CityController::class, 'search'])->name('api.cities.search');

Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::get('/verify-email', function () { return view('auth.verify-email'); })->name('verify-email');
Route::get('/forgot-password', function () { return view('auth.forgot-password'); })->name('forgot-password');
Route::get('/verify-reset-code', function () { return view('auth.verify-reset-code'); })->name('verify-reset-code');
Route::get('/reset-password', function () { return view('auth.reset-password'); })->name('reset-password');
Route::get('/resend-verification', function () { return view('auth.resend-verification'); })->name('resend-verification');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth.jwt');
Route::post('/resend-verification', [AuthController::class, 'resendVerificationCode']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


Route::get('/showtree', function () { return view('tree'); });
