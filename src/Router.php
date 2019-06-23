<?php

namespace Mmeshkatian\Ariel;
use Route;

class Router
{
    var $method = 'GET';
    var $name = '';
    var $prefix = '';
    var $parameters = [];
    var $RouteParameters = [];

    //actions
    const INDEX = 'index';
    const CREATE = 'create';
    const STORE = 'store';
    const SHOW = 'show';
    const EDIT = 'edit';
    const UPDATE = 'update';
    const DESTROY = 'destroy';

    //methods
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    public function __construct($prefix,$name = '',$params = [],$method = '')
    {
        $this->prefix = $prefix;
        $this->method = $method;
        $this->name = $name;
        $this->parameters = array_merge($this->parameters,$params);
    }

    public function getRoute($action = self::STORE,$method = self::GET,$params = [])
    {
        $this->name = $this->prefix.'.'.$action;
        $this->method = $method;
        $this->parameters = array_merge($this->parameters,$params);
        return $this;
    }

    public function setParm($params = [])
    {
        $this->RouteParameters = $params;
        return $this;

    }

    public function getSaveRoute()
    {
        return $this->getRoute(self::STORE,self::POST);
    }
    public function getUpdateRoute($id)
    {
        return $this->getRoute(self::UPDATE,self::PUT,["id"=>$id]);
    }

    public function getIndexRoute()
    {
        return $this->getRoute(self::INDEX,self::GET);

    }
    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        if(Route::has($this->name))
            return route($this->name,array_merge($this->RouteParameters,$this->parameters));
        else return asset('/');
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}
