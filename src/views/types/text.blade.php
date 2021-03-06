@section('input')
    <input type="{{$field->type}}" autocomplete="off" class="form-control @error($field->name) is-invalid @enderror" name="{{$field->name ?? ''}}" placeholder="{{$field->caption ?? ''}}" value="@if($field->type != 'password'){{$field->defaultValue}}@endif" @if($field->isRequired) required @endif>
@overwrite
@section('caption')
    <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
@overwrite
