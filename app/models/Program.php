<?php

class Program extends Eloquent
{
    const PER_WEEK = 1;
    const PER_MONTH = 2;
    const PER_YEAR = 3;

    const TYPE_OTHER = 0;
    const TYPE_PREGNANCY = 1;
    const TYPE_POSTPARTUM = 2;
    const TYPE_A1C = 3;

    const METRIC_NULL = 0;
    const METRIC_URINE = 1;
    const METRIC_BLOOD = 2;
    const METRIC_EYE = 3;
    const METRIC_BLOOD_AND_URINE = 4;

    const PREGNANCY_REPORT_ACTIVE = 0;
    const PREGNANCY_REPORT_DELIVERY = 1;

    protected $fillable = array('name', 'type', 'notes', 'sms_content', 'call_text', 'call_mp3', 'email_content', 'contact_frequency_times', 'contact_frequency_period', 'visit_requirement_times', 'visit_requirement_period');

    protected static $rules = array(
        'name' => 'required|min:2|max:255'
    );

    public function region()
    {
        return $this->belongsTo('Region');
    }

    public function patients()
    {
        return $this->belongsToMany('User', 'patient_program', 'program_id', 'patient_id');
    }

    public function practice_groups()
    {
        return $this->belongsToMany('PracticeGroup', 'practice_group_program', 'program_id', 'practice_group_id');
    }

    public function patient_notes($patient_id)
    {
        return \DB::table('patient_program')->select('patient_notes')->where('patient_id', '=', $patient_id)->where('program_id', '=', $this->id)->first();
    }

    public function patient_program($patient_id)
    {
        return \DB::table('patient_program')->where('patient_id', '=', $patient_id)->where('program_id', '=', $this->id)->first();
    }

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['name'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

    public function contact_frequency()
    {
        return $this->contact_frequency_times . " time" . (($this->contact_frequency_times !== 1) ? 's' : '') . " per " . $this->getPeriod($this->contact_frequency_period);
    }

    public function visit_requirement()
    {
        return $this->visit_requirement_times . " time" . (($this->visit_requirement_times !== 1) ? 's' : '') . " per " . $this->getPeriod($this->visit_requirement_period);
    }

    public function type()
    {
        switch ($this->type) {
            case self::TYPE_OTHER:
                return 'Other';
            case self::TYPE_PREGNANCY:
                return 'Pregnancy';
            case self::TYPE_POSTPARTUM:
                return 'Postpartum';
            case self::TYPE_A1C:
                return 'A1C';
        }

        return 'Undefined';
    }

    private function getPeriod($period)
    {
        if ($period == $this::PER_WEEK) {
            return 'week';
        } else if ($period == $this::PER_MONTH) {
            return 'month';
        } else if ($period == $this::PER_YEAR) {
            return 'year';
        }
        return '';
    }

}
