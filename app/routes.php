<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('as' => 'index', 'uses' => 'MainController@index'));
Route::get('/send-sms', array('as' => 'send-sms', 'uses' => 'MainController@send_sms'));
Route::get('/cron-job', array('as' => 'cron-job', 'uses' => 'MainController@cron_job'));
Route::get('/outreach', array('as' => 'outreach', 'uses' => 'MainController@outreach'));

Route::get('sign-in', array('as' => 'sign-in', 'uses' => 'AuthController@showAuthForm'));
Route::post('sign-in', array('uses' => 'AuthController@postAuthForm'));
Route::get('sign-out', array('as' => 'sign-out', 'uses' => 'AuthController@signOut'));

Route::get('404', array('as' => 'errors.404', function () {
    return View::make('errors.404');
}));

Route::get('500', array('as' => 'errors.500', function () {
    return View::make('errors.500');
}));

Route::group(array('prefix' => 'admin', 'namespace' => 'Admin', 'before' => 'auth.admin'), function () {
    Route::get('', array('as' => 'admin.index', 'uses' => 'DashboardController@showIndex'));
    Route::post('parse_file', array('uses' => 'BaseController@parse_file', 'as' => 'admin.parse_file'));
    Route::resource('insurance_companies', 'InsuranceCompaniesController');
    Route::resource('regions', 'RegionsController');
    Route::resource('programs', 'ProgramsController');

    /* Region Programs Begin */
    Route::get('regions/{regionId}/create_program', array('uses' => 'ProgramsController@create', 'as' => 'admin.regions.create_program'));
    Route::get('regions/{regionId}/programs_roster', array('uses' => 'ProgramsController@index', 'as' => 'admin.regions.programs_roster'));
    Route::get('regions/{regionId}/programs/{programId}/edit', array('uses' => 'ProgramsController@edit', 'as' => 'admin.programs.edit'));

    Route::get('regions/{regionId}/programs/{programId}/import_visit_dates', array('uses' => 'ProgramsController@import_visit_dates', 'as' => 'admin.programs.import_visit_dates'));
    Route::post('programs/store_imported_visit_dates', array('uses' => 'ProgramsController@store_imported_visit_dates', 'as' => 'admin.programs.store_imported_visit_dates'));
    /* Region Programs End */

    /* Patient Programs Relations Begin */
    Route::get('patients/{patientId}/programs/{programId}', array('uses' => 'ProgramsController@patient_visits', 'as' => 'admin.programs.patient_visits'));
    Route::put('regions/{regionId}/patients/{patientId}/programs/{programId}/add_patient_actual_visit', array('uses' => 'ProgramsController@add_patient_actual_visit', 'as' => 'admin.programs.add_patient_actual_visit'));

    Route::get('programs/{programId}/patients_list_csv', array('uses' => 'ProgramsController@patients_list_csv', 'as' => 'admin.programs.patients_list_csv'));
    Route::get('programs/{programId}/patients_list', array('uses' => 'ProgramsController@patients_list', 'as' => 'admin.programs.patients_list'));

    /* Patient Programs Relations End */

    /* Region patients Begin */
    Route::get('regions/{regionId}/import_patients', array('uses' => 'RegionsController@import_patients', 'as' => 'admin.regions.import_patients'));
    Route::post('regions/upload', array('uses' => 'RegionsController@upload', 'as' => 'admin.regions.upload'));
    Route::post('regions/store_imported_patients', array('uses' => 'RegionsController@store_imported_patients', 'as' => 'admin.regions.store_imported_patients'));
    Route::get('regions/{regionId}/create_patients', array('uses' => 'RegionsController@create_patients', 'as' => 'admin.regions.create_patients'));
    Route::post('regions/{regionId}/store_patients', array('uses' => 'RegionsController@store_patients', 'as' => 'admin.regions.store_patients'));
    Route::get('patients/{patientId}/edit_patients', array('uses' => 'RegionsController@edit_patients', 'as' => 'admin.regions.edit_patients'));
    Route::post('patients/{patientId}/update_patients', array('uses' => 'RegionsController@update_patients', 'as' => 'admin.regions.update_patients'));
    Route::get('regions/{regionId}/patients_roster', array('uses' => 'RegionsController@patients_roster', 'as' => 'admin.regions.patients_roster'));
    Route::get('regions/{regionId}/patients_roster_csv', array('uses' => 'RegionsController@patients_roster_csv', 'as' => 'admin.regions.patients_roster_csv'));
    Route::get('regions/{regionId}/all_doctors', array('uses' => 'RegionsController@all_doctors', 'as' => 'admin.regions.all_doctors'));
    Route::get('regions/{regionId}/all_doctors_csv', array('uses' => 'RegionsController@all_doctors_csv', 'as' => 'admin.regions.all_doctors_csv'));
    /* Region patients End */

    /* Patients report Begin */
    Route::get('patients/{patientId}/report', array('uses' => 'PatientsController@report', 'as' => 'admin.patients.report'));
    /* Patients report End */
    Route::get('patients/full_list_cvs', array('uses' => 'UsersController@full_list_cvs', 'as' => 'admin.users.full_list_cvs'));

    /* Program reports Begin */
    Route::get('program_reports', array('uses' => 'ProgramsController@program_reports', 'as' => 'admin.programs.report'));
    Route::get('program_reports_result', array('uses' => 'ProgramsController@generate_report', 'as' => 'admin.programs.generate_report'));
    Route::get('program_reports_result_csv', array('uses' => 'ProgramsController@generate_report_csv', 'as' => 'admin.programs.generate_report_csv'));

    Route::get('scheduled_visit_report', array('uses' => 'PatientProgramVisitController@scheduled_visit_report', 'as' => 'admin.reports.scheduled_visit_report'));
    Route::get('scheduled_visit_report_result', array('uses' => 'PatientProgramVisitController@generate_scheduled_visit_report', 'as' => 'admin.reports.generate_scheduled_visit_report'));
    Route::get('scheduled_visit_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_scheduled_visit_report_csv', 'as' => 'admin.reports.generate_scheduled_visit_report_csv'));

    Route::get('incentive_report', array('uses' => 'PatientProgramVisitController@incentive_report', 'as' => 'admin.reports.incentive_report'));
    Route::get('incentive_report_result', array('uses' => 'PatientProgramVisitController@generate_incentive_report', 'as' => 'admin.reports.generate_incentive_report'));
    Route::get('incentive_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_incentive_report_csv', 'as' => 'admin.reports.generate_incentive_report_csv'));

    Route::get('pregnancy_report', array('uses' => 'PatientProgramVisitController@pregnancy_report', 'as' => 'admin.reports.pregnancy_report'));
    Route::get('pregnancy_report_result', array('uses' => 'PatientProgramVisitController@generate_pregnancy_report', 'as' => 'admin.reports.generate_pregnancy_report'));
    Route::get('pregnancy_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_pregnancy_report_csv', 'as' => 'admin.reports.generate_pregnancy_report_csv'));

    Route::get('returned_gift_card_report', array('uses' => 'PatientProgramVisitController@returned_gift_card_report', 'as' => 'admin.reports.returned_gift_card_report'));
    Route::get('returned_gift_card_report_result', array('uses' => 'PatientProgramVisitController@generate_returned_gift_card_report', 'as' => 'admin.reports.generate_returned_gift_card_report'));
    Route::get('returned_gift_card_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_returned_gift_card_report_csv', 'as' => 'admin.reports.generate_returned_gift_card_report_csv'));

    Route::get('outreach_codes_report', array('uses' => 'PatientProgramVisitController@outreach_codes_report', 'as' => 'admin.reports.outreach_codes_report'));
    Route::get('outreach_codes_report_result', array('uses' => 'PatientProgramVisitController@generate_outreach_codes_report', 'as' => 'admin.reports.generate_outreach_codes_report'));
    Route::get('outreach_codes_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_outreach_codes_report_csv', 'as' => 'admin.reports.generate_outreach_codes_report_csv'));

    //get regions of an insurance company
    Route::get('insurance_company/{insurance_company_id}/regions', array('uses' => 'InsuranceCompaniesController@get_regions', 'as' => 'admin.insurance_company.regions'));
    Route::get('regions/{region_id}/programs', array('uses' => 'RegionsController@get_programs', 'as' => 'admin.region.programs'));

    /* Program reports End */

    Route::resource('users', 'UsersController');
    Route::get('patients', array('as' => 'admin.patients.index', 'uses' => 'UsersController@patients'));
    Route::get('phones', array('as' => 'admin.phones.index', 'uses' => 'PhonesController@index'));
    Route::post('phones/update', array('as' => 'admin.phones.update', 'uses' => 'PhonesController@update'));
    Route::get('discontinue_tracking_reasons', array('as' => 'admin.discontinue_tracking_reasons.index', 'uses' => 'DiscontinueTrackingReasonsController@index'));
    Route::post('discontinue_tracking_reasons/update', array('as' => 'admin.discontinue_tracking_reasons.update', 'uses' => 'DiscontinueTrackingReasonsController@update'));
    Route::get('outreach_codes', array('as' => 'admin.outreach_codes.index', 'uses' => 'OutreachCodesController@index'));
    Route::post('outreach_codes/update', array('as' => 'admin.outreach_codes.update', 'uses' => 'OutreachCodesController@update'));

    Route::resource('practice_groups', 'PracticeGroupsController');
    Route::get('regions/{regionId}/create_practice_group', array('uses' => 'PracticeGroupsController@create', 'as' => 'admin.regions.create_practice_group'));
    Route::get('regions/{regionId}/practice_groups_roster', array('uses' => 'PracticeGroupsController@index', 'as' => 'admin.regions.practice_groups_roster'));
    Route::get('regions/{regionId}/practice_groups/{practiceGroupsId}/edit', array('uses' => 'PracticeGroupsController@edit', 'as' => 'admin.practice_groups.edit'));
    Route::get('regions/{regionId}/import_practice_groups', array('uses' => 'PracticeGroupsController@import_practice_groups', 'as' => 'admin.regions.import_practice_groups'));
    Route::post('regions/store_imported_practice_groups', array('uses' => 'PracticeGroupsController@store_imported_practice_groups', 'as' => 'admin.regions.store_imported_practice_groups'));
    Route::get('regions/{regionId}/practice_groups_full_list_cvs', array('uses' => 'PracticeGroupsController@full_list_cvs', 'as' => 'admin.regions.practice_groups_full_list_cvs'));

    Route::resource('doctors', 'DoctorsController');
    Route::get('regions/{regionId}/practice_groups/{practiceGroupID}/create_doctor', array('uses' => 'DoctorsController@create', 'as' => 'admin.practice_groups.create_doctor'));
    Route::get('regions/{regionId}/practice_groups/{practiceGroupID}/doctors_roster', array('uses' => 'DoctorsController@index', 'as' => 'admin.practice_groups.doctors_roster'));
    Route::get('regions/{regionId}/practice_groups/{practiceGroupID}/doctors/{doctorsId}/edit', array('uses' => 'DoctorsController@edit', 'as' => 'admin.doctors.edit'));
    Route::get('regions/{regionId}/import_doctors', array('uses' => 'DoctorsController@import_doctors', 'as' => 'admin.regions.import_doctors'));
    Route::post('regions/store_imported_doctors', array('uses' => 'DoctorsController@store_imported_doctors', 'as' => 'admin.regions.store_imported_doctors'));

    Route::resource('manual_outreaches', 'ManualOutreachesController');
    Route::get('patients/{patientId}/programs/{programId}/manual_outreaches/{manualOutreachID}/edit', array('uses' => 'ManualOutreachesController@edit', 'as' => 'admin.manual_outreaches.edit'));

    Route::resource('patient_program_visits', 'PatientProgramVisitController');
    Route::get('patients/{patientId}/programs/{programId}/patient_program_visits/{patientProgramVisit_ID}/edit', array('uses' => 'PatientProgramVisitController@edit', 'as' => 'admin.patient_program_visits.edit'));

});