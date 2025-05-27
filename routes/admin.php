<?php

use App\Http\Middleware\HasAccessAdmin;

Route::group([
    'namespace' => 'App\Http\Controllers\Admin',
    'prefix' => config('admin.prefix'),
    'middleware' => ['auth', 'verified', HasAccessAdmin::class],
    'as' => 'admin.',
], function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::resource('user', 'UserController');
    Route::resource('role', 'RoleController');
    Route::resource('permission', 'PermissionController');
    Route::resource('media', 'MediaController');
    Route::resource('menu', 'MenuController')->except([
        'show',
    ]);
    Route::resource('menu.item', 'MenuItemController')->except([
        'show',
    ]);
    Route::group([
        'prefix' => 'category',
        'as' => 'category.',
    ], function () {
        Route::resource('type', 'CategoryTypeController')->except([
            'show',
        ]);
        Route::resource('type.item', 'CategoryController')->except([
            'show',
        ]);
    });
    Route::resource('comment', 'CommentController');
    Route::resource('thread', 'ThreadController');
    Route::resource('attribute', 'AttributeController');
    Route::resource('reaction', 'ReactionController');
    Route::get('edit-account-info', 'UserController@accountInfo')->name('account.info');
    Route::post('edit-account-info', 'UserController@accountInfoStore')->name('account.info.store');
    Route::post('change-password', 'UserController@changePasswordStore')->name('account.password.store');
    Route::get('project-data', 'ProjectDataController@index')->name('view.project.data');
    Route::get('project-data/{project}', 'ProjectDataController@show')->name('view.project.data.show');
    Route::get('project-data-count', 'ProjectDataController@count')->name('project.data.count');
    Route::post('save-column-order', 'ProjectDataController@saveColumnOrder')
    ->name('save.column.order');

    Route::resource('activitylog', 'ActivityLogController')->except([
        'create',
        'store',
        'edit',
        'update',
    ]);

    //Demo
    Route::group([
        'prefix' => 'demo',
        'as' => 'demo.',
    ], function () {
        Route::resource('forms', 'DemoFormsController')->except([
            'show',
            'edit',
            'update',
        ]);
    });
});
