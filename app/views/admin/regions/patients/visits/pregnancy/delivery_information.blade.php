<div class="row sepH_a">
    <div class="col-lg-6">
        <div class="input-group">
            {{ Form::label('date_added', 'Date Added to Program : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('date_added', Input::old('date_added'), array('class' => 'form-control form-control2 datepicker')) }}
            @if ($errors->has('date_added'))
                <span class="help-block">{{ $errors->first('date_added') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('due_date')) has-error @endif">
            {{ Form::label('due_date', 'Due Date : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('due_date', Input::old('due_date'), array('class' => 'form-control form-control2 datepicker')) }}
            @if ($errors->has('due_date'))
                <span class="help-block">{{ $errors->first('due_date') }}</span>
            @endif
        </div>
    </div>
</div>

<div class="row sepH_a">
    <div class="col-lg-4">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('delivery_date')) has-error @endif">
            {{ Form::label('delivery_date', 'Delivery Date : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('delivery_date', Input::old('delivery_date'), array('class' => 'form-control form-control2 datepicker')) }}
            @if ($errors->has('delivery_date'))
                <span class="help-block">{{ $errors->first('delivery_date') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('birth_weight')) has-error @endif">
            {{ Form::label('birth_weight', 'Birth Weight : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('birth_weight', Input::old('birth_weight'), array('class' => 'form-control form-control2')) }}
            @if ($errors->has('birth_weight'))
                <span class="help-block">{{ $errors->first('birth_weight') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group input-group-lg sepH_a">
            {{ Form::label('gestational_age', 'Gestational Age : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('gestational_age', Input::old('gestational_age'), array('class' => 'form-control form-control2')) }}
        </div>
    </div>
</div>

<div class="row sepH_a">
    <div class="col-lg-4">
        <div class="input-group input-group-lg sepH_a">
            {{ Form::label('pediatrician_id', 'Pediatrician ID : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('pediatrician_id', Input::old('pediatrician_id'), array('class' => 'form-control form-control2')) }}
        </div>
    </div>
    <div class="col-lg-4">
    </div>
    <div class="col-lg-4">
    </div>
</div>


<div class="row sepH_a" style="margin-top: 70px; margin-bottom: 90px !important;">
    <div class="col-lg-3">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('discontinue')) has-error @endif">
            {{ Form::checkbox('discontinue', 'true') }}
            {{ Form::label('discontinue', 'Discontinue Tracking : ', array('class' => 'control-label control-label2')) }}
            @if ($errors->has('discontinue'))
                <span class="help-block">{{ $errors->first('discontinue') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group input-group-lg sepH_a">
            {{Form::select('discontinue_reason', $discontinue_tracking_reasons, Input::old('discontinue_reason'), array('class' => 'form-control form-control2'))}}
        </div>
    </div>
</div>