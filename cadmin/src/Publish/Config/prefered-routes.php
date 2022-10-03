<?php

return [
    'getThumb' => 'Cactuar\Admin\Http\Controllers\ThumbController@getIndex',
    
    //login + logout
    'getLogin' => 'Cactuar\Admin\Http\Controllers\GuestController@getLogin',
    'postLogin' => 'Cactuar\Admin\Http\Controllers\GuestController@postLogin',
    'getLogout' => 'Cactuar\Admin\Http\Controllers\GuestController@getLogout',
    'getForgot' => 'Cactuar\Admin\Http\Controllers\GuestController@getForgot',
    'postForgot' => 'Cactuar\Admin\Http\Controllers\GuestController@postForgot',
    'getResetPassword' => 'Cactuar\Admin\Http\Controllers\GuestController@getResetPassword',
    
    //profile
    'getProfile' => 'Cactuar\Admin\Http\Controllers\ProfileController@getIndex',
    'postProfile' => 'Cactuar\Admin\Http\Controllers\ProfileController@postIndex',
    'getProfileChpass' => 'Cactuar\Admin\Http\Controllers\ProfileController@getChpass',
    'postProfileChpass' => 'Cactuar\Admin\Http\Controllers\ProfileController@postChpass',
    
    //admin
    'postAdminFilter' => 'Cactuar\Admin\Http\Controllers\AdminController@postFilter',
    
    //validate
    'postValidateUnique' => 'Cactuar\Admin\Http\Controllers\ValidateController@postUnique',
	
	//widget editor
	//'postSectionEditorGallery' => 'Cactuar\Admin\Http\Controllers\SectionEditorController@postGallery',
	//'postSectionEditorForm' => 'Cactuar\Admin\Http\Controllers\SectionEditorController@postEditor',
];