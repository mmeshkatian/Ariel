<div class="col-12">
    <div class="form-group row">
        <div class="col-md-4">
            <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
        </div>
        <div class="col-md-8">
            <select class="form-control select2" name="{{$field->name ?? ''}}"  @if($field->isRequired) required @endif>
                <option value="">انتخاب کنید</option>
                @foreach($field->valuesList ?? [] as $v=>$value)
                    <option @if($field->defaultValue == $v) selected @endif value="{{$v}}">{{$value}}</option>
                @endforeach
            </select>
            @error($field->name)
            <div class="invalid-feedback">
                {{$message}}
            </div>
            @enderror
        </div>
    </div>
</div>
