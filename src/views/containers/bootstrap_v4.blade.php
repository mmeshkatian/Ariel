<div class="col-12">
    <div class="form-group row">
        <div class="col-md-4">
            <span>{!! $caption !!}</span>
        </div>
        <div class="col-md-8">
            {!! $input !!}
            @error($field->name)
            <div class="invalid-feedback">
                {{$message}}
            </div>
            @enderror
        </div>
    </div>
</div>
