<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Route;
use File;
use Config;


class BaseController extends Controller
{
    var $model;
    var $RoutePrefix;

    private $colNames = [];
    private $cols = [];
    private $fields = [];
    private $actions = [];
    private $scripts = [];
    private $queryConditions = [];
    private $ListData = null;
    private $router;
    private $title = '';
    private $BladeSettings = [];
    private $isSingleRow = false;
    private $mainRoute;
    private $saveRoute;
    private $editRoute;
    private $updateRoute;
    private $createRoute;
    private $hideMainRoute  = false;
    private $isConfigCalled = false;

    protected function set($what,$data)
    {
        $this->$what = $data;
    }
    protected function get($what)
    {
        return $this->$what;
    }
    public function setModel($model)
    {
        $this->set('model',$model);
    }
    public function setTitle($title)
    {
        $this->set('title',$title);
    }
    public function addBladeSetting($key,$value)
    {
        $settings = $this->BladeSettings;
        $settings[$key] = $value;
        $this->set('BladeSettings',$settings);

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
     * configuration functions
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
    protected function addScript($script,$in = 'all'){
        $this->scripts [] =  ['script'=>$script,'in'=>$in];
    }
    protected function addAction($action,$icon,$caption,$defaultParms = [],$accessControlMethod = null,$options = [])
    {
        $this->actions [] = new ActionContainer($action,$icon,$caption,$defaultParms,$accessControlMethod,$options);
    }
    protected function addField($name,$caption,$validationRule='',$type='text',$value = '',$values=[],$process='',$processForce=true,$skip = false,$storeSkip = false)
    {
        $arr = new FieldContainer($name,$caption,$validationRule,$type,$value,$values,$process,$processForce,$skip,$storeSkip);
        $this->fields[] = $arr;
        return end($this->fields);
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
    public function CRUDPage(callable $config,$data = null,$page = 'index')
    {
        $this->getConfig();
        $this->colNames = [];
        $this->cols = [];
        $this->fields = [];
        $this->actions = [];
        $this->queryConditions = [];
        $config();
        $this->isConfigCalled = true;
        $request = request();
        $method = $request->getMethod();

        switch ($page){
            case 'create':
                return $this->create();
            break;
            case 'edit':
                return $this->edit($data->id);
            break;
            case 'store':
                return $this->store($request);
            break;
            case 'update':
                return $this->update($request,$data->id);
            break;
            default :
                return $this->index($request);

        }

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
        if($this->isSingleRow) {
            $data = $model::get()->first();
            return $this->create($data);
        }

        if(!empty($this->ListData)){
            $rows = $this->ListData;
        }else {
            $rows = $model::orderBy("created_at","desc")->where("id", "!=", "N");
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
                $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-trash"></i>', 'Permanent Delete', ['perm' => '1'], null, ['class' => 'ask']);
            }
        }
        $rows = $rows->get();

        $actions = $this->actions;
        $breadcrumbs = [
            ['name'=>'مدیریت','link'=>route('admin.dashboard.main')],
            ['name'=>$this->title,'link'=>$this->mainRoute],
            ['name'=>'لیست '.$this->title,'link'=>url()->current()],
        ];
        $title = 'لیست '.$this->title;
        $mainRoute = $this->mainRoute;
        $saveRoute = $this->saveRoute;
        $data = null;
        $BladeSettings = $this->BladeSettings;

        $scripts = $this->scripts;
        return view('ariel::index',compact('colNames','cols','fields','rows','createRoute','actions','mainRoute','saveRoute','data','BladeSettings','title','breadcrumbs','scripts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($data = null) {

        $this->getConfig();

        $id = $data->uuid ?? $data->id ?? null;
        if(!empty($data) && empty($id))
            $data = null;
        $fields = $this->fields;
        $mainRoute = $this->mainRoute;
        $parameters = request()->route()->parameters();
        $extraP = [$id];
        if(count($parameters) > 1){
            $extraP[] =Arr::first($parameters);

            $extraP = array_reverse($extraP);

        }

        $saveRoute = empty($data) ? $this->saveRoute : new ActionContainer($this->RoutePrefix.'.'.Router::UPDATE,'','',$extraP);
        $BladeSettings = $this->BladeSettings;
        $breadcrumbs = [
            ['name'=>'مدیریت','link'=>route('admin.dashboard.main')],
            ['name'=>$this->title,'link'=>$this->mainRoute],
            ['name'=> (empty($data) ? 'ثبت ' : 'ویرایش ').$this->title,'link'=>url()->current()],
        ];
        $title = (empty($data) ? 'ثبت ' : 'ویرایش ').$this->title;
        $script = $this->scripts;
        return view('ariel::create',compact('fields','mainRoute','saveRoute','id','data','breadcrumbs','title','BladeSettings','script'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$data = null,$return = false)
    {
        $edit = false;
        $this->getConfig();
        Config::set('ariel.upload_path',public_path(config('ariel.upload_path')));

        $this->validateRequest($request);


        if(!empty($data) && !empty($data->id)){
            if(is_numeric($data->id))
                $data = $this->model::where("id",$data->id);
            else
                $data = $this->model::where("uuid",$data->id);

            if($data->count() == 0)
                return abort(404);
            $data = $data->get()->first();
            $edit = true;
        }else{
            $data = new $this->model();
        }

        $foriegnDataTable = [];

        foreach ($this->fields as $field) {
            $thisValue = null;
            $name = $field->name ?? null;
            $process = null;



                //handle skip
                if ((!empty($field->skip) && $field->skip == true)) {

                    continue;
                }

                //handle Process
                if (!empty($field->process)) {

                    try {
                        $process = $field->process;
                        $thisValue = $this->$process($request, $request->input($field->name));
                    } catch (\Exception $e) {
                        //
                        if (($field->forceProcess)) {
                            throw ValidationException::withMessages([
                               $field->name => $e->getMessage()
                            ]);

                        }
                    }
                }

            try{
                if((!empty($field->type) && $field->type == 'disable'))
                    continue;

                if ((!empty($field->storeSkip) && $field->storeSkip == true))
                    continue;

                //handle hidden fields
                if (!empty($field->type) && $field->type == 'hidden') {

                    $data->$name = (empty($thisValue)) ? $field->defaultValue : $thisValue;

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
                            $thisValue = $this->$process($request, $request->input($field->name),$data,$field->name);
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
                return (method_exists($this,'afterStore') ? $this->afterStore($data,$edit) : redirect($this->mainRoute->getUrl()))->with(config('ariel.success_alert'), trans('ariel::ariel.success_text'));
            }
        }catch (\Exception $e){

            if(config('app.debug'))
                dd($e);
            else
                return (method_exists($this,'afterStore') ? $this->afterStore($data,$edit) : redirect($this->mainRoute->getUrl()))->with(config('ariel.danger_alert'), trans('ariel::ariel.exception_text'));

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
        $parameters = request()->route()->parameters();
        if(count($parameters) > 1){
            $id = end($parameters);
        }

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

        $parameters = $request->route()->parameters();
        if(count($parameters) > 1){
            $id = end($parameters);
        }
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
        $parameters = $request->route()->parameters();
        if(count($parameters) > 1){
            $id = end($parameters);
        }
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
                $data->forceDelete();
            }


            return redirect()->back()->with(config('ariel.success_alert'),trans('ariel::ariel.success_text'));
        }

        if(is_numeric($id))
            $data = $this->model::where("id",$id);
        else
            $data = $this->model::where("uuid",$id);

        if($data->count() == 0)
            return abort(404);
        $data = $data->get()->first();
        $data->delete();
        return redirect()->back()->with(config('ariel.success_alert'),trans('ariel::ariel.success_text'));
    }
}
