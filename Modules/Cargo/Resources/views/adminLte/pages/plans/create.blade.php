@extends('cargo::adminLte.layouts.master')

@section('content')

 <!--begin::Basic info-->
 <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Create New Customer Plan') }}</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <form id="kt_account_profile_details_form" class="form" action="{{route('admin.plan.store')}}" id="kt_form_1" method="POST"
            enctype="multipart/form-data">
        @csrf


        <div class="card-body">
            @if (auth()->check() && auth()->user()->role == 1)
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>{{__('Branches')}}:</label>
                        <select class="form-control kt-select2 branch" data-control="select2"
                                data-allow-clear="true" id="branch_id" name="branch_id">
                        
                            @foreach ($branchs as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
            </div>
            @endif 
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>{{ __('Plan Name') }}:</label>
                        <input type="text" name="name" class="form-control" required>
                        @error('name')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>{{ __('Copy From') }}:</label>
                        <select name="copy" data-control="select2"
                            data-allow-clear="true" id="copy" class="form-control">
                            <option value="">{{ __('No Select') }}</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->title }}</option>
                            @endforeach
                        </select>
                        @error('copy')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <div id="propr">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ __('Default Home Rate') }}:</label>
                            <input type="number" value="0" name="home" class="form-control" required>
                            @error('home')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ __('Default Desk Rate') }}:</label>
                            <input type="number" value="0" name="desk" class="form-control" required>
                            @error('desk')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ __('Default Return Rate') }}:</label>
                            <input type="number" value="0" name="return" class="form-control" required>
                            @error('return')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ __('Default Insurance Rate') }}:</label>
                            <input type="number" value="0" name="rate" class="form-control" required>
                            @error('rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- // add_update_v1
                //  START_CODE -->
                <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>{{ __('Default Company') }}:</label>
                        <select name="company" data-control="select2"
                             id="company" class="form-control">
                            <option value="0">{{__('Intern')}}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('company')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                </div>
                <!-- //  END_CODE -->
            </div>
            </div>

                <!--begin::Actions-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>
                    <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">@lang('view.create')</button>
                </div>
                <!--end::Actions-->

        </form>
    </div>

</div>



@endsection


@section('scripts')

<script type="text/javascript">
$(document).ready(function() {

        $('#copy').on('change',function () {
                // This function will be executed when the input value changes
                var inputValue = $(this).val();
                if (inputValue) {
                    $('#propr').css('display', 'none');
                } else {
                    $('#propr').css('display', 'block');
                }
        });
        // $('#change-country-to').change(function() {
    //     var id = $(this).val();
    //    id=4;
    //     $.get("{{route('ajax.getStates')}}?country_id=" + id, function(data) {
    //         $('select[name ="to_state_id"]').empty();
    //         $('select[name ="to_state_id"]').append('<option value=""></option>');
    //         for (let index = 0; index < data.length; index++) {
    //             const element = data[index];
    //             $('select[name ="to_state_id"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
    //         }


    //     });


    // add_update_v1
    //  START_CODE
    function getBranchPlan(id){ 
        $.get("{{route('admin.plan.get-branch-plan')}}?id=" + id, function(data) {
                $('select[name="copy"]').empty();
                $('select[name="copy"]').append('<option value="">No Select</option>');
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];
                    $('select[name ="copy"]').append('<option value="' + element['id'] + '">' + element['title'] + '</option>');
                }
        });
    }
    function getBranchCompany(id){ 
        $.get("{{route('admin.plan.get-branch-company')}}?id=" + id, function(data) {
                $('select[name="company"]').empty();
                $('select[name="company"]').append('<option value="0">Intern</option>');
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];
                    $('select[name ="company"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }
        });
    }
    getBranchCompany($('#branch_id').val());

    getBranchPlan($('#branch_id').val());

        $('#branch_id').change(function() {
            var id = $(this).val();
            getBranchPlan(id);
            getBranchCompany(id);
        });
        // END_CODE
});


</script>

@endsection
