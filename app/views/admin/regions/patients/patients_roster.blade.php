<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>Patients Roster</li>
        </ul>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                {{ Datatable::table()
->setUrl(route('admin.regions.patients_roster', $region->id))
->setId('datatable_patients_roster')
->setCustomValues('isSysAdmin', $isSysAdmin)
->setCustomValues('region_id', $region->id)
->render('admin/regions/patients/patients_datatable') }}

            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>

    <script>
        mytable = $('#datatable_patients_roster').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null, null, null, null, null, null, null, null, null, null, null
            ],
            dom: 'T<"clear">lfrtip',
            tableTools: {
                "sSwfPath": "{{asset('assets/lib/DataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf')}}",
                "aButtons": [
                    {
                        "sExtends": "csv",
                        "sButtonText": "Export to Excel",
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                ]
            }
        });

    </script>
@stop