<div class="scheduled_visit_fields_row">
    <div class="row sepH_a">
        <div class="col-lg-4">

            <div class="input-group input-group-lg sepH_a">
                <a data_href="#" class="remove_scheduled_row remove-option"><i
                            class="icon_minus_alt"></i></a>
                {{ Form::label('scheduled_visit[]', 'Scheduled Visit: ', array('class' => 'control-label control-label2')) }}
                {{ Form::text('scheduled_visit[]', \Helpers::format_date_display($actual_visit->scheduled_visit_date), array('class' => 'form-control form-control2 datepicker', 'autocomplete' => 'off')) }}
            </div>
        </div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('actual_visit[]', 'Actual Visit', array('class' => 'control-label')) }}
                {{ Form::text('actual_visit[]', \Helpers::format_date_display($actual_visit->actual_visit_date), array('class' => 'form-control form-control2 datepicker', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('doctor_id[]', 'Doctor Id', array('class' => 'control-label')) }}
                {{ Form::text('doctor_id[]', $actual_visit->incentive_type, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('incentive_type[]', 'Incentive Type', array('class' => 'control-label')) }}
                {{ Form::text('incentive_type[]', $actual_visit->incentive_type, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('incentive_value[]', 'Incentive Value: ', array('class' => 'control-label')) }}
                {{ Form::text('incentive_value[]', $actual_visit->incentive_value, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('gift_card_serial[]', 'Gift Card Serial: ', array('class' => 'control-label')) }}
                {{ Form::text('gift_card_serial[]', $actual_visit->gift_card_serial, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('incentive_date[]', 'Incentive Date: ', array('class' => 'control-label')) }}
                {{ Form::text('incentive_date[]', \Helpers::format_date_display($actual_visit->incentive_date_sent), array('class' => 'form-control form-control2 datepicker', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('visit_notes[]', 'Visit Notes ', array('class' => 'control-label')) }}
                {{ Form::text('visit_notes[]', $actual_visit->visit_notes, array('class' => 'form-control form-control2', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>


    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::checkbox('gift_card_returned[]', 'true', null, array('class' => 'gift_card_returned')) }}
                {{ Form::label('gift_card_returned[]', 'Gift Card Returned', array('class' => 'control-label control-label2')) }}
            </div>
        </div>
    </div>


    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('gift_card_returned_notes[]', 'Incentive Returned Notes', array('class' => 'control-label')) }}
                {{ Form::text('gift_card_returned_notes[]', Input::old('gift_card_returned_notes[]'), array('class' => 'form-control gift_card_returned_related_field', 'disabled')) }}
            </div>
        </div>
    </div>

    <div class="manual_outreach_rows">

        <div class="manual_outreach_row">

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        {{ Form::checkbox('manual_outreach[]', 'true', null, array('class' => 'manual_outreach_field')) }}
                        {{ Form::label('manual_outreach[]', 'Manual Outreach', array('class' => 'control-label control-label2')) }}
                    </div>
                </div>
            </div>

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        {{ Form::label('manual_outreach_date[]', 'Outreach Date', array('class' => 'control-label')) }}
                        {{ Form::text('manual_outreach_date[]', Input::old('manual_outreach_date'), array('class' => 'form-control datepicker manual_outreach_related_field', 'disabled')) }}
                    </div>
                </div>
            </div>

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        {{ Form::label('manual_outreach_code[]', 'Outreach Code', array('class' => 'control-label')) }}
                        {{Form::select('manual_outreach_code[]', $outreach_codes, Input::old('manual_outreach_code'), array('class' => 'form-control form-control2 manual_outreach_related_field', 'disabled'))}}
                    </div>
                </div>
            </div>

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        {{ Form::label('manual_outreach_notes[]', 'Outreach Notes', array('class' => 'control-label')) }}
                        {{ Form::text('manual_outreach_notes[]', Input::old('manual_outreach_notes'), array('class' => 'form-control manual_outreach_related_field', 'disabled')) }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                <a class="btn btn-sm btn-primary add_new_outreach">Add New Outreach</a>
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::checkbox('manually_added[]', 'true') }}
                {{ Form::label('manually_added[]', 'Manually Added', array('class' => 'control-label control-label2')) }}
            </div>
        </div>
    </div>

</div>