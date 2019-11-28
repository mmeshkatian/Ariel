<?php

namespace Mmeshkatian\Ariel;

class FormBuilder {
    var $fields = [];
    public function addField($name,$caption,$validationRule='',$type='text',$value = '',$values=[],$process='',$processForce=true,$skip = false,$storeSkip = false)
    {
        $arr = new FieldContainer($name,$caption,$validationRule,$type,$value,$values,$process,$processForce,$skip,$storeSkip);
        $this->fields[] = $arr;
        return end($this->fields);
    }

    public function render($data = null)
    {
        $fields =  $this->fields;
        return view('vendor.ariel.fields',compact('fields','data'));
    }

}

