@extends('cargo::adminLte.layouts.master')

@section('content')

    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        {{-- <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details"> --}}
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Fees') }}</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <div class="card-body">
            <table class="table table-striped table-bordered demo-dt-basic" id="tranlation-table" cellspacing="0"
                width="100%">
                <thead>
                    <tr>

                        <th>#</th>
                        <th width="20%">{{__('Plan')}}</th>
                        <th width="20%">{{__('State')}}</th>
                        <th width="20%">{{__('Home Fee')}}</th>
                        <th width="20%">{{__('Desk Fee')}}</th>
                        <th width="20%">{{__('Return Fee')}}</th>
                        <th width="20%">{{__('Insurance')}}&ensp; %</th>

                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                        @foreach($fees as $fee)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td class="key" > <a  href="{{ route('admin.plan.show', $fee->plan->id) }}" >{{ $fee->plan->title }}</a></td>
                            <td class="key">{{ $fee->state->id .". ". $fee->state->name }}</td>
                            <td class="key">{{ $fee->home_fee }}</td>
                            <td class="key">{{ $fee->desk_fee }}</td>
                            <td class="key">{{ $fee->return_fee }}</td>
                            <td class="key">{{ $fee->recovery_rate }}&ensp; %</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
    </div>
</div>

@endsection

