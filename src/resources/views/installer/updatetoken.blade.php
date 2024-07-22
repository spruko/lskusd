@extends('Installation::installer.layouts.InstallerMaster')

@section('template_title')
{{ 'Welcome' }}
@endsection

@section('title')
{{ 'Laravel Installer' }}
@endsection

@section('container')
<div class="d-flex justify-content-end">
  <a class="button" href="{{ route('SprukoAppInstaller::addnewpurchasecode') }}">Add New Purchasecode</a>
</div>
<p class="fs-12 text-center">
  {{ 'Please enter your token' }}
</p>
<form method="post" action="{{ route('SprukoAppInstaller::verifyUpdatetoken') }}" class="tabs-wrap">
  @csrf
  <div class="row">
    <input style="display: none" name="envato_purchasecode" value="{{$envato_purchasecode}}">
    <div class="form-group col-6 {{ $errors->has('app_token') ? ' has-error ' : '' }}">
      <label for="app_token">
        {{ trans('Enter Your Token.') }}
        <span class="text-red">*</span>
      </label>
      <input type="text" class="" name="app_token" id="app_token" value="" placeholder="{{ trans('Enter Your token') }}" />
      @if($message = Session::get('error'))
          <span class="error-block text-red">
              <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
              {{ $message }}
          </span>
      @endif
    </div>
  </div>

  <div class="buttons">
    <button class="button" type="submit" id="buttonfinal" onclick="button(this)">
      {{ trans('Verify') }}
      <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
    </button>
  </div>
</form>
@endsection
