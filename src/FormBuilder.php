<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use File;

class FormBuilder {

    var $fields = [];
    var $fullRender = false;
    var $route = '';
    var $title = '';
    var $breadcrumbs = [];

    /**
     * FormBuilder constructor.
     * @param bool $fullRender
     * @param ActionContainer $route
     */
    public function __construct($fullRender = false,ActionContainer $route = null)
    {
        $this->fullRender = $fullRender;
        $this->route = $route;
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this,$name) && is_callable($this,$name))
            return $this->$name(...$arguments);

        if(Str::contains($name,'add'))
            return $this->addField(...$arguments)->setType(strtolower(str_replace("add","",$name)));
    }

    /**
     * @param $name
     * @param $caption
     * @param string $validationRule
     * @param string $type
     * @param string $value
     * @param array $values
     * @param string $process
     * @param bool $processForce
     * @param bool $skip
     * @param bool $storeSkip
     * @return FieldContainer
     */
    public function addField($name,$caption,$validationRule='',$type='text',$value = '',$values=[],$process='',$processForce=true,$skip = false,$storeSkip = false)
    {
        $arr = new FieldContainer($name,$caption,$validationRule,$type,$value,$values,$process,$processForce,$skip,$storeSkip);
        $this->fields[] = $arr;
        return end($this->fields);
    }

    /**
     * @param array $data
     */
    public function setFields(array $data)
    {
        $this->fields = $data;
    }

    public function setTile($title)
    {
        $this->title = $title;
    }

    /**
     * @param null $data
     * @param null $script
     * @param array $breadcrumbs
     * @return Response
     */
    public function render($data = null,$script = null,$breadcrumbs = [],$sections = [])
    {
        $fields =  $this->fields;
        $saveRoute = $this->route;
        $title = $this->title;
        
        return view($this->fullRender ? 'vendor.ariel.create' : 'vendor.ariel.fields',compact('fields','data','saveRoute','script','breadcrumbs','sections','title'));
    }

    public function store($model)
    {
        $this->validate();


    }

    public function validate()
    {
        Validator::make(request()->all(),$this->getValidationRules())->validate();
    }

    public function getValidationRules()
    {
        $valid = [];
        foreach ($this->fields as $field) {
            $rule = $field->validationRule;

            if($field->isRequired) {
                $valid[$field->name][] = 'required';
            }
            if($field->type == 'image'){
                $valid[$field->name][] = 'mimes:jpeg,jpg,png,gif';
            }

            if(empty($rule))
                continue;
            if(is_array($rule))
                $valid[$field->name] = $rule;
            else
                $valid[$field->name] = explode("|",$rule);

            if(is_array($field->valuesList) && count($field->valuesList) > 0){
                $add = "in:" . implode(",", array_keys($field->valuesList));
                $name = $field->name;
                if(in_array($field->type,config('ariel.inArray'))){
                    $name = $name.'.*';
                }
                $valid[$name][] = $add;
            }
            foreach ($valid[$field->name] as $s=>$v) {
                if (is_string($v) && Str::contains($v,'unique')){
                    $valid[$field->name][$s] = $v.','.request()->input($field->name);
                }
            }



        }
        return $valid;
    }

}

