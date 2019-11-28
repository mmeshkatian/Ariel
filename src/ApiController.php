<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ApiController extends Controller
{
    private function parser($function , $version , $request)
    {
        $return = ["status" => "N\A","data"=>"N\A"];
        try{

            if(!method_exists($this,$function))
                throw new \Exception("methodNotFound");

            $return['data'] = $class->$function($request);
            $return['status'] = 'OK';

        }catch (\Exception $e){
            $return = [
                "status" => "failed",
                "error_msg"=>$e->getMessage()
            ];

            if(config('app.$_DEBUG')){
                $return['errorLine'] = $e->getLine();
                $return['errorFile'] = $e->getFile();
            }

        }
        return response()->json($return, 200, array('Content-Type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_UNICODE);
    }
}
