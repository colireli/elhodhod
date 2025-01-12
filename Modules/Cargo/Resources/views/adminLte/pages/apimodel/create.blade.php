@extends('cargo::adminLte.layouts.master')

@section('content')
    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Create API Model') }}</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <form id="kt_account_profile_details_form" class="form" action="{{ route('admin.apimodel.store') }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- redirect_input -->
            <div class="card-body">
                <div class="form-group">
                    <label>{{__('Api Name')}}:</label>
                    <input type="text" id="name" class="form-control" placeholder="{{__('Api Name')}}" name="apiModel[name]">
                </div>
                <!-- <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{__('Api Picture')}}:</label>

                            <div class="input-group " data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                <input type="hidden" name="img" class="selected-files" value="{{old('featured_image')}}">
                            </div>
                            <div class="file-preview">
                            </div>
                        </div>
                    </div>
                </div> -->



                <div class="form-group">
                    <h5 class="mb-0 h6">{{__('API Variabls')}}</h5>
                </div>

                <div class="form-group">
                    <label>api_token :</label>
                    <input type="text" id="api_token" class="form-control" placeholder="{{__('api_token')}}" name="apiModel[api_token]">
                </div>

                <div class="form-group">
                    <label>user_guid :</label>
                    <input type="text" id="user_guid" class="form-control" placeholder="{{__('user_guid')}}" name="apiModel[user_guid]">
                </div>

                <div class="form-group">
                    <label>reference :</label>
                    <input type="text" id="reference" class="form-control" placeholder="{{__('reference')}}" name="apiModel[reference]">
                </div>

                <div class="form-group">
                    <label>client :</label>
                    <input type="text" id="client" class="form-control" placeholder="{{__('client')}}" name="apiModel[client]">
                </div>

                <div class="form-group">
                    <label>phone :</label>
                    <input type="text" id="phone" class="form-control" placeholder="{{__('phone')}}" name="apiModel[phone]">
                </div>

                <div class="form-group">
                    <label>adresse :</label>
                    <input type="text" id="adresse" class="form-control" placeholder="{{__('adresse')}}" name="apiModel[adresse]">
                </div>

                <div class="form-group">
                    <label>wilaya_id :</label>
                    <input type="text" id="wilaya_id" class="form-control" placeholder="{{__('wilaya_id')}}" name="apiModel[wilaya_id]">
                </div>

                <div class="form-group">
                    <label>commune :</label>
                    <input type="text" id="commune" class="form-control" placeholder="{{__('commune')}}" name="apiModel[commune]">
                </div>

                <div class="form-group">
                    <label>montant :</label>
                    <input type="text" id="montant" class="form-control" placeholder="{{__('montant')}}" name="apiModel[montant]">
                </div>

                <div class="form-group">
                    <label>remarque :</label>
                    <input type="text" id="remarque" class="form-control" placeholder="{{__('remarque')}}" name="apiModel[remarque]">
                </div>

                <div class="form-group">
                    <label>produit :</label>
                    <input type="text" id="produit" class="form-control" placeholder="{{__('produit')}}" name="apiModel[produit]">
                </div>

                <div class="form-group">
                    <label>type_id :</label>
                    <input type="text" id="type_id" class="form-control" placeholder="{{__('type_id')}}" name="apiModel[type_id]">
                </div>

                <div class="form-group">
                    <label>poids :</label>
                    <input type="text" id="poids" class="form-control" placeholder="{{__('poids')}}" name="apiModel[poids]">
                </div>

                <div class="form-group">
                    <label>stop_desk :</label>
                    <input type="text" id="stop_desk" class="form-control" placeholder="{{__('stop_desk')}}" name="apiModel[stop_desk]">
                </div>
                
                <div class="form-group">
                    <label>reference stop desk :</label>
                    <input type="text" id="ref_stopdesk" class="form-control" placeholder="{{__('ref_stopdesk')}}" name="apiModel[ref_stopdesk]">
                </div>

                <div class="form-group">
                    <label>stock :</label>
                    <input type="text" id="stock" class="form-control" placeholder="{{__('stock')}}" name="apiModel[stock]">
                </div>

                <div class="form-group">
                    <label>quantite :</label>
                    <input type="text" id="quantite" class="form-control" placeholder="{{__('quantite')}}" name="apiModel[quantite]">
                </div>

                <div class="form-group">
                    <label>activity :</label>
                    <input type="text" class="form-control" placeholder="{{__('KEY')}}" name="apiModel[activity][KEY]">

                    <input type="text" class="form-control" placeholder="{{__('SAVED_STATUS')}}" name="apiModel[activity][SAVED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('REQUESTED_STATUS')}}" name="apiModel[activity][REQUESTED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('APPROVED_STATUS')}}" name="apiModel[activity][APPROVED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('CLOSED_STATUS')}}" name="apiModel[activity][CLOSED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('CAPTAIN_ASSIGNED_STATUS')}}" name="apiModel[activity][CAPTAIN_ASSIGNED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('RECIVED_STATUS')}}" name="apiModel[activity][RECIVED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('DELIVERED_STATUS')}}" name="apiModel[activity][DELIVERED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('PENDING_STATUS')}}" name="apiModel[activity][PENDING_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('SUPPLIED_STATUS')}}" name="apiModel[activity][SUPPLIED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('RETURNED_STATUS')}}" name="apiModel[activity][RETURNED_STATUS]">
                    <input type="text" class="form-control" placeholder="{{__('RETURNED_ON_RECEIVER')}}" name="apiModel[activity][RETURNED_ON_RECEIVER]">
                    <input type="text" class="form-control" placeholder="{{__('ALERT_STATUS')}}" name="apiModel[activity][ALERT_STATUS]">
                </div>

                <div class="form-group">
                    <label>tracking :</label>
                    <input type="text" id="tracking" class="form-control" placeholder="{{__('tracking')}}" name="apiModel[tracking]">
                </div>

                <div class="form-group">
                    <label>success :</label>
                    <input type="text" id="success" class="form-control" placeholder="{{__('success')}}" name="apiModel[success]">
                </div>

                 <!--begin::Actions-->
                 <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>
                    <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">@lang('view.create')</button>
                </div>
                <!--end::Actions-->
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">

    $(document).ready(function() {

        FormValidation.formValidation(
            document.getElementById('kt_form_1'), {
                fields: {
                    "apiModel[name]": {
                        validators: {
                            notEmpty: {
                                message: '{{__("This is required!")}}'
                            }
                        }
                    },

                },


                plugins: {
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    trigger: new FormValidation.plugins.Trigger(),
                    // Bootstrap Framework Integration
                    bootstrap: new FormValidation.plugins.Bootstrap(),
                    // Validate fields when clicking the Submit button
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    // Submit the form when all fields are valid
                    defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    icon: new FormValidation.plugins.Icon({
                        valid: 'fa fa-check',
                        invalid: 'fa fa-times',
                        validating: 'fa fa-refresh',
                    }),
                }
            }
        );
    });

</script>
@endsection
