@extends('installation.layout')
@section('content')
    <div id="wizard">
        <h4>Checking file permissions</h4>
        <a class="steps"><span class="current-info audible"></span><span class="number">1.</span> </a>
        <a class="steps current"><span class="current-info audible">current step: </span><span class="number">2.</span> </a>
        <a class="steps"><span class="current-info audible"></span><span class="number">3.</span> </a>
        <a class="steps"><span class="current-info audible"></span><span class="number">4.</span> </a>
        <a class="steps"><span class="current-info audible"></span><span class="number">5.</span> </a>
        <a class="steps"><span class="current-info audible"></span><span class="number">6.</span> </a>
        <a class="steps last"><span class="current-info audible"></span><span class="number">7.</span> </a>
        <section>
            <div class="form-row">
                <div class="tooltip">Please review server requirements. If all items are checked, you may proceed to enter your database connection credentials. </div>

                <ul class="list-group">
                    @php
                        $phpVersion = number_format((float)phpversion(), 2, '.', '');
                    @endphp
                    <li class="list-group-item text-semibold check @if ($phpVersion >= 8.0) check success @else close faild @endif">
                        <p>
                            Php version 8.0 +
                        </p>
                        <small class="text-gray-500">
                            <code>PHP v8.0</code> or higher is required for the application to work.
                        </small>
                    </li>
                    <li class="list-group-item text-semibold @if ($permission['curl_enabled']) check success @else close faild @endif">
                        <p>
                            CURL Enabled
                        </p>
                        <small class="text-gray-500">
                            <code>cURL</code> extension is required to fetch remote data.
                        </small>
                    </li>
                    <li class="list-group-item text-semibold check @if ($permission['db_file_write_perm']) check success @else close faild @endif">
                        <p>
                            <b>.env</b> File Permission
                        </p>
                        <small class="text-gray-500">
                            <code>.env</code> file must be writable.
                        </small>
                    </li>
                    <li class="list-group-item text-semibold check @if ($permission['routes_file_write_perm']) check success @else close faild @endif">
                        <p>
                            <b>RouteServiceProvider.php</b> File Permission
                        </p>
                        <small class="text-gray-500">
                            <code>app/Providers/RouteServiceProvider.php</code>
                            <br>
                            file must be writable.
                        </small>
                    </li>
                </ul>
            </div>

            <div class="actions">
                <ul>
                    <li><a href="{{ url('/installation') }}">Previous</a></li>

                    @if ($permission['curl_enabled'] == 1 && $permission['db_file_write_perm'] == 1 && $permission['routes_file_write_perm'] == 1 && $phpVersion >= 8.0)
                        @if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1')
                            <li><a href="{{ route('installation.step3') }}" class="next">Next</a></li>
                        @else
                            <li><a href="{{ route('installation.step3') }}" class="next">Next</a></li>
                        @endif
                    @endif
                </ul>
            </div>
        </section>
    </div>
@endsection
