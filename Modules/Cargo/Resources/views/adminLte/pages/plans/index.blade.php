@extends('cargo::adminLte.layouts.master')

@section('content')
   <!--begin::Card-->
   <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">

                <!--begin::Search-->
                {{ __('Costumer Plan') }}
                <!--end::Search-->

            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex flex-wrap align-items-center">

                    <!--begin::Add user-->
                    @if(auth()->user()->can('create-branches') || auth()->user()->role == 3)
                        <a href="{{ fr_route('admin.plan.create') }}" class="btn btn-primary m-1">{{ __('Add Plan') }}</a>
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
        <table class="table mb-0 aiz-table">
            <thead>
                <tr>
                    <th width="3%">#</th>
                    <th width="50%">{{__('Plan')}}</th>
                    <th width="30%">{{__('Creation Date')}}</th>
                    <th width="10%">{{__('Options')}}</th>
                </tr>
            </thead>
            <tbody>

                @foreach($plans as $plan)

                <tr>
                    <td>#</td>
                    <td>{{ $plan->title }}</td>


                    <td>{{ $plan->created_at }}</td>

                    <td class="text-center">
                        <a class="btn btn-sm btn-secondary btn-action-table"
                            href="{{ route('admin.plan.show', $plan->id) }}" title="{{ __('Edit') }}">
                            <i class="fas fa-edit fa-fw"></i>
                        </a>
                        <button class="btn btn-sm btn-secondary btn-action-table" onclick="openDeleteModel({{$plan->id}})" title="{{ __('view.delete') }}">
                            <i class="fas fa-trash fa-fw"></i>
                        </button>
                    </td>
                </tr>

                @endforeach

            </tbody>
        </table>
    </div>
</div>






@endsection


@section('scripts')
<script src="//cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script type='text/javascript' src="//github.com/niklasvh/html2canvas/releases/download/0.4.1/html2canvas.js"></script>
<script type="text/javascript">
    function show_ajax_loder_in_button(element){
        $(element).bind('ajaxStart', function(){
            $(this).addClass('spinner spinner-darker-success spinner-left mr-3');
            $(this).attr('disabled','disabled');
        }).bind('ajaxStop', function(){
            $(this).removeClass('spinner spinner-darker-success spinner-left mr-3');
            $(this).removeAttr('disabled');
        });
    }

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
                        $.get("{{fr_route('admin.plan.destroy')}}?id="+id, function(data) {
                            window.location.reload();
                        });
                    }
                });
    }
</script>

@endsection
