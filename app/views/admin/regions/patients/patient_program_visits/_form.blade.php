{{ Form::model($patient_program_visit, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('actual_visit_date', 'Actual Visit Date : ', array('class' => 'control-label')) }}
    {{ Form::text('actual_visit_date', Input::old('actual_visit_date'), array('class' => 'form-control datepicker')) }}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('doctor_id', 'Doctor Id : ', array('class' => 'control-label')) }}
    {{ Form::text('doctor_id', Input::old('doctor_id'), array('class' => 'form-control')) }}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('incentive_type', 'Incentive Type : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_type', Input::old('incentive_type'), array('class' => 'form-control')) }}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('incentive_value', 'Incentive Value : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_value', Input::old('incentive_value'), array('class' => 'form-control')) }}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('gift_card_serial', 'Gift Card Serial : ', array('class' => 'control-label')) }}
    {{ Form::text('gift_card_serial', Input::old('gift_card_serial'), array('class' => 'form-control')) }}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('incentive_date_sent', 'Incentive Date Sent : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_date_sent', Input::old('incentive_date_sent'), array('class' => 'form-control datepicker')) }}
</div>

@if($program->type == \Program::TYPE_A1C)
    <div class="input-group input-group-lg sepH_a">
        {{ Form::label('metric', 'Metric: ', array('class' => 'control-label nopaddingtop')) }}
        {{Form::select('metric', array(\Program::METRIC_URINE => 'Urine', \Program::METRIC_BLOOD => 'Blood', \Program::METRIC_EYE => 'Eye', \Program::METRIC_BLOOD_AND_URINE => 'Blood & Urine'), Input::old('metric'))}}
    </div>
@endif

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('visit_notes', 'Visit Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('visit_notes', Input::old('visit_notes'), array('class' => 'form-control')) }}
</div>

<div class="sepH_c text-right"></div>

