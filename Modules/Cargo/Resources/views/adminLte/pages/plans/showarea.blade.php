@extends('cargo::adminLte.layouts.master')

@section('content')

 <!--begin::Basic info-->
 <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0"><a href="{{ route('admin.plan.show', $plan->id) }}">{{$plan->title}}</a> : {{ $planFee->state->name }}</h3>
            </div>
            <h5 class="text-danger m-2">{{_('Make Desk Fee 0 For Desactive')}}</h5>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <form id="form1" class="form" action="{{ route('admin.plan.updatearea') }}"  method="POST">
        @csrf
        <!-- redirect_input -->
        <div class="card-body">
            <table class="table table-striped table-bordered demo-dt-basic" id="tranlation-table" cellspacing="0"
                width="100%">
                <input type="hidden" name="plan_fee_id" value="{{ $planFee->id }}">
                <input type="hidden" name="branch_id" value="{{$plan->branch_id}}">
                <thead>

                    <tr>
                        <th>#</th>
                        <th width="20%">{{__('Area')}}</th>
                        <th width="20%">{{__('Company')}}</th>
                        <th width="20%">{{__('Home Fee')}}</th>
                        <th width="20%">{{__('Desk Fee')}}</th>
                        <th width="20%">{{__('Return Fee')}}</th>
                        <th width="20%">{{__('Insurance')}}&ensp; %</th>
                        <th width="10%">{{__('Active')}}</th>
                    </tr>

                </thead>
                <tbody>
                    @php
                    $i = 1;
                    @endphp
                    @foreach ($planFee->areas as $fee)
                    <tr>
                        <td>{{ $i }}</td>
                        <td class="key">{{ $fee->area->name }}
                            <a class="btn btn-sm btn-secondary btn-action-table"
                               href="{{ route('areas.edit', $fee->area->id) }}" title="{{ __('Edit') }}">
                                <i class="fas fa-edit fa-fw"></i>
                            </a> 
                        </td> 
                        <td>
                        <div class="form-group">
                            <select class="form-control kt-select2 how-know-us" data-control="select2"
                             style="width:100%" id="{{ $i }}" name="company[{{ $fee->id }}]">
                            <option value="0">{{__('Intern')}}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    @if ($company->id == $fee->company )
                                    selected
                                    @endif>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                            </select>
                        </div>
                        </td>
                        <td>
                            <input type="number" class="form-control value" style="width:100%"
                                name="home[{{ $fee->id }}]" value="{{ $fee->home_fee }}">
                        </td>
                        <td>
                            <input type="number" class="form-control value" style="width:100%"
                                name="desk[{{ $fee->id }}]" value="{{ $fee->desk_fee }}">
                        </td>
                        <td>
                            <input type="number" class="form-control value" style="width:100%"
                                name="return[{{ $fee->id }}]" value="{{ $fee->return_fee }}">
                        </td>
                        <td>
                            <input type="number" class="form-control value" style="width:100%"
                                name="recovery_rate[{{ $fee->id }}]" value="{{ $fee->recovery_rate }}">
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    name="active[{{ $fee->id }}]" @if ($fee->active == 1) checked @endif>
                            </div>
                        </td>
                    </tr>
                    @php
                    $i++;
                    @endphp
                    @endforeach
                </tbody>
            </table>


        </div>
    </form>
    <form action="{{ route('admin.plan.updatearea') }}" id="form2" method="POST">
        @csrf
        <!-- redirect_input -->
        <input type="hidden" name="branch_id" value="{{$plan->branch_id}}">
        <input type="hidden" name="plan_fee_id" value="{{ $planFee->id }}">
        <input type="hidden" name="defult" value="defult">
    </form>
    <div class="form-group mb-2 mr-2 text-right">
        <button onclick="submitForm('form2')" class="btn btn-primary">{{__('defult as state')}}</button>
        <button onclick="submitForm('form1')" class="btn btn-primary">{{__('Save')}}</button>
    </div>
</div>

@endsection
@section('scripts')
<script>
    function submitForm(formId) {
        document.getElementById(formId).submit();
    }
</script>
@endsection
