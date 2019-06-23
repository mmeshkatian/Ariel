@extends('ariel::layout')
@section('content')
    <style>
        .required{
            color: #000;
        }
    </style>
    <form method="POST" action="{{ $saveRoute }}" enctype="multipart/form-data">
        @csrf
        @method($saveRoute->method)

        @foreach($fields as $field)
            <?php
                $name = $field->name ?? '';
                $fieldData = $data->$name ?? null;
            ?>
            @if(view()->exists('ariel::types.'.$field->type))
                @include('ariel::types.'.$field->type ?? '-',compact('field','fieldData'))
            @else
                @include('ariel::types.text',compact('field'))
            @endif
        @endforeach
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </div>
        </div>
    </form>


@endsection