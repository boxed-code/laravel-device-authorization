<?php

$authMiddleware = config()->get('device.routing.middleware');

Route::middleware($authMiddleware)->group(function () {
    $authController = config()->get('device.routing.controller');

    Route::get('/auth/device/error', $authController.'@showError')->name('device.error');
    Route::get('/auth/device/challenge', $authController.'@challenge')->name('device.challenge')->middleware('throttle:10,1');
    Route::get('/auth/device/challenged', $authController.'@showChallenged')->name('device.challenged');
    Route::get('/auth/device/verify/{token}', $authController.'@verifyAndAuthorize')->name('device.verify')->middleware('throttle:10,1');
});