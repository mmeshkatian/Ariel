@section('input')
    <div class="vs-checkbox-con vs-checkbox-primary">
        <input type="checkbox"  name="{{$field->name ?? ''}}" value="1" @if($field->defaultValue == '1') checked @endif @if($field->isRequired) required @endif>
        <span class="vs-checkbox vs-checkbox-lg">
                    <span class="vs-checkbox--check">
                        <i class="vs-icon feather icon-check"></i>
                    </span>
                </span>
        <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
    </div>
@overwrite
