@extends('cargo::adminLte.layouts.blank')

@php
    $pageTitle =  __('cargo::view.tracking_shipment') . ' #' . (isset($model) ? $model->code : __('cargo::view.error'));

    use \Milon\Barcode\DNS1D;
    $d = new DNS1D();

    $system_logo = App\Models\Settings::where('group', 'general')->where('name','system_logo')->first();
@endphp

@section('page-title', $pageTitle )

@section('page-type', 'page')

@section('styles')

@endsection

@section('page-content')
    @if(isset($error))

        <div id="shipments-tracking-page">
            <div id="shipments-tracking" class="widget bdaia-widget widget_mc4wp_form_widget">
                <div class="tracking-error">
                    <p class="bdaia-mc4wp-bform-p bd1-font"  >
                        {{ $error ?? '' }}
                    </p>
                </div>
                
                <div class="widget-inner">
                    <form class="form" action="{{route('shipments.tracking')}}" method="GET">
                        <div class="bdaia-mc4wp-form-icon">
                            <span class="bdaia-io text-primary" style="line-height: 0">
                                <svg style="width:auto" height="58px" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="m57.123 31.247v13.63h-12.247v-13.63zm3.925-4h27.552l-8.625-15.99h-20.155zm-18.868-15.99h-20.159l-8.621 15.99h27.551zm2.783 15.99h12.073l-1.229-15.99h-9.615zm2.03 44.992a25.612 25.612 0 0 1 .486-4.979h-19.888a5.133 5.133 0 0 0 -5.127 5.127v1.432a5.133 5.133 0 0 0 5.127 5.127h20.3a25.46 25.46 0 0 1 -.897-6.707zm7.393 17.875a25.231 25.231 0 0 1 -5.032-7.169h-21.763a9.137 9.137 0 0 1 -9.127-9.127v-1.431a9.137 9.137 0 0 1 9.127-9.127h21.04a25.28 25.28 0 0 1 41.507-8.9c.214.214.418.434.623.654v-23.767h-29.638v15.63a2 2 0 0 1 -2 2h-16.247a2 2 0 0 1 -2-2v-15.63h-29.638v60.185h44.58c-.49-.421-.97-.856-1.432-1.318zm10.36-23.922a2.08 2.08 0 0 0 -2.08 2.08v7.933a2.08 2.08 0 1 0 4.16 0v-7.932a2.08 2.08 0 0 0 -2.08-2.08zm9.6 2.08v7.933a2.08 2.08 0 1 1 -4.16 0v-7.932a2.08 2.08 0 0 1 4.16 0zm7.516 0v7.933a2.08 2.08 0 1 1 -4.16 0v-7.932a2.08 2.08 0 0 1 4.16 0zm-17.112-2.08a2.08 2.08 0 0 0 -2.08 2.08v7.933a2.08 2.08 0 1 0 4.16 0v-7.932a2.08 2.08 0 0 0 -2.084-2.08zm9.6 2.08v7.933a2.08 2.08 0 1 1 -4.16 0v-7.932a2.08 2.08 0 0 1 4.16 0zm7.516 0v7.933a2.08 2.08 0 1 1 -4.16 0v-7.932a2.08 2.08 0 0 1 4.16 0zm11.673 3.967a21.292 21.292 0 1 1 -21.3-21.292 21.292 21.292 0 0 1 21.292 21.292zm-6.716 0a14.576 14.576 0 1 0 -14.584 14.576 14.576 14.576 0 0 0 14.576-14.576zm29.934 37.387a6.864 6.864 0 0 1 -6.974 7.1 8.6 8.6 0 0 1 -6.214-2.785l-14.663-15.651a1 1 0 0 1 .023-1.391l.977-.977-3.057-3.057a25.493 25.493 0 0 0 6.036-6.044l3.061 3.061.977-.977a1 1 0 0 1 1.391-.023l15.651 14.656a8.624 8.624 0 0 1 2.784 6.088zm-4 .066a4.608 4.608 0 0 0 -1.52-3.233l-13.537-12.672-3.89 3.888 12.671 13.532a4.586 4.586 0 0 0 3.294 1.52 2.868 2.868 0 0 0 2.974-3.034z"/></svg>
                            </span>
                        </div>

                        <p class="bdaia-mc4wp-bform-p bd1-font"  >
                            {{ __('cargo::view.tracking_shipment') }}
                        </p>

                        <p class="bdaia-mc4wp-bform-p2 bd2-font" >
                            {{ __('cargo::view.enter_your_tracking_code') }}
                        </p>

                        <div class="mc4wp-form-fields">
                            <p>
                                <label >
                                    {{ __('cargo::view.enter_your_tracking_code') }}
                                </label>

                                <input type="text" name="code" placeholder="{{__('cargo::view.example_SH00001')}}">
                            </p>
                            <p>
                                <input type="submit" class="btn btn-submit submit" value="{{__('cargo::view.search')}}">
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div><!--#shipments-tracking-page -->
    @else
    <style>
            .payment-wrap {
            border: 1px solid #ececec;
            padding: 0 10px 10px;
            margin: 0 0 10px; 
            border-radius: 3px;}

            .payment-title {
            border-bottom: 1px solid #ccc;
            padding: 18px 0;
            margin: 0 0 26px; }
            .payment-title span {
                display: inline-block;
                color: #ff6b6b;
                font-size: 22px;
                margin: 0 8px 0 0; }
            .payment-title h4 {
                display: inline-block;
                margin: 0; }
                
            .track-title {
            border-bottom: 1px solid #ccc;
            padding: 3px 0;
            margin: 0 0 6px; }
            .track-title span {
                display: inline-block;
                color: #bbb;
                font-size: 18px;
                margin: 0 5px 0 0; }
            .track-title h4 {
                display: inline-block;
                margin: 0; }

            .trackstatus-title {
            border-bottom: 0px solid #ccc;
            padding: 3px 0;
            margin: 0 0 6px; }
            .trackstatus-title span {
                display: inline-block;
                color: #00ab4c;
                font-size: 18px;
                margin: 0 8px 0 0; }
            .trackstatus-title h4 {
                display: inline-block;
                margin: 0; }	

            .mapstatus-title {
            border-bottom: 0px solid #ccc;
            padding: 3px 0;
            margin: 0 0 6px; }
            .mapstatus-title span {
                display: inline-block;
                color: #2962FF;
                font-size: 18px;
                margin: 0 8px 0 0; }
            .mapstatus-title h4 {
                display: inline-block;
                margin: 0; }		

            .card-header:hover {
            text-decoration: none; }

            .card-header h5 {
            text-align: left;
            font-size: 20px;
            font-weight: 500; }

            .card-header img {
            width: 82px;
            position: absolute;
            right: 14px;
            top: 13px; }

            .booking-summary_block {
            border: 1px solid #ececec; }
            .booking-summary_block h6 {
                font-weight: 700; }
            .booking-summary_block span {
                font-size: 14px; }

            .booking-summary-box {
            padding: 24px; }

            .booking-summary_contact {
            margin: 22px 0 22px; }
            .booking-summary_contact p {
                font-size: 15px;
                margin: 0;
                line-height: 1.8; }

            .booking-summary_deatail h5 {
            font-weight: 600; }

            .min-height-block {
            min-height: 500px; }
            
            .mintrack-height-block {
            min-height: 250px; }

            .booking-cost {
            margin: 20px 0 0; }
            .booking-cost span {
                font-weight: 600; }
            .booking-cost p {
                font-size: 15px;
                margin: 10px 0 0;
                line-height: 1.8; }
                .booking-cost p span {
                float: right; }
                
            .track-cost {
            margin: 0px 0 0; }
            .track-cost span {
                font-weight: 600; }
            .track-cost p {
                font-size: 15px;
                margin: 10px 0 0;
                line-height: 1; }	  
                
                

            .payment-method-collapse .card-header {
            cursor: pointer; }

            .total-red {
            color: #ff6b6b; }

            .flex-fill {
            -ms-flex: 1 1 auto !important;
            -webkit-box-flex: 1 !important;
            flex: 1 1 auto !important; }

            /*# sourceMappingURL=style.css.map */


            .param {
                margin-bottom: 7px;
                line-height: 1.4;
            }
            .param-inline dt {
                display: inline-block;
            }
            .param dt {
                margin: 0;
                margin-right: 7px;
                font-weight: 600;
            }
            .param-inline dd {
                vertical-align: baseline;
                display: inline-block;
            }

            .param dd {
                margin: 0;
                vertical-align: baseline;
            } 

            .shopping-cart-wrap .price {
                font-size: 18px;
                margin-right: 5px;
                display: block;
            }

            .table {
                width: 100%;
                background: #fff;
                -webkit-box-shadow: rgba(0,0,0,.19) 0 2px 6px;
                box-shadow: 0 1px 3px rgba(0,0,0,.19);
                border-radius: 8px;
                border-color: #ff6b6b;
                border-radius: .35rem;
                -webkit-font-smoothing: antialiased; 
                color: #737373;
            }

            .text-muted {
                background: #fafafa;
                line-height: 2.5;
            }
            var {
                font-style: normal;
            }

            h5.form_sub {
                color: #797979;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                padding: 10px 10px 12px;
                background: #f3f3f3;
                margin: 23px 0 12px;
                font-size: 15px;
                text-align: left;
            }


            .timeline {
            position: relative;
            padding: 1em 3em;
            border-left: 2px solid #82b641;
            border-top: none;
            }

            .event .event-speaker {
            font-style: italic;
            text-align: right;
            }

            .timeline .event {
            border-bottom: 1px dashed rgba(89, 89, 89, 0.14);
            padding-bottom: 2em;
            margin-bottom: 0em;
            position: relative;
            }

            .timeline .event:last-of-type {
            padding-bottom: 0;
            margin-bottom: 0;
            border: none;
            }

            .timeline .event:after {
            position: absolute;
            display: block;
            }

            .timeline .event:after {
            box-shadow: 0 0 0 4px #82b641;
            left: -52.85px;
            background: #fff;
            border-radius: 50%;
            height: 8px;
            width: 8px;
            content: "";
            top: 15px;
            }

            .fake{
                background: #fff;
                padding: 30px;
            }

            .booking-page-container h1{
                text-align: center;
                padding: 30px;
            }

            #items {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

    </style>
<!-- ERROR PAGE -->
<section class="bg-home">
    <div class="home-center">
        <div class="home-desc-center">
            <div class="container">
                <div class="checkout-form">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="user-profile-data">

                                <br><br><br>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="trackstatus-title">
                                            <p><span class="ti-package align-top" style="font-size: 30px;"></span> <b>{{$model->getStatus()}}</b></p>
                                            <label> </label>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="trackstatus-title">
                                        <label>{{ __('cargo::view.shipment') }}: <b>{{$model->code}}</b></label>
                                    </div>
                                    </div>
                                    {{--
                                    <div class="col-md-4">
                                        <div class="trackstatus-title">
                                            <a class="btn btn-secondary btn-sm" target="blank" href="{{route('tracking.print', $model->id )}}"><i style="color:white" class="ti-printer"></i>&nbsp;{{ __('cargo::view.shipping_print') }}</a>
                                        </div>
                                    </div>
                                    --}}
                                </div>

                                <!-- General Information -->
                                <div class="payment-wrap">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="track-title">
                                                <h5 class="form_sub" style="background-color: #ff700a; border-radius: 3px; color:white">{{ __('cargo::view.Sender') }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <span class="ti-location-pin align-top"style="font-size: 30px;"></span> <label>{{ __('cargo::view.City_collection') }}<br>
                                                    <b>@if(isset($model->from_country)){{$model->from_country->name}} @endif</b></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <span class="ti-location-pin align-top"style="font-size: 30px;"></span> <label>{{ __('cargo::view.City_of_origin') }}<br>   
                                                    <b>@if(isset($model->from_state)){{$model->from_state->name}} @endif </b></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <span class="ti-calendar align-top"style="font-size: 30px;"></span> <label>{{ __('cargo::view.Date_of_shipment') }}<br>
                                                    <b>{{$model->shipping_date}}</b></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="track-title">
                                                    <span class="ti-timer align-top"style="font-size: 30px;"></span> <label>{{ __('cargo::view.Shipping_Time') }}<br>
                                                        <b>{{ $model->deliveryTime ? json_decode($model->deliveryTime->name, true)[app()->getLocale()] : ''}}</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <label>{{ __('cargo::view.Contact_name') }}<br> <b>{{ $client->name }}</b></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="track-title">
                                                    <span class="ti-direction-alt align-top" style="font-size: 30px;"></span> <label>   <br> <b>{{$ClientAddress->address}}</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach($PackageShipment as $package)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <label>{{ __('cargo::view.Shipping_quantity') }}<br> <b>{{$package->qty}}</b></label>
                                            </div>
                                        </div>
                                     
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="track-title">
                                                    <label>{{ __('cargo::view.weigh_length_width_height') }}<br> <b> {{$package->weight." ". __('cargo::view.KG')." x ".$package->length." ". __('cargo::view.CM') ." x ".$package->width." ".__('cargo::view.CM')." x ".$package->height." ".__('cargo::view.CM')}}</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="track-title">
                                                    <span class="ti-comment-alt align-top"
                                                        style="font-size: 30px;"></span>
                                                    <label>
                                                    {{ __('cargo::view.package_items') }} <br> <b>{{$package->description}}</b>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <!--// General Information -->

                                <!-- track shipment -->
                                <div class="payment-wrap">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="track-title">
                                                <h5 class="form_sub"  style="background-color: #ff700a; border-radius: 3px; color:white">{{ __('cargo::view.recipient') }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <span class="ti-location-pin align-top" style="font-size: 30px;"></span> <label>{{ __('cargo::view.delivery_city') }}<br>
                                                    <b>@if(isset($model->to_country)){{$model->to_country->name}} @endif</b></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <span class="ti-location-pin align-top" style="font-size: 30px;"></span> <label>{{ __('cargo::view.Destination_city') }}<br>
                                                    <b>@if(isset($model->to_state)){{$model->to_state->name}} @endif</b></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <span class="ti-calendar align-top"
                                                    style="font-size: 30px;"></span> <label>{{ __('cargo::view.Shipping_Time') }}<br>
                                                    <b>{{ $model->deliveryTime ? json_decode($model->deliveryTime->name, true)[app()->getLocale()] : ''}}</b>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="track-title">
                                                    <span class="ti-timer align-top"
                                                        style="font-size: 30px;"></span> <label>{{ __('cargo::view.expected_date_of_arrival') }}<br> <b>{{$model->collection_time}}</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="track-title">
                                                <label> {{ __('cargo::view.contact_name') }}<br> <b>{{$model->reciver_name}}</b></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="track-title">
                                                    <span class="ti-direction-alt align-top"
                                                        style="font-size: 30px;"></span> <label>{{ __('cargo::view.contact_address') }}<br> <b>{{$model->reciver_address}}</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- /.user-profile-data -->
                        </div> <!-- /.col- -->

                        <div class="col-lg-5">
                            <br><br><br><br><br><br><br>
                            <div class="booking-summary_block">
                                <div class="booking-summary-box">
                                    <h5>{{ __('cargo::view.shipping_record') }}</h5>
                                    <div class="track-cost">
                                        <ul class="timeline a">
                                            <li class="event">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <p class="text-left button5">{{$model->created_at->diffForHumans()}}</p>
                                                        <h6 class="text-left button4">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <p class="text-right button5">{{ __('cargo::view.created') }}</p>
                                                        <h4></h4>
                                                    </div>
                                                </div>
                                            </li>
                                            <!--event schedule 1 end-->
                                        </ul>
                                    </div>
                                    <div class="track-cost">

                                        <ul class="timeline a">
                                            @foreach($model->logs()->orderBy('id','asc')->get() as $log)
                                            <li class="event">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <p class="text-left button5">{{$log->created_at->diffForHumans()}}</p>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <p class="text-right button5">{{Modules\Cargo\Entities\Shipment::getClientStatusByStatusId($log->to)}}</p>
                                                        <h4></h4>
                                                    </div>
                                                </div>
                                            </li>
                                            <!--event schedule 1 end-->
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- /.row -->
                </div> <!-- /.checkout-form -->
            </div> <!-- /.container -->
        </div>
    </div>
    <br><br>

</section>


@endif
@endsection
