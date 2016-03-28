<?php

namespace Admin;

use View;

class PatientProgramVisitController extends BaseController
{

    public function edit($patient_id, $program_id, $patient_program_visit_id)
    {
        $patient_program_visit = \PatientProgramVisit::find($patient_program_visit_id);
        $program = \Program::where('id', $program_id)->first();

        if (!$patient_program_visit) {
            \App::abort(404);
        }
        $patient_program_visit->actual_visit_date = \Helpers::format_date_display($patient_program_visit->actual_visit_date);
        $patient_program_visit->incentive_date_sent = \Helpers::format_date_display($patient_program_visit->incentive_date_sent);

        $this->layout->content = View::make('admin/regions/patients/patient_program_visits/edit')
            ->with('patient_program_visit', $patient_program_visit)
            ->with('patient_id', $patient_id)
            ->with('program_id', $program_id)
            ->with('program', $program)
            ->with('route', array('admin.patient_program_visits.update', $patient_program_visit_id))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        $input['actual_visit_date'] = \Helpers::format_date_DB($input['actual_visit_date']);
        $input['incentive_date_sent'] = \Helpers::format_date_DB($input['incentive_date_sent']);
        $input['incentive_value'] = str_replace("$", "", $input['incentive_value']);

        $patient_program_visit = \PatientProgramVisit::find($id);
        $patient_program_visit->fill($input);
        $patient_program_visit->save();

        return \Redirect::route('admin.programs.patient_visits', array($patient_program_visit->patient_id, $patient_program_visit->program_id))
            ->with('success', 'A patient visit has been successfully updated.');
    }

    public function destroy($id)
    {
        $patient_program_visit = \PatientProgramVisit::find($id);

        if (!$patient_program_visit) {
            \App::abort(404);
        }

        $patient_program_visit->delete();
        return array('ok' => 1);
    }

    public function scheduled_visit_report()
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

        $this->layout->content = View::make('admin/report/scheduled_visits/scheduled_visit_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_scheduled_visit_report')
            ->with('method', 'GET');
    }

    public function generate_scheduled_visit_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $result = \DB::table('patient_program_visits')
            ->where('program_id', '=', $input["program"])
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->whereBetween('scheduled_visit_date', array($date_ranges[0], $date_ranges[1]))
            ->select('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'first_name', 'last_name')
                ->addColumn('scheduled_visit_date', function ($model) {
                    return \Helpers::format_date_display($model->scheduled_visit_date);
                })
                ->addColumn('actual_visit_date', function ($model) {
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

        $this->layout->content = View::make('admin/report/scheduled_visits/show_scheduled_visit_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_scheduled_visit_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $result = \DB::table('patient_program_visits')
            ->where('program_id', '=', $input["program"])
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->whereBetween('scheduled_visit_date', array($date_ranges[0], $date_ranges[1]))
            ->select('username', 'first_name', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')
            ->orderBy('username')
            ->get();

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
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
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

        //*/

    }


    public function incentive_report()
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

        $this->layout->content = View::make('admin/report/incentives/incentive_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_incentive_report')
            ->with('method', 'GET');
    }

    public function generate_incentive_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'delivery_date', 'gestational_age', 'birth_weight');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'first_name', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->showColumns('gestational_age', 'birth_weight')
                    ->searchColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added',
                        'delivery_date', 'gestational_age', 'birth_weight')
                    ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added',
                        'delivery_date', 'gestational_age', 'birth_weight')
                    ->make();
            }
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'first_name', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->searchColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added')
                    ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added')
                    ->make();
            }
        }

        $this->layout->content = View::make('admin/report/incentives/show_incentive_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_incentive_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'delivery_date', 'gestational_age', 'birth_weight')
                ->get();
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added')
                ->get();
        }

        $delimiter = ",";
        $filename = $program->name . " Incentive Report - Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script',
                'Delivery Date', 'Gestational Age', 'Birth Weight');
        } else {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script');
        }

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            $item->incentive_value = "$" . $item->incentive_value;
            $item->manually_added = ($item->manually_added) ? "Y" : "N";

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
            }

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added",
                    "$item->delivery_date", "$item->gestational_age", "$item->birth_weight"
                );
            } else {
                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                );
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function pregnancy_report()
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

        $this->layout->content = View::make('admin/report/pregnancy/pregnancy_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_pregnancy_report')
            ->with('method', 'GET');
    }

    public function generate_pregnancy_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        if ($input['pregnancy_report_type'] == \Program::PREGNANCY_REPORT_ACTIVE) {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'patient_program.patient_id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereNull('delivery_date')
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = patient_program.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"));

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'first_name', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'first_name', 'last_name', 'outreach_date', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                        'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'incentive_date_sent', 'gift_card_serial', 'manually_added', 'next_scheduled_visit')
                    ->orderColumns('username', 'first_name', 'last_name', 'outreach_date', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                        'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'incentive_date_sent', 'gift_card_serial', 'manually_added', 'next_scheduled_visit')
                    ->make();
            }
        } else {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereBetween('delivery_date', array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'users.first_name', 'users.last_name', 'delivery_date');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'first_name', 'last_name')
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->searchColumns('username', 'first_name', 'last_name', 'delivery_date')
                    ->orderColumns('username', 'first_name', 'last_name', 'delivery_date')
                    ->make();
            }
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $this->layout->content = View::make('admin/report/pregnancy/show_pregnancy_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_pregnancy_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        if ($input['pregnancy_report_type'] == \Program::PREGNANCY_REPORT_ACTIVE) {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'patient_program.patient_id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereNull('delivery_date')
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = patient_program.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"))
                ->get();

        } else {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereBetween('delivery_date', array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'users.first_name', 'users.last_name', 'delivery_date')
                ->get();
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $delimiter = ",";
        $filename = $program->name . " Incentive Report - Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($input['pregnancy_report_type'] == \Program::PREGNANCY_REPORT_ACTIVE) {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->metric = \User::metric_toString($item->metric);
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                , "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

        } else {
            $line = array('Patient ID', 'Patient First Name', 'Patient Last Name', 'Delivery Date');
            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);

                $line = array("$item->username", "$item->first_name", "$item->last_name", "$item->delivery_date");

                fputcsv($f, $line, $delimiter);
            }
        }


        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function returned_gift_card_report()
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

        $this->layout->content = View::make('admin/report/returned_gift_card/returned_gift_card_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_returned_gift_card_report')
            ->with('method', 'GET');
    }

    public function generate_returned_gift_card_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', '1')
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'delivery_date', 'gestational_age', 'birth_weight'
                    , 'gift_card_returned_notes');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'first_name', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->showColumns('gestational_age', 'birth_weight', 'gift_card_returned', 'gift_card_returned_notes')
                    ->searchColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added',
                        'delivery_date', 'gestational_age', 'birth_weight', 'gift_card_returned_notes')
                    ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added',
                        'delivery_date', 'gestational_age', 'birth_weight', 'gift_card_returned_notes')
                    ->make();
            }
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', 1)
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'gift_card_returned_notes');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'first_name', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->showColumns('gift_card_returned_notes')
                    ->searchColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added'
                        , 'gift_card_returned_notes')
                    ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date', 'actual_visit_date',
                        'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added'
                        , 'gift_card_returned_notes')
                    ->make();
            }
        }

        $this->layout->content = View::make('admin/report/returned_gift_card/show_returned_gift_card_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_returned_gift_card_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', '1')
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'delivery_date', 'gestational_age', 'birth_weight',
                    'gift_card_returned_notes')
                ->get();
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', '1')
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'gift_card_returned_notes')
                ->get();
        }

        $delimiter = ",";
        $filename = $program->name . " Returned Gift Card Report - Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script',
                'Delivery Date', 'Gestational Age', 'Birth Weight', 'Gift Card Returned Notes');
        } else {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Gift Card Returned Notes');
        }

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            $item->incentive_value = "$" . $item->incentive_value;
            $item->manually_added = ($item->manually_added) ? "Y" : "N";

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
            }

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added",
                    "$item->delivery_date", "$item->gestational_age", "$item->birth_weight", "$item->gift_card_returned_notes"
                );
            } else {
                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                , "$item->gift_card_returned_notes"
                );
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function outreach_codes_report()
    {
        $outreach_codes = \OutreachCode::all()->lists('code_name', 'id');
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

        $this->layout->content = View::make('admin/report/outreach_codes/outreach_codes_report')
            ->with('outreach_codes', $outreach_codes)
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_outreach_codes_report')
            ->with('method', 'GET');
    }

    public function generate_outreach_codes_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $input['outreach_codes'] = isset($input['outreach_codes']) ? $input['outreach_codes'] : array();

        $result = \DB::table('manual_outreaches')
            ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
            ->where('manual_outreaches.program_id', '=', $input["program"])
            ->whereIn('manual_outreaches.outreach_code', $input['outreach_codes'])
            ->whereBetween('outreach_date', array($date_ranges[0], $date_ranges[1]))
            ->select('username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'outreach_date', 'code_name', 'outreach_notes');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'first_name', 'last_name')
                ->addColumn('date_of_birth', function ($model) {
                    return \Helpers::format_date_display($model->date_of_birth);
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                ->addColumn('outreach_date', function ($model) {
                    return \Helpers::format_date_display($model->outreach_date);
                })
                ->showColumns('code_name', 'outreach_notes')
                ->searchColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'outreach_date', 'code_name', 'outreach_notes')
                ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'outreach_date', 'code_name', 'outreach_notes')
                ->make();
        }

        $this->layout->content = View::make('admin/report/outreach_codes/show_outreach_codes_report')
            //->with('outreach_codes', $outreach_codes)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_outreach_codes_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'delivery_date', 'gestational_age', 'birth_weight')
                ->get();
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween('incentive_date_sent', array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'username', 'users.first_name', 'users.last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added')
                ->get();
        }

        $delimiter = ",";
        $filename = $program->name . " Incentive Report - Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script',
                'Delivery Date', 'Gestational Age', 'Birth Weight');
        } else {
            $line = array('Metric', 'Patient ID', 'Patient First Name', 'Patient Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script');
        }

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            $item->incentive_value = "$" . $item->incentive_value;
            $item->manually_added = ($item->manually_added) ? "Y" : "N";

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
            }

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added",
                    "$item->delivery_date", "$item->gestational_age", "$item->birth_weight"
                );
            } else {
                $line = array("$item->metric", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                );
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


}
