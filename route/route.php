<?php
Route::miss(function () {
    echo '<h1 style="margin-top: 10vh; text-align: center; color: #ccc;">404 页面未找到</h1>';
});

Route::group('', function () {
    // 后台首页
    Route::get('/', 'admin/Index/index');
    Route::get('/admin/index/index', 'admin/Index/index');
    Route::get('/admin/index/welcome', 'admin/Index/welcome');
    // 日志页面
    Route::get('/admin/log/index', 'admin/Log/index');
    // 上传图片
    Route::post('/admin/index/uploadimg', 'admin/Index/uploadimg');
    // 用户管理
    Route::resource('/admin/user', 'admin/Admin');
    // 用户修改密码
    Route::get('/admin/user/changepwd', 'admin/Admin/changePwd');
    Route::post('/admin/user/changepwd', 'admin/Admin/changePwd');
})->middleware('CheckLogin');

// 登录
Route::get('/admin/login/index', 'admin/Login/index');
Route::post('/admin/login/login', 'admin/Login/login');
Route::get('/admin/login/logout', 'admin/Login/logout');

Route::controller('/test', 'index/Index');