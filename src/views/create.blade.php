@extends('vendor.ariel.layout')
@section('contenter')
    <div class="card">

        <div class="card-content">
            <div class="card-body">

                <form class="form form-horizontal" method="POST" action="{{ $saveRoute }}" enctype="multipart/form-data">
                    @csrf
                    @method($saveRoute->method)
                    <div class="form-body">
                        <div class="row">
                            @include('vendor.ariel.fields',$fields)
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                ذخیره
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection
@section('myscript')
    @foreach($script as $sc)

        {!! $sc['script'] !!}

    @endforeach
@endsection
