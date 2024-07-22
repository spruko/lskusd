@extends('Installation::installer.layouts.UpdateMaster')

@section('title')
    {{ trans('Verify your purchase code') }}
@endsection


@section('container')
        <form method="POST" action="{{url('admin/licenseinfoenter/')}}" class="tabs-wrap">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="row">
                <input type="text" class="form-control @error('envato_id') is-invalid @enderror" name="envato_id">
                @error('envato_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ lang($message) }}</strong>
                    </span>
                @enderror
                <div class="buttons">
                    <button class="button" type="submit" id="buttonfinal" >
                        {{ lang('Submit Purchase Code') }}
                    </button>
                </div>
            </div>
        </form>

@endsection
@section('scripts')
    <script src="{{asset('build/assets/plugins/bootstrap/popper.min.js')}}?v=<?php echo time(); ?>"></script>
    <script src="{{asset('build/assets/plugins/bootstrap/js/bootstrap.min.js')}}?v=<?php echo time(); ?>"></script>


    <script src="{{asset('build/assets/plugins/toastr/toastr.min.js')}}?v=<?php echo time(); ?>"></script>


@endsection
