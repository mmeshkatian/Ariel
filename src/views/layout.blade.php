<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel Ariel</title>

    </head>
    <body>
    <div class="flex-center position-ref full-height">

        <div class="content">
            <div class="title m-b-md">
                Laravel
            </div>
            <div>
                @include('ariel::alerts')

                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="card-text">
                                @yield('contenter')
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        </div>
    </div>
    </body>
    </html>
