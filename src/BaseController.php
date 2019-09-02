<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Str;
use Route;
use File;


class BaseController extends Controller
{
    var $model;
    var $RoutePrefix;
    private $colNames = [];
    private $cols = [];
    private $fields = [];
    private $actions = [];
    private $router;

    private $mainRoute;
    private $saveRoute;
    private $updateRoute;
    private $createRoute;

    private $mainUserRoute;
    private $saveUserRoute;
    private $updateUserRoute;
    private $createUserRoute;



    public function __call($method,$args)
    {
        if(!method_exists(get_class($this),'configure') ||  !is_callable([get_class($this),'configure']))
            abort('404','Configure method not found');
        if(!in_array($method,config('ariel.configureMethods'))) {
            $config = app(get_class($this))->configure();
            foreach ($config as $val => $item) {
                $this->$val = $item;
            }
        }

        if(empty($this->RoutePrefix)) {
            //try to autoConfigure route Names
            $RoutePrefix = \request()->route()->getName();
            $RoutePrefix = explode(".", $RoutePrefix);
            array_pop($RoutePrefix);
            $RoutePrefix = implode(".", $RoutePrefix);
            $this->RoutePrefix = $RoutePrefix;
        }

        if(empty($this->saveUserRoute))
            $this->saveRoute = (new Router($this->RoutePrefix))->getSaveRoute();
        else
            $this->saveRoute = $this->saveUserRoute;

        if(empty($this->updateUserRoute))
            $this->updateRoute = (new Router($this->RoutePrefix))->getUpdateRoute($args[0] ?? 0);
        else
            $this->updateRoute = $this->updateUserRoute;

        if(empty($this->mainUserRoute))
            $this->mainRoute = (new Router($this->RoutePrefix))->getIndexRoute();
        else
            $this->mainRoute = $this->mainUserRoute;

        if(empty($this->createUserRoute))
            $this->createRoute = (new Router($this->RoutePrefix))->getRoute(Router::CREATE,Router::GET);
        else
            $this->createRoute = $this->createUserRoute;



        return call_user_func_array(array($this, $method), $args);
    }

    /**
     *
     * configuration functions
     *
     */
    private function setRoute($route,$name,$params = [])
    {
        if(!in_array($route,["save","update","main","create"]))
            return;
        $route = $route."UserRoute";
        $this->$route = (new Router("",$name,$params));
    }

    private function addColumn($name,$value)
    {
        $this->colNames[] = $name;
        $this->cols[] = $value;
    }

    private function addAction($action,$icon,$caption,$ask = false,$defaultParms = [])
    {
        $arr = [
            "action" => (new Router($this->RoutePrefix))->getRoute($action,Router::GET,$defaultParms),
            "icon" => $icon,
            "caption" => $caption,
            "ask" => $ask,
        ];

        $this->actions [] = (Object) $arr;
    }

    private function addField($name,$caption,$validationRule='',$type='text',$value = '',$values=[],$process='',$processForce=true,$skip = false)
    {

        if($value == '' || empty($value))
            $value = null;

        $arr = ["caption"=>$caption,"name"=>$name,"type"=>$type,"valid"=>$validationRule,"values"=>$values,"value"=>$value,"process"=>$process,"processForce"=>$processForce,'skip'=>$skip];
        $fields = $this->fields;
        if (strpos($arr['valid'] ?? '', 'required') !== false) {
            $arr['isRequired'] = true;
        }else{
            $arr['isRequired'] = false;
        }
        $this->fields[] = (Object) $arr;
    }

    private function addHidden($name,$value,$process = null,$forceProcess = false,$skip = false)
    {

        $this->addField($name,"","","hidden",$value,[],$process,$forceProcess,$skip);
    }

    private function addProcess($processName,$force = false,$skip = true)
    {
        $this->addHidden($processName,"",$processName,$force,$skip);
    }

    private function clear($field = true,$column = true)
    {
        if($field)
            $this->fields = [];
        if($column)
            $this->colNames = [];
        if($column)
            $this->cols = [];
    }

    private function validateRequest($request)
    {
        $valid = [];
        $inArray = config('ariel.inArray');
        foreach ($this->fields as $field) {

            if (!empty($field->valid)) {

                $valid[$field->name] = $field->valid;

            }
            if(!empty($field->values) && is_array($field->values) && count($field->values)){
                //if multichosen
                //add IN require operation
                $add = "in:" . implode(",", array_keys($field->values));
                $name = $field->name;
                if(in_array($field->type,$inArray)){
                    $name = $name.'.*';
                }

                if (empty($valid[$name])) {
                    $valid[$name] = $add;
                } elseif (is_array($valid[$name])) {
                    $valid[$name][] = $add;
                } else {
                    $valid[$name] .= '|' . $add;
                }


            }
        }

        $this->validate($request,$valid);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private function index(Request $request)
    {
        $colNames = $this->colNames;
        $cols = $this->cols;
        $fields = $this->fields;
        $model = $this->model;
        $createRoute = $this->createRoute;
        $rows = $model::get();
        $actions = $this->actions;


        return view('ariel::index',compact('colNames','cols','fields','rows','createRoute','actions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function create($data = null) {
        $id = $data->id ?? null;
        $fields = $this->fields;
        $mainRoute = $this->mainRoute;
        $saveRoute = empty($data) ? $this->saveRoute : $this->updateRoute;

        return view('ariel::create',compact('fields','mainRoute','saveRoute','id','data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function store(Request $request,$data = null,$return = false)
    {
        $this->validateRequest($request);


        if(!empty($data)){
            $data = $this->model::where("id",$data->id);
            if($data->count() == 0)
                return abort(404);
            $data = $data->get()->first();
        }else{
            $data = new $this->model();
        }

        foreach ($this->fields as $field) {
            $thisValue = null;
            $name = $field->name ?? null;
            $process = null;

            try{

                //handle skip & disable form
                if ((!empty($field->skip) && $field->skip == true) || (!empty($field->type) && $field->type == 'disable'))
                    continue;

                //handle Process
                if (!empty($field->process)) {

                    try {
                        $process = $field->process;
                        $thisValue = $this->$process($request, $request->input($field->name));
                    } catch (\Exception $e) {
                        //
                        if (!empty($field->processForce)) {
                            return redirect()->back()->with(config('ariel.danger_alert'),__($field->caption." Process failed."));
                        }
                    }
                }



                //handle hidden fields
                if (!empty($field->type) && $field->type == 'hidden') {

                    $data->$name = (empty($thisValue)) ? $field->value : $thisValue;

                }elseif (!empty($field->type) && ($field->type == 'file' || $field->type == 'image')) {


                    $file = $request->file($name);

                    if (!empty($file)) {
                        if (!empty($data->$name)) {
                           //delete old
                            if(config('ariel.delete_old_files'))
                                File::delete(config('ariel.upload_path').'/'.$data->$name);
                        }
                        //upload file
                        $fileName = Str::random(50).'.'.$file->getClientOriginalExtension();
                        $model = explode("\\",$this->model);
                        $model = end($model);

                        File::makeDirectory(config('ariel.upload_path').'/'.$model, 0777 , true, true);
                        $file->move(config('ariel.upload_path').'/'.$model,$fileName);
                        $data->$name =$model.'/'.$fileName;

                    }

                } elseif (is_array($request->input($name))) {

                    $data->$name = json_encode($request->input($name));

                }else {
                    $data->$name = (empty($thisValue)) ? $request->input($name) : $thisValue;
                }


            }catch (\Exception $e){
                dd($e);
            }
        }
        try {
            $data->save();
            foreach ($this->fields as $field) {
                if ((!empty($field->skip) && $field->skip == true)){
                    if (!empty($field->process)) {

                        try {
                            $process = $field->process;
                            $thisValue = $this->$process($request, $request->input($field->name),$data);
                        } catch (\Exception $e) {
                            //
                            if (!empty($field->processForce)) {
                                return redirect()->back()->with(config('ariel.danger_alert'),__($field->caption." Process failed."));
                            }
                        }
                    }
                }
            }

            if($return){
                return $data;
            }else{
                return redirect($this->mainRoute->getUrl())->with(config('ariel.success_alert'), trans('ariel::ariel.success_text'));
            }
        }catch (\Exception $e){
            //dd(config('ariel.danger_alert'),trans('Ariel::ariel.success_text'));
            if(config('app.debug'))
                dd($e);
            else
                return redirect($this->mainRoute->getUrl())->with(config('ariel.danger_alert'), trans('ariel::ariel.exception_text'));

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function show($id)
    {
        return $this->edit($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function edit($id)
    {
        $data = $this->model::where("id",$id);
        if($data->count() == 0)
            return abort(404);
        $data = $data->get()->first();
        return $this->create($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function update(Request $request, $id)
    {
        $data = $this->model::where("id",$id);
        if($data->count() == 0)
            return abort(404);
        $data = $data->get()->first();

        return $this->store($request,$data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function destroy($id)
    {
        $data = $this->model::where("id",$id);
        if($data->count() == 0)
            return abort(404);
        $data = $data->get()->first();
        $data->delete();
        return redirect()->back()->with("success",trans('ariel.success_text'));
    }
}
