<?php

namespace Mmeshkatian\Ariel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Route;
use File;

class FieldContainer
{
    var $name;
    var $caption;
    var $validationRule;
    var $type;
    var $defaultValue;
    var $valuesList;
    var $process;
    var $forceProcess;
    var $skip;
    var $storeSkip;
    var $isRequired;
    var $scripts;
    var $class;

    public function __construct($name,$caption,$validationRule = '',$type = 'text' ,$defaultValue = '',$valuesList = [],$process = '',$forceProcess = false,$skip = false,$storeSkip = false,$scripts = [],$class = '')
    {
        $this->name = $name;
        $this->caption = $caption;
        $this->validationRule = $validationRule;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
        $this->valuesList = $valuesList;
        $this->process = $process;
        $this->forceProcess = $forceProcess;
        $this->skip = $skip;
        $this->storeSkip = $storeSkip;
        $this->scripts = $scripts;
        $this->class = ($class);

        $this->isRequired = !is_array($this->validationRule) ? (strpos(($this->validationRule), 'required') !== false) : in_array('required',$this->validationRule);
        if(!empty($this->validationRule['store']) || !empty($this->validationRule['update']))
            $this->isRequired = false;

    }

    public function onlyProcess($true = true)
    {
        $this->skip = $true;
        return $this;
    }

    public function ignore($true = true)
    {
        $this->storeSkip = $true;
        return $this;
    }

    public function setClass($class)
    {
        $this->class = app($class);
        return $this;
    }

    public function addProcess($process)
    {
        $this->process = $process;
        return $this;
    }

    public function force($true = true)
    {
        $this->forceProcess = $true;
        return $this;
    }

    public function setValues($values)
    {
        if(is_callable($values)) {
            $this->valuesList = ($values)($this,$this->getValue());
        }else
            $this->valuesList = $values;
        return $this;
    }

    public function setValue($value)
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function rules($rules)
    {
        $this->validationRule = $rules;
        return $this;
    }

    public function required($true = true)
    {
        $this->isRequired = $true;
        return $this;
    }

    public function addOpt($param,$value)
    {
        $this->$param = $value;
        return $this;

    }

    public function getValue($value)
    {
        if(!empty(request()->input($this->name))) {
            return request()->input($this->name);
        }

        if(!empty(old($this->name)))
            return old($this->name);

        if(is_callable($this->defaultValue))
            return ($this->defaultValue)($this,$value);

        if(!empty($this->defaultValue)) {
            $returnValue = $this->defaultValue;

            if (\Ariel::exists($this->defaultValue, "->")) {
                try {
                    $returnValue = ('$value->' . $this->defaultValue);
                    eval('$returnValue = ' . $returnValue . ' ?? "";');
                }catch (\Exception $e){
                    $returnValue = null;
                }
            }

            return $returnValue;
        }

        if(!empty($value))
            return $value->{$this->name} ?? '';


        return "";

    }

    public function getContainer()
    {
        return config('ariel.default_container');
    }

    public function getSections()
    {
        if(view()->exists('ariel::types.'.$this->type))
            $input =  view('ariel::types.'.$this->type,['field'=>$this])->renderSections();
        else
            $input = view('ariel::types.text',['field'=>$this])->renderSections();
        
        return $input;
    }
    public function getView($value,$withContainer = true)
    {
        $this->defaultValue = $this->getValue($value);
        
        $input = $this->getSections();
        

        $caption = $input['caption'] ?? '';
        $input = $input['input'] ?? '';

        if($withContainer)
            return view('ariel::containers.'.$this->getContainer(),['caption'=>$caption,'input'=>$input,'field'=>$this])->render();

        return $input;
    }

    //Js
    public function setHide()
    {
        \Ariel::listenScript();
        ?>
        <script>
            $("[name=<?php echo $this->name ?>]").parents('.form-group').hide();
        </script>
        <?php
        $script = \Ariel::setScript();
        $this->scripts[] = $script;

        return $this;
    }

    public function toggleWhen($event,$selector_name,$value)
    {
        return $this->doJsWhen('[name='.$selector_name.']',$value,"parents('.form-group').show","parents('.form-group').hide",null,$event);
    }

    public function doJs($selector,$action,$args = null,$event = null)
    {
        return $this->doJsWhen($selector,'-NAN','hide',$action,$args,$event);
    }

    public function doJsWhen($selector,$value,$trueAction,$falseAction,$args = null,$event = null)
    {
        if(empty($event)){
            if(in_array($this->type,config('ariel.inArray'))){
                $event = 'change';
            }else{
                $event = 'keyup';
            }
        }
        \Ariel::listenScript();
        ?>
        <script>
            $("<?php echo $selector; ?>").on("<?php echo $event ?>",function (e) {
                var value = $("<?php echo $selector; ?>").val();
                if(value == '<?php echo $value?>'){
                    $("[name=<?php echo $this->name ?>]").<?php echo $trueAction ?>(<?php echo $args ?? '' ?>);
                }else{
                    $("[name=<?php echo $this->name ?>]").<?php echo $falseAction ?>(<?php echo $args ?? '' ?>);
                }
            });

        </script>
        <?php
        $script = \Ariel::setScript();
        $this->scripts[] = $script;
        return $this;
    }

    public function renderScript()
    {
        $script_section = $this->getSections()['script'] ?? '';
        $out = $script_section;
        foreach ($this->scripts as $script) {
            $out .= $script;
        }
        return $out;
    }

    public function getStoreValue(Model $data)
{
        $request = request();
        $name = $this->name;

        if ((!empty($this->type) && $this->type == 'disable'))
            return null;

        if ((!empty($this->storeSkip) && $this->storeSkip == true))
            return null;

        if ((!empty($this->skip) && $this->skip == true))
            return null;

        if (!empty($this->type) && $this->type == 'hidden')
            return $this->defaultValue;

        if (!empty($this->process)) {
            try {
                $process = $this->process;
                return $this->class->$process($request, $request->input($this->name));
            } catch (\Exception $e) {
                if (($this->forceProcess))
                    throw ValidationException::withMessages([$this->name => $e->getMessage()]);
            }
        }
        if ($request->hasFile($name)) {
            //upload
            $file = $request->file($name);
            if (!empty($data->$name)) {
                //delete old
                if (config('ariel.delete_old_files'))
                    File::delete(config('ariel.upload_path') . '/' . $data->$name);
            }
            //upload file
            $fileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $model = explode("\\", get_class($data));
            $model = end($model);

            File::makeDirectory(config('ariel.upload_path') . '/' . $model, 0777, true, true);
            $file->move(config('ariel.upload_path') . '/' . $model, $fileName);
            return $model . '/' . $fileName;

        }
        if (is_array($request->input($name)))
            return json_encode($request->input($name));

        if (!empty($this->type) && $this->type == 'password') {
            if (!empty($request->input($name)))
                return $request->input($name);
            else return null;
        }

        return $request->input($name);

    }

    public function runAfterScript($storedData)
    {
        if(!empty($this->process))
            $this->class->{$this->process}(request(), request()->input($this->name),$storedData);
    }

}
