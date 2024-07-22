@extends('Installation::installer.layouts.adminrelatemaster')
@section('title')
    {{ trans('Token Generation') }}
@endsection


@section('content')
    <div class="container">
        <div class="row justify-content-center py-4">
            <div class="col-md-6">
                <div class="card ">
                    <div class="card-body">

                        <div class="mx-auto my-5 text-center" style="font-size: 18px; font-weight:bold">{{ lang('Welcome to the Token Generation Page!') }}</div><br>

                        <p>{{ lang('Generate a secure private token for your application with just a click! This page allows you
                            to effortlessly create a unique token that will serve as a crucial key for your application.') }}
                            <b>{{ lang('This token is generated only once') }}</b>.
                        </p>

                        <p>{{ lang('To generate your private token, simply click the') }} <b>{{ lang('"Generate Token"') }}</b> {{ lang('button below. Once
                            generated, make sure to') }} <b>{{ lang('securely store your token') }}</b>, {{ lang('as it will be required during') }}
                            <b>{{ lang('reinstallation on a different server') }}</b>. {{ lang('This token acts as a safeguard, ensuring the smooth
                            transition and retention of your application settings.') }}
                        </p>

                        <p>{{ lang('Remember, the generated token is a one-time key, so treat it with the utmost confidentiality.
                            If you ever need to reinstall your application on a different server, having this token on
                            hand will streamline the process and ensure a seamless transition.') }}</p>

                        <p>{{ lang('Click the') }} <b>{{ lang('"Generate Token"') }}</b> {{ lang('button now and experience the ease of securing and preserving
                            your
                            application settings!') }}</p>

                        <p class="p-3 border-1">

                            <b>{{ lang('Note') }} :</b> {{ lang('To receive a mail Should need to complete "SMTP" setup.') }}
                        </p>

                        <div id="aftertokengeneratediv d-none">
                            <span class="mx-auto">
                                <div class="d-flex">
                                    <p class="mt-2"> <b>{{ lang('Token') }}</b> : </p>
                                    <h5 style="display: inline-block;" id="tokenvalue" class="ms-2 pt-2"></h5>
                                    <div id="beforetokengeneratediv" class="ms-2 mb-5">
                                        <a href="javascript:void(0);" class="btn btn-primary mx-auto d-flex" id="generatingid">{{ lang('Generate Token') }}</a>
                                    </div>
                                </div>
                            </span>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script type="text/javascript">
        $(function() {
            "use strict";

            // Csrf Field
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#generatingid').on('click', function() {
                $('#generatingid').html('Sending ... <i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: '{{ route('admin.tokenGenerate') }}',
                    success: function(data) {
                        $('#aftertokengeneratediv').removeClass('d-none');
                        $('#beforetokengeneratediv').addClass('d-none');
                        $('#tokenvalue').html(data);
                        toastr.success('{{ lang('Token Generated Successfully.', 'alerts') }}');
                    },
                    error: function(data) {
                        console.log('error',data);
                    }
                });
            });

        });
    </script>
@endsection
