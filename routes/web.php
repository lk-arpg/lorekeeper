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

Route::get('/', 'HomeController@getIndex')->name('home');
Route::get('login', 'Auth\LoginController@getNewReply');

// Logging in with Aliases
Route::get('/login/redirect/{driver}', 'Auth\LoginController@getAuthRedirect');
Route::get('/login/callback/{driver}', 'Auth\LoginController@getAuthCallback');

// Registering with Aliases
Route::get('register/{driver}', 'Auth\RegisterController@getRegisterWithDriver');
Route::post('register/{driver}', 'Auth\RegisterController@postRegisterWithDriver');

// BROWSE
require_once __DIR__.'/lorekeeper/browse.php';

Route::feeds('feeds');

/**************************************************************************************************
    Routes that require login
**************************************************************************************************/
Route::group(['middleware' => ['auth', 'verified', 'post.throttle']], function () {
    // LINK OFF-SITE ACCOUNT
    Route::get('/link', 'HomeController@getLink')->name('link');

    Route::get('/auth/redirect/{driver}', 'HomeController@getAuthRedirect');
    Route::get('/auth/callback/{driver}', 'HomeController@getAuthCallback');

    // EMAIL
    Route::get('/email', 'HomeController@getEmail')->name('email');
    Route::post('/email', 'HomeController@postEmail');

    // SET BIRTHDATE
    Route::get('/birthday', 'HomeController@getBirthday')->name('birthday');
    Route::post('/birthday', 'HomeController@postBirthday');

    Route::get('/blocked', 'HomeController@getBirthdayBlocked')->name('blocked');

    // BANNED
    Route::get('banned', 'Users\AccountController@getBanned');

    // DEACTIVATED
    Route::get('deactivated', 'Users\AccountController@getDeactivated');
    Route::get('reactivate', 'Users\AccountController@getReactivateConfirmation');
    Route::post('reactivate', 'Users\AccountController@postReactivate');

    /**********************************************************************************************
        Routes that require having a linked account (also includes blocked routes when banned)
    **********************************************************************************************/
    Route::group(['middleware' => ['alias']], function () {
        require_once __DIR__.'/lorekeeper/members.php';

        /**********************************************************************************************
            Admin panel routes
        **********************************************************************************************/
        Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['staff']], function () {
            require_once __DIR__.'/lorekeeper/admin.php';
        });
    });
});
