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

        <form id="kt_account_profile_details_form" class="form" action="{{ route('admin.plan.update', $plan->id) }}" method="POST">
        @method('PATCH')
        @csrf
        <div class="card-body">
            <table class="table table-striped table-bordered demo-dt-basic" id="tranlation-table" cellspacing="0"
                width="100%">
                <!-- // edit_update_v1
                    //  START_CODE -->
                <thead>
                    <tr>
                        <th>#</th>
                        <th width="15%">{{__('State')}}</th>
                        <th width="15%">{{__('Home')}}</th>
                        <th width="15%">{{__('Stop Desk')}}</th>
                        <th width="15%">{{__('Company')}}</th>
                        <th width="15%">{{__('Home Fee')}}</th>
                        <th width="15%">{{__('Desk Fee')}}</th>
                        <th width="15%">{{__('Return Fee')}}</th>
                        <th width="15%">{{__('Insurance')}}&ensp; %</th>
                        <th width="5%">{{__('Active')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $i = 1;
                    @endphp
                    @foreach ($plan->fees as $fee)
                    <tr>
                        <td>{{ $i }}</td>
                        <td><span> {{ $fee->state->name }} </span></td>
                        <td class="key"> 
                            
                            <a href="{{ route('admin.plan.showarea', $fee->id) }}"> {{ __('Home') }}</a>
                            <button type="button" class="btn btn-sm btn-secondary btn-action-table" onclick="openCreateModel({{$fee->id}}, '{{ $fee->state->name }}')" title="{{ __('view.create') }}">
                                <i class="fas fa-add fa-fw"></i>
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('admin.plan.showstopdesk', $fee->id) }}"> {{ __('Stop Desk') }}</a>
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
                <!-- // END_CODE -->
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
            <!-- @if (auth()->check() && auth()->user()->role == 1)
                <div class="col-md-12">
                    <div class="form-group">
                        <label>{{__('Branches')}}:</label>
                        <select class="form-control kt-select2 branch" data-control="select2"
                                data-allow-clear="true" id="branch_id" name="branch_id">

                            @foreach ($branchs as $branch)
                                <option value="{{ $branch->id }}"
                                    @if ($branch->id == $plan->branch_id)
                                        selected
                                    @endif
                                >{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
            @endif -->



            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
            </div>
        </div>
    </form>
</div>

@endsection


@section('scripts')
<script type="text/javascript">

    function openCreateModel(id, name)
    {
            Swal.fire({
                    title: "Create New Area Into " + name,
                    input: 'text',
                    inputLabel: 'Your area',
                    inputPlaceholder: 'Type area name',
                    showCancelButton: true,
                    confirmButtonText: 'Create',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to enter something!';
                        }
                    }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Trigger the button click event to proceed with the server event
                            $.get("{{fr_route('admin.plan.createArea')}}?id="+id+"&area="+result.value+"").then(response => {
                                if(response){

                                    Swal.fire('Success!', 'Your area has been created.', 'success');
                                }else{ 
                                    Swal.fire('Warning!', 'Your area has been not created.', 'warning');

                                }
                            });
                        }
                    });
    }

</script>

@endsection

