@extends('cargo::adminLte.layouts.master')

@section('content')

<!--begin::Basic info-->
<div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ $plan->title }}</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <form id="kt_account_profile_details_form" class="form" action="{{ route('admin.company_plan.update', $plan->id) }}" method="POST">
        @method('PATCH')
        @csrf
        <div class="card-body">
            <table class="table table-striped table-bordered demo-dt-basic" id="tranlation-table" cellspacing="0"
                width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th width="30%">{{__('State')}}</th>
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
                    @foreach ($plan->fees as $fee)
                    <tr>
                        <td>{{ $i }}</td>
                        <td class="key">{{ $fee->state->name }}</td>
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

            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('Plan Name') }}:</label>
                    <input type="text" name="name" class="form-control" value="{{ $plan->title }}" required>
                    @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>



            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
            </div>
        </div>
    </form>
</div>

@endsection

