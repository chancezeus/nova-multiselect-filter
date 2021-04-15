<?php

use Illuminate\Support\Facades\Route;

Route::get('/{resource}/multi-select-filter/options', 'FilterController')
    ->name('resource.multi-select-filter.options');
Route::get('/{resource}/lens/{lens}/multi-select-filter/options', 'LensFilterController')
    ->name('resource.lens.multi-select-filter.options');
