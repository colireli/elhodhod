@php
    $user_role = auth()->user()->role;
    $date_now = \Carbon\Carbon::now()->format('d-m-Y');
    $branchs = Modules\Cargo\Entities\Branch::where('is_archived', 0)->get();

    $paymentSettings = resolve(\Modules\Payments\Entities\PaymentSetting::class)->toArray();
@endphp

@extends('cargo::adminLte.layouts.master')

@section('pageTitle')
    {{ __('cargo::view.barcode_scanner') }}
@endsection

@section('content')
    <div class="col-lg-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>{{ __('cargo::view.barcode_scanner') }}:</label>
                    <input type="text" autocomplete="off" autofocus id="barcode" class="form-control" placeholder="{{ __('cargo::view.barcode') }}" name="barcode"/>
                </div>

                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>
                    @if($user_role == 1)
                        <button onclick="submitForm('kt_form_5')" class="btn btn-light-danger me-2">{{__('Return Mission')}}</button>
                        <button onclick="submitForm('kt_form_4')" class="btn btn-light-info me-2">{{__('Create Transfer Mission')}}</button>
                        <button onclick="submitForm('kt_form_3')" class="btn btn-light-warning me-2">{{__('Returned to client')}}</button>
                        <button onclick="submitForm('kt_form_2')" class="btn btn-light-primary me-2">{{__('Delivery')}}</button>
                        <button onclick="submitForm('kt_form_6')" class="btn btn-light-success me-2">{{__('Approve & Assign')}}</button>
                        <button type="button" data-toggle="modal" data-target="#exampleModalCenter" class="btn btn-success">{{ __('cargo::view.change_status') }}</button>
                    @endif
                    @if($user_role != 1 && (auth()->user()->can('shipments-barcode-scanner') || $user_role == 3))
                        <button onclick="submitForm('kt_form_6')" class="btn btn-light-success me-2">{{__('Approve & Assign')}}</button>
                    @endif
                </div>
                @if($user_role == 1)
                    <form action="{{ fr_route('shipments.barcode.scanner.post') }}" id="kt_form_4" method="post" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="checked_ids" class="checked_ids" />
                        <input type="hidden" name="createTransferMission" value="{{true}}">
                        <div class="form-group">
                            <label>{{__('Transfer Mission To Branch')}}:</label>
                            <select class="form-control kt-select2 branch_id" id="branch_id"
                                name="to_branch_id">

                                <option></option>
                                @foreach ($branchs as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label style="display: block;">{{__('cargo::view.driver')}}:</label>
                                <select name="captain_id" class="form-control mb-4 captain_id kt-select2">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </form>
                @endif
                <ul class="list-group mt-3" id="list">

                </ul>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <form id="kt_form_1" class="form" action="{{ fr_route('shipments.barcode.scanner.post') }}" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('cargo::view.barcode_scanner') }} {{ __('cargo::view.change_status') }} <span class="count"></span> </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ __('cargo::messages.are_you_sure') }}

                        <input type="hidden" name="checked_ids" class="checked_ids" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ __('cargo::view.change_status') }}</button>
                </div>
                </div>
            </div>
        </div>
    </form>
    <form action="{{ fr_route('shipments.barcode.scanner.post') }}" id="kt_form_6" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="checked_ids" class="checked_ids" />
        <input type="hidden" name="force_change_status" value="{{true}}">
        <input type="hidden" name="APPROVED_STATUS" value="{{true}}">
    </form>
    <form action="{{ fr_route('shipments.barcode.scanner.post') }}" id="kt_form_2" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="checked_ids" class="checked_ids" />
        <input type="hidden" name="force_change_status" value="{{true}}">
        <input type="hidden" name="DELIVERED_STATUS" value="{{true}}">
    </form>
    <form action="{{ fr_route('shipments.barcode.scanner.post') }}" id="kt_form_3" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="checked_ids" class="checked_ids" />
        <input type="hidden" name="force_change_status" value="{{true}}">
        <input type="hidden" name="RETURNED_CLIENT_GIVEN" value="{{true}}">
    </form>

    <form action="{{ fr_route('shipments.barcode.scanner.post') }}" id="kt_form_5" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="checked_ids" class="checked_ids" />
        <input type="hidden" name="returnMission" value="{{true}}">
    </form>

@endsection

{{-- Inject styles --}}
@section('styles')
    <style type="text/css">
        .badge{
            cursor: pointer;
        }
    </style>
@endsection

{{-- Inject Scripts --}}
@push('js-component')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js" integrity="sha512-foIijUdV0fR0Zew7vmw98E6mOWd9gkGWQBWaoA1EOFAx+pY+N8FmmtIYAVj64R98KeD2wzZh1aHK0JSpKmRH8w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.js" integrity="sha512-bZAXvpVfp1+9AUHQzekEZaXclsgSlAeEnMJ6LfFAvjqYUVZfcuVXeQoN5LhD7Uw0Jy4NCY9q3kbdEXbwhZUmUQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-touchspin/4.3.0/jquery.bootstrap-touchspin.min.js" integrity="sha512-0hFHNPMD0WpvGGNbOaTXP0pTO9NkUeVSqW5uFG2f5F9nKyDuHE3T4xnfKhAhnAZWZIO/gBLacwVvxxq0HuZNqw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-touchspin/4.3.0/jquery.bootstrap-touchspin.js" integrity="sha512-k59zBVzm+v8h8BmbntzgQeJbRVBK6AL1doDblD1pSZ50rwUwQmC/qMLZ92/8PcbHWpWYeFaf9hCICWXaiMYVRg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/global/js/jquery.geocomplete.js') }}"></script>
    <!-- REQUIRED CDN  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8-beta.1/inputmask.js" integrity="sha512-aSxEzzrnqlqgASdjAelu/V291nzZNygMSFMJ0h4PFQ+uwdEz6zKkgsIMbcv0O0ZPwFRNPFWssY7gcL2gZ6/t9A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" crossorigin="anonymous"></script>


    <script>
        function submitForm(formId) {
            document.getElementById(formId).submit();
        }
    </script>
    <script>
        const incorrect = new Audio('{{ asset('assets/audio/incorrect.wav') }}');
        const correct = new Audio('{{ asset('assets/audio/correct.wav') }}');
        const double = new Audio('{{ asset('assets/audio/double.wav') }}');
        $('#branch_id').change(function() {

            var id = $(this).val();
            $.get("{{route('get-drivers-ajax')}}?branch_id=" + id, function(data) {
                $('select[name ="captain_id"]').empty();
                $('select[name ="captain_id"]').append('<option value=""></option>');
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];

                    $('select[name ="captain_id"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }
            });

            $('.captain_id').select2({
                placeholder: "{{__('cargo::view.select_driver')}}",
            })

            });

            $('#branch_id').select2({
            placeholder: "{{__('cargo::view.select_branch')}}",
            });

            $('.captain_id').select2({
                placeholder: "{{__('cargo::view.please_select_branch_first')}}",
            });

        var shipments_barcodes = [];
        $('#barcode').change('input',function(e){
            let barcode = $(this).val();
            if(barcode && barcode != ' '){
                $.post("{{route('shipment.get.one.barcode')}}",{
                    _token: "{{ csrf_token() }}",
                    barcode: barcode
                }, function(data) {
                    if(!data){
                        incorrect.play();
                        // alert("Barcode Non Valid, please try again !!");
                        Swal.fire({
                                        icon: 'error',
                                        title: "{{ _('Barcode Non Valid, please try again !!') }}",
                                        showConfirmButton: false,
                                        timer: 3500
                                    });
                    }else{

                        if(jQuery.inArray(barcode, shipments_barcodes) !== -1){
                            console.log(jQuery.inArray(barcode, shipments_barcodes));
                            double.play();
                            // alert("Shipment already scanned !!");
                            Swal.fire({
                                        icon: 'error',
                                        title: "{{ _('Shipment already scanned !!') }}",
                                        showConfirmButton: false,
                                        timer: 3500
                                    });
                        }else{
                            shipments_barcodes.push(barcode);
                            correct.play();
                            $('.checked_ids').val(JSON.stringify(shipments_barcodes));
                            $("#list").append(`
                            <li class="list-group-item remove_li_${barcode} d-flex justify-content-between align-items-center">
                                ${barcode}
                                <span onClick="delete_el('${barcode}')" class="delete_el badge badge-pill"><i class="fas fa-times-circle"></i></span>
                            </li>`);
                        }
                    }
                });
                $('#barcode').val('');
                $('#barcode').focus();
            }else{
                $('#barcode').val('');
                $('#barcode').focus();
            }
            $(".count").text('( '+ shipments_barcodes.length +' {{ __("cargo::view.shipments") }} )');
        });

        function delete_el(barcode){
            let index = shipments_barcodes.findIndex( (n)=> n == barcode );
            shipments_barcodes.splice(index,1);
            $(`.remove_li_${barcode}`).remove();
            $('.checked_ids').val(JSON.stringify(shipments_barcodes));
            $(".count").text('( '+ shipments_barcodes.length +' {{ __("cargo::view.shipments") }} )');
        }
    </script>
@endpush
