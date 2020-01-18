@extends('vendor.ariel.layout')
@section('content')
    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->render('create','top') !!}
        @endforeach
    @endif

    @parent

    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->render('create','bottom') !!}
        @endforeach
    @endif
@endsection
@section('content-inside')
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
    @parent
    <script src="{{asset('vendors/js/tables/datatable/datatables.min.js')}}"></script>
    <script src="{{asset('vendors/js/tables/datatable/datatables.bootstrap4.min.js')}}"></script>

    @if(!empty($script))
        @foreach($script as $sc)
            {!! $sc['script'] !!}
        @endforeach
    @endif
    @foreach($fields as $field)
        @continue(empty($field->renderScript()))

        {!! $field->renderScript() !!}
    @endforeach
    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->getScript('create') !!}
        @endforeach
    @endif
@endsection
@section('mystyle')
    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->getStyle('create') !!}
        @endforeach
    @endif
@endsection
