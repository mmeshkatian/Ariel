<?php

namespace Mmeshkatian\Ariel;
use Route;

class ActionContainer
{

    var $router;
    var $action;
    var $method;
    var $icon;
    var $caption;
    var $url;
    var $defaultParms;

    public function __construct($action,$icon = '',$caption = '',$defaultParms = [],$accessControlMethod = null)
    {
        $this->router = \Route::getRoutes()->getByName($action);
        $this->action = $this->router->getName();
        $this->method = in_array("GET",$this->router->methods()) ? 'GET' : ($this->router->methods()[0] ?? 'GET');
        $this->icon = $icon;
        $this->caption = $caption;
        $this->defaultParms = $defaultParms;
        $this->accessControlMethod = $accessControlMethod;
        //$this->url = $this->getUrl();
    }

    public function addParam($param,$value)
    {
        $this->defaultParms[$param] = $value;
        return $this;
    }

    public function hasAccess($row)
    {
        if(!empty($this->accessControlMethod) && is_callable($this->accessControlMethod)){
            return ($this->accessControlMethod)($this->action,$row);
        }else return true;
    }

    public function isGet()
    {
        return $this->method == 'GET';
    }


    public function getUrl()
    {
        $paramNames = $this->router->parameterNames();

//        if(count($paramNames) > count($this->defaultParms)){
//            $remain = count($paramNames) - count($this->defaultParms);
//            for($i=1;$i <= $remain;$i++){
//                $this->defaultParms[] = 'NA';
//            }
//        }

        return route($this->action,$this->defaultParms);
    }

    public function getIcon()
    {
        return $this->icon;
    }
    public function getCaption()
    {
        return $this->icon;
    }


    public function __toString()
    {
        return $this->getUrl();
    }
}
