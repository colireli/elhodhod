@extends('cargo::adminLte.layouts.master')
@section('content')


<!--begin::Subheader-->
<div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
    <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
        <!--begin::Info-->
        <div class="flex-wrap mr-1 d-flex align-items-center">
            <!--begin::Page Heading-->
            <div class="flex-wrap mr-5 d-flex align-items-baseline">
                <!--begin::Page Title-->
                <h5 class="my-1 mr-5 text-dark font-weight-bold">{{__('Payments')}}</h5>
                <!--end::Page Title-->
                <!--begin::Breadcrumb-->
                <ul
                    class="p-0 my-2 mr-5 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <!-- <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard')}}" class="text-muted">{{__('Dashboard')}}</a>
                    </li> -->
                    <li class="breadcrumb-item text-muted">
                        <a href="#" class="text-muted">{{ __('Payments') }}</a>
                    </li>
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page Heading-->
        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->

<div class="card">
    <div class="card-header row gutters-5">
        <!-- <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ __('Payment') }}</h5>
        </div> -->

    </div>
    <form id="tableForm" method="post" action="{{ route('admin.payment_client.store') }}">
        @csrf()
        <input type="hidden" name="client" value="{{ $client_id }}">
        <div class="card-body">
            <div class="col text-center text-md-left my-5">
                <h5 class="mb-md-0 h6">{{ __('PAID TO BRANCH') }}</h5>
            </div>
            <table class="table mb-0 aiz-table">
                <thead>
                    <tr>
                        <th width="3%"></th>
                        <th width="3%">#</th>
                        <th>{{__('Code')}}</th>
                        <th>{{__('Amount')}}</th>
                        <th>{{__('Delivery')}}</th>
                        <th>{{__('Return')}}</th>
                        <th>{{__('Total')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Creation Date')}}</th>
                        {{-- @if(isset($show_due_date)) <th>{{__('Due Date') ?? __('Due Date') }}</th> @endif
                        --}}
                    </tr>
                </thead>
                <tbody>

                    @foreach($shipments as $key=>$shipment)

                    <tr>
                        <td><label class="checkbox checkbox-success"><input class="ms-check" type="checkbox"
                                    name="checked_ids[]" value="{{$shipment->id}}" checked/><span></span></label></td>
                        <td width="3%"><a>{{ ($key+1) }}</a></td>
                        <td width="5%"><a>{{ $shipment->code }}</a></td>
                        <td>{{ format_price($shipment->amount_to_be_collected) }}</td>
                        <td>{{ format_price($shipment->cost + $shipment->shipping_cost) }}</td>
                        <td>{{ format_price($shipment->return_cost) }}</td>
                        <td>{{ $shipment->final_status == 1 ? format_price($shipment->amount_to_be_collected - $shipment->shipping_cost) :  format_price(-1 * $shipment->return_cost)}}</td>
                        <td>{{ $shipment->getFinalStatus() }}</td>
                        <td>{{ $shipment->created_at }}</td>

                    </tr>

                    @endforeach

                </tbody>
            </table>

            <div class="col text-center text-md-left my-5">
                <h5 class="mb-md-0 h6">{{ __('UNPAID TO BRANCH') }}</h5>
            </div>

            <table class="table mb-0 aiz-table">
                <thead>
                    <tr>
                        <th width="3%"></th>
                        <th width="3%">#</th>
                        <th>{{__('Code')}}</th>
                        <th>{{__('Amount')}}</th>
                        <th>{{__('Delivery')}}</th>
                        <th>{{__('Return')}}</th>
                        <th>{{__('Total')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Creation Date')}}</th>
                        {{-- @if(isset($show_due_date)) <th>{{__('Due Date') ?? __('Due Date') }}</th> @endif
                        --}}
                    </tr>
                </thead>
                <tbody>

                    @foreach($wants as $key=>$want)

                    <tr>
                        <td><label class="checkbox checkbox-success"><input class="ms-check" type="checkbox"
                                    name="checked_ids[]" value="{{$want->id}}"/><span></span></label></td>
                        <td width="3%"><a>{{ ($key+1) }}</a></td>
                        <td width="5%"><a>{{ $want->code }}</a></td>
                        <td>{{ format_price($want->amount_to_be_collected) }}</td>
                        <td>{{ format_price($want->shipping_cost) }}</td>
                        <td>{{ format_price($want->return_cost) }}</td>
                        <td>{{ $want->final_status == 1 ? format_price($want->amount_to_be_collected + $want->shipping_cost) :  format_price(-1 * $want->return_cost)}}</td>
                        <td>{{ $want->getFinalStatus() }}</td>
                        <td>{{ $want->created_at }}</td>

                    </tr>

                    @endforeach

                </tbody>
            </table>





            <div class="form-group my-5 text-right">
                <button type="submit" class="btn btn-lg btn-primary">{{__('Save')}}</button>
            </div>
        </div>
    </form>
</div>
<!--begin::Card-->


@endsection



@section('scripts')
<script src="//cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script type='text/javascript' src="//github.com/niklasvh/html2canvas/releases/download/0.4.1/html2canvas.js"></script>
<script type="text/javascript">


    $('#kt_datepicker_3').datepicker({
        orientation: "bottom auto",
        autoclose: true,
        format: 'yyyy-mm-dd',
        todayBtn: true,
        todayHighlight: true,
        startDate: new Date(),
    });
    $("#amount_pickup").on('keyup', function(){
        document.getElementById("selected_mission_amount").value    = document.getElementById('amount_pickup').value;;
    });
    function set_mission_id(mission_id , mission_amount , mission_type){
        document.getElementById("selected_mission_id").value        = mission_id;
        document.getElementById("amount_pickup").value              = mission_amount;
        document.getElementById("selected_mission_amount").value    = mission_amount;
        document.getElementById("mission_modal_body").style.display = "block";
    }
    function show_ajax_loder_in_button(element){
        $(element).bind('ajaxStart', function(){
            $(this).addClass('spinner spinner-darker-success spinner-left mr-3');
            $(this).attr('disabled','disabled');
        }).bind('ajaxStop', function(){
            $(this).removeClass('spinner spinner-darker-success spinner-left mr-3');
            $(this).removeAttr('disabled');
        });
    }
    function openCaptainModel(element,e)
    {
         var selected = 0;
            $('.ms-check:checked').each(function() {
                selected = selected+1;
            });
            if(selected > 0)
            {
                $('#tableForm').attr('action',$(element).data('url'));
                $('#tableForm').attr('method',$(element).data('method'));
                $('#assign-to-captain-modal').modal('toggle');
            }else if(selected == 0)
            {
               Swal.fire("{{__('Please Select Missions')}}", "", "error");
            }
    }
    function openAjexedModel(element,event)
    {
        event.preventDefault();
        show_ajax_loder_in_button(element);
        $.ajax({
            url: $(element).data('url'),
            type: 'get',
            success: function(response){
            // Add response in Modal body
            $('#ajaxed-model2 .modal-content').html(response);
            // Display Modal
            $('#ajaxed-model2').modal('toggle');
            }
        });
    }
    function openAjexedModel2(element,event)
    {
        event.preventDefault();
        show_ajax_loder_in_button(element);
        $.ajax({
            url: $(element).data('url'),
            type: 'get',
            success: function(response){
            // Add response in Modal body
            $('#ajaxed-model .modal-content').html(response);
            // Display Modal
            $('#ajaxed-model').modal('toggle');
            }
        });
    }
    $(document).ready(function() {
        $('.action-caller').on('click',function(e){
             e.preventDefault();
             var selected = 0;
            $('.ms-check:checked').each(function() {
                selected = selected+1;
            });
            if(selected > 0)
            {
               $('#tableForm').attr('action',$(this).data('url'));
                $('#tableForm').attr('method',$(this).data('method'));
                $('#tableForm').submit();
            }else if(selected == 0)
            {
                Swal.fire("{{__('Please Select Missions')}}", "", "error");
            }
        });
        $('#ajaxed-model').on('hidden.bs.modal', function () {
            $('#ajaxed-model .modal-content').empty();
        });
        FormValidation.formValidation(
            document.getElementById('tableForm'), {
                fields: {
                    "Mission[captain_id]": {
                        validators: {
                            notEmpty: {
                                message: '{{__("This is required!")}}'
                            }
                        }
                    },
                    "Mission[due_date]": {
                        validators: {
                            notEmpty: {
                                message: '{{__("This is required!")}}'
                            }
                        }
                    }
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
