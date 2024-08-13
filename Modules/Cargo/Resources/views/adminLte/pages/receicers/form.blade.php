@csrf


<!--css & jq country_code -->
@include('cargo::adminLte.components.inputs.phone')

<div class="row mb-6">
    <!--begin::Input group --  Receiver name -->
    <!--begin::Input group-->
     <input type="hidden" name="user_id" value="{{auth()->user()->id}}" />
    <div class="col-lg-6 fv-row">
        <!--begin::Label-->
        <label class="col-form-label fw-bold fs-6 required">{{ __('cargo::view.receiver_name') }}</label>
        <!--end::Label-->
        <div class="input-group mb-4">
            <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" placeholder="{{ __('cargo::view.receiver_name') }}" value="{{ old('name', isset($model) ? $model->name : '') }}" />
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <!--end::Input group-->

    <!--begin::Input group --  Receiver Phone -->
    <!--begin::Input group-->
    <div class="col-lg-6 fv-row">
        <!--begin::Label-->
        <label class="col-form-label fw-bold fs-6 required">{{ __('cargo::view.receiver_phone') }}</label>
        <!--end::Label-->
        <div class="input-group mb-4">
            <input  type="tel" name="receiver_mobile" id="phone" dir="ltr" autocomplete="off" required  class=" phone_input number-only form-control mb-3 inptFielsd @error('receiver_mobile') is-invalid @enderror" placeholder="{{ __('cargo::view.table.owner_phone') }}" value="{{ old('receiver_mobile', isset($model) ? $model->country_code.$model->receiver_mobile : base_country_code()) }}" />
            <input type="hidden" class="country_code" name="country_code" value="{{ old('country_code', isset($model) ? $model->country_code : base_country_code()) }}" data-reflection="phone">
            <div>
                @error('receiver_mobile')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror   
            </div>
        </div>
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->

<!--begin::Input group-->
<div class="form-group">
    <!--begin::Label-->
    <label class="col-form-label fw-bold fs-6 required">{{ __('cargo::view.table.branch') }}</label>
    <!--end::Label-->
    <select
        class="form-control  @error('branch_id') is-invalid @enderror"
        name="branch_id"
        data-control="select2"
        data-placeholder="{{ __('cargo::view.table.choose_branch') }}"
        data-allow-clear="true"
    >
        <option></option>
        @foreach($branches as $branch)
            <option value="{{ $branch->id }}"
                {{ old('branch_id') == $branch->id ? 'selected' : '' }}
                @if($typeForm == 'edit')
                    {{ $model->branch_id == $branch->id ? 'selected' : '' }}
                @endif
            >{{ $branch->name }}</option>
        @endforeach
    </select>
    @error('branch_id')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
<!--end::Input group-->


<div class="form-group">
        <label class="col-form-label fw-bold fs-6 required">{{ __('cargo::view.receiver_address') }}</label>
        <input type="text" value="{{ old('reciver_address', isset($model) ? $model->reciver_address : '') }}" placeholder="{{ __('cargo::view.receiver_address') }}" name="reciver_address" class="form-control @error('reciver_address') is-invalid @enderror" />
        @error('reciver_address')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
