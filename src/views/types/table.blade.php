@section('input')
    <?php
    $_selectedValues = old($field->name) ?? $fieldData ?? $field->defaultValue ?? '';
    if(!is_array($_selectedValues))
        $selectedValues = json_decode($_selectedValues) ?? $_selectedValues;
    else{
        $selectedValues = $_selectedValues;
    }
    ?>
    <select class="form-control select2" multiple name="{{$field->name ?? ''}}[]" id="multiselect_{{$field->name ?? ''}}"  @if($field->isRequired) required @endif>

        <option value="">انتخاب کنید</option>

        @foreach($field->valuesList ?? [] as $v=>$value)
            <option @if(is_array($selectedValues) ? in_array($v,$selectedValues) : $selectedValues == $v) selected @endif value="{{$v}}">{{$value}}</option>
        @endforeach
    </select>
@overwrite
@section('caption')
    <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>

@overwrite
