@section('caption')
    <span>{{$field->caption ?? ''}}  @if($field->isRequired) <span class="required">*</span> @endif</span>
@overwrite
@section('input')
    @if(!empty($field->defaultValue))
        <a href="{{asset('uploads/'.$field->defaultValue)}}" data-lightbox="image-{{rand(1,99999)}}" data-title="Image">دانلود فایل</a>
    @endif
    <input type="file" class="form-control @error($field->name) is-invalid @enderror mt-2" name="{{$field->name ?? ''}}" placeholder="{{$field->caption ?? ''}}"  @if($field->isRequired) required @endif>

@overwrite
