@extends('pages::adminLte.layouts.master')

@section('pageTitle')
    {{ __('customerappaddon::view.customer_app_settings') }}
@endsection

<style>
    #pageLoader .modal-content {
        padding: 300px;
    }
    .spinner-border {
        width: 7rem;
        height: 7rem;
    }
</style>

@section('content')

<!--begin::Basic info-->
<div class="card mb-5 mb-xl-10">

<div class="wrapper-settings">
    <div class="mx-auto mb-5 col-lg-12">
        @if($app_request != null && $app_request->app_status == 'new')
            <div class="alert alert-info my-5 text-center">
                <p>{{ __('customerappaddon::view.application_in_progress_status') }}</p>
                <p>{{ __('customerappaddon::view.sending_request_date') }}: {{date('d-m-Y', strtotime($app_request->created_at))}}</p>
            </div>
        @elseif($app_request != null && $app_request->app_status == 'done')
            <div class="alert alert-info my-5 text-center">
                <p>{{ __('customerappaddon::view.application_created_successfully') }}</p>
                <div class="app-container d-flex justify-content-center">
                    <div class="app mx-3">
                        <a target="_blank" href="{{ $app_request->android_url }}"><img width="25" height="25" src="{{ asset('assets/img/google-play.png') }}"></a>
                    </div>
                    <div class="app mx-3">
                        <a target="_blank" href="{{ $app_request->ios_url }}"><img width="25" height="25" src="{{ asset('assets/img/app-store.png') }}"></a>
                    </div>
                </div>
            </div>
        @else
        <div class="card mb-5">
            <div class="card-body">
                <div class="message  message--warning">
                    <p>{{ __('customerappaddon::view.customer_app_settings') }}.</p>
                </div>

                <form class="form-horizontal" id="kt_form_1" action="{{ fr_route('send.customer.app.info') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.cargo_purchase_code') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('cargo_purchase_code') is-invalid @enderror" name="cargo_purchase_code" required>

                            @error('cargo_purchase_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.addon_purchase_code') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('addon_purchase_code') is-invalid @enderror" name="addon_purchase_code" required>

                            @error('addon_purchase_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.app_name') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" name="app_name" required>

                            @error('app_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.app_name_shown') }}</span>

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.application_icon') }}</label>

                        <div class="col-md-12">
                            <input type="file" class="form-control @error('application_icon') is-invalid @enderror" name="application_icon" required>

                            @error('application_icon')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.upload_application_icon') }}</span>

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.app_bundle_id') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('app_bundle_id') is-invalid @enderror" placeholder="ex.. app.domain.cargo" name="app_bundle_id" required>

                            @error('app_bundle_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.application_splash_screen_background') }}</label>

                        <div class="col-md-1">
                            <input type="color" class="form-control @error('application_splash_screen_background') is-invalid @enderror" name="application_splash_screen_background" required>

                            @error('application_splash_screen_background')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.application_splash_screen') }}</label>

                        <div class="col-md-12">
                            <input type="file" class="form-control @error('application_splash_screen') is-invalid @enderror" name="application_splash_screen" required>

                            @error('application_splash_screen')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.upload_application_splash') }}</span>

                        </div>
                    </div>

                    <p class="alert alert-info" style="clear:both;">{{ __('customerappaddon::view.get_issuer_id') }} <a href="https://cargo.bdaia.com/courier/doc.html">{{ __('customerappaddon::view.from_here') }}</a> {{ __('customerappaddon::view.there_required') }}.</p>


                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.issuer_id') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('issuer_id') is-invalid @enderror" name="issuer_id" required>

                            @error('issuer_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.key_id') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('key_id') is-invalid @enderror" name="key_id" required>

                            @error('key_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.auth_key_file_content') }}</label>

                        <div class="col-md-12">
                            <textarea class="form-control @error('auth_key_content') is-invalid @enderror" name="auth_key_content" required></textarea>

                            @error('auth_key_content')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.apple_id') }}</label>

                        <div class="col-md-12">
                            <input type="text" class="form-control @error('apple_id') is-invalid @enderror" name="apple_id" required>

                            @error('apple_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.ios_app_store_number') }}</span>

                        </div>
                    </div>

                    <p class="alert alert-info" style="clear:both;">{{ __('customerappaddon::view.notifications_icon') }}</p>

                    <div class="form-group mb-5">

                        <div class="wrapper-inputs col-md-12">

                        <div class="custom-control custom-switch form-check form-switch pt-3">
                                <input class="custom-control-input form-check-input" type="checkbox" id="notification_icon">
                                <label class="custom-control-label" for="notification_icon"></label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group mb-5" id="small_notification_icon_color" style="display:none;">
                        <label for="file" class="col-md-12 control-labe requiredl">{{ __('customerappaddon::view.small_notification_icon_color') }}</label>

                        <div class="col-md-1">
                            <input type="color" class="form-control @error('small_notification_icon_color') is-invalid @enderror" name="small_notification_icon_color" required>

                            @error('small_notification_icon_color')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    <div class="form-group mb-5" id="small_notifications_icon" style="display:none;">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.small_notifications_icon') }}</label>

                        <div class="col-md-12">
                            <input type="file" class="form-control @error('small_notifications_icon') is-invalid @enderror" name="small_notifications_icon" required>

                            @error('small_notifications_icon')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.upload_small_notification_icon') }}</span>

                        </div>
                    </div>

                    <p class="alert alert-info" style="clear:both;">{{ __('customerappaddon::view.get_google_service') }} <a href="https://cargo.bdaia.com/courier/doc.html">{{ __('customerappaddon::view.from_here') }}</a>.</p>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.google_service_android') }}</label>

                        <div class="col-md-12">

                            <input type="file" class="form-control @error('google_service_android') is-invalid @enderror" name="google_service_android" required>

                            @error('google_service_android')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.upload_google_android_file') }}</span>

                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label for="file" class="col-md-12 control-label required">{{ __('customerappaddon::view.google_service_ios') }}</label>

                        <div class="col-md-12">
                            <input type="file" class="form-control @error('google_service_ios') is-invalid @enderror" name="google_service_ios" required>

                            @error('google_service_ios')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <span>{{ __('customerappaddon::view.upload_google_ios_file') }}</span>

                        </div>
                    </div>

                    <div class="mb-0 text-right form-group">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('customerappaddon::view.create_application') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
</div>

<div class="modal modal-fullscreen-xl" id="pageLoader" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">{{ __('customerappaddon::view.uploading') }}...</span>
                </div>
                <span class="d-block mt-3">{{ __('customerappaddon::view.sending') }}...</span>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
$("#custom_url").on('change', function() {
if ($(this).is(':checked')) {
    $(this).attr('checked', 'true');
    $("input[name='custom_url']").show();
}
else {
   $(this).removeAttr('checked');
   $("input[name='custom_url']").hide();
}});

$("#notification_icon").on('change', function() {
if ($(this).is(':checked')) {
    $(this).attr('checked', 'true');
    $("#small_notification_icon_color").show();
    $("#small_notifications_icon").show();
}
else {
   $(this).removeAttr('checked');
   $("#small_notification_icon_color").hide();
   $("#small_notifications_icon").hide();
}});

$( "#kt_form_1" ).submit(function( ) {

$('#pageLoader').show();
});

$(function () {

$('#pageLoader').hide();
})

</script>

@endsection
