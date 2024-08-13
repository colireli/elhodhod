<x-base-layout>


    <x-slot name="pageTitle">
        @lang('view.upload_addon')
    </x-slot>

    <style>
        .modal-content {
            padding: 300px;
        }
        .spinner-border {
            width: 7rem;
            height: 7rem;
        }
    </style>
    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">

        <div class="wrapper-settings">
            <div class="mx-auto mb-5 col-lg-12">

                <div class="card mb-5">
                    <div class="card-body">
                        <div class="message  message--warning">
                            <p>{{ __('view.upload_addon') }}.</p>
                        </div>

                        <form class="form-horizontal" id="kt_form_1" action="{{ route('post.addon.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group mb-5">
                                <label for="file" class="col-md-12 control-label">{{ __('view.ZIP_file_to_import') }}</label>

                                <div class="col-md-12">
                                    <input id="file" type="file" class="form-control @error('zip_file') is-invalid @enderror" name="zip_file" required>

                                    @error('zip_file')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>
                            </div>

                            <!-- <div class="form-group mb-5">
                                <label for="file" class="col-md-12 control-label">{{ __('view.upload_addon_image') }}</label>

                                <div class="col-md-12">
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" required>

                                    @error('image')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>
                            </div> -->

                            <div class="mb-0 text-right form-group">
                                <button type="submit" class="btn btn-sm btn-primary">{{ __('view.upload') }}</button>
                            </div>
                        </form>

                        <div class="modal modal-fullscreen-xl" id="pageLoader" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="sr-only">{{ __('view.uploading') }}...</span>
                                        </div>
                                        <span class="d-block mt-3">{{ __('view.uploading') }}...</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--end::Basic info-->
    @section('scripts')
    <script>

        $( "#kt_form_1" ).submit(function( ) {

            $('#pageLoader').show();
        });

        $(function () {

            $('#pageLoader').hide();
        })

    </script>
    @endsection
</x-base-layout>
