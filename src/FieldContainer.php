<?php

namespace Mmeshkatian\Ariel;
use Route;

class FieldContainer
{

    var $name;
    var $caption;
    var $validationRule;
    var $type;
    var $defaultValue;
    var $valuesList;
    var $process;
    var $forceProcess;
    var $skip;
    var $storeSkip;
    var $isRequired;

    public function __construct($name,$caption,$validationRule = '',$type = 'text' ,$defaultValue = '',$valuesList = [],$process = '',$forceProcess = false,$skip = false,$storeSkip = false)
    {
        $name = str_replace("->","__hasone__",$name);
        $this->name = $name;
        $this->caption = $caption;
        $this->validationRule = $validationRule;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
        $this->valuesList = $valuesList;
        $this->process = $process;
        $this->forceProcess = $forceProcess;
        $this->skip = $skip;
        $this->storeSkip = $storeSkip;
        $this->isRequired = !is_array($this->validationRule) ? (strpos(($this->validationRule), 'required') !== false) : in_array('required',$this->validationRule);
        if(!empty($this->validationRule['store']) || !empty($this->validationRule['update']))
            $this->isRequired = false;
    }
    public function onlyProcess($true = true)
    {
        $this->skip = $true;
        return $this;
    }
    public function ignore($true = true)
    {
        $this->storeSkip = $true;
        return $this;
    }
    public function addProcess($process)
    {
        $this->process = $process;
        return $this;
    }

    public function force($true = true)
    {
        $this->forceProcess = $true;
        return $this;
    }

    public function setValues($values)
    {
        $this->valuesList = $values;
        return $this;
    }

    public function setValue($value)
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    public function rules($rules)
    {
        $this->validationRule = $rules;
        return $this;
    }

    public function required($true = true)
    {
        $this->isRequired = $true;
        return $this;
    }

    public function addOpt($param,$value)
    {
        $this->$param = $value;
        return $this;

    }

    public function getValue($value)
    {
        if(!empty(request()->input($this->name)))
            return request()->input($this->name);
        if(!empty(old($this->name)))
            return old($this->name);

        if(is_callable($this->defaultValue))
            return ($this->defaultValue)($this,$value);

        if(!empty($this->defaultValue)) {
            $returnValue = $this->defaultValue;

            if (\Ariel::exists($this->defaultValue, "->")) {
                try {
                    $returnValue = ('$value->' . $this->defaultValue);
                    eval('$returnValue = ' . $returnValue . ' ?? "";');
                }catch (\Exception $e){
                    $returnValue = null;
                }
            }

            return $returnValue;
        }

        if(!empty($value))
            return $value->{$this->name} ?? '';


        return "";

    }


    public function getView($value)
    {
        $this->defaultValue = $this->getValue($value);

        if(view()->exists('ariel::types.'.$this->type))
            return view('ariel::types.'.$this->type,['field'=>$this]);
        else
            return view('ariel::types.text',['field'=>$this]);
    }
}
