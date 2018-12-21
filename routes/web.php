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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', 'UserController@login');
Route::post('/check_phone', 'UserController@checkPhone');
Route::post('/forgot_password', 'UserController@forgotPassword');
Route::post('/register', 'UserController@register');
Route::post('/login_social', 'UserController@loginSocial');
Route::post('/check_token','UserController@checkToken');
Route::post('/update_phone','UserController@updatePhone');
Route::group(['middleware' => 'jwtauth'], function () {
    Route::post('/logout','UserController@logout');
    Route::get('/get-service', 'ServiceController@service');
    Route::get('/get-province', 'AddressController@getListProvince');
    Route::get('/get-district/{province_id}', 'AddressController@getListDistrict');
    Route::get('/get-ward/{district_id}', 'AddressController@getListWard');
    Route::get('/get-role-user','UserController@getRoleUser');
    Route::put('/upgrade-account/{id_users}','UserController@upgradeAccount');
    Route::put('/update-code-intro/{id_users}','UserController@putCodeIntro');
    Route::get('/get-right-condition', 'RightConditionController@getListRightCondition');
    Route::get('/get-voucher', 'VoucherController@getListVoucher');
    Route::post('/upload/image', 'UploadController@image');
    Route::post('/upload/video', 'UploadController@video');
    Route::put('/change-voucher/{id_users}','UserController@changeVoucher');
    Route::get('/history-voucher/{id_users}', 'VoucherController@getListHistoryVoucher');
    Route::get('/config','SettingController@getConfig');
    Route::post('/recruitment', 'RecruitmentController@recruitment');
    Route::put('/recruitment/{id}', 'RecruitmentController@updateRecruitment');
    Route::delete('/recruitment/{id}', 'RecruitmentController@deleteRecruitment');
    Route::get('/info-user/{id_users}', 'UserController@getInfoUser');
    Route::put('/update-user/{id_users}', 'UserController@updateUser');
    Route::post('/profile','ProfileController@createProfile');
    Route::get('/profile-detail/{id_profile}', 'ProfileController@getProfileDetail');
    Route::post('/profile-saved','ProfileSavedController@profileSaved');
    Route::delete('/profile-saved/{id_profile}','ProfileSavedController@destroy');
    Route::post('/profile-job','ProfileSavedController@profileSaved');
    Route::get('/profile-saved/{id}', 'ProfileSavedController@getProfileSaved');
    Route::delete('/profile-saved/{id}','ProfileSavedController@destroy');
    Route::delete('/profile-saved/{id_profile}','ProfileSavedController@destroy');
    Route::post('/save-job', 'SaveJobController@saveJob');
    Route::get('/saved-job/{id}', 'SaveJobController@getJobSaved');
    Route::delete('/saved-job/{id}','SaveJobController@destroy');
    Route::get('/overtime','OvertimeController@overTime');
    Route::get('/profile/{type}', 'ProfileController@getProfile');
    Route::get('/profile-coordinate', 'ProfileController@getListProfileCoordinate');
    Route::post('/call-now/{id}','RecruitmentController@callNow');
    Route::get('/profile-of-me/{id}', 'ProfileController@getProfileOfMe');
    Route::put('/profile/{id}', 'ProfileController@updateProfile');
    Route::delete('/profile/{id}','ProfileController@deleteProfile');
    Route::post('/comments','CommentController@comment');
    Route::get('/comments/{id}', 'CommentController@getComment');
});
Route::get('/test-upgrade-user/{id_users}','UserController@testUpgradeUser');
Route::get('/list-recruitment/{type}/{user_id?}', 'RecruitmentController@getListRecruitment');
Route::get('/detail-recruitment/{id}/{user_id?}','RecruitmentController@getDetailRecruitment');
Route::get('/level','LevelController@getListLevel');
Route::get('/experience','ExperienceController@getListExperience');
Route::get('/recruitment-coordinate/{user_id?}', 'RecruitmentController@getListRecruitmentCoordinate');
Route::get('/concentrate-recruitment', 'RecruitmentController@getConcentrateRecruitment');
Route::get('/notice-recruitment', 'RecruitmentController@noticeRecruitment');
Route::get('/notice-worker','ProfileController@noticeWorker');
Route::get('/notice','RecruitmentController@notice');
Route::get('/recruitment/{id}', 'RecruitmentController@getRecruitment');
Route::get('/concentrate-profile', 'ProfileController@getConcentrateProfile');
