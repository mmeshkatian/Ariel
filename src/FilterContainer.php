<?php

namespace Mmeshkatian\Ariel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Route;

class FilterContainer
{
    var $field;
    var $callable;

    /**
     * FilterContainer constructor.
     * @param FieldContainer $field
     * @param $callable
     */
    public function __construct(FieldContainer $field,$callable)
    {
        $this->field = $field;
        $this->callable = $callable;
    }

    public function render()
    {
        return $this->field->getView(request()->input($this->field->name));
    }

    public function handle(Request $request,$query)
    {
        $value = $request->input($this->field->name);
        if(empty($value))
            return;

            $query->where(function ($q) use($request,$value){

                if(is_callable($this->callable)){
                    return $this->callable($q,$request,$value);
                }else{
                    $callable = explode(".",$this->callable);
                    $operator = $callable[2] ?? '=';
                    if($operator == 'like')
                        $value = '%'.$value.'%';
                    return $q->{$callable[0] ?? 'where'}($callable[1] ?? $this->field->name,$operator,$value);
                }
            });


    }

}
