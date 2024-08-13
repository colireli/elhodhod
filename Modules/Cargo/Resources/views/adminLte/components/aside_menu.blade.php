@php
$user_role = auth()->user()->role;
$admin = 1;
$staff = 0;
$branch = 3;
$client = 4;
$driver = 5;
@endphp


@if (auth()->user()->can('manage-shipments') || in_array($user_role, [$admin, $client, $branch]))
    <li
        class="nav-item {{ areActiveRoutes(['shipments','shipments.create','shipments.import','shipments.add.api','shipments.barcode.scanner','shipment-calc','shipments.index'],'menu-is-opening menu-open active') }} @foreach (Modules\Cargo\Entities\Shipment::status_info() as $item) {{ areActiveRoutes([$item['route_name']], 'menu-is-opening menu-open active') }} @endforeach ">
        <a href="#"
            class="nav-link {{ areActiveRoutes(['shipments','shipments.create','shipments.import','shipments.add.api','shipments.barcode.scanner','shipment-calc','shipments.index'],'menu-is-opening menu-open active') }} @foreach (Modules\Cargo\Entities\Shipment::status_info() as $item) {{ areActiveRoutes([$item['route_name']], 'menu-is-opening menu-open active') }} @endforeach  ">
            <i class="fas fa-box-open"></i>
            <p>
                {{ __('cargo::view.shipments') }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">
            <!-- Shipment Menu -->
            @if (auth()->user()->can('manage-shipments') || in_array($user_role, [$admin, $client, $branch]))

                <!-- create shipment -->
                @if (auth()->user()->can('create-shipments') || in_array($user_role, [$admin, $client, $branch]))
                    <li class="nav-item">
                        <a href="{{ fr_route('shipments.create') }}"
                            class="nav-link {{ areActiveRoutes(['shipments.create']) }}">
                            <i class="fas fa-plus fa-fw"></i>
                            <p>{{ __('cargo::view.create_new_shipment') }}</p>
                        </a>
                    </li>
                @endif



                @if (in_array($user_role, [$admin, $client, $branch]) || auth()->user()->can('import-shipments'))
                    <!-- import shipment -->
                    <li class="nav-item">
                        <a href="{{ fr_route('shipments.import') }}"
                            class="nav-link {{ areActiveRoutes(['shipments.import']) }}">
                            <i class="fas fa-file-import fa-fw"></i>
                            <p>{{ __('cargo::view.import_shipments') }}</p>
                        </a>
                    </li>
                @endif

                @if ($user_role == $client)
                    <!-- shipment api -->
                    <li class="nav-item">
                        <a href="{{ fr_route('shipments.add.api') }}"
                            class="nav-link {{ areActiveRoutes(['shipments.add.api']) }}">
                            <i class="fas fa-plus fa-fw"></i>
                            <p>{{ __('cargo::view.shipment_apis') }}</p>
                        </a>
                    </li>
                @endif

                <!-- shipment barcode scanner -->
                @if (auth()->user()->can('shipments-barcode-scanner') || $user_role == $admin || $user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('shipments.barcode.scanner') }}"
                            class="nav-link {{ areActiveRoutes(['shipments.barcode.scanner']) }}">
                            <i class="fas fa-qrcode fa-fw"></i>
                            <p>{{ __('cargo::view.barcode_scanner') }}</p>
                        </a>
                    </li>
                @endif

                <!-- shipment calc -->
                @if (Modules\Cargo\Entities\ShipmentSetting::getVal('is_shipping_calc_required') == 1)
                    <li class="nav-item">
                        <a href="{{ fr_route('shipments.calculator') }}"
                            class="nav-link {{ areActiveRoutes(['shipments.calculator']) }}">
                            <i class="fas fa-calculator fa-fw"></i>
                            <p>{{ __('cargo::view.shipping_calculator') }}</p>
                        </a>
                    </li>
                @endif

                <!-- all shipments -->
                <li class="nav-item">
                    <a href="{{ fr_route('shipments.index') }}"
                        class="nav-link {{ areActiveRoutes(['shipments.index']) }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>{{ __('cargo::view.all_Shipments') }}</p>
                    </a>
                </li>


                @foreach (Modules\Cargo\Entities\Shipment::status_info() as $item)
                    @if (in_array($user_role, [$admin, $client, $branch]) ||
    auth()->user()->hasAnyDirectPermission($item['permissions']))
                        @if ($item['status'] == Modules\Cargo\Entities\Shipment::SAVED_STATUS)
                            <li class="nav-item">
                                <a href="{{ route($item['route_name'], ['status' => $item['status'], 'type' => Modules\Cargo\Entities\Shipment::PICKUP]) }}"
                                    class="nav-link {{ active_route($item['route_name'], ['status' => $item['status'],'type' => Modules\Cargo\Entities\Shipment::PICKUP]) }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ __('cargo::view.saved_pickup') }}</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route($item['route_name'], ['status' => $item['status'],'type' => Modules\Cargo\Entities\Shipment::DROPOFF]) }}"
                                    class="nav-link {{ active_route($item['route_name'], ['status' => $item['status'],'type' => Modules\Cargo\Entities\Shipment::DROPOFF]) }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ __('cargo::view.saved_dropoff') }}</p>
                                </a>
                            </li>
                        @elseif($item['status'] == Modules\Cargo\Entities\Shipment::REQUESTED_STATUS)
                            <li class="nav-item">
                                <a href="{{ route($item['route_name'], ['status' => $item['status'], 'type' => Modules\Cargo\Entities\Shipment::PICKUP]) }}"
                                    class="nav-link {{ active_route($item['route_name'], ['status' => $item['status'],'type' => Modules\Cargo\Entities\Shipment::PICKUP]) }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ __('cargo::view.requested_pickup') }}</p>
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route($item['route_name'], ['status' => $item['status']]) }}"
                                    class="nav-link {{ active_route($item['route_name'], ['status' => $item['status']]) }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ $item['text'] }}</p>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach

            @endif
        </ul>
    </li>
@endif


@if (auth()->user()->can('manage-shipments') || in_array($user_role, [$admin, $client, $branch]))
    @can('manage-users')
        {{-- {{ areActiveRoutes(['users', ['class_name' => 'show']]) }} --}}
        <li class="nav-item {{ areActiveRoutes(['receivers.index', 'receivers.create'], 'menu-is-opening menu-open active') }}">
            <a href="#"
                class="nav-link {{ areActiveRoutes(['receivers.index', 'receivers.create'], 'menu-is-opening menu-open active') }}">
                <i class="fas fa-user"></i>
                <p>
                    {{ __('cargo::view.receiver') }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview">

                <!-- Receiver list -->
                @if (auth()->user()->can('view-users') || $user_role == $admin)
                    <li class="nav-item">
                        <a href="{{ fr_route('receivers.index') }}" class="nav-link {{ areActiveRoutes(['receivers.index']) }}">
                            <i class="fas fa-list fa-fw"></i>
                             <p>{{ __('cargo::view.receiver_list') }}</p>
                        </a>
                    </li>
                @endif

                <!-- Create new Receiver -->
                @if (auth()->user()->can('create-users') || $user_role == $admin)
                    <li class="nav-item">
                        <a href="{{ fr_route('receivers.create') }}" class="nav-link {{ areActiveRoutes(['receivers.create']) }}">
                            <i class="fas fa-plus fa-fw"></i>
                            <p>{{ __('cargo::view.create_new_receiver') }}</p>
                        </a>
                    </li>
                @endif

            </ul>
        </li>
    @endcan
@endif

@if (auth()->user()->can('manage-missions') ||
    $user_role == $admin ||
    $user_role == $branch ||
    $user_role == $driver)
    <li
        class="nav-item {{ active_uri('missions', ['class_name' => 'show']) }} {{ areActiveRoutes(['missions', 'missions.index'], 'menu-is-opening menu-open active') }}  @foreach (Modules\Cargo\Entities\Mission::status_info() as $item) {{ areActiveRoutes([$item['route_name']], 'menu-is-opening menu-open active') }} @endforeach">
        <a href="#"
            class="nav-link {{ active_uri('missions') }} {{ areActiveRoutes(['missions', 'missions.index'], 'menu-is-opening menu-open active') }}  @foreach (Modules\Cargo\Entities\Mission::status_info() as $item) {{ areActiveRoutes([$item['route_name']], 'menu-is-opening menu-open active') }} @endforeach">
            <i class="fas fa-shipping-fast"></i>
            <p>
                {{ __('cargo::view.missions') }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">
            <!-- Mission Menu -->
            @if (auth()->user()->can('manage-missions') ||
    $user_role == $admin ||
    $user_role == $branch ||
    $user_role == $driver)

                <li class="nav-item">
                    <a href="{{ fr_route('missions.index') }}"
                        class="nav-link {{ areActiveRoutes(['missions.index']) }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>{{ __('cargo::view.all_missions') }}</p>
                    </a>
                </li>

                @foreach (Modules\Cargo\Entities\Mission::status_info() as $item)
                    @if (in_array($user_role, $item['user_role']) ||
    auth()->user()->hasAnyDirectPermission($item['permissions']))
                        <li class="nav-item">
                            <a href="{{ route($item['route_name'], ['status' => $item['status']]) }}"
                                class="nav-link {{ active_route($item['route_name'], ['status' => $item['status']]) }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ $item['text'] }}</p>
                            </a>
                        </li>
                    @endif
                @endforeach

            @endif
        </ul>
    </li>
@endif


@if (auth()->user()->can('manage-manifests') || in_array($user_role, [$admin, $driver, $branch]))
    <li class="nav-item   {{ areActiveRoutes(['missions.manifests'], 'menu-is-opening menu-open active') }}">
        <a href="{{ fr_route('missions.manifests') }}"
            class="nav-link {{ areActiveRoutes(['missions.manifests']) }}">
            <i class="fas fa-truck-moving fa-fw"></i>
            <p>{{ __('cargo::view.manifest') }}</p>
        </a>
    </li>
@endif


@if (auth()->user()->can('manage-transactions') || in_array($user_role, [$admin, $branch, $driver, $client]))
    <li
        class="nav-item {{ active_uri('transactions', ['class_name' => 'show']) }}  {{ areActiveRoutes(['transactions', 'transactions.create', 'transactions.index'],'menu-is-opening menu-open active') }}">
        <a href="#"
            class="nav-link {{ active_uri('transactions') }}  {{ areActiveRoutes(['transactions', 'transactions.create', 'transactions.index'],'menu-is-opening menu-open active') }}">
            <i class="fas fa-money-check-alt"></i>
            <p>
                {{ __('cargo::view.transactions') }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">

            <!-- Transaction list -->
            @if (auth()->user()->can('manage-transactions') || in_array($user_role, [$admin, $branch, $driver, $client]))
                <li class="nav-item">
                    <a href="{{ fr_route('transactions.index') }}"
                        class="nav-link {{ areActiveRoutes(['transactions.index']) }}">
                        <i class="fas fa-list fa-fw"></i>
                        <p>{{ __('cargo::view.all_transactions') }}</p>
                    </a>
                </li>
            @endif

            <!-- Create new transaction -->
            @if (auth()->user()->can('create-transactions') || in_array($user_role, [$admin, $branch]))
                <li class="nav-item">
                    <a href="{{ fr_route('transactions.create') }}"
                        class="nav-link {{ areActiveRoutes(['transactions.create']) }}">
                        <i class="fas fa-plus fa-fw"></i>
                        <p>{{ __('cargo::view.create_new_transaction') }}</p>
                    </a>
                </li>
            @endif

        </ul>
    </li>
@endif



@if (auth()->user()->can('manage-branches') ||
    auth()->user()->can('manage-customers') ||
    auth()->user()->can('manage-drivers') ||
    $user_role == $admin ||
    $user_role == $branch)

    <!-- Subcontracting -->
    <li
        class="nav-item {{ active_uri('subcontracting', ['class_name' => 'show']) }}     {{ areActiveRoutes(['admin.company.create', 'admin.company.index', 'admin.company.show', 'admin.company.edit','admin.apimodel.index','admin.apimodel.create','admin.apimodel.show','admin.apimodel.edit','admin.company_plan.create','admin.company_plan.index','admin.company_plan.show'], 'menu-is-opening menu-open active') }}">
        <a href="#"
            class="nav-link {{ areActiveRoutes(['admin.company.create', 'admin.company.index', 'admin.company.show', 'admin.company.edit','admin.apimodel.index','admin.apimodel.create','admin.apimodel.show','admin.apimodel.edit','admin.company_plan.create','admin.company_plan.index','admin.company_plan.show'], 'menu-is-opening menu-open active') }}">
            <i class="menu-icon fas fa-handshake"></i>
            <p>
                {{ __('Subcontracting') }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">
            @if (auth()->user()->can('view-branches') || $user_role == $admin || $user_role == $branch)
            <li
                class="nav-item  {{ areActiveRoutes(['admin.company.create', 'admin.company.index'], 'menu-is-opening menu-open active') }}">
                <a href="#"
                    class="nav-link {{ areActiveRoutes(['admin.company.create', 'admin.company.index'], 'menu-is-opening menu-open active') }}">
                    <i class="fas fa-building"></i>
                    <p>
                        {{ __('Companies') }}
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                <!-- company list -->
                @if (auth()->user()->can('view-branches') || $user_role == $admin || $user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('admin.company.index') }}"
                            class="nav-link {{ areActiveRoutes(['admin.company.index']) }}">
                            <i class="fas fa-list fa-fw"></i>
                            <p>{{ __('Companies') }}</p>
                        </a>
                    </li>
                @endif

                <!-- Create new company -->
                @if (auth()->user()->can('create-branches') || $user_role == $admin || $user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('admin.company.create') }}"
                            class="nav-link {{ areActiveRoutes(['admin.company.create']) }}">
                            <i class="fas fa-plus fa-fw"></i>
                            <p>{{ __('Create new company') }}</p>
                        </a>
                    </li>
                @endif

                <!-- company_plan list -->
                @if (auth()->user()->can('view-branches') || $user_role == $admin || $user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('admin.company_plan.index') }}"
                            class="nav-link {{ areActiveRoutes(['admin.company_plan.index']) }}">
                            <i class="fas fa-list fa-fw"></i>
                            <p>{{ __('Company plans') }}</p>
                        </a>
                    </li>
                @endif
                </ul>
            </li>
            @endif

        </ul>
        <ul class="nav nav-treeview">
            @if ($user_role == $branch)
            <li
                class="nav-item  {{ areActiveRoutes(['admin.rappayment.import_payment', 'admin.rappayment.import_delivery','admin.rappayment.imported_payment'], 'menu-is-opening menu-open active') }}">
                <a href="#"
                    class="nav-link {{ areActiveRoutes(['admin.rappayment.import_payment', 'admin.rappayment.import_delivery','admin.rappayment.imported_payment'], 'menu-is-opening menu-open active') }}">
                    <i class="fas fa-file"></i>
                    <p>
                        {{ __('Reports') }}
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                <!-- import list -->
                @if ($user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('admin.rappayment.import_payment') }}"
                            class="nav-link {{ areActiveRoutes(['admin.rappayment.import_payment']) }}">
                            <i class="fas fa-plus fa-fw"></i>
                            <p>{{ __('Import payments') }}</p>
                        </a>
                    </li>
                @endif

                <!-- Create new import -->
                @if ($user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('admin.rappayment.import_delivery') }}"
                            class="nav-link {{ areActiveRoutes(['admin.rappayment.import_delivery']) }}">
                            <i class="fas fa-plus fa-fw"></i>
                            <p>{{ __('Import delivery case') }}</p>
                        </a>
                    </li>
                @endif

                <!-- reports list -->
                @if ($user_role == $branch)
                    <li class="nav-item">
                        <a href="{{ fr_route('admin.rappayment.imported_payment') }}"
                            class="nav-link {{ areActiveRoutes(['admin.rappayment.imported_payment']) }}">
                            <i class="fas fa-list fa-fw"></i>
                            <p>{{ __('Report Payments') }}</p>
                        </a>
                    </li>
                @endif
                </ul>
            </li>
            @endif

        </ul>
        <ul class="nav nav-treeview">
            @if (auth()->user()->can('view-branches') || $user_role == $admin)
            <li
                class="nav-item  {{ areActiveRoutes(['admin.apimodel.create', 'admin.apimodel.index'], 'menu-is-opening menu-open active') }}">
                <a href="#"
                    class="nav-link {{ areActiveRoutes(['admin.apimodel.create', 'admin.apimodel.index'], 'menu-is-opening menu-open active') }}">
                    <i class="fas fa-bars"></i>
                    <p>
                        {{ __('APIs') }}
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <!-- apimodel list -->
                    @if (auth()->user()->can('view-branches') || $user_role == $admin)
                        <li class="nav-item">
                            <a href="{{ fr_route('admin.apimodel.index') }}"
                                class="nav-link {{ areActiveRoutes(['admin.apimodel.index']) }}">
                                <i class="fas fa-list fa-fw"></i>
                                <p>{{ __('APIs List') }}</p>
                            </a>
                        </li>
                    @endif

                    <!-- Create new apimodel -->
                    @if (auth()->user()->can('view-branches') || $user_role == $admin)
                        <li class="nav-item">
                            <a href="{{ fr_route('admin.apimodel.create') }}"
                                class="nav-link {{ areActiveRoutes(['admin.apimodel.create']) }}">
                                <i class="fas fa-plus fa-fw"></i>
                                <p>{{ __('Create new api model') }}</p>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
            @endif

        </ul>
    </li>
    <li
        class="nav-item {{ active_uri('shipment-team', ['class_name' => 'show']) }} {{ areActiveRoutes(['admin.plan.index','branches','branches.create','branches.index','branches','clients.index','clients.create','clients','drivers.index','drivers.create','drivers','shipment-team'],'menu-is-opening menu-open active') }}">
        <a href="#"
            class="nav-link {{ areActiveRoutes(['admin.plan.index','branches','branches.create','branches.index','branches','clients.index','clients.create','clients','drivers.index','drivers.create','drivers','shipment-team'],'menu-is-opening menu-open active') }}">
            <i class="fas fa-users"></i>
            <p>
                {{ __('cargo::view.shipment_team') }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">



            <!-- Branch Menu -->
            @if (auth()->user()->can('manage-branches') || $user_role == $admin)
                <li
                    class="nav-item {{ active_uri('branches', ['class_name' => 'show']) }}     {{ areActiveRoutes(['branches.create', 'branches.index', 'branches'], 'menu-is-opening menu-open active') }}">
                    <a href="#"
                        class="nav-link {{ areActiveRoutes(['branches.create', 'branches.index', 'branches'], 'menu-is-opening menu-open active') }}">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>
                            {{ __('cargo::view.branches') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <!-- Branch list -->
                        @if (auth()->user()->can('view-branches') || $user_role == $admin)
                            <li class="nav-item">
                                <a href="{{ fr_route('branches.index') }}"
                                    class="nav-link {{ areActiveRoutes(['branches.index']) }}">
                                    <i class="fas fa-list fa-fw"></i>
                                    <p>{{ __('cargo::view.branch_list') }}</p>
                                </a>
                            </li>
                        @endif

                        <!-- Create new branch -->
                        @if (auth()->user()->can('create-branches') || $user_role == $admin)
                            <li class="nav-item">
                                <a href="{{ fr_route('branches.create') }}"
                                    class="nav-link {{ areActiveRoutes(['branches.create']) }}">
                                    <i class="fas fa-plus fa-fw"></i>
                                    <p>{{ __('cargo::view.create_new_branch') }}</p>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            <!-- Customer Menu -->
            @if (auth()->user()->can('manage-customers') ||
    $user_role == $admin ||
    $user_role == $branch)

                <li
                    class="nav-item {{ active_uri('clients', ['class_name' => 'show']) }} {{ areActiveRoutes(['admin.plan.index','clients.index', 'clients.create', 'clients'], 'menu-is-opening menu-open active') }}">
                    <a href="#"
                        class="nav-link {{ areActiveRoutes(['admin.plan.index','clients.index', 'clients.create', 'clients'], 'menu-is-opening menu-open active') }}">
                        <i class="fas fa-user-friends"></i>
                        <p>
                            {{ __('cargo::view.clients') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <!-- Customers list -->
                        @if (auth()->user()->can('view-customers') ||
    $user_role == $admin ||
    $user_role == $branch)
                            <li class="nav-item">
                                <a href="{{ fr_route('clients.index') }}"
                                    class="nav-link {{ areActiveRoutes(['clients.index']) }}">
                                    <i class="fas fa-list fa-fw"></i>
                                    <p>{{ __('cargo::view.client_list') }}</p>
                                </a>
                            </li>
                        @endif

                        <!-- Create new customer -->
                        @if (auth()->user()->can('create-customers') ||
    $user_role == $admin ||
    $user_role == $branch)
                            <li class="nav-item">
                                <a href="{{ fr_route('clients.create') }}"
                                    class="nav-link {{ areActiveRoutes(['clients.create']) }}">
                                    <i class="fas fa-plus fa-fw"></i>
                                    <p>{{ __('cargo::view.create_new_client') }}</p>
                                </a>
                            </li>
                        @endif
                         <!-- plan list -->
                        @if (auth()->user()->can('view-branches') || $user_role == $admin  || $user_role == $branch)
                            <li class="nav-item">
                                <a href="{{ fr_route('admin.plan.index') }}"
                                    class="nav-link {{ areActiveRoutes(['admin.plan.index']) }}">
                                    <i class="fas fa-list fa-fw"></i>
                                    <p>{{ __('Costumer plans') }}</p>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            <!-- Driver Menu -->
            @if (auth()->user()->can('manage-drivers') ||
    $user_role == $admin ||
    $user_role == $branch)

                <li
                    class="nav-item {{ active_uri('drivers', ['class_name' => 'show']) }}  {{ areActiveRoutes(['drivers.index', 'drivers.create', 'drivers'], 'menu-is-opening menu-open active') }}">
                    <a href="#"
                        class="nav-link {{ areActiveRoutes(['drivers.index', 'drivers.create', 'drivers'], 'menu-is-opening menu-open active') }}">
                        <i class="fas fa-people-carry"></i>
                        <p>
                            {{ __('cargo::view.drivers') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">

                        <!-- Driver list -->
                        @if (auth()->user()->can('view-drivers') ||
    $user_role == $admin ||
    $user_role == $branch)
                            <li class="nav-item">
                                <a href="{{ fr_route('drivers.index') }}"
                                    class="nav-link {{ areActiveRoutes(['drivers.index']) }}">
                                    <i class="fas fa-list fa-fw"></i>
                                    <p>{{ __('cargo::view.driver_list') }}</p>
                                </a>
                            </li>
                        @endif

                        <!-- Create new driver -->
                        @if (auth()->user()->can('create-drivers') ||
    $user_role == $admin ||
    $user_role == $branch)
                            <li class="nav-item">
                                <a href="{{ fr_route('drivers.create') }}"
                                    class="nav-link {{ areActiveRoutes(['drivers.create']) }}">
                                    <i class="fas fa-plus fa-fw"></i>
                                    <p>{{ __('cargo::view.create_new_driver') }}</p>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

        </ul>
    </li>
@endif
