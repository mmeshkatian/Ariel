@if (Session::has(config('ariel.success_alert')) )

    <div class="alert alert-success">
        {!! Session::get(config('ariel.success_alert')) !!}
    </div>

@endif
@if (Session::has(config('ariel.danger_alert')) )

    <div class="alert alert-danger">
        {!! Session::get(config('ariel.danger_alert')) !!}
    </div>

@endif
