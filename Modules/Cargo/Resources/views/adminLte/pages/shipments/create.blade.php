
@php
    $user_role = auth()->user()->role;
    $admin  = 1;
    $auth_staff  = 0;
    $auth_branch = 3;
    $auth_client = 4;

    $branches = Modules\Cargo\Entities\Branch::where('is_archived', 0)->get();
    $clients = Modules\Cargo\Entities\Client::where('is_archived', 0)->get();
    $receivers = Modules\Cargo\Entities\Receiver::where('is_archived', 0)->get();
    

    $countries = Modules\Cargo\Entities\Country::where('covered',1)->get();
    $packages = Modules\Cargo\Entities\Package::all();


    $paymentSettings = resolve(\Modules\Payments\Entities\PaymentSetting::class)->toArray();
@endphp


@extends('cargo::adminLte.layouts.master')

@section('pageTitle')
    {{ __('cargo::view.create_new_shipment') }}
@endsection

@section('content')

    <style>      
        .notification {
            display: flex;
            justify-content: space-between;
            background-color: #ff1a1ac9;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>  
        
    <div>

        @if(auth()->user()->can('shipping-rates') || $user_role == $admin )
            @if( Modules\Cargo\Entities\ShipmentSetting::getVal('def_shipping_cost') == null)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_configure_shipping_rates_in_creation_will_be_zero_without_configuration') }},
                    <a class="alert-link" href="{{ route('shipments.settings.fees') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif



        @if(auth()->user()->can('add-covered-countries') || $user_role == $admin )
            @if(count($countries) == 0 || Modules\Cargo\Entities\State::where('covered', 1)->count() == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_configure_your_covered_countries_and_regions') }},
                    <a class="alert-link" href="{{ route('countries.index') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif

        @if(auth()->user()->can('manage-areas') || $user_role == $admin )
            @if(Modules\Cargo\Entities\Area::count() == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_add_areas_before_creating_your_first_shipment') }},
                    <a class="alert-link" href="{{ route('areas.create') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif

        @if(auth()->user()->can('manage-packages') || $user_role == $admin )
            @if(count($packages) == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_add_package_types_before_creating_your_first_shipment') }},
                    <a class="alert-link" href="{{ route('packages.create') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif

        @if(auth()->user()->can('manage-branches') || $user_role == $admin )
            @if($branches->count() == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_add_branches_types_before_creating_your_first_shipment') }},
                    <a class="alert-link" href="{{ route('branches.create') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif

        @if(auth()->user()->can('manage-clients') || $user_role == $admin )
            @if($clients->count() == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_add_clients_types_before_creating_your_first_shipment') }},
                    <a class="alert-link" href="{{ route('clients.create') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif
        

        @if(auth()->user()->can('manage-clients') || $user_role == $admin )
            @if($receivers->count() == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_add_receivers_types_before_creating_your_first_shipment') }},
                    <a class="alert-link" href="{{ route('receivers.create') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif
        

        @if(auth()->user()->can('payments-settings') || $user_role == $admin )
            @if(count($paymentSettings) == 0)
            <div class="row">
                <div class=" notification alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                    {{ __('cargo::view.please_add_payments_before_creating_your_first_shipment') }},
                    <a class="alert-link" href="{{ route('payments.index') }}">{{ __('cargo::view.configure_now') }}</a>
                </div>
            </div>
            @endif
        @endif

        @if($user_role == $auth_branch || $user_role == $auth_staff || $user_role == $auth_client )
            @if( Modules\Cargo\Entities\ShipmentSetting::getVal('def_shipping_cost') == null || count($countries) == 0 || Modules\Cargo\Entities\State::where('covered', 1)->count() == 0 || Modules\Cargo\Entities\Area::count() == 0 || count($packages) == 0 || $branches->count() == 0 || $clients->count() == 0)
                <div class="row">
                    <div class=" notification text-center alert alert-danger col-lg-8" style="margin: auto;margin-top:10px;" role="alert">
                        {{ __('cargo::view.please_ask_your_administrator_to_configure_shipment_settings_first_before_you_can_create_a_new_shipment') }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Start Modal  Create a New  Client Form -->
    <div>
        <div class="modal fade" id="myModal">
            <div class="modal-dialog " style=" max-width: 1478px !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('cargo::view.create_new_client') }}</h4>
                        <button type="button" id="closeModalButton" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Your form content goes here -->
                        <form id="kt_account_profile_details_form" class="form">
                            <!-- Form fields -->
                            @include('cargo::adminLte.pages.clients.form', ['typeForm' => 'create'])

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                            {{-- <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>--}}
                                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit_client">@lang('view.create')</button> 
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Create a New  Client Form -->

    <!-- Start Modal  Create a New  Receiver  Form -->
    <div>
        <div class="modal fade" id="myModalReciver">
            <div class="modal-dialog " style=" max-width: 1478px !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('cargo::view.create_new_receiver') }}</h4>
                        <button type="button" id="closeModalButtonReciver" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Your form content goes here -->
                        <form id="kt_account_profile_details_receicer_form" class="form">
                            <!-- Form fields -->
                            @include('cargo::adminLte.pages.receicers.form', ['typeForm' => 'create'])

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                            {{--   <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>--}}
                                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit_client">@lang('view.create')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Create a New  Receiver  Form -->
            
    <br><br>
    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('cargo::view.create_new_shipment') }}</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->
        <!--begin::Content-->
        <div>
            <!--begin::Form-->
            <form id="kt_form_1" class="form" action="{{ fr_route('shipments.store') }}" method="post" enctype="multipart/form-data" novalidate>
                <!--begin::Card body-->
                <div class="card-body border-top p-9">
                    @include('cargo::adminLte.pages.shipments.form', ['typeForm' => 'create'])
                </div>
                <!--end::Card body-->
                <!--begin::Actions-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>
                    <button type="button" class="btn btn-primary" onclick="get_estimation_cost()" id="kt_account_profile_details_submit">@lang('view.create')</button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Basic info-->

@endsection

{{-- Inject Scripts --}}
@push('js-component')
    <script>
        $(document).ready(function() {
            $('#kt_account_profile_details_form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: '{{ route('store.in.shipmint') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide Modal
                        $("#myModal").modal("hide");

                        var clientId = response.client.id;

                        var clientName = response.client.name;
                        var clientPhone = response.client.responsible_mobile;
                        var clientCountryCode = response.client.country_code;
                        var clientAddress = response.client_address;
                        
                        var option = '<option selected value="' + clientId + '" data-phone="' + clientPhone + '" data-country="' + clientCountryCode + '">' + clientName + '</option>';
                        
                        // Add option to customer list
                        $('#client-id').append(option);
                        
                        // Make the added option checked
                    
                        // get Client Addresses 
                        getClientAddresses(clientId);

                        $("#client_phone").val(clientPhone);  
                        // change  client Country Code auto

                    },
                    error: function(error) {
                        alert('Verify that all data is present correctly');
                    }
                });
            });
            $('#closeModalButton').click(function() {
                $('#myModal').modal('hide');
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#kt_account_profile_details_receicer_form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: '{{ route('store.receiver.in.shipmint') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide Modal
                        $("#myModalReciver").modal("hide");

                        var receiverId = response.receiver.user_id;
                        var receiverName = response.receiver.name;
                        var receiverPhone = response.receiver.receiver_mobile;
                        var receiverCountryCode = response.receiver.country_code;
                        var clientAddress = response.receiver.reciver_address;
                        var message = response.message ;
                        var errors = response.error ;
                        var option = '<option selected  value="' + receiverName + '" data-phone="' + receiverPhone + '" data-country="' + receiverCountryCode + '">' + receiverName + '</option>';
                        
                        // Add option to customer list
                        $('#reciver-id').append(option);
                        
                        // Make the added option checked
                        $('#reciver-id').val(receiverName);

                        $("#reciver_phone").val(receiverPhone);  
                        $("#country_code").val(receiverCountryCode);
                        $("#reciver_address").val(clientAddress);

                    },
                    error: function(error) {
                        alert('Verify that all data is present correctly');
                    }
                });
            });
            $('#closeModalButtonReciver').click(function() {
                $('#myModalReciver').modal('hide');
            });
        });
    </script>
@endpush