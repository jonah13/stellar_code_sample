@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>Edit</li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/regions/_form')
@stop
