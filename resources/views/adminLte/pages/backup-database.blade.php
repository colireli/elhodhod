<x-base-layout>

    <x-slot name="pageTitle">
       @lang('view.backup_database')
    </x-slot>

    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">
        <div class="wrapper-settings">
            <div class="mx-auto mb-5 col-lg-12">
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="message  message--warning">
                            <p>{{ __('view.backup_database_note') }}.</p>
                        </div>
 
                        <form class="form-horizontal" id="kt_form_1" action="{{ route('post.backup.database') }}" method="GET" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-5">
                                <!-- <label for="file" class="col-md-12 control-label">{{ __('view.ZIP_file_to_import') }}</label> -->
                                <div class="col-md-12">
                                    <button href="{{ route('post.backup.database') }}" class="btn btn-primary">{{ __('view.backup_database_button') }}</button>
                                </div>
                            </div>
                        <!-- </form> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
