@extends('cargo::adminLte.layouts.master')

@section('content')
    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Edit Company') }}</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <form id="kt_account_profile_details_form" class="form" action="{{ route('admin.company.update') }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- redirect_input -->
            <div class="card-body">
                <input type="text" class="d-none" name="id" value="{{$company->id}}">
                <div class="form-group">
                    <label>{{__('Company  Name')}}:</label>
                    <input type="text" id="name" class="form-control" value="{{$company->name}}" placeholder="{{__('Here')}}" value="{{$company->name}}" name="company[name]">
                </div>
                <div class="form-group">
                    <label>{{__('Api Token')}}:</label>
                    <input id="api_token-field" type="text" class="form-control" placeholder="{{__('Api Token')}}" value="{{$company->api_token}}" name="company[api_token]">
                </div>
                <div class="form-group">
                    <label>{{__('User Guid')}}:</label>
                    <input id="user_guid-field" type="text" class="form-control" placeholder="{{__('User Guid')}}" value="{{$company->user_guid}}" name="company[user_guid]">
                </div>

                <div class="form-group">
                    <h5 class="mb-0 h6">{{__('API Urls')}}</h5>
                </div>

                <div class="form-group">
                    <label>{{__('Create Order')}}:</label>
                    <input type="text" id="create_order" class="form-control" placeholder="{{__('Create Order')}}" value="{{$company->create_order}}" name="company[create_order]">
                </div>

                <div class="form-group">
                    <label>{{__('Valid Order')}}:</label>
                    <input type="text" id="valid_order" class="form-control" placeholder="{{__('Valid Order')}}" value="{{$company->valid_order}}" name="company[valid_order]">
                </div>

                <div class="form-group">
                    <label>{{__('Delete Order')}}:</label>
                    <input type="text" id="delete_order" class="form-control" placeholder="{{__('Delete Order')}}" value="{{$company->delete_order}}" name="company[delete_order]">
                </div>

                <div class="form-group">
                    <label>{{__('Tracking Order')}}:</label>
                    <input type="text" id="tracking_order" class="form-control" placeholder="{{__('Tracking Order')}}" value="{{$company->tracking_order}}" name="company[tracking_order]">
                </div>

                <!-- <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{__('Company Picture')}}:</label>

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
                    <label>{{__('Company Api Model')}}:</label>
                    <select class="form-control kt-select1 apimodel" id="select-api" data-control="select2"
                            data-allow-clear="true" name="company[model_id]">
                        <option></option>
                        @foreach ($apiModels as $model)
                        <option value="{{ $model->id }}"
                            @if ($model->id == $company->model_id)
                                selected
                            @endif>
                            {{ $model->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @if (auth()->check() && auth()->user()->role == 1)

                <div class="form-group">
                    <label>{{__('Company Branch')}}:</label>
                    <select class="form-control kt-select2 branch" id="branch_id" data-control="select2"
                            data-allow-clear="true" name="company[branch_id]">
                        <option></option>
                        @foreach ($branchs as $branch)
                        <option value="{{ $branch->id }}"
                            @if ($branch->id == $company->branch_id)
                                selected
                            @endif>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @else

                <input type="text" class="d-none" value="{{ $company->branch_id }}" name="company[branch_id]">

                @endif

                <div class="form-group">
                    <label>{{__('Company Plan')}}:</label>
                    <select class="form-control kt-select1 plan" id="select-plan" data-control="select2"
                            data-allow-clear="true" name="company[plan_id]">
                        <option></option>
                        @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}"
                            @if ($plan->id == $company->plan_id)
                                selected
                            @endif>
                            {{ $plan->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                 <!--begin::Actions-->
                 <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>
                    <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">@lang('view.update')</button>
                </div>
                <!--end::Actions-->
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')

<script>
// add_update_v1
    //  START_CODE
    $('#branch_id').on('change',function () {
        var id = $(this).val();
        $.get("{{route('admin.company.get-company-plan')}}?id=" + id, function(data) {
            $('select[name ="company[plan_id]"]').empty();
            // $('select[name ="company[plan_id]"]').append('<option value="">Select Plan</option>');
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                $('select[name ="company[plan_id]"]').append('<option value="' + element['id'] + '">' + element['title'] + '</option>');
            }

         });
        });
    // END_CODE
</script>

<script type="text/javascript">

$('.branch').select2({
        placeholder: 'Select Branch',
        language: {
          noResults: function() {
            return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{ fr_route('branches.create') }}?redirect=admin.captains.create"
              class="btn btn-primary" >Manage {{__('Branchs')}}</a>
              </li>`;
          },
        },
        escapeMarkup: function(markup) {
          return markup;
        },
    });

    $('.apimodel').select2({
        placeholder: 'Select API Model',
        language: {
          noResults: function() {
            return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{ fr_route('branches.create') }}?redirect=admin.captains.create"
              class="btn btn-primary" >Manage {{__('API Models')}}</a>
              </li>`;
          },
        },
    });

    $(document).ready(function() {


            FormValidation.formValidation(
                document.getElementById('kt_form_1'), {
                    fields: {
                        "company[name]": {
                            validators: {
                                notEmpty: {
                                    message: '{{__("This is required!")}}'
                                }
                            }
                        },
                        "company[api_token]": {
                            validators: {
                                notEmpty: {
                                    message: '{{__("This is required!")}}'
                                },
                            }
                        },

                        "company[model_id]": {
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
