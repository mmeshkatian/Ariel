@if(session()->has('info'))
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        <p class="mb-0">
            {{session()->get('info')}}
        </p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
@endif
@if(session()->has('danger'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <p class="mb-0">
            {{session()->get('danger')}}
        </p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
@endif
