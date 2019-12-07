@extends('vendor.ariel.layout')
@section('title',$title)
@section('contenter')
    <section>
        <!-- Begin Users Profile -->
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-between">

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
                </div>
                <div class="card-dashboard">
                    <div class="table-responsive">

                        <table id="table" class="display table table-data-width">
                            <thead>
                            <tr>
                                <th>ردیف</th>
                                @foreach($colNames as $col)
                                    <th>{{$col}}</th>
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
                                    @foreach($cols as $col)
                                        @if(strpos($col,"ToNFT") !== false)
                                            <?php
                                            $col = explode("ToNFT",$col)[0] ?? 'n';
                                            ?>
                                            <td>{!! (@number_format($row->$col ?? '0')." ".config('ariel.number_format_postfix'))  !!}</td>
                                        @elseif(strpos($col,"ToPC") !== false)
                                            <?php
                                            $col = explode("ToPC",$col)[0] ?? 'n';
                                            ?>
                                            <td>{!! (@number_format($row->$col ?? '0')." %")  !!}</td>
                                        @elseif(strpos($col,"ToTEXT") !== false)
                                            <?php
                                            $col = explode("ToTEXT",$col)[0] ?? 'n';
                                            $value = \Ariel::getdata($col,"ToTEXT");

                                            $field = \Ariel::searchIn($fields,"name",$value);

                                            $_value = $row->$col;
                                            $_value = json_decode($_value) ?? $_value;
                                            if(is_array($_value)){
                                                $val = '';
                                                $i = 0;
                                                foreach ($_value as $v) {
                                                    $i++;
                                                    $val .= ($field->valuesList[$v] ?? '-') .(count($_value) == $i ? '' : ' - ');
                                                }
                                            }else
                                                $val = $field->valuesList[$row->$col] ?? '-';

                                            ?>
                                            <td>{!! $val ?? '' !!}</td>
                                        @else
                                            <td>{!! $row->$col !!}</td>
                                        @endif
                                    @endforeach
                                    @if(!empty($actions))
                                        <td>
                                            @foreach($actions as $action)
                                                @continue(!$action->hasAccess($row))
                                                @if($action->isGet())
                                                    <a data-toggle="tooltip" class="{{$action->options['class'] ?? ''}}" data-placement="top" title="{{$action->caption ?? ''}}" style="margin-left: 10px" href="{{(empty($row->uuid) ? $action->addParam("id",$row->id)->getUrl() : $action->addParam("uuid",$row->uuid)->getUrl())}}">{!! $action->icon ?? '' !!}</a>
                                                @else
                                                    <form data-toggle="tooltip" data-placement="top"  class="{{$action->options['class'] ?? ''}}" title="{{$action->caption ?? ''}}" style="margin-left: 10px;display: inline" method="post" action="{{(empty($row->uuid) ? $action->addParam("id",$row->id)->getUrl() : $action->addParam("uuid",$row->uuid)->getUrl())}}">
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


@endsection
@section('myscript')
    @parent


@endsection
