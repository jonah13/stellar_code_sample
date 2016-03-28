<?php

namespace Admin;

use View;

class ProgramsController extends BaseController
{

    public function index($region_id)
    {
        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();

        $programs = $region->programs()->get();

        $this->layout->content = View::make('admin/programs/index')
            ->with('programs', $programs)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('region_id', $region_id);
    }

    public function create($region_id)
    {
        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();

        $types_array = array(\Program::TYPE_OTHER => 'Other', \Program::TYPE_A1C => 'A1C', \Program::TYPE_PREGNANCY => 'Pregnancy', \Program::TYPE_POSTPARTUM => 'Postpartum');
        $periods_table = array(\Program::PER_WEEK => 'Week', \Program::PER_MONTH => 'Month', \Program::PER_YEAR => 'Year');
        $all_practice_groups = $region->practiceGroups()->get();

        $this->layout->content = View::make('admin/programs/create')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('practice_groups', array())
            ->with('all_practice_groups', $all_practice_groups)
            ->with('program', new \Program())
            ->with('region_id', $region_id)
            ->with('types_array', $types_array)
            ->with('periods_table', $periods_table)
            ->with('route', 'admin.programs.store');
    }

    public function store()
    {
        $input = \Input::all();

        $validator = \Program::validate($input);

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.create_program', array($input['region_id']))
                ->withInput()
                ->withErrors($validator);
        } else {
            $region = \Region::find($input['region_id']);
            $program = \Program::create($input);
            $program->region_id = $input['region_id'];
            $program->region()->associate($region);

            if (\Input::get('practice_groups_id') == null) {
                $practice_groups_ids = array();
            } else {
                $practice_groups_ids = \Input::get('practice_groups_id');
            }
            $program->practice_groups()->sync($practice_groups_ids);

            $program->save();

            return \Redirect::route('admin.regions.index')
                ->with('success', 'A program has been successfully created.');
        }
    }

    public function show()
    {
        return 'show';
    }

    public function edit($region_id, $program_id)
    {
        $types_array = array(\Program::TYPE_OTHER => 'Other', \Program::TYPE_A1C => 'A1C', \Program::TYPE_PREGNANCY => 'Pregnancy', \Program::TYPE_POSTPARTUM => 'Postpartum');
        $periods_table = array(\Program::PER_WEEK => 'Week', \Program::PER_MONTH => 'Month', \Program::PER_YEAR => 'Year');
        $program = \Program::find($program_id);

        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();
        $practice_groups = $program->practice_groups()->get();
        $all_practice_groups = $region->practiceGroups()->get();

        $this->layout->content = View::make('admin/programs/edit')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('practice_groups', $practice_groups)
            ->with('all_practice_groups', $all_practice_groups)
            ->with('program', $program)
            ->with('region_id', $region_id)
            ->with('types_array', $types_array)
            ->with('periods_table', $periods_table)
            ->with('route', array('admin.programs.update', $program_id))
            ->with('method', 'PUT');
    }

    public function update($program_id)
    {
        $input = \Input::all();
        $validator = \Program::validate($input, $program_id);

        if ($validator->fails()) {
            return \Redirect::route('admin.programs.edit', array($input['region_id'], $program_id))
                ->withInput()
                ->withErrors($validator);
        } else {
            $program = \Program::find($program_id);
            $program->fill($input);


            if (\Input::get('practice_groups_id') == null) {
                $practice_groups_ids = array();
            } else {
                $practice_groups_ids = \Input::get('practice_groups_id');
            }
            $program->practice_groups()->sync($practice_groups_ids);

            $program->save();

            return \Redirect::route('admin.regions.programs_roster', $input['region_id'])
                ->with('success', 'A program has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $program = \Program::find($id);

        if (!$program) {
            \App::abort(404);
        }
        $program->delete();

        return array('ok' => 1);
    }

    public function program_reports()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_regions_as_key_value_array();

        $programs = array();
        foreach ($regions as $key => $value) {
            $region = \Region::find($key);
            $programs = $region->get_programs_as_key_value_array();

            break;
        }

        $this->layout->content = View::make('admin/report/programs/program_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.programs.generate_report')
            ->with('method', 'GET');
    }

    public function generate_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        if ($input["kept_appt"] == 'y') {
            $result = \DB::table('patient_program_visits')->where('program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->whereBetween('actual_visit_date', array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial');

        } else {
            $first_date_of_year = date('Y-01-01 00:00:00', strtotime($date_ranges[1]));
            $last_date_of_year = date('Y-12-31 23:59:59', strtotime($date_ranges[1]));

            $result = \DB::table('users')
                ->join('patient_program', 'users.id', '=', 'patient_program.patient_id')
                ->leftJoin('patient_program_visits', 'users.id', '=', 'patient_program_visits.patient_id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereRaw("( scheduled_visit_date is null or scheduled_visit_date BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "' )")
                ->whereRaw("( actual_visit_date is null or actual_visit_date not BETWEEN '" . $first_date_of_year . "' and '" . $last_date_of_year . "' )")
                ->select('username', 'first_name', 'last_name', 'actual_visit_date', 'scheduled_visit_date', 'incentive_type', 'gift_card_serial');
        }

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'first_name', 'last_name')
                ->addColumn('scheduled_visit_date', function ($model) use (&$isSysAdmin) {
                    return \Helpers::format_date_display($model->scheduled_visit_date);
                })
                ->addColumn('actual_visit_date', function ($model) use (&$isSysAdmin) {
                    return \Helpers::format_date_display($model->actual_visit_date);
                })
                ->showColumns('incentive_type', 'gift_card_serial')
                ->searchColumns('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')
                ->orderColumns('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')
                ->make();
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $this->layout->content = View::make('admin/report/programs/show_program_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_report_csv()
    {

        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        if ($input["kept_appt"] == 'y') {
            $result = \DB::table('patient_program_visits')->where('program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->whereBetween('actual_visit_date', array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')
                ->get();

        } else {
            $first_date_of_year = date('Y-01-01 00:00:00', strtotime($date_ranges[1]));
            $last_date_of_year = date('Y-12-31 23:59:59', strtotime($date_ranges[1]));

            $result = \DB::table('users')
                ->join('patient_program', 'users.id', '=', 'patient_program.patient_id')
                ->leftJoin('patient_program_visits', 'users.id', '=', 'patient_program_visits.patient_id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereRaw("( scheduled_visit_date is null or scheduled_visit_date BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "' )")
                ->whereRaw("( actual_visit_date is null or actual_visit_date not BETWEEN '" . $first_date_of_year . "' and '" . $last_date_of_year . "' )")
                ->select('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')->get();
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $delimiter = ",";
        $filename = $program->name . " Report - Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        $line = array('Patient ID', 'Patient First Name', 'Patient Last Name', 'Scheduled Visit Date', 'Actual Visit Date', 'Incentive Type', 'Incentive Code');
        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->scheduled_visit_date = ($item->scheduled_visit_date !== null) ? date_format(date_create($item->scheduled_visit_date), 'm/d/Y') : 'Not Available';
            $item->actual_visit_date = ($item->actual_visit_date !== null) ? date_format(date_create($item->actual_visit_date), 'm/d/Y') : 'Not Available';
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';

            $line = array("$item->username", "$item->first_name", "$item->last_name", "$item->scheduled_visit_date", "$item->actual_visit_date", "$item->incentive_type", "$item->gift_card_serial");

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patient_visits($patient_id, $program_id)
    {
        $discontinue_tracking_reasons_obj = \DiscontinueTrackingReason::all();
        $discontinue_tracking_reasons = [];
        foreach ($discontinue_tracking_reasons_obj as $discontinue_tracking_reason) {
            $discontinue_tracking_reasons[$discontinue_tracking_reason->id] = $discontinue_tracking_reason->reason;
        }

        //$region = \Region::find($region_id);
        $patient = \User::find($patient_id);
        $program = \Program::find($program_id);
        $patient_program = $program->patient_program($patient_id);
        //$program->patient_notes = $program->patient_notes($patient_id)->patient_notes;
        $program->patient_notes = $patient_program->patient_notes;
        $program->date_added = \Helpers::format_date_display($patient_program->date_added);
        $program->due_date = \Helpers::format_date_display($patient_program->due_date);
        $program->delivery_date = \Helpers::format_date_display($patient_program->delivery_date);
        $program->postpartum_start = \Helpers::format_date_display($patient_program->postpartum_start);
        $program->postpartum_end = \Helpers::format_date_display($patient_program->postpartum_end);
        $program->birth_weight = $patient_program->birth_weight;
        $program->pediatrician_id = $patient_program->pediatrician_id;
        $program->discontinue = $patient_program->discontinue;
        $program->discontinue_reason = $patient_program->discontinue_reason_id;
        $program->gestational_age = $patient_program->gestational_age;

        $previous_contacts = $patient->previous_contacts($program->id);
        $actual_visits = $patient->actual_visits($program->id);
        $manual_outreaches = $patient->manual_outreaches($program->id);

        $outreach_codes = \OutreachCode::all()->lists('code_name', 'id');
        $outreach_codes = array_merge(array("0" => "--Select A value--"), $outreach_codes);

        $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . date('Y')));
        $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . date('Y')));

        $scheduled_visit_date_for_current_year = \DB::table('patient_program_visits')
            ->where('patient_id', '=', $patient_id)
            ->where('program_id', '=', $program_id)
            ->whereBetween('scheduled_visit_date', array($first_date_of_year, $last_date_of_year))
            ->first();

        if (count($scheduled_visit_date_for_current_year) > 0) {
            $program->scheduled_visit_date = \Helpers::format_date_display($scheduled_visit_date_for_current_year->scheduled_visit_date);
            $program->scheduled_visit_date_notes = $scheduled_visit_date_for_current_year->scheduled_visit_date_notes;
        }

        $this->layout->content = View::make('admin/regions/patients/patient_visits')
            //->with('region', $region)
            ->with('patient', $patient)
            ->with('program', $program)
            ->with('discontinue_tracking_reasons', $discontinue_tracking_reasons)
            ->with('previous_contacts', $previous_contacts)
            ->with('actual_visits', $actual_visits)
            ->with('outreach_codes', $outreach_codes)
            ->with('manual_outreaches', $manual_outreaches)
            ->with('route', 'admin.programs.add_patient_actual_visit')
            ->with('method', 'PUT');
    }

    public function add_patient_actual_visit()
    {
        $input = \Input::all();

        $program = \Program::where('id', $input['program_id'])->first();

        if (isset($input['manual_outreach'])) {
            for ($i = 0; $i < count($input['manual_outreach']); $i++) {

                \DB::table('manual_outreaches')->insert(
                    array('patient_id' => $input['patient_id'],
                        'program_id' => $input['program_id'],
                        'outreach_date' => \Helpers::format_date_DB($input['manual_outreach_date'][$i]),
                        'outreach_code' => $input['manual_outreach_code'][$i],
                        'outreach_notes' => $input['manual_outreach_notes'][$i],
                        'created_by' => \Sentry::getUser()->id,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                );
            }
        }

        if ($program->type == \Program::TYPE_PREGNANCY) {

            $this->add_patient_actual_visit_for_pregnancy_program($input, $program);

        } else {

            $this->add_patient_actual_visit_for_programs_other_than_pregnancy($input, $program);
        }

        return \Redirect::route('admin.regions.patients_roster', array($program->region->id))
            ->with('success', 'An actual visit date has been successfully added.');
    }


    private function add_patient_actual_visit_for_pregnancy_program($input, $program)
    {
        if (!empty($input['delivery_date'])) {
            $patient = \User::where('id', '=', $input['patient_id'])->first();
            $region = $program->region;
            $all_region_programs = $region->programs()->get();

            foreach ($all_region_programs as $prg) {
                if ($prg->type == \Program::TYPE_POSTPARTUM) {
                    $patient->programs()->sync(array($prg->id), false);

                    $postpartum_start = date('Y-m-d', strtotime("+21 days", strtotime($input['delivery_date'])));
                    $postpartum_end = date('Y-m-d', strtotime("+56 days", strtotime($input['delivery_date'])));

                    \DB::table('patient_program')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $prg->id)
                        ->where('postpartum_start', '=', Null)
                        ->where('postpartum_end', '=', Null)
                        ->update(array('patient_notes' => $input['patient_notes'],
                            'date_added' => \Helpers::format_date_DB($input['date_added']),
                            'due_date' => \Helpers::format_date_DB($input['due_date']),
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'discontinue' => isset($input['discontinue']) ? true : false,
                            'discontinue_reason_id' => $input['discontinue_reason'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'postpartum_start' => $postpartum_start,
                            'postpartum_end' => $postpartum_end,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                    \DB::table('patient_program')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $prg->id)
                        ->update(array(
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                }
            }
        }

        \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
            ->update(array('patient_notes' => $input['patient_notes'],
                'date_added' => \Helpers::format_date_DB($input['date_added']),
                'due_date' => \Helpers::format_date_DB($input['due_date']),
                'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                'birth_weight' => $input['birth_weight'],
                'pediatrician_id' => $input['pediatrician_id'],
                'discontinue' => isset($input['discontinue']) ? true : false,
                'discontinue_reason_id' => $input['discontinue_reason'],
                'gestational_age' => floatval($input['gestational_age']),
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
            ));

        $scheduled_visits_rows = [];

        if (!empty($input['scheduled_visit'])) {
            for ($i = 0; $i < count($input['scheduled_visit']); $i++) {

                if (!empty($input['scheduled_visit'][$i])) {
                    $scheduled_visits_row = [];
                    $scheduled_visits_row['scheduled_visit'] = \Helpers::format_date_DB($input['scheduled_visit'][$i]);
                    $scheduled_visits_row['actual_visit'] = \Helpers::format_date_DB($input['actual_visit'][$i]);

                    /*
                    $doctor_id = null;
                    if (isset($input['doctor_id'][$i])) {
                        $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'][$i])->first();
                        if ($doctor) {
                            $doctor_id = $doctor->id;
                        }
                    }
                    //*/

                    $scheduled_visits_row['doctor_id'] = isset($input['doctor_id'][$i]) ? $input['doctor_id'][$i] : '';
                    $scheduled_visits_row['incentive_type'] = isset($input['incentive_type'][$i]) ? $input['incentive_type'][$i] : '';
                    $scheduled_visits_row['incentive_value'] = !empty($input['incentive_value'][$i]) ? str_replace("$", "", $input['incentive_value'][$i]) : null;
                    $scheduled_visits_row['gift_card_serial'] = isset($input['gift_card_serial'][$i]) ? $input['gift_card_serial'][$i] : '';
                    $scheduled_visits_row['incentive_date'] = \Helpers::format_date_DB($input['incentive_date'][$i]);
                    $scheduled_visits_row['visit_notes'] = !empty($input['visit_notes'][$i]) ? $input['visit_notes'][$i] : '';
                    $scheduled_visits_row['gift_card_returned'] = isset($input['gift_card_returned'][$i]) ? true : false;
                    $scheduled_visits_row['gift_card_returned_notes'] = !empty($input['gift_card_returned_notes'][$i]) ? $input['gift_card_returned_notes'][$i] : '';
                    $scheduled_visits_row['manually_added'] = isset($input['manually_added'][$i]) ? true : false;

                    $scheduled_visits_rows[] = $scheduled_visits_row;
                }
            }
        }

        if (!empty($input['sign_up'])) {

            \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('sign_up', true)
                ->delete();

            \DB::table('patient_program_visits')->insert(
                array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                    'actual_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                    'scheduled_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                    'incentive_type' => $input['sign_up_incentive_type'],
                    'incentive_value' => !empty($input['sign_up_incentive_value']) ? str_replace("$", "", $input['sign_up_incentive_value']) : null,
                    'gift_card_serial' => $input['sign_up_gift_card_serial'],
                    'incentive_date_sent' => \Helpers::format_date_DB($input['sign_up_incentive_date']),
                    'visit_notes' => $input['sign_up_notes'],
                    'sign_up' => true,
                    'manually_added' => true,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
        }

        \DB::table('patient_program_visits')
            ->where('patient_id', '=', $input['patient_id'])
            ->where('program_id', '=', $input['program_id'])
            ->where('sign_up', '<>', true)
            ->delete();

        foreach ($scheduled_visits_rows as $scheduled_visits_row) {
            \DB::table('patient_program_visits')->insert(
                array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                    'actual_visit_date' => $scheduled_visits_row['actual_visit'],
                    'scheduled_visit_date' => $scheduled_visits_row['scheduled_visit'],
                    'doctor_id' => $scheduled_visits_row['doctor_id'],
                    'incentive_type' => $scheduled_visits_row['incentive_type'],
                    'incentive_value' => $scheduled_visits_row['incentive_value'],
                    'gift_card_serial' => $scheduled_visits_row['gift_card_serial'],
                    'incentive_date_sent' => $scheduled_visits_row['incentive_date'],
                    'visit_notes' => $scheduled_visits_row['visit_notes'],
                    'gift_card_returned' => $scheduled_visits_row['gift_card_returned'],
                    'gift_card_returned_notes' => $scheduled_visits_row['gift_card_returned_notes'],
                    'manually_added' => $scheduled_visits_row['manually_added'],
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
        }
    }

    private function add_patient_actual_visit_for_programs_other_than_pregnancy($input, $program)
    {
        if ($program->type == \Program::TYPE_POSTPARTUM) {
            //$postpartum_start = date('Y-m-d', strtotime("+21 days", $input['delivery_date']));
            //$postpartum_end = date('Y-m-d', strtotime("+56 days", $input['delivery_date']));

            \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                ->update(array(
                    'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                    'postpartum_start' => \Helpers::format_date_DB($input['postpartum_start']),
                    'postpartum_end' => \Helpers::format_date_DB($input['postpartum_end']),
                    'birth_weight' => $input['birth_weight'],
                    'gestational_age' => floatval($input['gestational_age']),
                    'pediatrician_id' => $input['pediatrician_id'],
                    'patient_notes' => $input['patient_notes']
                ));

            // update pregnancy program information
            $patient = \User::where('id', '=', $input['patient_id'])->first();
            $region = $program->region;
            $all_region_programs = $region->programs()->get();

            foreach ($all_region_programs as $prg) {
                if ($prg->type == \Program::TYPE_PREGNANCY) {
                    \DB::table('patient_program')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $prg->id)
                        ->update(array(
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));
                }
            }


        } else {
            \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                ->update(array('patient_notes' => $input['patient_notes']));
        }

        if (!empty($input['actual_visit_date'])) {

            $actual_visit_date = strtotime($input['actual_visit_date']);
            $scheduled_visit_date = '';
            $period = $program->visit_requirement_period;

            if ($period == \Program::PER_WEEK) {
                $scheduled_visit_date = strtotime('+1 week', $actual_visit_date);
            } else if ($period == \Program::PER_MONTH) {
                $scheduled_visit_date = strtotime('+1 month', $actual_visit_date);
            } else if ($period == \Program::PER_YEAR) {
                $scheduled_visit_date = strtotime('+1 year', $actual_visit_date);
            }

            if ($scheduled_visit_date !== '') {
                $scheduled_visit_date = date('Y-m-d', $scheduled_visit_date);
            }

            if ($program->type == \Program::TYPE_A1C) {
                // matching function to match existing doctor with the entered doctor_id
                /*
                $doctor_id = null;
                if (isset($input['doctor_id'])) {
                    $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'])->first();
                    if ($doctor) {
                        $doctor_id = $doctor->id;
                    }
                }
                //*/

                \DB::table('patient_program_visits')->insert(
                    array('patient_id' => $input['patient_id'],
                        'program_id' => $input['program_id'],
                        'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                        'scheduled_visit_date' => $scheduled_visit_date,
                        'doctor_id' => $input['doctor_id'],
                        'incentive_type' => $input['incentive_type'],
                        'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : null,
                        'gift_card_serial' => $input['gift_card_serial'],
                        'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                        'metric' => $input['metric'],
                        'visit_notes' => $input['visit_notes'],
                        'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                        'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                        'manually_added' => isset($input['manually_added']) ? true : false,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            } else {
                // matching function to match existing doctor with the entered doctor_id
                /*
                $doctor_id = null;
                if (isset($input['doctor_id'])) {
                    $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'])->first();
                    if ($doctor) {
                        $doctor_id = $doctor->id;
                    }
                }
                //*/

                \DB::table('patient_program_visits')->insert(
                    array('patient_id' => $input['patient_id'],
                        'program_id' => $input['program_id'],
                        'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                        'scheduled_visit_date' => $scheduled_visit_date,
                        'doctor_id' => $input['doctor_id'],
                        'incentive_type' => $input['incentive_type'],
                        'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : null,
                        'gift_card_serial' => $input['gift_card_serial'],
                        'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                        'visit_notes' => $input['visit_notes'],
                        'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                        'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                        'manually_added' => isset($input['manually_added']) ? true : false,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            }

        }


        if (!empty($input['scheduled_visit_date'])) {

            $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . date('Y')));
            $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . date('Y')));

            $scheduled_visit_date_for_current_year = \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->whereBetween('scheduled_visit_date', array($first_date_of_year, $last_date_of_year))
                ->first();


            if (count($scheduled_visit_date_for_current_year) > 0) {

                \DB::table('patient_program_visits')
                    ->where('patient_id', '=', $input['patient_id'])
                    ->where('program_id', '=', $input['program_id'])
                    ->whereBetween('scheduled_visit_date', array($first_date_of_year, $last_date_of_year))
                    ->update(
                        array(
                            'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                            'scheduled_visit_date_notes' => isset($input['scheduled_visit_date_notes']) ? $input['scheduled_visit_date_notes'] : null,
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));
            } else {

                \DB::table('patient_program_visits')->insert(
                    array(
                        'patient_id' => $input['patient_id'],
                        'program_id' => $input['program_id'],
                        'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                        'scheduled_visit_date_notes' => isset($input['scheduled_visit_date_notes']) ? $input['scheduled_visit_date_notes'] : null,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                    ));
            }

        }
    }

    public function import_visit_dates($region_id, $program_id)
    {
        $region = \Region::find($region_id);
        $program = \Program::find($program_id);

        $this->layout = \View::make('admin.layouts.base_iframe');

        $this->layout->content = View::make('admin/programs/import/import_visit_dates')
            ->with('region', $region)
            ->with('program', $program)
            ->with('route', array('admin.index'));
    }

    public function store_imported_visit_dates()
    {
        if (strpos(getcwd(), 'public') !== false) {
            $baselink = 'uploads/';
        } else {
            $baselink = 'public/uploads/';
        }

        $filename = $baselink . (\Input::get('file'));

        $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        unset($data[0]);
        $data = array_values($data);

        $program = \DB::table('programs')->where('id', '=', \Input::get('program'))->first();

        $usernames = [];
        $drs_ids = [];
        $rows_count = 10; //($program->type == \Program::TYPE_POSTPARTUM) ? 11 : 10;

        // fetch all usernames for all rows, build an array import_data containing data to be imported
        $import_data = array();
        foreach ($data as $row) {
            $rowData = explode(",", $row);

            if (count($rowData) == $rows_count && !empty($rowData[0])) {

                $usernames[] = $rowData[0];

                $item = array();
                $item['first_name'] = trim($rowData[1]);
                $item['last_name'] = trim($rowData[2]);
                $item['actual_visit_date'] = trim($rowData[3]);
                $item['doctor_id'] = trim($rowData[4]);
                $drs_ids[] = trim($rowData[4]);
                $item['incentive_type'] = trim($rowData[5]);
                $item['incentive_value'] = trim($rowData[6]);
                $item['gift_card_serial'] = str_replace(".00", "", trim($rowData[7]));
                $item['incentive_date_sent'] = trim($rowData[8]);
                $item['visit_notes'] = trim($rowData[9]);
                //$item['delivery_date'] = trim($rowData[10]);

                $import_data[$rowData[0]] = $item;
            }
        }

        // find usernames ( to exclude ) that already have an actual visit date set for the current year
        $date_of_service = \Input::get('date_of_service');

        $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . $date_of_service));
        $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . $date_of_service));

        $metric_type = \Program::METRIC_NULL;
        if ($program->type == \Program::TYPE_A1C) {
            $metric_type = \Input::get('metric');
        }

        $already_set_actual_visit_date = \DB::table('patient_program_visits')
            ->select('users.username')
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->where('program_id', '=', \Input::get('program'))
            ->where('metric', '=', $metric_type)
            ->whereIn('users.username', $usernames)
            ->whereBetween('actual_visit_date', array($first_date_of_year, $last_date_of_year))
            ->get();

        $already_set_actual_visit_date_usernames = [];
        foreach ($already_set_actual_visit_date as $item) {
            $already_set_actual_visit_date_usernames[] = $item->username;
        }

        $patients_list = \User::whereIn('username', $usernames)->get();

        // get the doctors from the DB to a key value array
        $doctors = \Doctor::whereIn('pcp_id', $drs_ids)->lists('id', 'pcp_id');
        // deduce not matching doctors
        $not_matching_doctors = array_unique(array_diff($drs_ids, array_keys($doctors)));

        \DB::beginTransaction();
        $existing_items = [];
        foreach ($patients_list as $patient) {
            $existing_items[] = $patient->username;
            // exclude usernames that already have an actual visit date set
            if (in_array($patient->username, $already_set_actual_visit_date_usernames)) {
                continue;
            }

            /*
            if (in_array($import_data[$patient->username]['doctor_id'], $not_matching_doctors)) {
                continue;
            }
            */

            $actual_visit_date = strtotime($import_data[$patient->username]['actual_visit_date']);
            $incentive_type = $import_data[$patient->username]['incentive_type'];
            $incentive_value = $import_data[$patient->username]['incentive_value'];
            $gift_card_serial = $import_data[$patient->username]['gift_card_serial'];
            $incentive_date_sent = '';
            if ($import_data[$patient->username]['incentive_date_sent'] != '') {
                $incentive_date_sent = \Helpers::format_date_DB(trim($import_data[$patient->username]['incentive_date_sent']));
            }
            $visit_notes = $import_data[$patient->username]['visit_notes'];
            /*
            $delivery_date = '';
            if ($import_data[$patient->username]['delivery_date'] != '') {
                $delivery_date = date('Y-m-d', strtotime(trim($import_data[$patient->username]['delivery_date'])));
            }
            */
            $created_at = \Carbon\Carbon::now()->toDateTimeString();

            $scheduled_visit_date = '';
            $period = $program->visit_requirement_period;

            // TODO we supposed that visit_requirement_time is always "once"
            if ($period == \Program::PER_WEEK) {
                $scheduled_visit_date = strtotime('+1 week', $actual_visit_date);
            } else if ($period == \Program::PER_MONTH) {
                $scheduled_visit_date = strtotime('+1 month', $actual_visit_date);
            } else if ($period == \Program::PER_YEAR) {
                $scheduled_visit_date = strtotime('+1 year', $actual_visit_date);
            }

            if ($scheduled_visit_date !== '') {
                $scheduled_visit_date = date('Y-m-d H:i:s', $scheduled_visit_date);
            }
            $actual_visit_date = date('Y-m-d', $actual_visit_date);

            try {
                \DB::table('patient_program_visits')->insert(
                    array('patient_id' => $patient->id,
                        'program_id' => $program->id,
                        'actual_visit_date' => $actual_visit_date,
                        'scheduled_visit_date' => $scheduled_visit_date,
                        'incentive_type' => $incentive_type,
                        'incentive_value' => $incentive_value,
                        'gift_card_serial' => $gift_card_serial,
                        'incentive_date_sent' => $incentive_date_sent,
                        'doctor_id' => isset($doctors[$import_data[$patient->username]['doctor_id']]) ? $doctors[$import_data[$patient->username]['doctor_id']] : null,
                        'metric' => $metric_type,
                        'visit_notes' => $visit_notes,
                        //'delivery_date' => $delivery_date,
                        'created_at' => $created_at,
                        'updated_at' => $created_at));


            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        \DB::commit();
        // find not matching usernames : usernames in the import file that don't exist in the DB
        $nonExistentItemsUsernames = array_diff($usernames, $existing_items);

        $nonExistentItems = [];
        foreach ($nonExistentItemsUsernames as $username) {
            $item = array();

            $item['last_name'] = $import_data[$username]['last_name'];
            $item['first_name'] = $import_data[$username]['first_name'];

            $nonExistentItems[] = $item;
        }

        // build notifications messages to tell the admin which rows aren't existing and which rows have their actual
        // visit dates already set
        $alreadySetItems = [];
        foreach ($already_set_actual_visit_date_usernames as $username) {
            $item = array();

            $item['last_name'] = $import_data[$username]['last_name'];
            $item['first_name'] = $import_data[$username]['first_name'];

            $alreadySetItems[] = $item;
        }
        $message = '';
        if (count($nonExistentItems) > 0) {
            $message .= 'These patients do not exist for this region: <br/>';
            foreach ($nonExistentItems as $item) {
                $message .= ($item['last_name'] . ', ' . $item['first_name'] . '<br/>');
            }
            $message .= '<br/>';
        }

        if (count($alreadySetItems) > 0) {
            $message .= 'These patients already have an actual visit date set: <br/>';
            foreach ($alreadySetItems as $item) {
                $message .= ($item['last_name'] . ', ' . $item['first_name'] . '<br/>');
            }
            $message .= '<br/>';
        }

        if (count($not_matching_doctors) > 0) {
            $message .= 'These doctors IDs do not exist: <br/>';
            foreach ($not_matching_doctors as $item) {
                $message .= ($item . '<br/>');
            }
        }

        if (count($nonExistentItems) > 0 || count($alreadySetItems) > 0 || count($not_matching_doctors) > 0) {

            // Create a csv file based on $message Begin
            if (strpos(getcwd(), 'public') !== false) {
                $baselink = 'uploads/';
            } else {
                $baselink = 'public/uploads/';
            }
            $filename = $baselink . uniqid() . ".csv";

            $delimiter = ",";
            $f = fopen("$filename", 'w');

            if (count($nonExistentItems) > 0) {
                $line = array("These patients do not exist for this region:");
                fputcsv($f, $line, $delimiter);
                foreach ($nonExistentItems as $item) {
                    $str = $item['last_name'] . ', ' . $item['first_name'];
                    fputcsv($f, array("$str"), $delimiter);
                }
            }

            if (count($alreadySetItems) > 0) {
                $line = array("These patients already have an actual visit date set:");
                fputcsv($f, $line, $delimiter);
                foreach ($alreadySetItems as $item) {
                    $str = $item['last_name'] . ', ' . $item['first_name'];
                    fputcsv($f, array("$str"), $delimiter);
                }
            }

            if (count($not_matching_doctors) > 0) {
                $line = array("These doctors IDs do not exist:");
                fputcsv($f, $line, $delimiter);
                foreach ($not_matching_doctors as $item) {
                    fputcsv($f, array("$item"), $delimiter);
                }
            }
            fclose($f);
            // Create a csv file based on $message End
            //return \Response::make(array('ok' => json_encode($message)), 201);
            return \Response::make(array('ok' => $message, 'result_file' => str_replace("public/", "", $filename)), 201);
        } else {
            return \Response::make(array('ok' => true), 201);
        }
    }

    public function patients_list_csv($id)
    {
        $program = \Program::find($id);
        if ($program == null) {
            $patients = array();
        } else {
            $patients = $program->patients()->select('username', 'last_name', 'first_name', 'date_of_birth', 'address1', 'address2', 'city', 'zip', 'phone1')->get();
        }

        $delimiter = ",";
        if ($program == null) {
            $filename = "no program found.csv";
        } else {
            $filename = $program->region->name . " - " . $program->name . " - Patients.csv";
        }

        $f = fopen('php://memory', 'w');

        $line = array('Patient ID', 'Last Name', 'First Name', 'Date of Birth', 'Address 1', 'Address 2', 'City', 'Zip', 'Phone');
        fputcsv($f, $line, $delimiter);

        foreach ($patients as $patient) {
            $patient->date_of_birth = date_format(date_create($patient->date_of_birth), 'm/d/Y');

            $line = array("$patient->username", "$patient->last_name", "$patient->first_name", "$patient->date_of_birth", "$patient->address1", "$patient->address2", "$patient->city", "$patient->zip", "$patient->phone1");

            fputcsv($f, $line, $delimiter);
        }
        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patients_list($id)
    {
        $program = \Program::find($id);

        $user = \User::find(\Sentry::getUser()->id);
        $isSysAdmin = $user->isSysAdmin();

        $query = \DB::table('users')
            ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
            ->where('patient_program.program_id', '=', $program->id);

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($query)
                ->showColumns('username', 'first_name', 'last_name')
                ->addColumn('date_of_birth', function ($model) use (&$isSysAdmin) {
                    return date("m/d/Y", strtotime($model->date_of_birth));
                })
                ->showColumns('id')
                ->searchColumns('username', 'first_name', 'last_name')
                ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth')
                ->make();
        }

        $this->layout->content = View::make('admin/programs/patients')
            ->with('isSysAdmin', $isSysAdmin)
            ->with('program_id', $program->id);

    }

}
