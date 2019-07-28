<?php

Route::get('/auth/device/error', 'Auth\DeviceAuthController@showError')->name('device.error');
Route::get('/auth/device/challenge', 'Auth\DeviceAuthController@challenge')->name('device.challenge')->middleware('throttle:10,1');
Route::get('/auth/device/challenged', 'Auth\DeviceAuthController@showChallenged')->name('device.challenged');
Route::get('/auth/device/verify/{token}', 'Auth\DeviceAuthController@verify')->name('device.verify')->middleware('throttle:10,1');