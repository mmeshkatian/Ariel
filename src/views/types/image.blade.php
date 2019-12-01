<div class="col-12">
    <div class="form-group row">
        <div class="col-md-4">
            <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
        </div>
        <div class="col-md-8">
            @if(!empty($field->defaultValue))
            <a href="{{asset('uploads/'.$field->defaultValue)}}" data-lightbox="image-{{rand(1,99999)}}" data-title="Image">
                <img style="width: 30%" src="{{asset('uploads/'.$field->defaultValue)}}">
            </a>
            @endif
            <input type="file" class="form-control @error($field->name) is-invalid @enderror mt-2" name="{{$field->name ?? ''}}" placeholder="{{$field->caption ?? ''}}"  @if($field->isRequired) required @endif>

            @error($field->name)
            <div class="invalid-feedback">
                {{$message}}
            </div>
            @enderror
        </div>
    </div>
</div>
