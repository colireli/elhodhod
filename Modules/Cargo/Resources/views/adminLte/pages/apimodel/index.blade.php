@extends('cargo::adminLte.layouts.master')

@section('content')

<!--begin::Card-->
<div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">

                <!--begin::Search-->
                {{ __('API Model') }}
                <!--end::Search-->

            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex flex-wrap align-items-center">

                    <!--begin::Add user-->
                    @if(auth()->user()->can('create-branches') || auth()->user()->role == 1)
                        <a href="{{ fr_route('admin.apimodel.create') }}" class="btn btn-primary m-1">{{ __('Add API Model') }}</a>
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
            <!-- <thead>
                <tr>
                    <th  width="3%">#</th>
                    <th >{{__('Name')}}</th>
                    <th  width="10%" class="text-center">{{__('Options')}}</th>
                </tr>
            </thead> -->
            <tbody>
                @foreach($apiModels as $key => $api)

                        <tr>
                            <td  width="3%">{{ ($key+1) + ($apiModels->currentPage() - 1)*$apiModels->perPage() }}</td>
                            <td width="80%"><a href="{{route('admin.apimodel.show', $api->id)}}">
                                {{$api->name}}</a></td>

                            <td class="text-center">
                                    <a class="btn btn-sm btn-secondary btn-action-table" href="{{route('admin.apimodel.show', $api->id)}}" title="{{ __('Show') }}">
                                        <i class="fas fa-eye fa-fw"></i>
		                            </a>
		                            <a class="btn btn-sm btn-secondary btn-action-table" href="{{route('admin.apimodel.edit', $api->id)}}" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit fa-fw"></i>
		                            </a>
                                    <button class="btn btn-sm btn-secondary btn-action-table" onclick="openDeleteModel({{$api->id}})" title="{{ __('view.delete') }}">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </button>
		                        </td>
                        </tr>

                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $apiModels->appends(request()->input())->links() }}
        </div>
    </div>
</div>
{{-- Inject styles --}}
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/custom/datatables/datatables.bundle.css') }}">
@endsection

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
                        $.get("{{fr_route('admin.apimodel.destroy')}}?id="+id, function(data) {
                           window.location.reload();
                        });
                    }
                });
    }

</script>

@endsection

