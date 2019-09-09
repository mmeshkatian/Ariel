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
        $this->isRequired = !is_array($this->validationRule) ? (strpos(($this->validationRule), 'required') !== false) : false;

    }

    public function addOpt($param,$value)
    {
        $this->$param = $value;
        return $this;

    }

    public function getValue($value)
    {
        if(is_callable($this->defaultValue))
            return ($this->defaultValue)($this,$value);

        if(!empty(old($this->name)))
            return old($this->name);


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
            return $value->{$this->name};


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
