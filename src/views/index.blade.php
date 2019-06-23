@extends('ariel::layout')
@section('content')
    <a href="{{$createRoute}}"  class="btn btn-primary">New Record</a>
    <hr>
    <div class="table-responsive">

        <table id="table" class="display table table-data-width">
            <thead>
            <tr>
                <th>i</th>
                @foreach($colNames as $col)
                    <th>{{$col}}</th>
                @endforeach
                @if(!empty($actions))
                    <th>Operation</th>
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


                                $val = $field->values[$row->$col] ?? '-';
                            ?>
                            <td>{!! $val ?? '' !!}</td>
                        @else
                            <td>{!! $row->$col !!}</td>
                        @endif
                    @endforeach
                    @if(!empty($actions))
                        <td>
                            @foreach($actions as $action)
                                <a data-toggle="tooltip" data-placement="top" title="{{$action->caption ?? ''}}" style="margin-left: 10px" href="{{$action->action->setParm(["id"=>$row->id])}}">{!! $action->icon ?? '' !!}</a>
                            @endforeach
                        </td>
                    @endif


                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

@endsection