<?php

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

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware'=>['auth','Share','Permission']], function () {
    Route::get('/', 'HomeController@index');
    Route::get('home', 'HomeController@index')->name('home');

    Route::resource('user', 'User\UserController', ['only'=>['index','create','store','show','edit','update']]);
    Route::post('user/search', 'User\UserController@search')->name('user.search');

    Route::resource('admin', 'Admin\AdminController', ['only'=>['index','create','store','edit','update','destroy']]);
    Route::post('admin/search', 'Admin\AdminController@search')->name('admin.search');
    Route::post('admin/{admin}/save', 'Admin\AdminController@save')->name('admin.save');

    Route::resource('admin_role', 'Admin\AdminRoleController', ['only'=>['index','create','store','edit','update','destroy']]);
    Route::post('admin_role/search', 'Admin\AdminRoleController@search')->name('admin_role.search');
    Route::post('admin_role/{admin}/save', 'Admin\AdminRoleController@save')->name('admin_role.save');

    Route::resource('admin_nav', 'Admin\AdminNavController', ['only'=>['index','create','store','edit','update','destroy']]);
    Route::post('admin_nav/search', 'Admin\AdminNavController@search')->name('admin_nav.search');
    Route::post('admin_nav/{admin_nav}/save', 'Admin\AdminNavController@save')->name('admin_nav.save');
});
