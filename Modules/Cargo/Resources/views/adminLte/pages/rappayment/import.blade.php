@extends('cargo::adminLte.layouts.master')
@section('content')

<div class="card">
    <div class="card-header row gutters-5" >
   
    </div>
    <form class="form-horizontal" action="{{ route('admin.rappayment.import_payment_sub') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('Select File') }}:</label>
                    <input type="file" name="file" class="form-control" required>
                    @error('file')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-group mb-0 text-right">
                <a href="{{ asset('sample-payment.xlsx') }}" class="btn btn-info">{{ __('Get Sample') }}&ensp;<i class="las la-file-alt"></i></a>
                <button type="submit" class="btn btn-primary">{{__('Import')}}</button>
            </div>
        </div>
    </form>
</div>

@endsection

