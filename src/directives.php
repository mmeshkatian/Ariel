<?php
use Illuminate\Support\Facades\Blade;

Blade::directive('field',function($data){
    return '<?php echo (new \Mmeshkatian\Ariel\FieldContainer(...(explode(",","'.$data.'"))))->getView(null);  ?>';
});

?>
