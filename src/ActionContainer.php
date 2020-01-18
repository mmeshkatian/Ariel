<?php

namespace Mmeshkatian\Ariel;
use Illuminate\Support\Str;
use Route;

class ActionContainer
{

    var $router;
    var $action;
    var $method;
    var $icon;
    var $caption;
    var $url;
    var $defaultParams;
    var $options;

    public function __construct($action,$icon = '',$caption = '',$defaultParams = [],$accessControlMethod = null,$options = [])
    {

        $this->router = \Route::getRoutes()->getByName($action);
        if(empty($this->router))
            throw new \Exception("Route Not Defined {$action}");
        $this->action = $this->router->getName();

        $this->method = in_array("GET",$this->router->methods()) ? 'GET' : ($this->router->methods()[0] ?? 'GET');
        $this->icon = $icon;
        $this->caption = $caption;
        $this->defaultParams = $defaultParams;
        $this->accessControlMethod = $accessControlMethod;
        $this->options = $options;
        //$this->url = $this->getUrl();
    }

    public function addParam($param,$value)
    {
        $this->defaultParams[$param] = $value;
        $this->defaultParams = array_reverse($this->defaultParams);
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

    public function getUrl($data = null)
    {
        $defaultParams = [];
        $paramNames = $this->router->parameterNames();

        foreach ($this->defaultParams as $p=>$v){
            if(Str::contains($v,"$")) {
                $par = str_replace("$", "", $v);
                $defaultParams[$p] = $data->$par;
            }else{
                $defaultParams[$p] = $v;
            }
        }
//        $this->defaultParams = $defaultParams;


        $route = route($this->action,$defaultParams);
            return $route;
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
