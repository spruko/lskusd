@extends('Installation::installer.layouts.InstallerMaster')

@section('template_title')
{{ trans('Step 3 | Environment Settings') }}
@endsection

@section('title')
{!! trans('Environment Settings') !!}
@endsection

@section('container')
<div class="tabs tabs-full">

    <div>
        <div class="settings-page-head mb-3">Choose an Option :</div>
    </div>
    <div class="settings-page-content">
        <div>
            <div class="settings-page-head-title">Option1: For New Installation</div>
            <p class="settings-page-sub-title">Begin by creating your very own database:</p>
            <a href="{{ route('SprukoAppInstaller::environment') }}" class="social-icon button text-primary mb-3">
                {{ 'Create New Database' }}
            </a>
        </div>
        <div class="mb-3 mt-3">
            <div class="settings-page-head-title">Option2: For Domain Change</div>
            <p class="settings-page-sub-title">Already have a database? Fantastic! Take the next step by uploading your existing database. Seamlessly transition your data into a new environment:</p>
            <a href="{{ route('SprukoAppInstaller::importsql') }}" class="social-icon button text-secondary mb-3">
                {{ 'Import Database' }}
            </a>
        </div>
    </div>

</div>
@endsection
@section('scripts')

<script type="text/javascript">
    "use strict";

    function button(bt) {
        document.getElementById("nextbutton").innerHTML = `Please Wait... <i class="fa fa-spinner fa-spin"></i>`;
        bt.disabled = true;
        bt.form.submit();
        document.getElementById("nextbutton").style.cursor = "not-allowed";
        document.getElementById("nextbutton").style.opacity = "0.5";
    }
</script>
@endsection
