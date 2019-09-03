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
    private $queryConditions = [];
    private $ListData = null;
    private $router;

    private $mainRoute;
    private $saveRoute;
    private $editRoute;
    private $updateRoute;
    private $createRoute;


    private function set($what,$data)
    {
        $this->$what = $data;
    }

    public function setModel($model)
    {
        $this->set('model',$model);
    }

    public function configure(){}


    public function getConfig()
    {
        $this->colNames = [];
        $this->cols = [];
        $this->fields = [];
        $this->actions = [];

        $config = $this->configure();
        if(empty($this->RoutePrefix)) {
            //try to autoConfigure route Names
            $RoutePrefix = \request()->route()->getName();
            $RoutePrefix = explode(".", $RoutePrefix);
            array_pop($RoutePrefix);
            $RoutePrefix = implode(".", $RoutePrefix);
            $this->RoutePrefix = $RoutePrefix;
        }

        if(empty($this->mainRoute))
            $this->mainRoute = new ActionContainer($this->RoutePrefix.'.'.Router::INDEX,'');

        if(empty($this->saveRoute))
            $this->saveRoute = new ActionContainer($this->RoutePrefix.'.'.Router::STORE,'');

        if(empty($this->editRoute))
            $this->editRoute = new ActionContainer($this->RoutePrefix.'.'.Router::EDIT,'');


        if(empty($this->updateRoute))
            $this->updateRoute = new ActionContainer($this->RoutePrefix.'.'.Router::UPDATE,'');

        if(empty($this->createRoute))
            $this->createRoute = new ActionContainer($this->RoutePrefix.'.'.Router::CREATE,'');

    }


    /**
     *
     * configuration functions
     *
     */

    protected function addColumn($name,$value)
    {
        $this->colNames[] = $name;
        $this->cols[] = $value;
    }
    protected function addQueryCondition($function,$data)
    {
        $this->queryConditions[] = ["function" => $function,"data" => $data];
    }
    protected function setListData($data)
    {
        $this->ListData = $data;
    }

    protected function addAction($action,$icon,$caption,$defaultParms = [],$accessControlMethod = null)
    {
        $this->actions [] = new ActionContainer($action,$icon,$caption,$defaultParms,$accessControlMethod);
    }

    protected function addField($name,$caption,$validationRule='',$type='text',$value = '',$values=[],$process='',$processForce=true,$skip = false,$storeSkip = false)
    {
        $arr = new FieldContainer($name,$caption,$validationRule,$type,$value,$values,$process,$processForce,$skip,$storeSkip);
        $this->fields[] = $arr;
    }

    protected function addHidden($name,$value,$process = null,$forceProcess = false,$skip = false)
    {

        $this->addField($name,"","","hidden",$value,[],$process,$forceProcess,$skip);
    }

    protected function addProcess($processName,$force = false,$skip = true)
    {
        $this->addHidden($processName,"",$processName,$force,$skip);
    }

    protected function validateRequest($request)
    {
        $valid = [];
        $this->getConfig();
        $inArray = config('ariel.inArray');

        foreach ($this->fields as $field) {
            if (!empty($field->validationRule)) {
                if(!empty($field->validationRule['store']) && $this->saveRoute->action == request()->route()->getName()){
                    $valid[$field->name] = $field->validationRule['store'];
                }elseif(!empty($field->validationRule['update']) && $this->updateRoute->action == request()->route()->getName()){
                    $valid[$field->name] = $field->validationRule['update'];
                }elseif(empty($field->validationRule['store']) && empty($field->validationRule['update'])) {
                    $valid[$field->name] = $field->validationRule;
                }
            }
            if(!empty($field->valuesList) && is_array($field->valuesList) && count($field->valuesList)){
                //if multichosen
                //add IN require operation
                $add = "in:" . implode(",", array_keys($field->valuesList));
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

    public function index(Request $request)
    {
        $this->getConfig();

        $colNames = $this->colNames;
        $cols = $this->cols;
        $fields = $this->fields;
        $model = $this->model;
        $createRoute = $this->createRoute;
        if(!empty($this->ListData)){
            $rows = $this->ListData;
        }else {
            $rows = $model::where("id", "!=", "N");
            foreach ($this->queryConditions as $queryCondition) {
                if(!empty($queryCondition['function'])) {
                    $function = $queryCondition['function'];

                    if(!empty($queryCondition['data'])){
                        $rows = $rows->$function(...($queryCondition['data']));
                    }else{
                        $rows = $rows->$function();
                    }
                }
            }
            $rows = $rows->get();
        }


        $actions = $this->actions;


        return view('ariel::index',compact('colNames','cols','fields','rows','createRoute','actions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($data = null) {

        $this->getConfig();

        $id = $data->uuid ?? $data->id ?? null;
        $fields = $this->fields;
        $mainRoute = $this->mainRoute;
        $saveRoute = empty($data) ? $this->saveRoute : new ActionContainer($this->RoutePrefix.'.'.Router::UPDATE,'','',['id'=>$id]);

        return view('ariel::create',compact('fields','mainRoute','saveRoute','id','data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$data = null,$return = false)
    {
        $this->getConfig();

        $this->validateRequest($request);

        if(!empty($data)){
            if(is_numeric($data->id))
                $data = $this->model::where("id",$data->id);
            else
                $data = $this->model::where("uuid",$data->id);

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

                //handle skip
                if ((!empty($field->skip) && $field->skip == true))
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
                if((!empty($field->type) && $field->type == 'disable'))
                    continue;

                if ((!empty($field->storeSkip) && $field->storeSkip == true))
                    continue;



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

                    $data->$name = (empty($thisValue)) ? json_encode($request->input($name)) : $thisValue;

                }elseif (!empty($field->type) && $field->type == 'password') {
                    if(!empty($thisValue) || !empty($request->input($name))){
                        $data->$name = (empty($thisValue)) ? $request->input($name) : $thisValue;
                    }
                }else{
                        $data->$name = (empty($thisValue)) ? $request->input($name) : $thisValue;
                    }


            }catch (\Exception $e){
                if(config('app.debug'))
                    dd($e);
                else
                    abort(500);
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
    public function show($id)
    {
        $this->getConfig();
        return $this->edit($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->getConfig();
        if(is_numeric($id))
            $data = $this->model::where("id",$id);
        else
            $data = $this->model::where("uuid",$id);

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
    public function update(Request $request, $id)
    {
        $this->getConfig();

        if(is_numeric($id))
            $data = $this->model::where("id",$id);
        else
            $data = $this->model::where("uuid",$id);
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
    public function destroy($id)
    {
        $this->getConfig();

        if(is_numeric($id))
            $data = $this->model::where("id",$id);
        else
            $data = $this->model::where("uuid",$id);

        if($data->count() == 0)
            return abort(404);
        $data = $data->get()->first();
        $data->delete();
        return redirect()->back()->with("success",trans('ariel.success_text'));
    }
}
