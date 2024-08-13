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

<!--begin::Card-->
<div class="card card-custom gutter-b">
    <div class="flex-wrap py-3 card-header">
        <div class="card-title">
            <h3 class="card-label">
                {{ __('Payments') }}
            </h3>
        </div>
        
       
    </div>

    
    <!--end::Search Form-->



    <form id="tableForm">
        @csrf()
        <table class="table mb-0 aiz-table">
            <thead>
                <tr>
                    <th width="3%"></th>
                    <th width="3%">#</th>
                    <th>{{__('Code')}}</th>
                    <th>{{__('Net')}}</th>
                    <th>{{__('Collected')}}</th>
                    <th>{{__('Charged')}}</th>
                    <th>N° {{__('Delivered')}}</th>
                    <th>N° {{__('Returned')}}</th>
                    <th>{{__('IP')}}</th>
                    <th>{{__('Creation Date')}}</th>
                    {{-- @if(isset($show_due_date)) <th>{{__('Due Date') ?? __('Due Date') }}</th> @endif
                    --}}

                    <th class="text-center">{{__('Files')}}</th>
                </tr>
            </thead>
            <tbody>

                @foreach($payments as $key=>$payment)

                <tr>
                    <td><label class="checkbox checkbox-success"><input class="ms-check" type="checkbox"
                                name="checked_ids[]" value="{{$payment->id}}" /><span></span></label></td>
                    <td width="3%"><a>{{ ($key+1) +
                            ($payments->currentPage() - 1) * $payments->perPage() }}</a></td>
                    <td width="5%"><a>{{ $payment->code }}</a></td>
                    <td>{{ format_price($payment->net) }}</td>
                    <td>{{ format_price($payment->collected) }}</td>
                    <td>{{ format_price($payment->charged) }}</td>
                    <td>{{ $payment->delivered }}</td>
                    <td>{{ $payment->returned }}</td>
                    <td>{{ $payment->ip }}</td>
                    <td>{{ $payment->created_at }}</td>
                    

                    <td class="text-center">
                        
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            href="{{route('admin.rappayment.imported_payment.downxls', $payment->id)}}"
                            title="{{ __('XLS File') }}">
                            <i class="las la-file-alt"></i>
                        </a>
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            href="{{route('admin.rappayment.imported_payment.downpdf', $payment->id)}}"
                            title="{{ __('PDF File') }}">
                            <i class="las la-file-pdf"></i>
                        </a>
                        
                    </td>
                </tr>

                @endforeach

            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $payments->appends(request()->input())->links() }}
        </div>
        <!-- Assign-to-captain Modal -->
        
        
    </form>
    
</div>


<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{__('Confirm Receive Mission')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body d-none" id="mission_modal_body">
                <h5 class="mb-2 modal-title" id="exampleModalLabel">{{__('Mission Amount')}}</h5>
                <input type="number" id="amount_pickup" class="form-control" name="amount" />
            </div>

            <div class="modal-footer">
                <!-- <form action="route('admin.missions.action.change.recived.mission')" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="checked_ids[]" id="selected_mission_id" />
                    <input type="hidden" name="amount" id="selected_mission_amount" />
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Confirm')}}</button>
                </form> -->
            </div>
        </div>
    </div>
</div>
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