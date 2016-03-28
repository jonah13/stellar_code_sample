@extends('admin.layouts.base_iframe')

@section('content')
    <div class="page-head">
        <h4 class="orange_color">Regions: Import Visit Dates</h4>
    </div>

    <div class="cl-mcont" style="padding: 6px 0 0 0;">

        <div class="block-flat no-padding">
            <div class="content">
                <form role="form">
                    <?php
                    $current_year = date("Y");
                    ?>

                    <div class="input-group input-group-lg sepH_a">
                        {{ Form::label('date_of_service', 'Date Of Service: ', array('class' => 'control-label nopaddingtop')) }}
                        {{Form::selectRange('date_of_service', ($current_year-2), ($current_year+1), $current_year)}}
                    </div>

                    @if($program->type == \Program::TYPE_A1C)
                        <div class="input-group input-group-lg sepH_a">
                            {{ Form::label('metric', 'Metric: ', array('class' => 'control-label nopaddingtop')) }}
                            {{Form::select('metric', array(\Program::METRIC_URINE => 'Urine', \Program::METRIC_BLOOD => 'Blood', \Program::METRIC_EYE => 'Eye', \Program::METRIC_BLOOD_AND_URINE => 'Blood & Urine'), Input::old('metric'))}}
                        </div>
                    @endif

                    <div class="dropzone import_visit_dates_drop_zone"
                         data-upload-extensions="csv,xls,xlsx"
                         style="padding: 15px 8px 5px 20px">
                        <div class="btn btn-md btn-warning btn-rad">Proceed</div>
                        <input type="hidden" name="imported_file" rv-value="model:imported_file"/>
                    </div>
                </form>
                <br/><br/>

                <div class="form-group list-component" id="imported-visit-dates" style="display: none">

                    <button class="btn btn-primary add_imported_visit_dates" region_id="{{$region->id}}"
                            program_id="{{$program->id}}" type="submit">Add
                        Patients
                    </button>
                    <a class="btn btn-default cancel_importing" name="cancel" value="cancel">Cancel Import</a>
                    <br/><br/>

                    <table class="table table-striped table-bordered" width="150%">
                        <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Date Of Service</th>
                            <th>Doctor ID</th>
                            <th>Incentive Type</th>
                            <th>Incentive Value</th>
                            <th>Incentive Code</th>
                            <th>Incentive Date</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>
@stop
