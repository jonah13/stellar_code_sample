<?php

class InsuranceCompany extends Eloquent
{
    protected $fillable = array('name');

    protected static $rules = array(
        'name' => 'required|min:5|max:255'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['name'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

    public function regions()
    {
        return $this->hasMany('Region');
    }

    public function patients()
    {
        return $this->hasMany('User')->where('region_id', '<>', 'NULL');
    }

    public function clients()
    {
        return $this->hasMany('User')->where('region_id', '=', Null);
    }

    public function get_regions_as_key_value_array()
    {
        $regions_obj = $this->regions;
        $regions = [];
        foreach ($regions_obj as $region) {
            $regions[$region->id] = $region->name;
        }

        return $regions;
    }

}
