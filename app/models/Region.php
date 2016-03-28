<?php

class Region extends Eloquent
{
    protected $fillable = array('name');

    protected static $rules = array(
        'name' => 'required|min:5|max:255'
    );

    public function insurance_company()
    {
        return $this->belongsTo('InsuranceCompany');
    }

    public function programs()
    {
        return $this->hasMany('Program');
    }

    public function practiceGroups()
    {
        return $this->hasMany('PracticeGroup');
    }

    public function patients()
    {
        return $this->hasMany('User');
    }

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['name'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

    public function get_programs_as_key_value_array()
    {
        $programs_obj = $this->programs;
        $programs = [];
        foreach ($programs_obj as $program) {
            $programs[$program->id] = $program->name;
        }

        return $programs;
    }
}
