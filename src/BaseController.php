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
    private $title = '';

    private $mainRoute;
    private $saveRoute;
    private $editRoute;
    private $updateRoute;
    private $createRoute;
    private $isConfigCalled = false;


    private function set($what,$data)
    {
        $this->$what = $data;
    }

    public function setModel($model)
    {
        $this->set('model',$model);
    }
    public function setTitle($title)
    {
        $this->set('title',$title);
    }

    public function configure(){}


    public function getConfig()
    {
        if($this->isConfigCalled)
            return;

        $this->isConfigCalled = true;


        $colNames = $this->colNames;
        $cols = $this->cols;
        $fields = $this->fields;
        $actions = $this->actions;

        $this->colNames = [];
        $this->cols = [];
        $this->fields = [];
        $this->actions = [];

        $config = $this->configure();

        $this->colNames = array_merge($this->colNames,$colNames);
        $this->cols = array_merge($this->cols,$cols);
        $this->fields = array_merge($this->fields,$fields);
        $this->actions = array_merge($this->actions,$actions);


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

    protected function addAction($action,$icon,$caption,$defaultParms = [],$accessControlMethod = null,$options = [])
    {
        $this->actions [] = new ActionContainer($action,$icon,$caption,$defaultParms,$accessControlMethod,$options);
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

        }

        if($request->input('trash') == '1'){

            try {
                $rows = $rows->onlyTrashed();
            }catch (\Exception $e){
                $rows = $rows->where("id","NN");
            }
            $this->actions = [];
            if(\Route::has($this->RoutePrefix.'.destroy')) {

                $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-refresh-cw"></i>', 'Restore', ['restore' => '1'], null, ['class' => 'ask']);
                $user = \Auth::guard('internal_users')->user();
                if ($user->hasRole('SuperAdmin'))
                    $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-trash"></i>', 'Permanent Delete', ['perm' => '1'], null, ['class' => 'ask']);
            }
        }
        $rows = $rows->get();

        $actions = $this->actions;


        $breadcrumbs = [
            ['name'=>'Deartime Administration','link'=>route('admin.dashboard.main')],
            ['name'=>$this->title,'link'=>$this->mainRoute],
            ['name'=>$this->title.' List','link'=>url()->current()],
        ];
        $title = $this->title.' List';
        return view('ariel::index',compact('colNames','cols','fields','rows','createRoute','actions','breadcrumbs','title'));
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
        $breadcrumbs = [
            ['name'=>'Deartime Administration','link'=>route('admin.dashboard.main')],
            ['name'=>$this->title,'link'=>$this->mainRoute],
            ['name'=> (empty($data) ? 'Create a ' : 'Edit ').$this->title,'link'=>url()->current()],
        ];
        $title = (empty($data) ? 'Create a ' : 'Edit ').$this->title;
        return view('ariel::create',compact('fields','mainRoute','saveRoute','id','data','breadcrumbs','title'));
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

        $foriegnDataTable = [];
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

                if(\Ariel::exists($field->name,"__hasone__")){

                    $relName = explode("__hasone__",$field->name);
                    $namee = $relName[0] ?? '';
                    $key = $relName[1] ?? '';

                    $foriegnDataTable[$namee.'|'.$key] = $data->$name ;
                    unset($data->$name);
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
            $foriegnData = [];
            foreach ($this->fields as $field) {
                //handle hasone relations
                if(\Ariel::exists($field->name,"__hasone__")){
                    $relName = explode("__hasone__",$field->name);
                    $name = $relName[0] ?? '';
                    $key = $relName[1] ?? '';
                    if(empty($name) || empty($key))
                        continue;


                    $ForeignKeyName = $data->$name() ?? '';
                    if(empty($ForeignKeyName))
                        continue;
                    $ForeignKeyName = $ForeignKeyName->getForeignKeyName();

                    $Related = $data->$name()->getRelated();
                    $ParentKey = $data->$name()->getLocalKeyName();

                    if(empty($foriegnData[$name])) {
                        $foriegnData[$name] = $Related->where($ForeignKeyName, $data->$ParentKey)->get()->first();

                        if (empty($foriegnData[$name]))
                            $foriegnData[$name] = new $Related();
                    }


                    $foriegnData[$name]->$ForeignKeyName = $data->$ParentKey;
                    $foriegnData[$name]->$key = $foriegnDataTable[$name.'|'.$key] ?? null;
//                    $foriegnData[$name]->save();


                }

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
            foreach ($foriegnData as $fd) {
                $fd->save();
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
    public function destroy(Request $request,$id)
    {
        $this->getConfig();
        if($request->input('restore') == '1' || $request->input('perm') == '1'){
            try {
                if (is_numeric($id))
                    $data = $this->model::where("id", $id)->withTrashed();
                else
                    $data = $this->model::where("uuid", $id)->withTrashed();
            }catch (\Exception $e){
                return abort(404);
            }
            if($data->count() == 0)
                return abort(404);

            $data = $data->get()->first();

            if($request->input('restore') == '1')
                $data->restore();
            else{
                $user = \Auth::guard('internal_users')->user();
                if($user->hasRole('SuperAdmin'))
                    $data->forceDelete();
                else abort("403");
            }


            return redirect()->back()->with("success",trans('ariel::ariel.success_text'));
        }

        if(is_numeric($id))
            $data = $this->model::where("id",$id);
        else
            $data = $this->model::where("uuid",$id);

        if($data->count() == 0)
            return abort(404);
        $data = $data->get()->first();
        $data->delete();
        return redirect()->back()->with("success",trans('ariel::ariel.success_text'));
    }
}
