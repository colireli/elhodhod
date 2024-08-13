@extends('cargo::adminLte.layouts.master')


@section('sub_title'){{$company->name}}@endsection


@section('content')
<!--begin::Entry-->
<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Card-->
        <div class="card card-custom gutter-b">
            <div class="card-body">
                <!--begin::Details-->
                <div class="d-flex mb-9">
                    <!--begin: Pic-->
                    <!-- <div class="flex-shrink-0 mr-7 mt-lg-0 mt-3">
                        <div class="symbol symbol-50 symbol-lg-60">
                            <img src="@if($company->img){{uploaded_asset($company->img)}} @else {{ asset('assets/img/avatar-place.png') }} @endif" alt="image" />
                        </div>
                    </div> -->
                    <!--end::Pic-->
                    <!--begin::Info-->
                    <div class="flex-grow-1">
                        <!--begin::Title-->
                        <div class="d-flex justify-content-between flex-wrap mt-1">
                            <div class="d-flex mr-3">
                               <h3> {{$company->name}}</h3>
                                
                            </div>
                        </div>
                        <!--end::Title-->
                        <!--begin::Content-->
                        <div class="d-flex flex-wrap justify-content-between mt-1">
                            <div class="d-flex flex-column flex-grow-1 pr-8">
                                <div class="d-flex flex-wrap mb-4">
                                    <a href="#" class="text-dark-50 text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                    <i class="la la-user mr-2 font-size-lg"></i>{{$branch->responsible_name}}</a>
                                    <a href="@if(auth()->check() && auth()->user()->role == 1)
                                    {{ route('branchs.login', encrypt($branch->id)) }}
                                    @else
                                    #
                                    @endif" class="text-dark-50 text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                    <i class="la la-laptop mr-2 font-size-lg"></i>{{$branch->name}}</a>
                                    <a href="#" class="text-dark-50 text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                    <i class="la la-code mr-2 font-size-lg"></i>{{$company->model->name}}</a>
                                    <a href="{{ route('admin.company_plan.show', $plan) }}" class="text-dark-50 text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                    <i class="la la-check mr-2 font-size-lg"></i>{{$plan->title}}</a>
                                </div>
                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->
                <div class="separator separator-solid"></div>
                <!--begin::Items-->
                <div class="d-flex align-items-center flex-wrap mt-8">
                    <!--begin::Item-->
                    <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                        <span class="mr-4">
                            <i class="flaticon-piggy-bank display-4 text-muted font-weight-bold"></i>
                        </span>
                        @php
                            $company_ship = Modules\Cargo\Entities\Shipment::where('company' , $company->id);
                        @endphp
                        <div class="d-flex flex-column text-dark-75">
                            <span class="font-weight-bolder font-size-sm">{{__('Shipments')}}</span>
                            <span class="font-weight-bolder font-size-h5">{{format_price($company_ship->sum('shipping_cost'))}}</span>
                        </div>
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                        <span class="mr-4">
                            <i class="flaticon-chat-1 display-4 text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column text-dark-75">
                            <span class="font-weight-bolder font-size-sm">{{__('Company Cost')}}</span>
                            <span class="font-weight-bolder font-size-h5">{{format_price($company_ship->sum('company_cost'))}}</span>
                        </div>
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                        <span class="mr-4">
                            <i class="flaticon-chat-1 display-4 text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column">
                            <span class="text-dark-75 font-weight-bolder font-size-sm">{{$company_ship->count()}} {{__('shipments')}}</span>
                        </div>
                    </div>
                    <!--end::Item-->
                </div>
            </div>
        </div>
            <!--begin::Row-->
        <div class="row">
            <div class="col-lg-12">
                <!--begin::Advance Table Widget 2-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bolder text-dark text-uppercase">{{$company->name}} {{__('shipments')}}</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-2 pb-0 mt-n3">
                        <div class="tab-content mt-5" id="myTabTables11">
                            <!--begin::Table-->
                            <div class="table-responsive">

                                <table class="table mb-0 aiz-table">
                                    <thead>
                                        <tr>
                                            <th width="3%"></th>
                                            <th width="3%">#</th>
                                            <th>{{__('Code')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Type')}}</th>
                                            <th>{{__('Customer')}}</th>
                                            <th>{{__('Branch')}}</th>

                                            <th>{{__('Shipping Cost')}}</th>
                                            <th>{{__('Payment Method')}}</th>
                                            <th>{{__('Paid')}}</th>
                                            <th>{{__('Shipping Date')}}</th>
                                            <th>{{__('Driver')}}</th>
                                            <th>{{__('Mission')}}</th>
                                            <th class="text-center">{{__('Created At')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($company_ship->paginate(30) as $shipment)

                                        <tr>
                                            <td><label class="checkbox checkbox-success"><input data-clientaddress="{{$shipment->client_address}}" data-clientid="{{$shipment->client->id}}" data-branchid="{{$shipment->branch_id}}" data-branchname="{{$shipment->branch->name}}"  type="checkbox" class="sh-check" name="checked_ids[]" value="{{$shipment->id}}" /><span></span></label></td>
                                            <td width="3%"><a href="{{ fr_route('shipments.show', $shipment->id)}}">{{ $shipment->id }}</a></td>
                                            <td width="5%"><a href="{{ fr_route('shipments.show', $shipment->id)}}">{{$shipment->barcode}}</a></td>
                                            <td>{{$shipment->getStatus()}}</td>
                                            <td>{{$shipment->type}}</td>
                                            <td><a href="{{route('clients.show',$shipment->client_id)}}">{{$shipment->client->name}}</a></td>
                                            <td><a href="{{route('branches.show',$shipment->branch_id)}}">{{$shipment->branch->name}}</a></td>

                                            <td>{{format_price($shipment->shipping_cost)}}</td>
                                            <td>{{$shipment->pay->name ?? ""}}</td>
                                            <td>@if($shipment->paid == 1) {{__('Paid')}} @else - @endif</td>
                                            <td>{{$shipment->shipping_date}}</td>
                                            <td>@isset($shipment->captain_id) {{$shipment->captain->name}} @else - @endisset</td>
                                            <td>@isset($shipment->current_mission->id) {{$shipment->current_mission->code}} @else - @endisset</td>
                                            <td class="text-center">
                                                {{$shipment->created_at->format('Y-m-d')}}
                                            </td>
                                        </tr>

                                        @endforeach

                                    </tbody>
                                </table>

                            </div>
                            <!--end::Table-->

                            <div class="aiz-pagination">
                                {{ $company_ship->paginate(30)->appends(request()->input())->links() }}
                            </div>
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Entry-->
                </div>
            </div>
        </div>
        @yield('profile')
    </div>
</div>

@endsection
