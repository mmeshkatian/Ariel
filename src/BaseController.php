<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
    private $filters = [];
    private $sections = [];
    private $batchActions = [];
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
    private $builder = null;

    public function __construct()
    {
        $this->builder = new FormBuilder();
    }
    public function __call($name, $arguments)
    {
        if(method_exists($this,$name) && is_callable($this,$name))
            return $this->$name(...$arguments);

        if(Str::contains($name,'add'))
            return $this->addField(...$arguments)->setType(strtolower(str_replace("add","",$name)));
    }
    public function set($what,$data)
    {
        $this->$what = $data;
    }
    public function get($what)
    {
        return $this->$what;
    }
    public function builder(){
        return $this->builder;
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
        $RoutePrefix = \request()->route();
        $RouteParameters = $RoutePrefix->parameters();

        if(empty($this->RoutePrefix)) {
            //try to autoConfigure route Names
            $RoutePrefix = $RoutePrefix->getName();
            $RoutePrefix = explode(".", $RoutePrefix);
            array_pop($RoutePrefix);
            $RoutePrefix = implode(".", $RoutePrefix);
            $this->RoutePrefix = $RoutePrefix;
        }

        if(empty($this->mainRoute))
            $this->mainRoute = new ActionContainer($this->RoutePrefix.'.'.Router::INDEX,'','',$RouteParameters);

        if(empty($this->saveRoute))
            $this->saveRoute = new ActionContainer($this->RoutePrefix.'.'.Router::STORE,'','',$RouteParameters);

        if(empty($this->editRoute))
            $this->editRoute = new ActionContainer($this->RoutePrefix.'.'.Router::EDIT,'','',$RouteParameters);


        if(empty($this->updateRoute))
            $this->updateRoute = new ActionContainer($this->RoutePrefix.'.'.Router::UPDATE,'','',$RouteParameters);

        if(empty($this->createRoute))
            $this->createRoute = new ActionContainer($this->RoutePrefix.'.'.Router::CREATE,'','',$RouteParameters);

    }
    protected function addColumn($name,$value)
    {
        $this->cols[] = new ColumnContainer($name,$value,get_class($this));
    }
    protected function addQueryCondition($function,$data)
    {
        $this->queryConditions[] = ["function" => $function,"data" => $data];
    }
    protected function addFilter($field,$callable)
    {
        $this->filters[] = new FilterContainer($field,$callable);
    }
    protected function addSection($view,$in = 'all',$position = 'top',$vars = [])
    {
        $this->sections[] = new SectionContainer($view,$in,$position,$vars);
    }
    protected function setListData($data)
    {
        $this->ListData = $data;
    }
    protected function addScript($script,$in = 'all'){
        $this->scripts [] =  ['script'=>$script,'in'=>$in];
    }
    protected function listenScript()
    {
        \Ariel::listenScript();
    }
    protected function setScript($in = 'all')
    {
        $script = \Ariel::setScript();
        $this->addScript($script,$in);
    }
    protected function addAction($action,$icon,$caption,$defaultParms = [],$accessControlMethod = null,$options = [])
    {
        $this->actions [] = new ActionContainer($action,$icon,$caption,$defaultParms,$accessControlMethod,$options);
    }
    protected function addBatchAction($action,$icon,$caption,$defaultParms = [],$accessControlMethod = null,$options = [])
    {
        $this->batchActions [] = new ActionContainer($action,$icon,$caption,$defaultParms,$accessControlMethod,$options);
    }
    /**
     * @param $name
     * @param $caption
     * @param string $validationRule
     * @param string $type
     * @param string $value
     * @param array $values
     * @param string $process
     * @param bool $processForce
     * @param bool $skip
     * @param bool $storeSkip
     * @return FieldContainer
     */
    protected function addField($name,$caption,$validationRule='',$type='text',$value = '',$values=[],$process='',$processForce=true,$skip = false,$storeSkip = false)
    {
        $arr = new FieldContainer($name,$caption,$validationRule,$type,$value,$values,$process,$processForce,$skip,$storeSkip);
        $arr->setClass(get_class($this));
        $this->fields[] = $arr;

        $this->builder->setFields($this->fields);
        return end($this->fields);
    }
    /**
     * @param $name
     * @return FieldContainer
     */
    protected function getField($name)
    {
        return array_values(Arr::where($this->fields,function ($data) use($name) {
           return $data->name == $name;
        }))[0] ?? null;
    }
    protected function addHidden($name,$value,$process = null,$forceProcess = false,$skip = false)
    {

        return $this->addField($name,"","","hidden",$value,[],$process,$forceProcess,$skip);
    }
    protected function addProcess($processName,$force = false,$skip = true)
    {
        return $this->addHidden($processName,"",$processName,$force,$skip);
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
                //if multiChosen
                //add IN require operation
                $v = $field->valuesList;
                $v[null] = 'Null';

                $add = "in:" . implode(",", array_keys($v));
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
            foreach ($this->filters as $filter) {
                $filter->handle($request,$rows);
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

                $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-refresh-cw"></i>', 'Restore', ['$id','restore' => '1'], null, ['class' => 'ask']);
                $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-trash"></i>', 'Permanent Delete', ['$id','perm' => '1'], null, ['class' => 'ask']);
            }
        }
        $rows = $rows->get();

        $breadcrumbs = [];
        $title = 'لیست '.$this->title;
        $mainRoute = $this->mainRoute;
        $saveRoute = $this->saveRoute;
        $data = null;
        $BladeSettings = $this->BladeSettings;

        $actions = $this->actions;
        $batchActions = $this->batchActions;

        $scripts = $this->scripts;
        $filters = $this->filters;
        $sections = $this->sections;
        foreach ($sections as $section)
            $section->compile(null);

        return view('ariel::index',compact('colNames','cols','fields','rows','createRoute','actions','mainRoute','saveRoute','data','BladeSettings','title','breadcrumbs','scripts','filters','sections','batchActions'));
    }
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
        $breadcrumbs = [];
        $title = (empty($data) ? 'ثبت ' : 'ویرایش ').$this->title;
        $script = $this->scripts;
        $formBuilder = new FormBuilder(true,$saveRoute);
        $formBuilder->setFields($this->fields);
        $formBuilder->setTile($title);
        $sections = $this->sections;

        foreach ($sections as $section)
            $section->compile($data);

        return $formBuilder->render($data,$script,$breadcrumbs,$sections);
//        return view('ariel::create',compact('fields','mainRoute','saveRoute','id','data','breadcrumbs','title','BladeSettings','script'));
    }
    public function store(Request $request,$data = null,$return = false)
    {
        $edit = false;
        $this->getConfig();
        config()->set('ariel.upload_path',public_path(config('ariel.upload_path')));


        $formBuilder = new FormBuilder();
        $formBuilder->setFields($this->fields);
        $formBuilder->store($data);


        if(!empty($data) && !empty($data->id)){
                $data = $this->model::where('id',$data->id);
            if($data->count() == 0)
                return abort(404);
            $data = $data->get()->first();
            $edit = true;
        }else
            $data = new $this->model();

        foreach ($this->fields as $field) {
            $value = $field->getStoreValue($data);
            if(!empty($value) || $value == '0')
                $data->{$field->name} = $value;

        }
        $data->save();
        try {


            foreach ($this->fields as $field)
                $field->runAfterScript($data);

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
    public function show($id)
    {
        $this->getConfig();
        return $this->edit($id);
    }
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
