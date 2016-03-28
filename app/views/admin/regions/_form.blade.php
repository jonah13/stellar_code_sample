{{ Form::model($region, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

<div class="input-group input-group-lg sepH_a @if ($errors->has('name')) has-error @endif">
    {{ Form::label('name', 'Name : ', array('class' => 'control-label')) }}
    {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
    @if ($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
    @endif
</div>

<div class="input-group input-group-lg">
    {{ Form::label('insurance_company', 'Insurance Company : ', array('class' => 'control-label')) }}
    {{Form::select('insurance_company', $insurance_companies,  (isset($region->insurance_company()->first()->id))?$region->insurance_company()->first()->id:Input::old('insurance_company'), array('class' => 'form-control'))}}
</div>

<div class="sepH_c text-right"></div>

<!-- include('admin/modals/programs_modal') -->

<div class="form-group sepH_c">
    <button type="submit" class="btn btn-lg btn-primary ">Save</button>
    <a href="{{ URL::route('admin.regions.index') }}" class="btn btn-lg btn-default ">Cancel</a>
</div>
{{ Form::close() }}

