<?php

namespace Mmeshkatian\Ariel;


class ArielService
{

    public function exists($haystack,$needle){
        return strpos( $haystack, $needle ) !== false;
    }
    public function getdata($text,$type){
        $exp = explode($type,$text);
        return $exp[0] ?? '';
    }
    public function searchIn($array,$field,$search){
        foreach ($array as $item) {
            if($item->$field == $search)
                return $item;
        }
        return false;
    }

    public function Resource($name,$controller)
    {
        \Route::resource($name, $controller);
        \Route::get($name.'/{id}/destroy',$controller.'@destroy')->name($name.'.'.Router::DESTROY);

    }


}
