@section('input')
    <select class="form-control select2" name="{{$field->name ?? ''}}"  @if($field->isRequired) required @endif>
        <option value="">انتخاب کنید</option>
        @foreach($field->valuesList ?? [] as $v=>$value)
            <option @if($field->defaultValue == $v) selected @endif value="{{$v}}">{{$value}}</option>
        @endforeach
    </select>
@overwrite
@section('caption')
    <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
@overwrite
