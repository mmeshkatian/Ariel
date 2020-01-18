@section('input')
    <?php
    $selectedValue = old($field->name) ?? $field->value ?? '';
    ?>
    @foreach($field->values ?? [] as $v=>$value)
        <input type="checkbox" name="{{$field->name ?? ''}}[]" value="{{$v}}"/>{{$value}}<br>
    @endforeach
@overwrite
@section('caption')
    <label for="{{$field->name ?? ''}}" class="col-md-4 col-form-label text-md-right">{{$field->caption ?? ''}}  @if($field->isRequired)  <span class="required">*</span>  @endif</label>
@overwrite
