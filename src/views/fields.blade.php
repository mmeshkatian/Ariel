@foreach($fields as $field)
    <?php
    $name = $field->name ?? '';
    $fieldData = $data->$name ?? null;
    ?>
    <div class="col-12">
        {!! $field->getView($data) !!}

    </div>

@endforeach
