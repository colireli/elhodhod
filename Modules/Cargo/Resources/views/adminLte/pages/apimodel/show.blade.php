@extends('cargo::adminLte.layouts.master')


@section('sub_title'){{$apiModel->name}}@endsection


@section('content')
<!--begin::Entry-->
<div class="card mb-5 mb-xl-10">
            <div class="col-lg-12">
                <!--begin::Advance Table Widget 2-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bolder text-dark text-uppercase">{{$apiModel->name}} {{__('Api Model')}}</span>
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
                                            <th># API Variabls</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>api_token</td>
                                            <td>{{$apiModel->api_token}}</td>
                                        </tr>
                                        <tr>
                                            <td>user_guid</td>
                                            <td>{{$apiModel->user_guid}}</td>
                                        </tr>
                                        <tr>
                                            <td>reference</td>
                                            <td>{{$apiModel->reference}}</td>
                                        </tr>
                                        <tr>
                                            <td>client</td>
                                            <td>{{$apiModel->client}}</td>
                                        </tr>
                                        <tr>
                                            <td>phone</td>
                                            <td>{{$apiModel->phone}}</td>
                                        </tr>
                                        <tr>
                                            <td>adresse</td>
                                            <td>{{$apiModel->adresse}}</td>
                                        </tr>
                                        <tr>
                                            <td>wilaya_id</td>
                                            <td>{{$apiModel->wilaya_id}}</td>
                                        </tr>
                                        <tr>
                                            <td>commune</td>
                                            <td>{{$apiModel->commune}}</td>
                                        </tr>
                                        <tr>
                                            <td>montant</td>
                                            <td>{{$apiModel->montant}}</td>
                                        </tr>
                                        <tr>
                                            <td>remarque</td>
                                            <td>{{$apiModel->remarque}}</td>
                                        </tr>
                                        <tr>
                                            <td>produit</td>
                                            <td>{{$apiModel->produit}}</td>
                                        </tr>
                                        <tr>
                                            <td>type_id</td>
                                            <td>{{$apiModel->type_id}}</td>
                                        </tr>
                                        <tr>
                                            <td>poids</td>
                                            <td>{{$apiModel->poids}}</td>
                                        </tr>
                                        <tr>
                                            <td>stop_desk</td>
                                            <td>{{$apiModel->stop_desk}}</td>
                                        </tr>
                                        <tr>
                                            <td>stock</td>
                                            <td>{{$apiModel->stock}}</td>
                                        </tr>
                                        <tr>
                                            <td>quantite</td>
                                            <td>{{$apiModel->quantite}}</td>
                                        </tr>
                                        <tr>
                                            <td>activity</td>
                                            <td>{{$apiModel->activity}}</td>
                                        </tr>
                                        <tr>
                                            <td>tracking</td>
                                            <td>{{$apiModel->tracking}}</td>
                                        </tr>
                                        <tr>
                                            <td>success</td>
                                            <td>{{$apiModel->success}}</td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                            <!--end::Table-->

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <a href="{{ url()->previous() }}" class="btn btn-light btn-active-light-primary me-2">@lang('view.discard')</a>
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

