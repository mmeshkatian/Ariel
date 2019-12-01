<div class="col-12">
    <div class="form-group row">
        <div class="col-md-4">
            <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
        </div>
        <div class="col-md-8">
            <input type="{{$field->type}}" class="form-control @error($field->name) is-invalid @enderror" name="{{$field->name ?? ''}}" placeholder="{{$field->caption ?? ''}}" value="@if($field->type != 'password'){{$field->defaultValue}}@endif" @if($field->isRequired) required @endif>
            @error($field->name)
            <div class="invalid-feedback">
                {{$message}}
            </div>
            @enderror
        </div>
    </div>
</div>
