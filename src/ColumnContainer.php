<?php

namespace Mmeshkatian\Ariel;
use Illuminate\Support\Arr;
use Route;

class ColumnContainer
{
    var $name;
    var $value;
    var $class;

    public function __construct($name,$value,$class)
    {
        $this->name = $name;
        $this->value = $value;
        $this->class = app($class);

    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue($data)
    {

        //check if need a callable
        $callable = $this->value;
        if(is_callable($callable) && gettype($callable) == 'object') {


            return $callable($data);
        }

        $value = explode(".",$this->value);

        if(!empty($value[1]) && method_exists($this,$value[1]))
            return $this->{$value[1]}($data,$value[0]);

        elseif(!empty($value[1]) && method_exists($this->class,$value[1]))
            return ($this->class)->{$value[1]}($data,$value[0]);

            return $data->{$this->value};
    }

    private function text($row_data,$value)
    {
        $class = $this->class;
        $class->getConfig();

        $field = array_values(Arr::where($class->get('fields'),function ($data) use($value){
           return $data->name == $value;
        }))[0] ?? null;

        $_value = $row_data->$value;
        $_value = json_decode($_value) ?? $_value;
        if(is_array($_value)){
            $val = '';
            $i = 0;
            foreach ($_value as $v) {
                $i++;
                $val .= ($field->valuesList[$v] ?? '-') .(count($_value) == $i ? '' : ' - ');
            }
        }else
            $val = $field->valuesList[$row_data->$value] ?? '-';

        return $val;
    }

}
