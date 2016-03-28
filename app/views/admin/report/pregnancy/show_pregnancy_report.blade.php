@extends('admin.layouts.base')

@section('title'){{"$insurance_company->name, $region->name, $program->name - Program Patient Report"}}@stop

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Report</a></li>
        </ul>
    </nav>
@stop


@section('content')

    <div class="container-fluid export_report_page">
        <div class="row">
            <div class="col-md-12">

                @if($input['pregnancy_report_type'] == \Program::PREGNANCY_REPORT_ACTIVE)

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_pregnancy_report'))
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/pregnancy/show_pregnancy_report_active_datatable') }}

                    @else

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_pregnancy_report'))
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/pregnancy/show_pregnancy_report_delivery_datatable') }}

                    @endif


            </div>
        </div>
    </div>
@stop

