@extends('vendor.ariel.layout')
@section('title',$title)
@section('content')
    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->render('index','top') !!}
        @endforeach
    @endif

    <section>
        <!-- Begin Users Profile -->
        <div class="card search-area" @if(count(request()->all()) == 0 || empty($filters))style="display: none" @endif>
            <div class="card-body">
                <form action="" method="get">
                    <div class="row justify-content-between">
                        @if(!empty($filters))
                            @foreach($filters as $filter)
                                <div class="col-md-6">
                                    {!! $filter->render() !!}
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="submit" class="btn btn-success round waves-effect waves-light">جستجو</button>
                </form>
            </div>
        </div>
    </section>

    @parent

    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->render('index','bottom') !!}
        @endforeach
    @endif
@endsection
@section('content-inside')


    <div class="clearfix"></div>
    <section>
        <!-- Begin Users Profile -->
        <div class="card">
            <div class="card-body">

                <div class="row justify-content-between">
                    @foreach($batchActions ?? [] as $action)
                        <a href="#" data-href="{{$action->getUrl()}}" class="btn btn-primary round waves-effect waves-light batchActions" >
                            {{$action->caption ?? ''}}
                        </a>
                    @endforeach
                    <form class="d-none" id="batchActionsForm" action="" method="post">@csrf <input type="hidden" name="ids" value=""></form>
                    @if(!($BladeSettings['disableCreate'] ?? false))
                        <div class="ml-2">

                                @if(!request()->input('trash') == '1')
                                    @if(($BladeSettings['side'] ?? false))
                                        <a href="#" class="btn btn-primary round waves-effect waves-light addNewData" >
                                            افزودن
                                        </a>
                                    @else
                                        <a href="{{$createRoute}}" class="btn btn-primary round waves-effect waves-light" >
                                            افزودن
                                        </a>
                                    @endif
                                @endif


                        </div>
                        <div class="ml-2">
                        @if(request()->input('trash') == '1')
                            <a href="{{url()->current()}}" class="btn btn-dark round waves-effect waves-light pull-left" >
                                بازگشت
                            </a>
                        @else
                            <a data-toggle="tooltip" data-placement="top" title="سطل زباله" href="{{url()->current()}}?trash=1" class="btn btn-danger round waves-effect waves-light" >
                                <i class="feather icon-trash"></i>
                            </a>
                        @endif
                        </div>
                    @endif
                    <div class="ml-2">
                        @if(!empty($filters))
                        <a data-toggle="tooltip" data-placement="top" title="جستجو" href="#" class="btn btn-success round waves-effect waves-light show-search" >
                            <i class="feather icon-search"></i>
                        </a>
                        @endif
                    </div>


                </div>

                <div class="card-dashboard">
                    <div class="table-responsive">

                        <table id="table" class="display table table-data-width">
                            <thead>
                            <tr>
                                <th>ردیف</th>
                                @if(($BladeSettings['addSelector'] ?? false))
                                    <th>انتخاب</th>
                                @endif
                                @foreach($cols as $col)
                                    <th>{{$col->getName()}}</th>
                                @endforeach
                                @if(!empty($actions))
                                    <th>عملیات</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; ?>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{$i++}}</td>
                                    @if(($BladeSettings['addSelector'] ?? false))
                                        <th>{!! (new \Mmeshkatian\Ariel\FieldContainer('_ids',''))->setType('ncheckbox')->setValue($row->id)->getView(null,false)  !!} </th>
                                    @endif
                                    @foreach($cols as $col)
                                        <td>{!! $col->getValue($row) !!}</td>
                                    @endforeach
                                    @if(!empty($actions))
                                        <td class="d-flex">
                                            @foreach($actions as $action)
                                                @continue(!$action->hasAccess($row))
                                                @if($action->isGet())
                                                    <a data-toggle="tooltip" class="{{$action->options['class'] ?? ''}}" data-placement="top" title="{{$action->caption ?? ''}}" style="margin-left: 10px" href="{{$action->getUrl($row)}}">{!! $action->icon ?? '' !!}</a>
                                                @else
                                                    <form data-toggle="tooltip" data-placement="top"  class="{{$action->options['class'] ?? ''}}" title="{{$action->caption ?? ''}}" style="margin-left: 10px;display: inline" method="post" action="{{$action->getUrl($row)}}">
                                                        @method($action->method)
                                                        @csrf
                                                        {!! $action->icon ?? '' !!}
                                                    </form>
                                                @endif
                                            @endforeach
                                        </td>
                                    @endif


                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Users Profile -->
        <div class="add-new-data-sidebar">
            <div class="overlay-bg"></div>
            <form id="add-form" action="{{ $saveRoute }}" method="post">
                @csrf
                @method($saveRoute->method)
                <div class="add-new-data">
                    <div class="div mt-2 px-2 d-flex new-data-title justify-content-between">
                        <div>
                            <h4>ثبت جدید</h4>
                        </div>
                        <div class="hide-data-sidebar">
                            <i class="feather icon-x"></i>
                        </div>
                    </div>
                    <div class="data-items py-3 ps">

                        <div >
                            @foreach($fields as $field)
                                <?php
                                $name = $field->name ?? '';
                                $fieldData = $data->$name ?? null;
                                ?>
                                <div class="col-12">
                                    {!! $field->getView($data) !!}

                                </div>

                            @endforeach
                        </div>

                    </div>
                    <div class="add-data-footer d-flex justify-content-around px-3 mt-2">
                        <div class="add-data-btn">
                            @csrf
                            <button type="submit" class="btn btn-primary">ثبت اطلاعات و ذخیره</button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </section>



@endsection
@section('mystyle')
    <link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/datatables.min.css')}}">
    <link rel="stylesheet" href="{{ asset(mix('css/pages/data-list-view.css')) }}">

    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->getStyle('index') !!}
        @endforeach
    @endif

@endsection
@section('myscript')
    @parent
    <script src="{{asset('vendors/js/tables/datatable/datatables.min.js')}}"></script>
    <script src="{{asset('vendors/js/tables/datatable/datatables.bootstrap4.min.js')}}"></script>
    <script src="{{asset(mix('js/scripts/data-list-view.js'))}}"></script>
    <script>
        $(".overlay-bg").on("click",function (e) {
            $(".hide-data-sidebar").click();
        })
        $(".addNewData").on("click",function (e) {
            e.preventDefault();
            $(".add-new-data").addClass("show");
            $(".overlay-bg").addClass("show");
        })
    </script>

    <script>
        $(".show-search").on("click",function (e) {
            $(".search-area").toggle(1);
        });
        $(document).ready(function() {
            @if($errors->count() > 0)
            $(".addNewData").click();
            @endif
            @if(!empty(request()->input('add')))
            $(".addNewData").click();
            @endif
            $('#table').DataTable({

                select: {
                    style:    'os',
                    selector: 'td:first-child'
                },
                order: [[ 0, 'asc' ]],

                "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
                language: {
                    search: "_INPUT_",
                    "search": '<i class="fa fa-search"></i>',
                    "searchPlaceholder": "جستجو",
                }
            });
        });
    </script>
    <script>
        $(".batchActions").on("click",function (e) {
            e.preventDefault();
            var data = [];
            $("input[type=checkbox]:checked").each(function(val){
                data.push($(this).val());
            });
            if(data.length == 0){
                Swal.fire({
                    title: 'پیام',
                    text: 'لطفا حداقل یک رکورد اضافه کنید',
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
                return;
            }
            $("input[name=ids]").val(JSON.stringify(data));
            $("#batchActionsForm").attr('action',$(".batchActions").data('href'));
            $("#batchActionsForm").submit();
        });
        $('a.ask').on('click', function (e) {
            var thiis = this;
            e.preventDefault();
            Swal.fire({
                title: 'آیا مطمین هستید ؟',
                text: "پس از تایید امکان بازگشت این عملیات وجود ندارد.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'تایید',
                cancelButtonText: 'لغو',
                confirmButtonClass: 'btn btn-primary ml-1',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    if($(thiis).is('form')){
                        $(thiis).submit();
                    }else{
                        window.location = $(thiis).attr('href');
                    }
                }
            })
        });
        $('td>form').on('click', function (e) {
            e.preventDefault();
            if(!$(this).hasClass("ask"))
                return $(this).submit();
            else {
                var thiis = this;
                Swal.fire({
                    title: 'آیا مطمین هستید ؟',
                    text: "پس از تایید امکان بازگشت این عملیات وجود ندارد.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'تایید',
                    cancelButtonText: 'لغو',
                    confirmButtonClass: 'btn btn-primary ml-1',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        $(thiis).submit();
                    }
                })
            }

        });
    </script>

    @if(!empty($sections))
        @foreach($sections as $section)
            {!! $section->getScript('index') !!}
        @endforeach
    @endif

@endsection
