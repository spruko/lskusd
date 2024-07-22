@extends('Installation::installer.layouts.InstallerMaster')

@section('template_title')
{{ trans('Step 3 | Environment Settings') }}
@endsection

@section('title')
{!! trans('Environment Settings') !!}
@endsection


@section('container')

@if ($message = Session::get('success'))

<p class="paragraph para-heading text-center">
    {{ $message }}
</p>
@endif
@if ($message = Session::get('info'))

<p class="paragraph para-heading text-center">
    {{ $message }}
</p>
@endif

<div class="col-xl-12 col-lg-12 col-md-12">
    <div class="card ">

        @if (isset($errors) & $errors->any())

        <div class="alert alert-danger">
            @foreach ($errors->all() as $item)

            {{$item}}
            @endforeach

        </div>
        @endif
        <form action="{{ route('SprukoAppInstaller::database.sqlimport') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group mt-3 col-6 {{ $errors->has('app_url') ? ' has-error ' : '' }}">
                <label for="app_url">
                    {{ trans('App Url') }}

                    <span class="text-red">*</span>
                </label>
                <input type="url" name="app_url" id="app_url" value="{{url('/')}}" placeholder="{{ trans('App Url') }}" />
                @if ($errors->has('app_url'))
                <span class="error-block">
                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    {{ $errors->first('app_url') }}
                </span>
                @endif
            </div>

            <div class="form-group col-6 {{ $errors->has('database_hostname') ? ' has-error ' : '' }}">
                <label for="database_hostname">
                    {{ trans('Database Host') }}

                    <span class="text-red">*</span>
                </label>
                <input type="text" name="database_hostname" id="database_hostname" value="127.0.0.1" placeholder="{{ trans('Database Host') }}" />
                @if ($errors->has('database_hostname'))
                <span class="error-block">
                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    {{ $errors->first('database_hostname') }}
                </span>
                @endif
            </div>

            <div class="form-group col-6 {{ $errors->has('database_port') ? ' has-error ' : '' }}">
                <label for="database_port">
                    {{ trans('Database Port') }}

                    <span class="text-red">*</span>
                </label>
                <input type="number" name="database_port" id="database_port" value="3306" placeholder="{{ trans('Database Port') }}" />
                @if ($errors->has('database_port'))
                <span class="error-block">
                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    {{ $errors->first('database_port') }}
                </span>
                @endif
            </div>
            <label for="database_name">Database Name:</label>
            <input type="text" name="database_name" id="database_name">


            <label for="database_username">Database Username:</label>
            <input type="text" name="database_username" id="database_username">


            <label for="database_password">Database Password:</label>
            <input type="password" name="database_password" id="database_password">



            <div class="form-group">
                <label class="form-label">Upload File</label>
                <div class="input-group file-browser">
                    <input type="file" class="form-control" name="sql_file" style="height: 22px">
                </div>
            </div>
            <br>
            <div class="buttons">
                <button class="button" id="nextbutton" type="submit" onclick="button(this)">
                    {{ trans('Next') }}
                    <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
                </button>
            </div>
        </form>

    </div>
</div>

@endsection
@section('scripts')
<script src="{{asset('installer/clipboard/clipboard.js')}}?v=<?php echo time(); ?>"></script>

<script type="text/javascript">
    "use strict";
    var clipboard = new ClipboardJS('.btn');

    clipboard.on('success', function(e) {
        console.log(e);
    });

    clipboard.on('error', function(e) {
        console.log(e);
    });

    function button(bt) {
        document.getElementById("nextbutton").innerHTML = `Please Wait... <i class="fa fa-spinner fa-spin"></i>`;
        bt.disabled = true;
        bt.form.submit();
        document.getElementById("nextbutton").style.cursor = "not-allowed";
        document.getElementById("nextbutton").style.opacity = "0.5";
    }
</script>
@endsection
