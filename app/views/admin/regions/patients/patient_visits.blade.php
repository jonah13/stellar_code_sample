@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.programs.index') }}">Regions</a></li>
            <li><a href="#">Patient Visits</a></li>
        </ul>
    </nav>
@stop


@section('content')

    <div class="row sepH_a" style="margin-bottom: 30px !important;">
        <div class="col-lg-2">
            Patient ID: {{$patient->username}}
        </div>
        <div class="col-lg-3">
            {{"$patient->last_name $patient->first_name"}}
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} contact dates
                    for {{$program->name}} program</span><br/><br/>
                <table id="datatable_previous_contacts" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contact Tool</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($previous_contacts as $previous_contact)
                        <tr>
                            <td>{{$previous_contact->created_at}}</td>
                            <td>{{\twilio::contact_tool_toString($previous_contact->contact_tool)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <br/><br/>

                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} actual visit dates
                    for {{$program->name}} program</span><br/><br/>
                <table id="datatable_actual_visits" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Incentive Type</th>
                        <th>Gift Card Serial</th>
                        <th>Incentive Date Sent</th>
                        @if($program->type==Program::TYPE_A1C)
                            <th>Metric</th>
                        @endif
                        <th>Notes</th>
                        <th>Doctor ID</th>
                        <th style="width: 120px; text-align: center;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($actual_visits as $actual_visit)
                        @if($actual_visit->actual_visit_date!=="0000-00-00 00:00:00" && $actual_visit->actual_visit_date !=null)
                            <tr>
                                <td>{{\Helpers::format_date_display($actual_visit->actual_visit_date)}}</td>
                                <td>{{$actual_visit->incentive_type}}</td>
                                <td>{{$actual_visit->gift_card_serial}}</td>
                                <td>{{\Helpers::format_date_display($actual_visit->incentive_date_sent)}}</td>
                                @if($program->type==Program::TYPE_A1C)
                                    <?php
                                    $metric = 'Undefined';
                                    switch ($actual_visit->metric) {
                                        case \Program::METRIC_URINE:
                                            $metric = 'Urine';
                                            break;
                                        case \Program::METRIC_BLOOD:
                                            $metric = 'Blood';
                                            break;
                                        case \Program::METRIC_EYE:
                                            $metric = 'Eye';
                                            break;
                                        case \Program::METRIC_BLOOD_AND_URINE:
                                            $metric = 'Blood & Urine';
                                            break;
                                    }
                                    ?>

                                    <td>{{$metric}}</td>
                                @endif
                                <td>{{$actual_visit->visit_notes}}</td>
                                <td>{{$actual_visit->doctor_id}}</td>

                                <td style="text-align: center;">
                                    <a href="{{ URL::route('admin.patient_program_visits.edit', array($patient->id, $program->id, $actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary" type="button">Edit</a>
                                    <a href="{{ URL::route('admin.patient_program_visits.destroy', array($actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary delete_actual_visit" type="button"
                                       data-action="remove">Delete</a>
                                </td>

                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>

                <br/><br/>

                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} returned gift cards
                    for {{$program->name}} program</span><br/><br/>
                <table id="datatable_returned_gift_cards" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">

                    <thead>
                    <tr>
                        <th>Actual Visit Date</th>
                        <th>Incentive Type</th>
                        <th>Gift Card Serial</th>
                        <th>Incentive Date Sent</th>
                        <th>Gift Card Returned Notes</th>
                        <th style="width: 150px; text-align: center;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($actual_visits as $actual_visit)
                        @if($actual_visit->actual_visit_date!=="0000-00-00 00:00:00" && $actual_visit->actual_visit_date !=null && $actual_visit->gift_card_returned)
                            <tr>
                                <td>{{\Helpers::format_date_display($actual_visit->actual_visit_date)}}</td>
                                <td>{{$actual_visit->incentive_type}}</td>
                                <td>{{$actual_visit->gift_card_serial}}</td>
                                <td>{{\Helpers::format_date_display($actual_visit->incentive_date_sent)}}</td>
                                <td>{{$actual_visit->gift_card_returned_notes}}</td>

                                <td style="text-align: center;">
                                    <a href="{{ URL::route('admin.patient_program_visits.edit', array($patient->id, $program->id, $actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary" type="button">Edit</a>
                                    <a href="{{ URL::route('admin.patient_program_visits.destroy', array($actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary delete_actual_visit" type="button"
                                       data-action="remove">Delete</a>
                                </td>

                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>


                @if($program->type==Program::TYPE_PREGNANCY)
                    <br/><br/>

                    <span class="heading_text">{{"$patient->last_name $patient->first_name"}} scheduled visit dates
                    for {{$program->name}} program</span><br/><br/>
                    <table id="datatable_actual_visits" class="table table-striped table-bordered" cellspacing="0"
                           width="100%">
                        <thead>
                        <tr>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($actual_visits as $actual_visit)
                            @if($actual_visit->scheduled_visit_date!=="0000-00-00 00:00:00" && $actual_visit->scheduled_visit_date !=null && !$actual_visit->sign_up)
                                <tr>
                                    <td>{{\Helpers::format_date_display($actual_visit->scheduled_visit_date)}}</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>

                @endif

                <br/><br/>

                <span class="heading_text">Manual Outreaches</span><br/><br/>
                <table id="datatable_manual_outreaches" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Outreach Date</th>
                        <th>Outreach Code</th>
                        <th>Outreach Notes</th>
                        <th>Created By</th>
                        <th style="width: 120px; text-align: center;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($manual_outreaches as $manual_outreach)
                        <tr>
                            <td>{{\Helpers::format_date_display($manual_outreach->outreach_date)}}</td>
                            <td>{{$manual_outreach->code_name}}</td>
                            <td>{{$manual_outreach->outreach_notes}}</td>
                            <td>{{$manual_outreach->created_by}}</td>
                            <td style="text-align: center;">
                                <a href="{{ URL::route('admin.manual_outreaches.edit', array($patient->id, $program->id, $manual_outreach->id)) }}"
                                   class="btn btn-sm btn-primary" type="button">Edit</a>
                                <a href="{{ URL::route('admin.manual_outreaches.destroy', array($manual_outreach->id)) }}"
                                   class="btn btn-sm btn-primary" type="button" data-action="remove">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>


                @if($program->type==Program::TYPE_PREGNANCY)
                    @include('admin/regions/patients/visits/pregnancy')
                @elseif($program->type==Program::TYPE_POSTPARTUM)
                    @include('admin/regions/patients/visits/post_partum')
                @elseif($program->type==Program::TYPE_A1C)
                    @include('admin/regions/patients/visits/a1c')
                @else
                    @include('admin/regions/patients/visits/generic')
                @endif

            </div>
        </div>
    </div>

@stop



@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>

    <script>
        $(function () {
            var table = $('#datatable_previous_contacts').DataTable({
                "iDisplayLength": 25,
                "stateSave": true
            });

            var table2 = $('#datatable_actual_visits').DataTable({
                "iDisplayLength": 25,
                "stateSave": true
            });

            var table3 = $('#datatable_manual_outreaches').DataTable({
                "iDisplayLength": 25,
                "stateSave": true
            });
        })
    </script>
@stop