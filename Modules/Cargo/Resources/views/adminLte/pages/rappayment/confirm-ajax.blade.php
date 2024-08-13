<style>
    canvas#signaturePad {
        background-color: #f7f8fa;
        border: 1px solid #ebedf2;
        width: 100%;
        display: block;
        border-radius: 5px;
        color: #000;
        margin-top:5px;
    }
    #signaturePadImg{
        display:none;
    }
</style>

<form action="{{ route('admin.rappayment.client_payments.picked', $payment->id) }}" method="POST">
    @csrf
    <div class="modal-header">
        <h4 class="modal-title h6">{{__('Confirm Payment')}}</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                {{ __('Are you sure you want to submit this Operation?') }} <br> {{ __('This Payment will be picked:') }}&ensp; <strong>{{ $payment->code }}</strong> <br> {{ __('Can`t undo Operation!') }}
            </div>

        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
        <button type="submit" id="confirm" class="btn btn-primary">{{__('Confirm Picked and Done')}}</button>
    </div>
</form>

