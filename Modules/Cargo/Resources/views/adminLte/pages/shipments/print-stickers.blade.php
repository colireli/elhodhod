
@php
$n = 0;
use \Milon\Barcode\DNS1D;
$d = new DNS1D();
$cash_payment = 'cash_payment';
@endphp
<div id="wholepage" style="width:450px; margin-left:-8px; grid-auto-flow: row;">

@foreach ($shipments as $shipment)
@php
    $n++;
@endphp
<div class="ticket" style="width:450px; /*margin-left:-8px;*/">
<div class="page" style="padding-top:0px;">
    <div class="subpage">
        <table border="0" cellpadding="0" cellspacing="0" style="font-size:10px;font-family:Arial, Helvetica, sans-serif; ">
            <tr>
                <td>
                    <table width="450px" border="0" cellpadding="0" cellspacing="0" style="font-size:16px;font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                            <td height="21px" colspan="3" style="padding-left:5px; padding-bottom:5px;">
                                <table width="100%" border="0" align="center" >
                                    <tr>
                                        <td valign="middle" style="padding-left:5px; height: 90px;">
                                            @php
                                                $system_logo = App\Models\Settings::where('group', 'general')->where('name','system_logo')->first();
                                            @endphp
                                            <img alt="Logo" src="{{  $system_logo->getFirstMediaUrl('system_logo') ? $system_logo->getFirstMediaUrl('system_logo') : asset('assets/lte/cargo-logo.svg') }}" class="logo" style="max-height: 90px; width: 90px;" />
                                        </td>
                                        <td  style="text-align: right;">
                                                @if($shipment->barcode != null)
                                                <span style="font-size:20px; font-weight:bold;">{{$shipment->code}}</span>
                                                    @php
                                                        echo '<img src="data:image/png;base64,' . $d->getBarcodePNG($shipment->code, "C128") . '" alt="barcode"  style="max-height: 90px; max-width: 290px; margin-right:4px" />';
                                                    @endphp
                                                @endif




                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="21px" colspan="3" style="border-top:#000000  1px solid;border-bottom:#000000 1px solid;">
                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="81%" height="21px" valign="top">
                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td>
                                                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td style="padding-left:10px; font-size:20px; font-weight:bold; width:400px;">
                                                                     {{($shipment->delivery_type == 1)?'HOME':'DESK'}}
                                                                </td>

                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left:10px;font-size: 14px;white-space: pre-line;word-wrap: break-word;max-width: 360px;">
                                                      @if(isset($shipment->to_state)){{$shipment->to_state->name ?? 'Null'}} @endif
                                                      @if(isset($shipment->to_area) && $shipment->delivery_type == 1){{$shipment->to_area->name  ?? 'Null'}} @else {{ $shipment->to_stopdesk->address ?? 'Null'}} @endif

                                                  	</td>

                                                </tr>
                                            </table>
                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style=" border-bottom: 0px solid #000000;">
                                                        <div style="margin-top:1px;">
                                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                    <td style="text-align: center; padding-top: 6px;" colspan="1">
                                                                    <span style="font-size:18px; font-weight:bold; padding:3px;">
                                                                        @if($shipment->track != null)
                                                                            {{$shipment->track}}
                                                                        @endif
                                                                    </span>
                                                                        <br />

                                                                    </td>

                                                                </tr>
                                                                <tr>
                                                                    <td style="padding-left: 2px; text-align: center">
                                                                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                            <tr>
                                                                                <td  style="text-align: center">
                                                                                    <br />
                                                                                        @if($shipment->track != null)
                                                                                            @php
                                                                                                echo '<img src="data:image/png;base64,' . $d->getBarcodePNG($shipment->track, "C128") . '" alt="tracking" />';
                                                                                            @endphp
                                                                                        @endif
                                                                                    <br />
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" style=" padding:8px">
                                                                        <span style="font-size:16px; font-weight:bold;">
                                                                        <br />
                                                                        @if($shipment->order_id != null) {{ __('cargo::view.order_id') }}: {{$shipment->order_id}} / @endif {{$shipment->code}} / {{$shipment->total_weight}} {{ __('cargo::view.KG') }} / @if(strpos($shipment->shipping_date, '/' ))
                            {{ Carbon\Carbon::createFromFormat('d/m/Y', $shipment->shipping_date)->format('d-m-Y') }}
                        @else
                            {{\Carbon\Carbon::parse($shipment->shipping_date)->format('d-m-Y')}}
                        @endif
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding:5px; font-size:16px;">
                                                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td style="font-size:16px;word-wrap: break-word;max-width: 360px;">
                                                                    <span style="font-weight:bold;">{{ __('cargo::view.from') }}: </span>
                                                                    {{$shipment->client->name}}<br />
                                                                    {{$shipment->client_phone}}<br />
                                                                    {{ $shipment->from_address ? $shipment->from_address->address : ''}}
                                                                </td>
                                                                <td style="font-size:16px;word-wrap: break-word;max-width: 360px;">
                                                                    <span style="font-weight:bold;">{{ __('cargo::view.to') }}: </span>
                                                                    {{$shipment->reciver_name}}<br />
                                                                    {{$shipment->reciver_phone}}<br />
                                                                    {{$shipment->reciver_address}}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin-top:5px;">
                                                <tr>
                                                    <td style="padding:5px; font-size:15px; text-align:center">
                                                        <span style="font-weight:bold; font-size: 14px;">{{ __('cargo::view.contains') }}: </span>
                                                        @php $i=0; @endphp
                                                        @foreach(Modules\Cargo\Entities\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)
                                                            @if ($i != 0 ), @endif{{$package->description}}
                                                            @php $i++; @endphp
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>


        </table>
    </div>
</div>
<div class="page" style="padding-top:0px;">
    <div class="subpage">
        <style media="all">
            @media print {
                @page {
                    size : 100mm 100mm;
                    margin : 0;
                }
            }
            .upper-container, .main-container, .order-units-container, .address-container, .return-authorization {
                margin: 5px;
            }
            .float-left {
                float: left;
            }
            .float-right {
                float: right;
            }
            .upper-container, .main-container, .upper-units-container {
                display: block;
                overflow: auto;
            }

            .upper-container, .order-units-container {
                border: 1px solid #ccc;
            }

            .order-units-container {
                /*clear: both;*/
            }

            .upper-container {
                margin-top: 20px;
            }

            .item-info-container {
                padding: 5px;
            }

            .main-container {
                width: 70%;
            }

            .barcode-container {
                /*width: 50%;*/
                text-align: right;
            }

            .order-id-container {
                padding: 10px;
            }

            .header-container {
                overflow: auto;
                display: block;
                margin-bottom: 0;
                background-color: #f7f7f7;
                border-bottom: 1px solid #ccc;
                padding: 10px;
            }

            .upper-units-container {
                width: 100%;
            }

            .image-container {
                width: 40px;
                margin-right: 10px;
            }

            .margin-top {
                margin-top: 20px;
            }

            .not-first-unit-container {
                border-top: 1px dashed #ccc;
            }

            .bold {
                font-weight: bold;
            }

            .font-20 {
                font-size: 20px;
            }

            .item-image {
                width: 40px;
            }

            .address {
                margin-bottom: 3px;
            }

            .width-90-px {
                width: 90px;
            }

            .width-200-px {
                width: 200px;
            }

            .margin-right {
                margin-right: 10px;
            }

            .green-text {
                color: #008000;
            }

            .red-text {
                color: red;
            }

            .not-inspected {
                color: #EB6E13;
            }

            .info-container {
                width: 45%;
            }

            .text-wrap {
                overflow-wrap: break-word;
            }

            .text-align-right {
                text-align: right;
            }

        </style>
        <style media="print">
            .no-print {
                display: none;
            }

            .main-container {
                width: 100%;
            }

            .page-break {
                page-break-before: always;
            }


        </style>

        <br>

        <table class="flex-container" style="width:100%; font-size:24px; font-weight:bold; padding:2px 6px 2px 6px;  margin: 2px 4px 2px 4px;">
         <tr>
          <td colspan="2">
            @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)

                @if($shipment->payment_type == Modules\Cargo\Entities\Shipment::POSTPAID )
                    {{($shipment->amount_to_be_collected + $shipment->shipping_cost)}}.00 DA
                @else
                    {{($shipment->amount_to_be_collected)}}.00 DA
                @endif

            @else
                @if($shipment->payment_type == Modules\Cargo\Entities\Shipment::POSTPAID )
                    {{( $shipment->shipping_cost)}}.00 DA
                @else
                    0.00 DA
                @endif
            @endif
          </td>
          <td style="background-color:yellow; text-align:center;">{{$shipment->to_state_id}}</td>
          </tr>
        </table>
            </tr>
        <!-- <div class="page-break"></div> -->
    </div>
</div>


</div>
@if(count($shipments) > $n)
<div class="page-break"></div>
@endif
@endforeach
</div>

<script>
window.onload = function() {
javascript:window.print();
};
</script>

