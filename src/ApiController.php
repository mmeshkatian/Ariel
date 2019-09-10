<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class ApiController extends Controller
{

    public function parser(Request $request , $version , $function)
    {
        $responseCode = 200;
        $function = str_replace("-","",$function);
        $return = ["status" => "N\A","data"=>"N\A"];
        try {

            if (!method_exists($this, $function))
                throw new ApiException("methodNotFound",404);

            $return['data'] = $this->$function($request);
            $return['status'] = 'OK';

            return response()->json($return, 200, array('Content-Type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_UNICODE);


        }catch (ApiException $e){
            $return = [
                "status" => "failed",
                "error_msg"=>$e->getMessage(),
                "details"=>$e->extraDetails,
            ];
            return response()->json($return, $e->status, array('Content-Type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_UNICODE);
        }
        catch (ValidationException $e){
            $return = [
                "status" => "failed",
                "error_msg"=>$e->getMessage(),
                "details"=>$e->errors(),
            ];
            return response()->json($return, 422, array('Content-Type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_UNICODE);

        }
        catch (\Exception $e){

            $return = [
                "status" => "failed",
                "error_msg"=>$e->getMessage(),
                "details"=>[],
            ];
            return response()->json($return, 400, array('Content-Type' => 'application/json; charset=utf-8'), JSON_UNESCAPED_UNICODE);

        }
    }



    public function checkLogin()
    {
        $user = auth('api')->user();
        if(empty($user))
            throw new ApiException("Authentication Required",401);
        return $user;
    }
}
