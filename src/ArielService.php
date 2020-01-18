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
    public function listenScript()
    {
        ob_start();
    }

    public function setScript()
    {
        $script = ob_get_clean();
        return $script;
    }
}
