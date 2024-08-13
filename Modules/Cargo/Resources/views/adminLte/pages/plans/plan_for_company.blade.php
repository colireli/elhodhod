@extends('cargo::adminLte.layouts.master')

@section('content')

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ $plan->title }}</h5>
        </div>

    </div>
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

            



            
        </div>
    
</div>

@endsection

