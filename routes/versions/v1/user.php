<?php

use App\Http\Controllers\V1\UserControllers\AnalyticsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\UserControllers\LeadController;
use App\Http\Controllers\V1\UserControllers\ClientController;
use App\Http\Controllers\V1\UserControllers\CampaignController;
use App\Http\Controllers\V1\UserControllers\CompanyController;
use App\Http\Controllers\V1\UserControllers\ContactController;
use App\Http\Controllers\V1\UserControllers\SmtpServerController;
use App\Http\Controllers\V1\UserControllers\EmailTemplateController;
use App\Http\Controllers\V1\UserControllers\ImapController;
use App\Http\Controllers\V1\UserControllers\ListController;
use App\Http\Controllers\V1\UserControllers\SegmentController;

Route::middleware('dynamic.permission')->group(function () {

    // Route group for 'lead' controller
    Route::prefix('lead')->group(function () {
        $module = 'lead';

        Route::post('create', [LeadController::class, 'store'])->name("create_$module");
        Route::post('update', [LeadController::class, 'update'])->name("update_$module");
        Route::post('delete', [LeadController::class, 'delete'])->name("delete_$module");
        Route::get('get', [LeadController::class, 'get'])->name("get_$module");
        Route::post('attach_clients', [LeadController::class, 'attach_clients'])->name("attach_clients_$module");
    });

    // Route group for 'segment' controller
    Route::prefix('segment')->group(function () {
        $module = 'segment';

        Route::post('create', [SegmentController::class, 'store'])->name("create_$module");
        Route::post('update', [SegmentController::class, 'update'])->name("update_$module");
        Route::post('delete', [SegmentController::class, 'delete'])->name("delete_$module");
        Route::get('get', [SegmentController::class, 'get'])->name("get_$module");
        Route::post('attach_clients', [SegmentController::class, 'attach_clients'])->name("attach_clients_$module");
    });

    // Route group for 'client' controller
    Route::prefix('client')->group(function () {
        $module = 'client';

        Route::post('create', [ClientController::class, 'store'])->name("create_$module");
        Route::post('update', [ClientController::class, 'update'])->name("update_$module");
        Route::post('delete', [ClientController::class, 'delete'])->name("delete_$module");
        Route::get('get', [ClientController::class, 'get'])->name("get_$module");
        Route::get('get_all_ids', [ClientController::class, 'get_all_ids'])->name("get_all_ids_$module");
        Route::post('import', [ClientController::class, 'import'])->name("import_$module");
        Route::post('attach_leads', [ClientController::class, 'attach_leads'])->name("attach_leads_$module");
    });

    // Route group for 'list' controller
    Route::prefix('list')->group(function () {
        $module = 'list';

        Route::post('create', [ListController::class, 'store'])->name("create_$module");
        Route::post('update', [ListController::class, 'update'])->name("update_$module");
        Route::post('delete', [ListController::class, 'delete'])->name("delete_$module");
        Route::get('get', [ListController::class, 'get'])->name("get_$module");
        Route::get('get_contacts', [ListController::class, 'get_contacts'])->name("get_contacts_$module");
        Route::post('attach_contacts', [ListController::class, 'attach_contacts'])->name("attach_contacts_$module");
    });

    // Route group for 'contact' controller
    Route::prefix('contact')->group(function () {
        $module = 'contact';

        Route::post('create', [ContactController::class, 'store'])->name("create_$module");
        Route::post('update', [ContactController::class, 'update'])->name("update_$module");
        Route::post('delete', [ContactController::class, 'delete'])->name("delete_$module");
        Route::get('get', [ContactController::class, 'get'])->name("get_$module");
        Route::get('get_all_ids', [ContactController::class, 'get_all_ids'])->name("get_all_ids_$module");
        Route::post('import', [ContactController::class, 'import'])->name("import_$module");
        Route::post('attach_lists', [ContactController::class, 'attach_lists'])->name("attach_lists_$module");
    });

    // Route group for 'company' controller
    Route::prefix('company')->group(function () {
        $module = 'company';

        Route::post('create', [CompanyController::class, 'store'])->name("create_$module");
        Route::post('update', [CompanyController::class, 'update'])->name("update_$module");
        Route::post('delete', [CompanyController::class, 'delete'])->name("delete_$module");
        Route::get('get', [CompanyController::class, 'get'])->name("get_$module");
    });

    // Route group for 'smtp' controller
    Route::prefix('smtp')->group(function () {
        $module = 'smtp';

        Route::post('create', [SmtpServerController::class, 'store'])->name("create_$module");
        Route::post('update', [SmtpServerController::class, 'update'])->name("update_$module");
        Route::post('delete', [SmtpServerController::class, 'delete'])->name("delete_$module");
        Route::get('get', [SmtpServerController::class, 'get'])->name("get_$module");
    });

    // Route group for 'imap' controller
    Route::prefix('imap')->group(function () {
        $module = 'imap';

        Route::post('create', [ImapController::class, 'store'])->name("create_$module");
        Route::post('update', [ImapController::class, 'update'])->name("update_$module");
        Route::post('delete', [ImapController::class, 'delete'])->name("delete_$module");
        Route::get('get', [ImapController::class, 'get'])->name("get_$module");
        Route::get('get_hosts', [ImapController::class, 'get_hosts'])->name("get_hosts_$module");
        Route::get('get_emails', [ImapController::class, 'get_emails'])->name("get_emails_$module");
    });

    // Route group for 'email_template' controller
    Route::prefix('email_template')->group(function () {
        $module = 'email_template';

        Route::post('create', [EmailTemplateController::class, 'store'])->name("create_$module");
        Route::post('update', [EmailTemplateController::class, 'update'])->name("update_$module");
        Route::post('delete', [EmailTemplateController::class, 'delete'])->name("delete_$module");
        Route::get('get', [EmailTemplateController::class, 'get'])->name("get_$module");
    });

    // Route group for 'campaign' controller
    Route::prefix('campaign')->group(function () {
        $module = 'campaign';

        Route::post('create', [CampaignController::class, 'store'])->name("create_$module");
        Route::post('update', [CampaignController::class, 'update'])->name("update_$module");
        Route::post('delete', [CampaignController::class, 'delete'])->name("delete_$module");
        Route::get('get', [CampaignController::class, 'get'])->name("get_$module");
        Route::post('clone', [CampaignController::class, 'clone'])->name("clone_$module");
    });

    // Route group for 'analytics' controller
    Route::prefix('analytics')->group(function () {
        $module = 'analytics';

        Route::get('get', [AnalyticsController::class, 'get'])->name("get_$module");
        Route::get('get_lead', [AnalyticsController::class, 'get_lead_analytics'])->name("get_lead_$module");
        Route::get('get_segment', [AnalyticsController::class, 'get_segment_analytics'])->name("get_segment_$module");
        Route::get('get_list', [AnalyticsController::class, 'get_list_analytics'])->name("get_list_$module");
        Route::get('get_campaign', [AnalyticsController::class, 'get_campaign_analytics'])->name("get_campaign_$module");
    });
});
