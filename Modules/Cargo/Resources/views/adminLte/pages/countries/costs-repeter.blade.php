@php
    $user_role = auth()->user()->role;
    $admin  = 1;

    $default_country_cost = Modules\Cargo\Entities\Cost::where('from_country_id',$from->id)->where('to_country_id',$to->id)->where('from_state_id',0)->where('to_state_id',0)->first();
    $is_def_mile_or_fees = Modules\Cargo\Entities\ShipmentSetting::getVal('is_def_mile_or_fees');
    ini_set('max_input_vars', 5000);
@endphp

@extends('cargo::adminLte.layouts.master')

@section('pageTitle')
    {{ __('cargo::view.package_list') }}
@endsection

@section('content')
<div class="col-lg-12 mx-auto">
    <div class="card">
        <form class="form-horizontal" action="{{ route('countries.config.costs') }}" id="kt_form_1" method="GET" enctype="multipart/form-data">
            <input type="hidden" name="from_country" value="{{$from->id}}">
            <input type="hidden" name="to_country" value="{{$to->id}}">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>{{ __('cargo::view.from_region') }}:</label>
                        <select name="from_region" class="form-control select-country" required>
                            <option value=""></option>

                            @foreach($from_cities as $covered)
                            <option value="{{$covered->id}}" @if(isset($from_region) && $from_region->id == $covered->id) selected @endif>{{$covered->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('cargo::view.to_region') }}:</label>
                        <select name="to_region" class="form-control select-country" required>
                            <option value=""></option>
                            @foreach($to_cities as $covered)
                            <option value="{{$covered->id}}" @if(isset($to_region) && $to_region->id == $covered->id) selected @endif>{{$covered->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('cargo::view.configure_costs') }}:</label>
                        <button class="btn btn-primary form-control">{{ __('cargo::view.configure_selected_regions_costs') }}</button>
                    </div>


                </div>
            </div>
        </form>

        <form class="form-horizontal" action="{{ route('post.countries.config.costs') }}?from_country={{$from->id}}&to_country={{$to->id}}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-header">
                <h5 class="mb-0 h6">{{ __('cargo::view.from') }}: ( {{$from->name}} ) | {{ __('cargo::view.to') }}: ( {{$to->name}})</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>{{ __('cargo::view.from_country') }}:</label>
                                <input disabled readonly class="form-control disabled" value="{{$from->name}}">
                                <input type="hidden" name="from_country_h[]" value="{{$from->id}}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('cargo::view.to_country') }}:</label>
                                <input disabled readonly class="form-control disabled" value="{{$to->name}}">
                                <input type="hidden" name="to_country_h[]" value="{{$to->id}}">
                            </div>
    
                            @if( $is_def_mile_or_fees == '1')
                                <div class="form-group col-md-3">
                                    <label>{{ __('cargo::view.mile_cost') }}:</label>
                                    <input type="number" onchange="changeShippingCosts(this)" min="0" id="name" class="form-control" placeholder="{{ __('cargo::view.here') }}" value="@php if(isset($default_country_cost->mile_cost)){echo ($default_country_cost->mile_cost);}else{ echo 0;} @endphp" name="mile_cost[]">
                                </div>
                            @elseif($is_def_mile_or_fees == '2' )
                                <div class="form-group col-md-3">
                                    <label>{{ __('cargo::view.shipping_cost') }}:</label>
                                    <input type="number" onchange="changeShippingCosts(this)" min="0" id="name" class="form-control" placeholder="{{ __('cargo::view.here') }}" value="@php if(isset($default_country_cost->shipping_cost)){echo ($default_country_cost->shipping_cost);}else{ echo 0;} @endphp" name="shipping_cost[]">
                                </div>
                            @endif
                            <div class="form-group col-md-3">
                                <label>{{ __('cargo::view.tax') }}%:</label>
                                <input type="number" onchange="changeTax(this)" min="0" id="name" class="form-control" placeholder="{{ __('cargo::view.here') }}" value="@php if(isset($default_country_cost->tax)){echo $default_country_cost->tax;}else{ echo 0;} @endphp" name="tax[]">
                            </div>
                            <div class="form-group col-md-3">
                                <label>{{ __('cargo::view.insurance') }}:</label>
                                <input type="number" onchange="changeInsurance(this)" min="0" id="name" class="form-control" placeholder="{{ __('cargo::view.here') }}" value="@php if(isset($default_country_cost->insurance)){echo ($default_country_cost->insurance);}else{ echo 0;} @endphp" name="insurance[]">
                            </div>
    
                            @if( $is_def_mile_or_fees == '1')
                                <div class="form-group col-md-3">
                                    <label>{{ __('cargo::view.returned_mile_cost') }}:</label>
                                    <input type="number" onchange="changeReturnCosts(this)" min="0" id="name" class="form-control" placeholder="{{ __('cargo::view.here') }}" value="@php if(isset($default_country_cost->return_mile_cost)){echo ($default_country_cost->return_mile_cost);}else{ echo 0;} @endphp" name="return_mile_cost[]">
                                </div>
                            @elseif($is_def_mile_or_fees == '2' )
                                <div class="form-group col-md-3">
                                    <label>{{ __('cargo::view.returned_shipmen_cost') }}:</label>
                                    <input type="number" onchange="changeReturnCosts(this)" min="0" id="name" class="form-control" placeholder="{{ __('cargo::view.here') }}" value="@php if(isset($default_country_cost->return_cost)){echo ($default_country_cost->return_cost);}else{ echo 0;} @endphp" name="return_cost[]">
                                </div>
                            @endif
                        </div>
                        <hr>
                        
                    </div>
    
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div id="placeholder_cost">
                        @if(isset($from_region) && isset($to_region) )
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ __('cargo::view.from') }}: ( {{$from_region->name}} ) | {{ __('cargo::view.to') }}: ( {{$to_region->name}} )</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>{{ __('cargo::view.from_region') }}:</label>
                                                <input disabled readonly class="form-control disabled" value="{{$from_region->name}}">
                                                <input type="hidden" name="from_state[]" value="{{$from_region->id}}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>{{ __('cargo::view.to_region') }}:</label>
                                                <input disabled readonly class="form-control disabled" value="{{$to_region->name}}">
                                                <input type="hidden" name="to_state[]" value="{{$to_region->id}}">
                                            </div>

                                            @if( $is_def_mile_or_fees == '1')
                                                <div class="form-group col-md-3">
                                                    <label>{{ __('cargo::view.mile_cost') }}:</label>
                                                    <input type="number" onchange="changeShippingCosts(this,{{$from_region->id}})" min="0"  class="form-control shipp_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$region_cost->mile_cost ?? 0}}" name="state_mile_cost[]">
                                                </div>
                                            @elseif($is_def_mile_or_fees == '2' )
                                                <div class="form-group col-md-3">
                                                    <label>{{ __('cargo::view.shipping_cost') }}:</label>
                                                    <input type="number" onchange="changeShippingCosts(this,{{$from_region->id}})" min="0"  class="form-control shipp_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$region_cost->shipping_cost ?? 0}}" name="state_shipping_cost[]">
                                                </div>
                                            @endif
                                            <div class="form-group col-md-3">
                                                <label>{{ __('cargo::view.tax') }}%:</label>
                                                <input type="number" onchange="changeTax(this,{{$from_region->id}})" min="0"  class="form-control tax_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$region_cost->tax ?? 0}}" name="state_tax[]">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>{{ __('cargo::view.insurance') }}:</label>
                                                <input type="number" onchange="changeInsurance(this,{{$from_region->id}})" min="0"  class="form-control insurance_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$region_cost->insurance ?? 0}}" name="state_insurance[]">
                                            </div>

                                            @if( $is_def_mile_or_fees == '1')
                                                <div class="form-group col-md-3">
                                                    <label>{{ __('cargo::view.returned_mile_cost') }}:</label>
                                                    <input type="number" onchange="changeReturnCosts(this,{{$from_region->id}})" min="0"  class="form-control return_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$region_cost->return_mile_cost ?? 0}}" name="state_return_mile_cost[]">
                                                </div>
                                            @elseif($is_def_mile_or_fees == '2' )
                                                <div class="form-group col-md-3">
                                                    <label>{{ __('cargo::view.returned_shipmen_cost') }}:</label>
                                                    <input type="number" onchange="changeReturnCosts(this,{{$from_region->id}})" min="0"  class="form-control return_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$region_cost->return_cost ?? 0}}" name="state_return_cost[]">
                                                </div>
                                            @endif
                                        </div>
                                        <hr>

                                    </div>

                                </div>
                            </div>
                            <hr>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @foreach($from_areas as $key => $area)
                                            @foreach($to_areas as $key => $to_area)
                                                @php
                                                    $area_cost = $area_costs->where('from_area_id', $area->id)->where('to_area_id', $to_area->id)->first();
                                                @endphp
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <label>{{ __('cargo::view.from_area') }}:</label>
                                                        <input disabled readonly class="form-control disabled" value="{{$area->name}}">
                                                        <input type="hidden" name="from_area[]" value="{{$area->id}}">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label>{{ __('cargo::view.to_area')}}:</label>
                                                        <input disabled readonly class="form-control disabled" value="{{$to_area->name}}">
                                                        <input type="hidden" name="to_area[]" value="{{$to_area->id}}">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>@if($is_def_mile_or_fees =='2') {{ __('cargo::view.shipping_cost') }} @else {{ __('cargo::view.mile_cost') }} @endif:</label>
                                                        <input type="number" min="0" id="name" class="form-control shipp_cost shipp_cost{{$from_region->id}}" placeholder="{{ __('cargo::view.here') }}" value="{{$area_cost->shipping_cost ?? 0 }}" @if($is_def_mile_or_fees =='2') name="shipping_cost[]" @else name="mile_cost[]" @endif>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>{{ __('cargo::view.tax') }}%:</label>
                                                        <input type="number" min="0" id="name" class="form-control tax_cost tax_cost{{$from_region->id}}" placeholder="{{ __('cargo::view.here') }}" value="{{$area_cost->tax ?? 0 }}" name="tax[]">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>{{ __('cargo::view.insurance') }}:</label>
                                                        <input type="number" min="0" id="name" class="form-control insurance_cost{{$from_region->id}} insurance_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$area_cost->insurance ?? 0 }}" name="insurance[]">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>@if($is_def_mile_or_fees =='2') {{ __('cargo::view.return_cost') }} @else {{ __('cargo::view.returned_mile_cost') }} @endif:</label>
                                                        <input type="number" min="0" id="name" class="form-control return_cost{{$from_region->id}} return_cost" placeholder="{{ __('cargo::view.here') }}" value="{{$area_cost->return_cost ?? 0 }}" @if($is_def_mile_or_fees =='2') name="return_cost[]" @else name="return_mile_cost[]" @endif>
                                                    </div>
                                                </div>
                                                <hr>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>


                        @endif
                    </div>

                </div>

            </div>


            <div class="card-footer">
                <div class="row">
                    <div class="col-lg-5"></div>
                    <div class="col-lg-7">
                        <button type="submit" class="evenAjaxButton btn btn-lg btn-primary">{{ __('cargo::view.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

{{-- Inject styles --}}
@section('styles')
    <style>
        label {
            font-weight: bold !important;
        }
        .card-header{
            display: flex !important;
            align-items: center !important;
        }
        .form-control {
            margin-bottom: 15px !important;
        }
    </style>
@endsection

{{-- Inject Scripts --}}
@section('scripts')
    <script>
        var inputs = document.getElementsByTagName('input');

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].type.toLowerCase() == 'number') {
                inputs[i].onkeydown = function(e) {
                    if (!((e.keyCode > 95 && e.keyCode < 106) ||
                            (e.keyCode > 47 && e.keyCode < 58) ||
                            e.keyCode == 8)) {
                        return false;
                    }
                }
            }
        }
        function changeShippingCosts(element, id = null)
        {
            if(id){
                $('.shipp_cost'+id).val($(element).val());
            }else{
                $('.shipp_cost').val($(element).val());
            }
        }
        function changeTax(element, id = null)
        {
            if(id){
                $('.tax_cost'+id).val($(element).val());
            }else{
                $('.tax_cost').val($(element).val());
            }
        }
        function changeInsurance(element, id = null)
        {
            if(id){
                $('.insurance_cost'+id).val($(element).val());
            }else{
                $('.insurance_cost').val($(element).val());
            }
        }
        function changeReturnCosts(element, id = null)
        {
            if(id){
                $('.return_cost'+id).val($(element).val());
            }else{
                $('.return_cost').val($(element).val());
            }
        }
        $('.select-country').select2({
            placeholder: "Select Region"
        });
        
    </script>
@endsection