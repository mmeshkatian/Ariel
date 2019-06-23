
<div class="form-group row">
    <label for="{{$field->name ?? ''}}" class="col-md-4 col-form-label text-md-right">{{$field->caption ?? ''}}  @if($field->isRequired)  <span class="required">*</span>  @endif</label>

    <div class="col-md-6">
        <input type="checkbox" class="form-control" name="{{$field->name ?? ''}}" value="1" @if(old($field->name) || $fieldData || ($field->value ?? 0)) checked @endif @if($field->isRequired) required @endif>

    </div>
</div>