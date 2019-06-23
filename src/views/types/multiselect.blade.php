
<div class="form-group row">
    <label for="{{$field->name ?? ''}}" class="col-md-4 col-form-label text-md-right">{{$field->caption ?? ''}}  @if($field->isRequired)  <span class="required">*</span>  @endif</label>

    <div class="col-md-6">
        <select class="form-control" multiple name="{{$field->name ?? ''}}[]"  @if($field->isRequired) required @endif>
            <?php
            $selectedValue = old($field->name) ?? $fieldData ?? $field->value ?? '';
            ?>
            @foreach($field->values ?? [] as $v=>$value)
                <option @if($selectedValue == $v) selected @endif value="{{$v}}">{{$value}}</option>
            @endforeach
        </select>
    </div>
</div>