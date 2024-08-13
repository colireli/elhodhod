@php
    $user_role = auth()->user()->role;
    $admin  = 1;
@endphp

@extends('cargo::adminLte.layouts.master')

@section('pageTitle')
    {{ __('company_list') }}
@endsection

@section('content')

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">

                <!--begin::Search-->
                {{ __('Companies') }}
                <!--end::Search-->

            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex flex-wrap align-items-center">

                    <!--begin::Add user-->
                    @if(auth()->user()->can('create-branches') || $user_role == $admin)
                        <a href="{{ fr_route('admin.company.create') }}" class="btn btn-primary m-1">{{ __('Add company') }}</a>
                    @endif
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->

            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->


        <!--begin::Card body-->
        <div class="card-body pt-6">

        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th  width="3%">#</th>
                    <th >{{__('Name')}}</th>
                    <th >{{__('Api Model')}}</th>
                    <th >{{__('created_at')}}</th>

                    <th  width="10%" class="text-center">{{__('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $key => $company)

                        <tr>
                            <td  width="3%">{{ ($key+1) + ($companies->currentPage() - 1)*$companies->perPage() }}</td>
                            <td width="20%">
                                {{$company->name}}</td>
                            <td width="20%"><a href="{{route('admin.apimodel.show', $company->model->id)}}">{{$company->model->name}}</a></td>
                            <td width="15%">{{$company->created_at->format('Y-m-d')}}</td>

                            <td class="text-center">
                                    <a class="btn btn-sm btn-secondary btn-action-table" href="{{route('admin.company.showfees', $company->id)}}" title="{{ __('Plans') }}">
                                        <i class="fas fa-box fa-fw"></i>
		                            </a>
                                    <a class="btn btn-sm btn-secondary btn-action-table" href="{{route('admin.company.show', $company->id)}}" title="{{ __('Show') }}">
                                        <i class="fas fa-eye fa-fw"></i>
		                            </a>
		                            <a class="btn btn-sm btn-secondary btn-action-table" href="{{route('admin.company.edit', $company->id)}}" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit fa-fw"></i>
		                            </a>
                                    <button class="btn btn-sm btn-secondary btn-action-table" onclick="openDeleteModel({{$company->id}})" title="{{ __('view.delete') }}">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </button>
		                        </td>
                        </tr>

                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $companies->appends(request()->input())->links() }}
        </div>


        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

@endsection


@section('toolbar-btn')
    <!--begin::Button-->
    {{-- <a href="{{ fr_route('users.create') }}" class="btn btn-sm btn-primary">Create <i class="ms-2 fas fa-plus"></i> </a> --}}
    <!--end::Button-->
@endsection


{{-- Inject styles --}}
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/custom/datatables/datatables.bundle.css') }}">
@endsection

@section('scripts')
<script type="text/javascript">

function openDeleteModel(id)
    {
            Swal.fire({
                    icon: 'error',
                    title: "Delete Confirmation",
                    text: "Are you sure to delete this?",
                    showCancelButton: true,
                    confirmButtonText: 'DELETE',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Trigger the button click event to proceed with the server event
                        $.get("{{fr_route('admin.company.destroy')}}?id="+id, function(data) {
                            window.location.reload();
                        });
                    }
                });
    }

</script>

@endsection
