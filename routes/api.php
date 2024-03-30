<?php

use App\Http\Controllers\AmoCrmAuthController;
use App\Http\Controllers\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::post('create-integration', [AmoCrmAuthController::class,'createIntegration']);

Route::get('get-leads', [LeadController::class, 'getAllLeads']);

Route::post('create-lead',[LeadController::class, 'createLead']);

Route::put('update-lead/{id}', [LeadController::class, 'updateLead']);

Route::post('create-leads-from-file',[LeadController::class, 'createLeadsFromFile']);
